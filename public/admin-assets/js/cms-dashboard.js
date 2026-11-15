/* Workbench server snapshot: on demand only; no polling or HTML injection. */
(() => {
    'use strict';
    const dialog = document.getElementById('server-details');
    const more = document.getElementById('server-more');
    if (!document.getElementById('server-summary')) return;
    const buttons = [...document.querySelectorAll('[data-server-refresh]')];
    const statuses = [...document.querySelectorAll('[data-server-status]')];
    const tabs = [...(dialog?.querySelectorAll('[role="tab"]') || [])];
    let snapshot = null;
    let pending = null;
    let restoreFocus = null;
    const node = (tag, text, className) => {
        const element = document.createElement(tag);
        if (text !== undefined) element.textContent = text;
        if (className) element.className = className;
        return element;
    };
    function rows(target, values) {
        const fragment = document.createDocumentFragment();
        for (const row of values || []) {
            const item = node('div', undefined, 'cms-dashboard-detail');
            item.dataset.infoKey = row.key;
            item.append(node('dt', row.label));
            const value = node('dd', row.value);
            if (row.note) value.append(node('small', row.note));
            item.append(value);
            fragment.append(item);
        }
        target.replaceChildren(fragment);
    }
    const bytes = value => {
        if (value === null || value === undefined) return '不可用';
        let number = value;
        for (const unit of ['B', 'KiB', 'MiB', 'GiB', 'TiB']) {
            if (number < 1024 || unit === 'TiB') return `${Number(number.toFixed(2))} ${unit}`;
            number /= 1024;
        }
    };
    const duration = value => value === null ? '不可用' : `${Math.floor(value / 86400)} 天 ${Math.floor(value % 86400 / 3600)} 时 ${Math.floor(value % 3600 / 60)} 分`;
    function status(message, failed) {
        const time = snapshot ? new Date(snapshot.sampled_at).toLocaleString() : '暂无';
        for (const item of statuses) {
            item.textContent = `${message}采样时间：${time}`;
            item.classList.toggle('cms-server-error', !!failed);
        }
    }
    function render(data) {
        if (!data || !Array.isArray(data.summary) || (dialog && (!data.groups || !Array.isArray(data.site?.processes)))) throw new Error('invalid snapshot');
        snapshot = data;
        rows(document.getElementById('server-summary'), data.summary);
        if (!dialog) { status('', false); return; }
        for (const key of ['memory', 'resources', 'environment']) rows(document.getElementById(`server-group-${key}`), data.groups[key]);
        const body = document.getElementById('server-processes');
        body.replaceChildren();
        for (const process of data.site.processes) {
            const row = node('tr');
            for (const text of [`${process.pid} / ${process.category}`, bytes(process.pss), bytes(process.rss), bytes(process.private), duration(process.uptime)]) row.append(node('td', text));
            body.append(row);
        }
        document.getElementById('server-process-note').textContent = data.site.membership_complete
            ? '已核验本站主进程及直属工作进程。PSS、RSS 与 PHP 分配内存口径不同；RSS 可能重复计算共享页。'
            : '采样不完整：进程退出、身份变化或权限不足。部分合计不能作为完整整站内存；可重试。';
        status('', false);
    }
    function refresh() {
        if (pending) return pending;
        buttons.forEach(button => { button.disabled = true; });
        document.getElementById('server-summary').setAttribute('aria-busy', 'true');
        status('正在采样… ', false);
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 10000);
        pending = fetch('/web-admin/index/serverInfo' + (dialog ? '?details=1' : ''), {credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, signal: controller.signal})
            .then(async response => {
                if (!response.ok) throw new Error('request failed');
                const result = await response.json();
                if (result.code !== 0) throw new Error('sampling failed');
                render(result.data);
            })
            .catch(() => status('刷新失败，保留上次结果。请点击刷新重试。', true))
            .finally(() => {
                clearTimeout(timeout);
                pending = null;
                buttons.forEach(button => { button.disabled = false; });
                document.getElementById('server-summary').removeAttribute('aria-busy');
            });
        return pending;
    }
    buttons.forEach(button => button.addEventListener('click', refresh));
    more?.addEventListener('click', () => {
        if (dialog.open) return;
        restoreFocus = document.activeElement;
        dialog.showModal();
        dialog.querySelector('[aria-selected="true"]').focus();
        refresh();
    });
    document.getElementById('server-close')?.addEventListener('click', () => dialog.close());
    dialog?.addEventListener('close', () => { if (restoreFocus?.isConnected) restoreFocus.focus(); });
    function activate(tab) {
        for (const item of tabs) {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
            document.getElementById(item.getAttribute('aria-controls')).hidden = !selected;
        }
        tab.focus();
    }
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab));
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next !== undefined) { event.preventDefault(); activate(tabs[next]); }
        });
    });
    try { render(JSON.parse(document.getElementById('server-initial-snapshot').textContent)); }
    catch (_) { status('暂无采样，请点击刷新重试。', true); }
})();
