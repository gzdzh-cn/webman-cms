#!/usr/bin/env python3
"""Verify or restore a format-2 asset package; default is read-only."""
import argparse
import gzip
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import shutil
import tarfile
import tempfile

PROJECT = Path(__file__).resolve().parents[3]
MANIFEST = PROJECT / 'app/cms/resources/source-package-assets.json.gz'
ROOTS = ('public/admin-assets/',
         'app/cms/resources/fonts/', 'app/cms/resources/defaults/', 'app/cms/resources/legacy-assets/',
         'app/view/cms/legacy-template/')
EXECUTABLE = {'.php', '.phtml', '.phar', '.php3', '.php4', '.php5', '.php7', '.php8', '.cgi', '.pl', '.sh'}


def hash_stream(stream):
    result = hashlib.sha256()
    for chunk in iter(lambda: stream.read(1024 * 1024), b''):
        result.update(chunk)
    return result.hexdigest()


def digest(path):
    with path.open('rb') as stream:
        return hash_stream(stream)


def safe_name(name):
    if not isinstance(name, str) or not name or '\\' in name or '\x00' in name:
        raise ValueError('Invalid asset path')
    path = PurePosixPath(name)
    if path.is_absolute() or any(part in ('', '.', '..') or part.startswith('.') for part in name.split('/')) or str(path) != name:
        raise ValueError('Invalid asset path: ' + name)
    if not any(name.startswith(root) for root in ROOTS) or path.suffix.lower() in EXECUTABLE:
        raise ValueError('Unexpected asset target: ' + name)


def no_links(path):
    path = path.absolute()
    current = Path(path.anchor)
    for part in path.parts[1:]:
        if part in ('.', '..'):
            raise ValueError('Non-canonical path')
        current /= part
        if current.is_symlink():
            raise ValueError('Symlink in path: ' + str(current))
    return path


def safe_target(destination, name):
    safe_name(name)
    return no_links(destination / name)


def sha(value):
    return isinstance(value, str) and len(value) == 64 and all(c in '0123456789abcdef' for c in value)


def load_manifest(path):
    no_links(path)
    with gzip.open(path, 'rt', encoding='utf-8') as stream:
        data = json.load(stream)
    if data.get('format') != 2:
        raise ValueError('Only format-2 resource packages are supported; legacy packages cannot restore the new layout')
    if not isinstance(data.get('files'), dict) or not data['files'] or not sha(data.get('archive', {}).get('sha256')):
        raise ValueError('Invalid format-2 resource manifest')
    for name, record in data['files'].items():
        safe_name(name)
        size = record.get('size', record.get('bytes'))
        mode = record.get('mode', 0o644)
        if type(size) is not int or size < 0 or not sha(record.get('sha256')) or type(mode) is not int or mode < 0 or mode > 0o777:
            raise ValueError('Invalid asset metadata: ' + name)
        if 'bytes' in record and 'size' in record and record['bytes'] != record['size']:
            raise ValueError('Inconsistent asset size: ' + name)
        record['size'], record['mode'] = size, mode
    return data


def restore(archive_path, destination, manifest_path=MANIFEST, apply=False):
    destination = no_links(destination)
    if not destination.is_dir():
        raise ValueError('Destination must be an existing directory')
    archive_path = no_links(archive_path)
    data = load_manifest(manifest_path)
    if not archive_path.is_file() or digest(archive_path) != data['archive']['sha256']:
        raise ValueError('Archive SHA256 mismatch')
    archive_size = data['archive'].get('size', data['archive'].get('bytes'))
    if archive_size is not None and archive_path.stat().st_size != archive_size:
        raise ValueError('Archive size mismatch')
    expected = data['files']
    with tarfile.open(archive_path, 'r:gz') as archive:
        members = archive.getmembers()
        names = [member.name for member in members]
        if len(set(names)) != len(names) or set(names) != set(expected):
            raise ValueError('Archive member list does not match manifest')
        pending = []
        for member in members:
            safe_name(member.name)
            record = expected[member.name]
            if member.type not in (tarfile.REGTYPE, tarfile.AREGTYPE) or member.size != record['size']:
                raise ValueError('Invalid member type or size: ' + member.name)
            with archive.extractfile(member) as stream:
                if hash_stream(stream) != record['sha256']:
                    raise ValueError('Asset SHA256 mismatch: ' + member.name)
            target = safe_target(destination, member.name)
            if target.exists():
                if not target.is_file() or target.stat().st_size != record['size'] or digest(target) != record['sha256']:
                    raise ValueError('Existing file differs; refusing overwrite: ' + member.name)
            else:
                pending.append(member)
        if apply:
            with tempfile.TemporaryDirectory(prefix='webman-assets-') as folder:
                staging = Path(folder)
                for member in pending:
                    staged = staging / member.name
                    staged.parent.mkdir(parents=True, exist_ok=True)
                    with archive.extractfile(member) as source, staged.open('wb') as output:
                        shutil.copyfileobj(source, output)
                    if digest(staged) != expected[member.name]['sha256']:
                        raise ValueError('Staged asset mismatch: ' + member.name)
                created = []
                try:
                    for member in pending:
                        target = safe_target(destination, member.name)
                        target.parent.mkdir(parents=True, exist_ok=True)
                        safe_target(destination, member.name)
                        temporary = None
                        try:
                            with tempfile.NamedTemporaryFile(dir=target.parent, prefix='.asset-', delete=False) as output:
                                temporary = Path(output.name)
                                with (staging / member.name).open('rb') as source:
                                    shutil.copyfileobj(source, output)
                                output.flush()
                                os.fsync(output.fileno())
                            os.chmod(temporary, expected[member.name]['mode'])
                            safe_target(destination, member.name)
                            # Atomic publication with no overwrite, including concurrent restorers.
                            os.link(temporary, target)
                            created.append((target, expected[member.name]['sha256']))
                        finally:
                            if temporary is not None:
                                temporary.unlink(missing_ok=True)
                except Exception:
                    for target, expected_sha in reversed(created):
                        if not target.is_symlink() and target.is_file() and digest(target) == expected_sha:
                            target.unlink()
                    raise
    return {'mode': 'restored' if apply else 'checked', 'format': 2,
            'verified_files': len(expected), 'pending_files': len(pending)}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--archive', type=Path, required=True)
    parser.add_argument('--destination', type=Path, default=PROJECT)
    parser.add_argument('--manifest', type=Path, default=MANIFEST)
    parser.add_argument('--apply', action='store_true', help='write missing assets after complete validation')
    args = parser.parse_args()
    print(json.dumps(restore(args.archive, args.destination, args.manifest, args.apply), ensure_ascii=False))


if __name__ == '__main__':
    try:
        main()
    except (ValueError, OSError, tarfile.TarError) as error:
        raise SystemExit(str(error))
