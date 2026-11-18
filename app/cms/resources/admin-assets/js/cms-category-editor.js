/*! Category form initialization; shared by create and edit pages. */
(function (window, document) {
    'use strict';
    function request(path, params) {
        const url = new URL(path, location.href);
        Object.entries(params || {}).forEach(([key, value]) => url.searchParams.set(key, value));
        const controller = new AbortController(), timer = setTimeout(() => controller.abort(), 10000);
        return fetch(url.href, {signal: controller.signal}).then(async response => {
            const result = await response.json();
            if (!response.ok || result.code !== 0 || !Array.isArray(result.data)) throw Error(result.msg || '栏目数据读取失败');
            return result.data;
        }).catch(error => { throw Error(error.name === 'AbortError' ? '栏目数据读取超时，请重试' : error.message); }).finally(() => clearTimeout(timer));
    }
    function options(select, rows, placeholder, value, preserve) {
        select.replaceChildren(new Option(placeholder, '0'));
        rows.forEach(row => select.add(new Option(String(row.name), String(row.value))));
        select.value = rows.some(row => String(row.value) === String(value)) ? String(value) : preserve && value ? '0' : String(rows[0]?.value || '0');
    }
    window.CmsCategoryEditor = {
        create(settings) {
            const form = settings.element, ui = settings.form;
            const model = form.querySelector('#current-channel-select');
            const parent = form.querySelector('select[name=parent_id]');
            const list = form.querySelector('select[name=templist]'), view = form.querySelector('select[name=tempview]');
            const listLabel = form.querySelector('#templist-label'), viewItem = form.querySelector('#tempview-item');
            const controls = Array.from(form.querySelectorAll('[lay-submit],button[type=submit]'));
            const disabled = controls.map(button => button.disabled && !button.hasAttribute('data-cms-initializing'));
            let state = 'loading', epoch = 0, initial = true, errorBox, hydrated = false;
            const release = window.CmsPageReady?.hold() || (() => {});
            function status(value) {
                state = value; form.dataset.cmsCategoryState = value;
                controls.forEach((button, index) => { button.disabled = value !== 'ready' || disabled[index]; });
            }
            function clearError() { errorBox?.remove(); errorBox = null; }
            function fail(error, retry) {
                status('error'); clearError();
                errorBox = document.createElement('div'); errorBox.className = 'cms-ui-error'; errorBox.setAttribute('role', 'alert');
                const text = document.createElement('span'); text.textContent = error.message || '栏目初始化失败';
                const button = document.createElement('button'); button.type = 'button'; button.className = 'pear-btn pear-btn-sm'; button.textContent = '重新加载'; button.addEventListener('click', retry);
                errorBox.append(text, document.createTextNode(' '), button); form.prepend(errorBox);
            }
            function language() { return window.CmsEditContext?.get()?.edit || new URL(location.href).searchParams.get('lang') || 'cn'; }
            async function changeModel(value, selected) {
                const version = ++epoch, id = Number(value) || 0, single = id === 6 || id === 8;
                status('loading'); clearError();
                const previous = selected || {templist: list.value, tempview: view.value};
                listLabel.textContent = id === 6 ? '单页模板' : id === 8 ? '留言模板' : '栏目模板';
                listLabel.classList.toggle('required', id > 0);
                if (id > 0) list.setAttribute('lay-verify', 'required'); else list.removeAttribute('lay-verify');
                viewItem.style.display = single ? 'none' : '';
                if (id > 0 && !single) view.setAttribute('lay-verify', 'required'); else view.removeAttribute('lay-verify');
                viewItem.querySelector('.layui-form-label')?.classList.toggle('required', id > 0 && !single);
                if (settings.editing) {
                    const access = form.querySelector('#typearcrank-item'), inherit = form.querySelector('#inherit-option-item');
                    if (access) access.style.display = id === 6 ? 'none' : '';
                    if (inherit) inherit.style.display = '';
                }
                try {
                    if (id < 1) throw Error('请选择内容模型');
                    const params = {channel_id: id, lang: language()};
                    const [lists, views] = await Promise.all([
                        request('/web-admin/arctype/getTemplates', {...params, type: 'list'}),
                        single ? Promise.resolve([]) : request('/web-admin/arctype/getTemplates', {...params, type: 'view'})
                    ]);
                    if (version !== epoch) return false;
                    options(list, lists, '请选择模板', previous.templist, !!selected);
                    options(view, views, '请选择模板', previous.tempview, !!selected);
                    if (!initial) ui.render();
                    if (!lists.length || (!single && !views.length)) throw Error(id === 6 ? '该模型缺少模板文件：lists_single.htm' : id === 8 ? '该模型缺少模板文件：lists_guestbook.htm' : '该模型缺少栏目或文档模板，请检查当前主题');
                    if (!initial) status('ready');
                    return true;
                } catch (error) {
                    if (version === epoch) fail(error, () => changeModel(model.value, previous));
                    return false;
                }
            }
            async function initialize() {
                initial = true; status('loading'); clearError();
                try {
                    const fields = Promise.resolve(settings.fields());
                    const modelsRequest = request('/web-admin/channeltype/select', {format: 'select'});
                    // A top-level category's template family depends on its model,
                    // not on the parent tree. Overlap these two independent reads.
                    const earlyTemplates = !settings.editing && !Number(settings.parentId)
                        ? modelsRequest.then(models => changeModel(models.find(item => String(item.name).includes('文章'))?.value || models[0]?.value, {}), () => false)
                        : null;
                    const [models, tree, records] = await Promise.all([
                        modelsRequest,
                        request('/web-admin/arctype/select', {format: 'tree', limit: 2000, lang: language()}),
                        settings.recordUrl ? request(settings.recordUrl) : Promise.resolve([])
                    ]);
                    const row = records[0];
                    if (settings.editing && !row) throw Error('栏目不存在或无权读取，请返回列表');
                    let parentRow = null;
                    const parentId = Number(row?.parent_id || settings.parentId || 0), currentId = Number(row?.id || 0), rows = [];
                    function flatten(nodes, level, excluded) {
                        nodes.forEach(node => {
                            if (Number(node.id) === parentId) parentRow = node;
                            const skip = excluded || (currentId > 0 && Number(node.id) === currentId);
                            if (!skip) rows.push({value: node.id, name: '├─'.repeat(level) + (node.title || node.typename || '未命名')});
                            if (Array.isArray(node.children)) flatten(node.children, level + 1, skip);
                        });
                    }
                    flatten(tree, 0, false);
                    const channel = row?.current_channel || parentRow?.current_channel || models.find(item => String(item.name).includes('文章'))?.value || models[0]?.value;
                    options(model, models, '请选择模型', channel, !!row);
                    options(parent, [{value: 0, name: '顶级栏目'}, ...rows], '顶级栏目', parentId);
                    // Keep a single top-level option and preserve the selected parent.
                    parent.options[0].remove(); parent.value = String(parentId);
                    if (!hydrated) { settings.hydrate?.(row || parentRow || {}); hydrated = true; }
                    const [modelReady, fieldsReady] = await Promise.all([earlyTemplates || changeModel(model.value, row || {}), fields]);
                    if (!fieldsReady) throw Error('栏目自定义字段读取失败，请重试');
                    if (!modelReady) { ui.render(); return; }
                    ui.render();
                    status('ready'); window.CmsUI?.clean(form);
                } catch (error) { epoch++; fail(error, initialize); }
                finally { initial = false; release(); window.CmsPageReady?.complete(); }
            }
            status('loading');
            return {initialize, changeModel, isReady: () => state === 'ready'};
        }
    };
})(window, document);
