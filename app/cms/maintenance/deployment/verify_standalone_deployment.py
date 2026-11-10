#!/usr/bin/env python3
"""Owned, independent deployment rehearsal. check is read-only; run is explicit."""
from __future__ import annotations

import argparse
import concurrent.futures
import fcntl
import hashlib
from html.parser import HTMLParser
import json
import os
from pathlib import Path
import re
import secrets
import shutil
import signal
import socket
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[4]
CONTROL = None  # Explicit external --work-dir; runtime is entirely disposable.
PHP_DEFAULT = "/Users/lizheng/Library/FlyEnv/env/php/bin/php"
COPY_ROOTS = ("app", "vendor", "public", "template",
              "config", "support")
OPTIONAL_COPY_ROOTS = ("plugin",)
COPY_FILES = ("composer.json", "composer.lock", "start.php", "webman", "LICENSE")
EXCLUDED = {".git", "runtime", "__pycache__", ".pytest_cache", "node_modules", ".DS_Store"}
PRIVATE_DATABASE_STATE = {"config/cms-database-traffic.lock",
                          "config/.cms-install-env.lock",
                          "config/cms-database-maintenance.json"}
ORIGIN = "http://127.0.0.1:8793"
NONCE = re.compile(r"[a-f0-9]{12}\Z")
HELPER = "app/cms/maintenance/deployment/helper.php"


class Refusal(RuntimeError):
    pass


def require(condition, message):
    if not condition:
        raise Refusal(message)


def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def private_json(path, value, exclusive=False):
    mode = "x" if exclusive else "w"
    descriptor = os.open(path, os.O_WRONLY | os.O_CREAT |
                         (os.O_EXCL if exclusive else os.O_TRUNC), 0o600)
    with os.fdopen(descriptor, mode) as stream:
        json.dump(value, stream, ensure_ascii=False, indent=2)
        stream.write("\n")
    os.chmod(path, 0o600)


def installation_directory(php):
    result = command([php, str(ROOT / "app/cms/maintenance/deployment/installer_inventory.php")],
                     ROOT, json_output=True)
    directory = result.get("directory")
    require(directory is None or directory == "install" or
            isinstance(directory, str) and re.fullmatch(r"install_[a-z]{12}", directory),
            "Invalid installation-package inventory")
    return directory


def inventory(php=PHP_DEFAULT):
    entries = []
    install_directory = installation_directory(php)
    allowed_roots = COPY_ROOTS + OPTIONAL_COPY_ROOTS + COPY_FILES
    if install_directory is not None:
        allowed_roots += (install_directory,)
    # All upload URL aliases share the sole physical public/uploads directory.
    # Refuse legacy source trees rather than silently omitting real old assets.
    for name in ("upload", "uploads", "public/upload"):
        legacy = ROOT / name
        require(not legacy.exists() and not legacy.is_symlink(),
                "Legacy upload path " + name + " must be migrated into public/uploads"
                + " before deployment; legacy upload paths are not copied")
    upload_root = ROOT / "public/uploads"
    require(upload_root.is_dir() and not upload_root.is_symlink(),
            "public/uploads must be a real directory before deployment")

    def visit(path):
        name = path.name
        if name in EXCLUDED or name.startswith(".env"):
            return
        rel = path.relative_to(ROOT).as_posix()
        if rel in PRIVATE_DATABASE_STATE:
            return
        if path.is_symlink():
            target = path.resolve(strict=True)
            require(target.is_relative_to(ROOT), "A copied symlink escapes the source tree")
            target_rel = target.relative_to(ROOT)
            require(target_rel.parts[0] in allowed_roots,
                    "A copied symlink points to an excluded tree")
            require(not any(p in EXCLUDED or p.startswith(".env") for p in target_rel.parts),
                    "A copied symlink points to private or runtime content")
            require(target_rel.as_posix() not in PRIVATE_DATABASE_STATE,
                    "A copied symlink points to private database state")
            entries.append({"path": rel, "symlink": target_rel.as_posix()})
        elif path.is_dir():
            for child in sorted(path.iterdir()):
                visit(child)
        else:
            require(path.is_file(), "Unsupported special file in deployment input")
            entries.append({"path": rel, "sha256": digest(path), "bytes": path.stat().st_size})

    for name in COPY_ROOTS + COPY_FILES:
        require((ROOT / name).exists(), "A required deployment input is absent: " + name)
        visit(ROOT / name)
    for name in OPTIONAL_COPY_ROOTS:
        path = ROOT / name
        if path.exists() or path.is_symlink():
            if name == "plugin" and path.is_dir():
                require(not any(child.name.lower() == "admin" for child in path.iterdir()),
                        "The application-owned backend must not be deployed from plugin/admin")
            visit(path)
    # The PHP service validates only the original directory or the exact journal target.
    # Preserve every recorded byte and empty directory; normal source exclusions do not apply.
    def visit_installation(path):
        require(not path.is_symlink(), "Installation package contains a symlink")
        relative = path.relative_to(ROOT).as_posix()
        if path.is_dir():
            entries.append({"path": relative, "directory": True})
            for child in sorted(path.iterdir()):
                visit_installation(child)
        else:
            require(path.is_file(), "Installation package contains a special file")
            entries.append({"path": relative, "sha256": digest(path), "bytes": path.stat().st_size})

    if install_directory is not None:
        visit_installation(ROOT / install_directory)
        require(installation_directory(php) == install_directory,
                "Installation package changed during inventory")
    entries.sort(key=lambda r: r["path"])
    raw = json.dumps(entries, sort_keys=True, separators=(",", ":")).encode()
    return entries, hashlib.sha256(raw).hexdigest()


