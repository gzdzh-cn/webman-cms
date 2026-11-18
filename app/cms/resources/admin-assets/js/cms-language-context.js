/* Report first-load readiness to the workspace. The parent retains mounted layouts. */
(function (window, document) {
    'use strict';
    // Only embedded registered pages report initial readiness; standalone editors stay visible.
    try { if (parent === window || !parent.CmsNavigation?.resolve(location.href)) return; } catch (_) { return; }
    var root = document.documentElement;
    // Hidden iframe clocks are throttled; schedule presentation on the workspace.
    var clock = window.parent;
    var active = true, loaded = false, pending = 0, revision = 0, scheduled = false, explicit = false;
    var nativeFetch = window.fetch, nativeSend = XMLHttpRequest.prototype.send;
    root.dataset.cmsPageState = 'pending';
    root.setAttribute('aria-busy', 'true');
    var resources = new Set(), completed = new WeakSet();
    function watchResource(node) {
        if (node.nodeType !== 1) return;
        if ((node.matches('script[src]') || node.matches('link[rel="stylesheet"]')) && !completed.has(node)) resources.add(node);
        node.querySelectorAll('script[src],link[rel="stylesheet"]').forEach(function (item) { if (!completed.has(item)) resources.add(item); });
    }
    function resourceDone(event) {
        var node = event.target;
        if (!(node instanceof Element) || !node.matches('script[src],link[rel="stylesheet"]')) return;
        completed.add(node); resources.delete(node); revision++; schedule();
    }
    document.addEventListener('load', resourceDone, true);
    document.addEventListener('error', resourceDone, true);
    var observer = new MutationObserver(function (records) {
        records.forEach(function (record) { record.addedNodes.forEach(watchResource); });
        revision++; schedule();
    });
    observer.observe(document, {childList: true, subtree: true});
    var deadline = clock.setTimeout(function () { finish('timeout'); }, 12000);
    function finish(reason) {
        if (!active) return;
        active = false;
        observer.disconnect();
        document.removeEventListener('load', resourceDone, true);
        document.removeEventListener('error', resourceDone, true);
        clock.clearTimeout(deadline);
        root.dataset.cmsPageState = reason === 'timeout' ? 'timeout' : 'ready';
        root.removeAttribute('aria-busy');
        window.dispatchEvent(new CustomEvent('cms-page-ready', {detail: {reason: reason}}));
        window.frameElement?.dispatchEvent(new CustomEvent('cms-frame-content-ready'));
    }
    function schedule() {
        resources.forEach(function (node) { if (!node.isConnected) resources.delete(node); });
        if (!active || !loaded || (!explicit && pending) || resources.size || scheduled) return;
        scheduled = true;
        var stamp = revision, checked = false;
        function check() {
            if (checked) return;
            checked = true;
            scheduled = false;
            if (!active || (!explicit && pending) || resources.size) return;
            if (stamp !== revision) { schedule(); return; }
            finish('settled');
        }
        // Use the visible workspace clock even while this document is preloading.
        clock.requestAnimationFrame(function () { clock.requestAnimationFrame(check); });
        clock.setTimeout(check, 32);
    }
    function begin() {
        if (!active) return function () {};
        pending++;
        var ended = false;
        return function () { if (ended) return; ended = true; pending--; revision++; schedule(); };
    }
    window.fetch = function () {
        var end = begin();
        try { return nativeFetch.apply(this, arguments).finally(end); }
        catch (error) { end(); throw error; }
    };
    XMLHttpRequest.prototype.send = function () {
        var end = begin();
        this.addEventListener('loadend', end, {once: true});
        try { return nativeSend.apply(this, arguments); }
        catch (error) { this.removeEventListener('loadend', end); end(); throw error; }
    };
    window.CmsPageReady = {hold: begin, complete: function () { explicit = true; revision++; schedule(); }};
    document.addEventListener('DOMContentLoaded', function () { loaded = true; revision++; schedule(); }, {once: true});
    if (document.readyState !== 'loading') { loaded = true; schedule(); }
})(window, document);

