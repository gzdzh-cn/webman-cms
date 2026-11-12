<!doctype html>
<html lang="<?= $view['lang'] ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $view['title'] ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
            background: #f5f6f8; color: #333; font: 16px/1.7 system-ui, sans-serif; }
        .system-message { width: min(100%, 500px); padding: 38px 28px; text-align: center;
            border-radius: 14px; background: #fff; box-shadow: 0 8px 36px #17212b12; }
        .status-ico { display: inline-grid; place-items: center; width: 64px; height: 64px;
            border-radius: 50%; color: #238765; background: #eaf7f1; font-size: 34px; }
        .status-ico-error { color: #c34b4b; background: #fff0f0; }
        h1 { margin: 18px 0 10px; font-size: 20px; overflow-wrap: anywhere; }
        .jump { color: #777; font-size: 14px; }
        a { color: #2677b7; text-underline-offset: 3px; }
    </style>
</head>
<body>
    <main class="system-message" role="status">
        <span class="status-ico <?= $view['success'] ? 'status-ico-ok' : 'status-ico-error' ?>" aria-hidden="true"><?= $view['success'] ? '✓' : '!' ?></span>
        <h1 class="<?= $view['success'] ? 'success' : 'error' ?>"><?= $view['message'] ?></h1>
        <p class="jump"><?= $view['jump'] ?> <b id="wait"><?= $view['wait'] ?></b></p>
    </main>
    <script>
        (function () {
            var config = <?= $view['script'] ?>;
            var wait = document.getElementById('wait');
            function navigate() {
                if (config.back) window.history.back();
                else window.location.assign(config.url);
            }
            document.getElementById('href').addEventListener('click', function (event) {
                event.preventDefault(); navigate();
            });
            var timer = window.setInterval(function () {
                var seconds = Math.max(0, Number(wait.textContent) - 1);
                wait.textContent = seconds;
                if (seconds === 0) { window.clearInterval(timer); navigate(); }
            }, 1000);
            var data = /^gbook_submit\|(\d+)\|(\d+)\|([01])$/.exec(config.data);
            if (data) {
                function notify(url, values) {
                    fetch(url, {method: 'POST', credentials: 'same-origin',
                        headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams(values).toString()}).catch(function () {});
                }
                notify(config.email, {type: 'gbook_submit', tid: data[1], aid: data[2], form_type: data[3], _ajax: 1});
                notify(config.sms, {scene: 11, aid: data[2], _ajax: 1});
            }
        })();
    </script>
</body>
</html>