def clean_environment():
    env = dict(os.environ)
    prefixes = ("DB_", "CMS_", "ADMIN_DB_", "WEBMAN_", "APP_", "TPL_", "LOG_", "SQL_LOG_")
    for key in list(env):
        if key.startswith(prefixes):
            env.pop(key)
    env.pop("MYSQL_PWD", None)
    return env


def command(args, cwd, timeout=120, json_output=False, environment=None):
    result = subprocess.run(args, cwd=cwd, env=clean_environment() if environment is None else environment, capture_output=True,
                            timeout=timeout, check=False)
    require(result.returncode == 0, "Child step failed; exit status " + str(result.returncode))
    if json_output:
        try:
            return json.loads(result.stdout)
        except (ValueError, UnicodeError):
            raise Refusal("Child step did not return the expected safe JSON") from None
    return {"exit_status": result.returncode}


def port_is_free():
    with socket.socket() as probe:
        # Match the server's address-reuse semantics: TIME_WAIT is reusable,
        # while an active listener on this endpoint still refuses this bind.
        probe.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        try:
            probe.bind(("127.0.0.1", 8793))
        except OSError:
            return False
    return True


class LocalRedirects(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, msg, headers, newurl):
        require(urllib.parse.urlsplit(newurl).netloc == "127.0.0.1:8793",
                "The independent deployment redirected outside its listener")
        return super().redirect_request(request, fp, code, msg, headers, newurl)


OPENER = urllib.request.build_opener(urllib.request.ProxyHandler({}), LocalRedirects())


def get(path):
    require(path.startswith("/") and not path.startswith("//"), "Invalid local HTTP path")
    request = urllib.request.Request(ORIGIN + path, headers={
        "User-Agent": "StandaloneDeploymentVerifier/1.0", "Connection": "close"})
    try:
        with OPENER.open(request, timeout=15) as response:
            body = response.read()
            return response.status, body, response.url
    except urllib.error.HTTPError as error:
        return error.code, error.read(), error.url


class Assets(HTMLParser):
    def __init__(self):
        super().__init__()
        self.urls = set()

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        value = a.get("src") if tag in {"script", "img", "source"} else (
            a.get("href") if tag == "link" and a.get("rel") != "canonical" else None)
        if value:
            self.urls.add(value)