/* Page-level adapter: editor language comes from the workspace, never a second picker. */
(function (window, document) {
    'use strict';
    if (window.CmsEditContext) return;
    var context = window.CmsLanguageInitial || null, pending = null, nativeFetch = window.fetch.bind(window);
    var selectors = 'select#lang,select#language,select[name="lang"]';
    function parentState() { try { return window.CmsLanguage && window.CmsLanguage.get() || window.parent !== window && window.parent.CmsLanguage && window.parent.CmsLanguage.get(); } catch (_) { return null; } }
    async function ready() {
        if (parentState()) return context = parentState();
        if (context) return context;
        if (pending) return pending;
        pending = nativeFetch('/web-admin/language/state', {credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}}).then(function (response) { return response.json(); }).then(function (result) { if (result.code !== 0) throw Error(result.msg || '编辑语言读取失败'); context = result.data; fixControls(); return context; }).finally(function () { pending = null; });
        return pending;
    }
    function editableRequest(value) {
        try { var url = new URL(value instanceof Request ? value.url : value, window.location.href); return url.origin === window.location.origin && url.pathname.startsWith('/web-admin/') ? url : null; } catch (_) { return null; }
    }
    window.fetch = async function (input, options) {
        var url = editableRequest(input);
        if (!url || url.pathname === '/web-admin/language/state') return nativeFetch(input, options);
        var current = await ready(), isRequest = input instanceof Request;
        var settings = Object.assign({}, options || {}), headers = new Headers(isRequest ? input.headers : undefined);
        new Headers(settings.headers).forEach(function (value, key) { headers.set(key, value); });
        headers.set('X-CMS-Language-Revision', String(current.revision));
        headers.set('X-CMS-Edit-Language', current.edit || '');
        settings.headers = headers;
        var method = String(settings.method || (isRequest ? input.method : 'GET')).toUpperCase();
        if(method!=='GET'&&/\/(configure|save|insert|update|edit|newfile)\/?$/.test(url.pathname)&&!url.pathname.startsWith('/web-admin/update/')&&!settings.signal&&!(isRequest&&input.signal.aborted))settings.signal=AbortSignal.any([...(isRequest?[input.signal]:[]),AbortSignal.timeout(60000)]);
        if (method === 'GET' && url.searchParams.has('lang') && !url.pathname.startsWith('/web-admin/language/')) {
            url.searchParams.set('lang', current.edit || '');
            if (isRequest && url.href !== input.url) input = new Request(url.href, input);
        }
        // Preserve FormData as the original fetch body; rebuilding it through Request can stream it.
        return nativeFetch(isRequest ? input : url.href, settings);
    };
    var open = XMLHttpRequest.prototype.open, send = XMLHttpRequest.prototype.send, abort = XMLHttpRequest.prototype.abort;
    XMLHttpRequest.prototype.open = function (method, href) {
        this.cmsContextUrl = editableRequest(href); this.cmsContextMethod=String(method).toUpperCase(); this.cmsContextAsync = arguments.length < 3 || arguments[2] !== false; this.cmsContextCancelled = false; this.cmsContextSending = false;
        var args = Array.from(arguments), current = parentState() || context;
        if (this.cmsContextUrl && current && String(method).toUpperCase() === 'GET' && this.cmsContextUrl.searchParams.has('lang') && !this.cmsContextUrl.pathname.startsWith('/web-admin/language/')) { this.cmsContextUrl.searchParams.set('lang', current.edit || ''); args[1] = this.cmsContextUrl.href; }
        return open.apply(this, args);
    };
    XMLHttpRequest.prototype.send = function (body) {
        var xhr = this;
        if (!xhr.cmsContextUrl) return send.call(xhr, body);
        function apply(current) { xhr.setRequestHeader('X-CMS-Language-Revision', String(current.revision)); xhr.setRequestHeader('X-CMS-Edit-Language', current.edit || ''); }
        if (!xhr.cmsContextAsync) { var current = parentState() || context; if (current) apply(current); return send.call(xhr, body); }
        if (xhr.cmsContextSending) throw new DOMException('Request already sent', 'InvalidStateError');
        xhr.cmsContextSending = true;
        if(xhr.cmsContextMethod!=='GET'&&/\/(configure|save|insert|update|edit|newfile)\/?$/.test(xhr.cmsContextUrl.pathname)&&!xhr.cmsContextUrl.pathname.startsWith('/web-admin/update/')&&!xhr.timeout)xhr.timeout=60000;
        ready().then(function (current) { if (!xhr.cmsContextCancelled) { apply(current); send.call(xhr, body); } }).catch(function () { xhr.abort(); });
    };
    XMLHttpRequest.prototype.abort = function () { this.cmsContextCancelled = true; return abort.apply(this, arguments); };
    function fixControls() {
        var current = parentState() || context;
        if (!current || document.body && document.body.classList.contains('cms-language-management')) return;
        document.querySelectorAll(selectors).forEach(function (control) {
            if (control.closest('dialog,[data-cms-language-target]') || control.multiple) return;
            if (Array.from(control.options).some(function (option) { return option.value === current.edit; })) control.value = current.edit;
            control.classList.add('cms-fixed-language-control');
            control.tabIndex = -1; control.setAttribute('aria-hidden', 'true');
            var rendered = control.nextElementSibling;
            if (rendered && rendered.classList.contains('layui-form-select')) rendered.classList.add('cms-fixed-language-control');
            var label = control.closest('label');
            if (label && label.querySelectorAll('input,select,button,textarea').length === 1) label.classList.add('cms-fixed-language-control');
            if (control.id) document.querySelectorAll('label[for="' + control.id + '"]').forEach(function (item) { item.classList.add('cms-fixed-language-control'); });
        });
    }
    function install() {
        var style = document.createElement('style'); style.textContent = '.cms-fixed-language-control{display:none!important}'; document.head.append(style);
        function markDirty(event) {
            var form = event.target instanceof Element && event.target.closest('form');
            if (form && !form.matches('[data-cms-filter]') && !event.target.closest('[data-cms-filter]')) form.dataset.cmsDirty = '1';
        }
        document.addEventListener('input', markDirty, true);
        document.addEventListener('change', markDirty, true);
        var frames = new WeakSet(), frameDocuments = new WeakSet();
        function watchFrames() {
            document.querySelectorAll('form iframe').forEach(function (frame) {
                if (frames.has(frame)) return;
                frames.add(frame);
                function listen() {
                    try {
                        var inner = frame.contentDocument;
                        if (!inner || frameDocuments.has(inner)) return;
                        frameDocuments.add(inner);
                        function changed() { var form = frame.closest('form'); if (form && !form.matches('[data-cms-filter]')) form.dataset.cmsDirty = '1'; }
                        inner.addEventListener('input', changed, true);
                        inner.addEventListener('change', changed, true);
                    } catch (_) {}
                }
                frame.addEventListener('load', listen); listen();
            });
        }
        fixControls(); watchFrames(); new MutationObserver(function () { fixControls(); watchFrames(); }).observe(document.body, {childList: true, subtree: true});
        ready().then(function (current) {
            var url = new URL(window.location.href); url.searchParams.set('lang', current.edit || '');
            window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
            fixControls();
        }).catch(function (error) { var host = document.querySelector('[data-cms-module-navigation]'); if (host) host.textContent = error.message; });
    }
    var remotePending = false;
    async function remoteChanged() {
        if (window.CmsLanguage || parentState() || remotePending || !context) return;
        remotePending = true;
        try {
            var response = await nativeFetch('/web-admin/language/state', {credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}}), result = await response.json();
            if (result.code !== 0 || String(result.data.revision) === String(context.revision)) return;
            var dirty = document.querySelector('form[data-cms-dirty="1"],dialog[open],.layui-layer-page,.layui-layer-iframe');
            if (dirty && !await window.CmsUI.confirm('后台编辑语言已在其他窗口更改。是否放弃当前未保存内容并重新载入？', {title: '编辑语言已更改', confirmText: '放弃修改并载入', cancelText: '继续编辑'})) {
                var notice = document.querySelector('[data-cms-module-navigation]');
                if (notice) { var warning = document.createElement('p'); warning.setAttribute('role', 'alert'); warning.textContent = '当前语言上下文已过期，未保存内容保留。请复制内容后刷新，旧上下文不能提交。'; notice.append(warning); }
                return;
            }
            window.CmsUI?.clean(document);
            document.querySelectorAll('form[data-cms-dirty]').forEach(function (form) { delete form.dataset.cmsDirty; });
            var target = new URL(window.location.href);
            target.search = ''; target.searchParams.set('lang', result.data.edit || '');
            if (/\/(insert|update|edit|singlePage)\/?$/.test(target.pathname)) target.pathname = target.pathname.replace(/\/(insert|update|edit|singlePage)\/?$/, '/index');
            window.location.assign(target.pathname + target.search);
        } catch (_) {} finally { remotePending = false; }
    }
    window.addEventListener('storage', function (event) { if (event.key === 'cms-admin-language-context-v1') remoteChanged(); });
    try { var channel = new BroadcastChannel('cms-admin-language-context-v1'); channel.onmessage = remoteChanged; } catch (_) {}
    window.CmsEditContext = {ready: ready, get: function () { return parentState() || context; }};
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', install, {once: true}); else install();
})(window, document);