def local_asset_paths(pages):
    found = set()
    for url, body in pages:
        parser = Assets()
        parser.feed(body.decode("utf-8", errors="replace"))
        for value in parser.urls:
            absolute = urllib.parse.urlsplit(urllib.parse.urljoin(url, value))
            if absolute.netloc != "127.0.0.1:8793":
                continue
            if absolute.path.startswith(("/template/", "/uploads/", "/upload/", "/public/")):
                found.add(urllib.parse.urlunsplit(("", "", absolute.path, absolute.query, "")))
    return sorted(found)


class Rehearsal:
    def __init__(self, args):
        self.args = args
        self.nonce = args.nonce
        self.directory = CONTROL / self.nonce
        self.app = self.directory / "app"
        self.journal = self.directory / "ownership.json"
        self.process = None
        self.server_log = None
        self.registered_this_run = False
        self.report = {"kind": "controlled_standalone_deployment", "nonce": self.nonce,
                       "pass": False, "steps": [], "limits": [
                           "Finite original enterprise theme HTTP checks; no real mobile touch or admin login UI.",
                           "Backup/restore supports only this owned InnoDB baseline without views, routines, events, triggers or foreign keys.",
                           "Source runtime, credentials and cookies are never copied into the report."] }

    def step(self, name, details):
        self.report["steps"].append({"name": name, "pass": True, "details": details})

    def helper(self, action, role="primary"):
        # Only configure resolves the source using the caller's process values.
        # Every later helper, CLI and server uses a scrubbed environment.
        environment = dict(os.environ) if action == "configure" else None
        return command([self.args.php, str(self.app / HELPER), action,
                        str(self.app), self.nonce, role], self.app, json_output=True,
                       environment=environment)

    def owned_state(self):
        require(self.journal.is_file() and not self.journal.is_symlink(),
                "No registered deployment ownership journal")
        state = json.loads(self.journal.read_text())
        require(state.get("nonce") == self.nonce and state.get("clone_root") == str(self.app)
                and state.get("source_root") == str(ROOT), "Ownership journal does not match")
        require(self.directory.resolve() == self.directory and self.app.parent == self.directory,
                "Unsafe deployment directory")
        return state

    def prepare(self, entries, source_sha):
        require(not self.directory.exists() and not self.directory.is_symlink(),
                "Refusing to reuse an existing nonce directory")
        self.directory.mkdir(mode=0o700)
        self.app.mkdir(mode=0o700)
        state = {"version": 1, "nonce": self.nonce, "clone_root": str(self.app),
                 "source_root": str(ROOT), "control_root": str(CONTROL), "source_tree_sha256": source_sha,
                 "ownership_key": secrets.token_hex(24), "created_databases": [],
                 "database_names": {"primary": "wmcms_verify_" + self.nonce,
                                    "restore": "wmcms_verify_" + self.nonce + "_restore"},
                 "server_pid": None}
        private_json(self.journal, state, exclusive=True)
        self.registered_this_run = True
        for row in entries:
            target = self.app / row["path"]
            target.parent.mkdir(parents=True, exist_ok=True)
            if row.get("directory"):
                target.mkdir(exist_ok=True)
            elif "symlink" in row:
                target.symlink_to(os.path.relpath(self.app / row["symlink"], target.parent))
            else:
                shutil.copy2(ROOT / row["path"], target)
                require(digest(target) == row["sha256"], "Source changed while it was copied")
        require(inventory(self.args.php)[1] == source_sha, "Source changed during deployment copy")
        for name in ("runtime", "runtime/compat", "runtime/logs", ".verify"):
            (self.app / name).mkdir(mode=0o700, parents=True, exist_ok=True)
        self.step("independent_copy", {"source_sha256": source_sha, "entries": len(entries),
                                     "old_runtime_copied": False, "sibling_source_required": False})
        self.step("private_environment", self.helper("configure"))

    def start(self):
        require(port_is_free(), "Port 8793 is occupied; no process will be stopped")
        self.helper("inspect-env")
        log_path = self.app / ".verify/server-output.log"
        descriptor = os.open(log_path, os.O_WRONLY | os.O_CREAT | os.O_APPEND, 0o600)
        self.server_log = os.fdopen(descriptor, "ab")
        self.process = subprocess.Popen([self.args.php, str(self.app / "start.php"), "start"],
                                        cwd=self.app, env=clean_environment(),
                                        stdout=self.server_log, stderr=subprocess.STDOUT,
                                        start_new_session=True)
        state = self.owned_state()
        state["server_pid"] = self.process.pid
        private_json(self.journal, state)
        deadline = time.monotonic() + 25
        while time.monotonic() < deadline:
            require(self.process.poll() is None, "Independent server exited during startup")
            pid_file = self.app / "runtime/webman.pid"
            if pid_file.is_file() and pid_file.read_text().strip() == str(self.process.pid):
                try:
                    if get("/app/admin")[0] == 200:
                        return
                except (OSError, urllib.error.URLError):
                    pass
            time.sleep(0.15)
        raise Refusal("Independent server did not become ready")

    def stop(self):
        state = self.owned_state()
        pid = state.get("server_pid")
        if not pid:
            return
        if self.process is None:
            # Recovery refuses a recycled or unrelated PID; never signal a port owner.
            try:
                observed = subprocess.run(["ps", "-p", str(pid), "-o", "command="],
                                          capture_output=True, text=True, check=False).stdout
                os.kill(pid, 0)
            except ProcessLookupError:
                observed = ""
            if observed:
                require(str(self.app / "start.php") in observed,
                        "Recorded PID no longer identifies this copy; stop refused")
        if self.process is not None and self.process.poll() is None or (
                self.process is None and observed):
            require(os.getpgid(pid) == pid, "Recorded server does not own its process group")
            os.killpg(pid, signal.SIGINT)
            deadline = time.monotonic() + 8
            while time.monotonic() < deadline:
                if self.process is not None:
                    if self.process.poll() is not None:
                        break
                else:
                    try:
                        os.kill(pid, 0)
                    except ProcessLookupError:
                        break
                time.sleep(0.1)
            else:
                os.killpg(pid, signal.SIGKILL)
            if self.process is not None:
                self.process.wait(timeout=5)
        self.process = None
        if self.server_log is not None:
            self.server_log.close()
            self.server_log = None
        # The master may exit just before worker sockets finish closing. Wait
        # without inspecting or signalling any later listener that may appear.
        deadline = time.monotonic() + 10
        while not port_is_free() and time.monotonic() < deadline:
            time.sleep(0.1)
        require(port_is_free(), "Port remains occupied after the owned stop release window")
        state["server_pid"] = None
        private_json(self.journal, state)

    def website(self, fixture):
        pages = []
        for page in fixture["pages"]:
            status, body, final_url = get(page["path"])
            require(status == 200 and page["contains"].encode() in body,
                    "Original template HTTP content assertion failed: " + page["name"])
            require(not re.search(rb"\{/?eyou:", body), "Uncompiled original template tag")
            pages.append((final_url, body))
        status, body, _ = get("/app/admin")
        require(status == 200 and b"<html" in body.lower(), "Backend entry is unavailable")
        assets = []
        for path in local_asset_paths(pages):
            status, body, _ = get(path)
            require(status == 200 and len(body) > 0, "Local theme resource is unreadable")
            assets.append({"path": path, "status": status, "bytes": len(body),
                           "sha256": hashlib.sha256(body).hexdigest()})
        require(assets, "No original theme assets were observed")
        for path, expected_sha in fixture["owned_assets"].items():
            status, body, _ = get(path)
            require(status == 200 and hashlib.sha256(body).hexdigest() == expected_sha,
                    "Original upload/public resource path failed")
        for path in ("/.env", "/runtime/local-admin.json", "/.verify/backup.json"):
            require(get(path)[0] in (403, 404), "A private deployment path is exposed")
        with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
            bodies = list(pool.map(lambda _: get(fixture["worker_probe"]), range(48)))
        pids = set()
        for status, body, _ in bodies:
            require(status == 200, "Worker probe failed")
            match = re.fullmatch(rb"STANDALONE_" + self.nonce.encode() + rb":([0-9]+)\s*", body)
            require(match is not None, "Listener did not serve the owned worker probe")
            pids.add(int(match.group(1)))
        require(len(pids) == 2, "Expected two observed HTTP worker PIDs")
        return {"original_template_pages": len(pages), "local_asset_GETs": assets,
                "owned_asset_path_checks": len(fixture["owned_assets"]),
                "worker_pids": sorted(pids), "admin_entry": 200}

    def cleanup(self):
        if not self.journal.exists():
            return {"nothing_registered": True}
        self.stop()
        state = self.owned_state()
        if self.app.exists():
            for role in ("restore", "primary"):
                name = state["database_names"][role]
                if name in state.get("created_databases", []):
                    self.helper("drop", role)
                    state = self.owned_state()
            require(not state["created_databases"], "Owned databases remain; files preserved")
            require(self.app.is_dir() and not self.app.is_symlink(), "Unsafe copy cleanup")
            shutil.rmtree(self.app)
        state["cleanup_complete"] = True
        state["server_pid"] = None
        private_json(self.journal, state)
        return {"owned_databases_removed": True, "owned_copy_removed": not self.app.exists(),
                "private_env_and_backup_removed": not self.app.exists()}

    def run(self, entries, source_sha):
        try:
            self.prepare(entries, source_sha)
            self.step("claim_new_primary_database", self.helper("claim"))
            init = [self.args.php, str(self.app / "webman"), "cms:database-init"]
            first_dry = command(init + ["--dry-run"], self.app, json_output=True)
            first = command(init + ["--apply"], self.app, json_output=True)
            require(first["created_count"] == first["baseline_tables"] == 164,
                    "Fresh baseline did not create all 164 source tables")
            self.step("fresh_initialization", {"dry_run": first_dry, "execution": first})
            admin_input = self.directory / "admin-input.json"
            private_json(admin_input, {"username": "admin", "password": secrets.token_urlsafe(32)}, exclusive=True)
            try:
                self.step("bootstrap_admin", command(
                    [self.args.php, str(self.app / "webman"), "cms:admin-bootstrap", "--input=" + str(admin_input)], self.app))
            finally:
                admin_input.unlink(missing_ok=True)
            self.step("bootstrap_menu", command(
                [self.args.php, str(self.app / "webman"), "cms:sync-menu", "--apply", "--missing-only"], self.app))
            fixture = self.helper("fixture")
            self.step("service_created_fixture", fixture)
            before = self.helper("snapshot")
            for repeat in range(2):
                dry = command(init + ["--dry-run"], self.app, json_output=True)
                actual = command(init + ["--apply"], self.app, json_output=True)
                require(dry["created_count"] == actual["created_count"] == 0
                        and dry["preserved_count"] == actual["preserved_count"] == 164
                        and dry["migrations"] == actual["migrations"] == [],
                        "Repeated initialization was not a no-op")
                self.step("repeat_initialization_" + str(repeat + 1), {"dry_run": dry, "execution": actual})
            require(self.helper("snapshot") == before, "Repeated initialization changed schema or data")
            self.step("repeat_preserves_all_tables", {"table_count": len(before["tables"])})
            self.start()
            self.step("first_start_original_theme", self.website(fixture))
            self.stop()
            persisted = self.helper("snapshot")
            self.start()
            require(self.helper("snapshot") == persisted, "Restart changed persisted schema or data")
            self.step("restart_persistence", self.helper("verify-fixture"))
            self.step("restart_original_theme", self.website(fixture))
            self.stop()
            self.step("owned_PDO_backup", self.helper("backup"))
            self.step("claim_new_restore_database", self.helper("claim", "restore"))
            self.step("owned_PDO_restore", self.helper("restore", "restore"))
            require(self.helper("snapshot") == self.helper("snapshot", "restore"),
                    "Restored schema or full table data hash differs")
            self.step("restored_full_schema_data_equal", {"pass": True})
            self.report["pass"] = True
        except Exception as error:
            # Do not persist child stdout, exception values, environment or cookies.
            self.report["error"] = str(error) if isinstance(error, Refusal) else type(error).__name__
        finally:
            # A refusal to reuse an old nonce must never clean up that earlier run.
            if self.registered_this_run:
                try:
                    self.report["cleanup"] = self.cleanup()
                except Exception as error:
                    self.report["pass"] = False
                    self.report["cleanup_error"] = str(error) if isinstance(error, Refusal) else type(error).__name__
                    self.report["limits"].append("Cleanup refused; owned private directory retained for recovery.")
                if self.directory.is_dir():
                    private_json(self.directory / "report.json", self.report, exclusive=True)
        return self.report


def main():
    global CONTROL
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("action", choices=("check", "run", "cleanup"))
    parser.add_argument("--php", default=PHP_DEFAULT)
    parser.add_argument("--work-dir", type=Path, help="Private rehearsal directory outside the application")
    parser.add_argument("--nonce", help="Fresh twelve-digit lowercase hexadecimal nonce")
    parser.add_argument("--expected-source-sha256")
    parser.add_argument("--database-lock-confirmed", action="store_true",
                        help="Operator has reserved the shared database rehearsal window")
    args = parser.parse_args()
    if args.action == "check":
        entries, sha = inventory(args.php)
        runtime = command([args.php, "-r", "echo json_encode(['php'=>PHP_VERSION,'pdo_mysql'=>extension_loaded('pdo_mysql'),'pcntl'=>extension_loaded('pcntl'),'posix'=>extension_loaded('posix')]);"],
                          ROOT, json_output=True)
        command([args.php, "-l", str(ROOT / HELPER)], ROOT)
        require(all(runtime[k] for k in ("pdo_mysql", "pcntl", "posix")), "Required PHP extensions missing")
        require(tuple(map(int, runtime["php"].split(".")[:2])) >= (8, 2), "PHP >=8.2 required")
        print(json.dumps({"mode": "read_only_preflight", "source_sha256": sha,
                          "entries": len(entries), "runtime": runtime, "port_8793_free": port_is_free(),
                          "database_accessed": False, "server_started": False}))
        return 0
    require(args.work_dir is not None, "An explicit external --work-dir is required")
    candidate = args.work_dir.expanduser().absolute()
    require(not candidate.is_symlink(), "Work directory cannot be a symlink")
    CONTROL = candidate.resolve()
    require(not CONTROL.is_relative_to(ROOT) and not ROOT.is_relative_to(CONTROL),
            "Work directory must be separate from the application tree")
    require(args.database_lock_confirmed, "Explicit database rehearsal window confirmation required")
    args.nonce = args.nonce or secrets.token_hex(6)
    require(NONCE.fullmatch(args.nonce) is not None, "Invalid nonce")
    require(not CONTROL.is_symlink(), "Unsafe deployment control directory")
    CONTROL.mkdir(mode=0o700, parents=True, exist_ok=True)
    require(CONTROL.resolve() == CONTROL, "Deployment control path contains a symlink")
    with (CONTROL / ".lock").open("a+") as lock:
        os.chmod(CONTROL / ".lock", 0o600)
        try:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            raise Refusal("Another standalone rehearsal owns this workspace") from None
        rehearsal = Rehearsal(args)
        if args.action == "cleanup":
            print(json.dumps(rehearsal.cleanup()))
            return 0
        require(not rehearsal.directory.exists() and not rehearsal.directory.is_symlink(),
                "Refusing to reuse an existing nonce directory")
        entries, sha = inventory(args.php)
        require(args.expected_source_sha256 == sha, "A matching frozen source SHA256 is required")
        require(port_is_free(), "Port 8793 is occupied")
        report = rehearsal.run(entries, sha)
        print(json.dumps({"pass": report["pass"], "report": str(rehearsal.directory / "report.json"),
                          "cleanup_complete": report.get("cleanup", {}).get("owned_copy_removed", False)}))
        return 0 if report["pass"] else 1


if __name__ == "__main__":
    try:
        sys.exit(main())
    except (Refusal, OSError, ValueError, subprocess.TimeoutExpired) as error:
        print(json.dumps({"pass": False, "error": str(error) if isinstance(error, Refusal)
                          else type(error).__name__}), file=sys.stderr)
        sys.exit(1)
