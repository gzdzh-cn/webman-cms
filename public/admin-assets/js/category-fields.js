(function () {
    'use strict';
    let fields = [], host, ready = false, requestVersion = 0, typeId = 0;
    let provenance;
    const richEditors = new Map();
    const controls = new Map();
    function element(tag, text, className) {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    }
    function input(value, type) {
        const node = element('input', undefined, 'layui-input');
        node.type = type || 'text'; node.value = value == null ? '' : String(value); node.defaultValue = node.value;
        // Custom controls are collected once below, including unchecked/empty values.
        return node;
    }
    function button(text, action) {
        const node = element('button', text, 'layui-btn layui-btn-sm'); node.type = 'button'; node.onclick = action; return node;
    }
    function uploader(target, image) {
        const file = input('', 'file'); file.hidden = true;
        if (image) file.accept = 'image/*';
        const choose = button(image ? '上传图片' : '上传文件', () => file.click());
        file.onchange = async () => {
            if (!file.files.length) return;
            choose.disabled = true;
            try {
                const data = new FormData(); data.append('file', file.files[0]);
                const result = await (await fetch('/web-admin/upload/' + (image ? 'image' : 'file'), {method: 'POST', body: data})).json();
                if (result.code !== 0 || !result.data || !result.data.url) throw Error(result.msg || '上传失败');
                target.value = result.data.url;
            } catch (error) { layui.layer.msg(error.message); }
            finally { choose.disabled = false; file.value = ''; }
        };
        const wrapper = element('div'); wrapper.append(choose, file); return wrapper;
    }
    function imageRows(container, rows) {
        function add(row) {
            const wrapper = element('div'); wrapper.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center';
            const path = input(row.image_url || ''), intro = element('textarea', undefined, 'layui-textarea');
            intro.value = intro.defaultValue = row.intro == null ? '' : String(row.intro); intro.rows = 2;
            path.placeholder = '图片地址'; intro.placeholder = '图片说明'; path.dataset.path = ''; intro.dataset.intro = '';
            wrapper.append(path, intro, uploader(path, true), button('移除', () => wrapper.remove()));
            container.append(wrapper);
        }
        rows.forEach(add);
        return button('添加图片', () => add({}));
    }
    function render(field) {
        const item = element('div', undefined, 'layui-form-item'), label = element('label', field.title, 'layui-form-label');
        const body = element('div', undefined, 'layui-input-block'); item.append(label, body);
        const dtype = field.dtype, value = field.value == null ? '' : field.value;
        if (['checkbox', 'checkboxs', 'radio', 'radios', 'select', 'selects'].includes(dtype)) {
            const options = field.options || [], multiple = ['checkbox', 'checkboxs'].includes(dtype), selected = String(value).split(',');
            if (['select', 'selects'].includes(dtype)) {
                const select = element('select'); select.className = 'layui-select'; select.setAttribute('lay-ignore', '');
                select.append(new Option('请选择', ''));
                options.forEach(option => select.append(new Option(option.text, option.value, false, selected.includes(String(option.value)))));
                body.append(select); controls.set(field.name, () => select.value);
            } else {
                const nodes = [];
                options.forEach(option => {
                    const optionLabel = element('label'); optionLabel.style.marginRight = '16px';
                    const choice = input(option.value, multiple ? 'checkbox' : 'radio'); choice.className = ''; choice.setAttribute('lay-ignore', '');
                    choice.name = 'category-choice-' + field.id; choice.checked = choice.defaultChecked = selected.includes(String(option.value));
                    optionLabel.append(choice, document.createTextNode(' ' + option.text)); body.append(optionLabel); nodes.push(choice);
                });
                if (!multiple && field.ifrequire != 1) body.append(button('清空', () => nodes.forEach(node => { node.checked = false; })));
                controls.set(field.name, () => multiple ? nodes.filter(node => node.checked).map(node => node.value).join(',') : (nodes.find(node => node.checked)?.value || ''));
            }
        } else if (dtype === 'imgs') {
            const rows = element('div'); body.append(rows, imageRows(rows, Array.isArray(value) ? value : []));
            controls.set(field.name, () => Array.from(rows.children).map(row => ({image_url: row.querySelector('[data-path]').value, intro: row.querySelector('[data-intro]').value})));
        } else if (['htmltext', 'multitext', 'files'].includes(dtype)) {
            const textarea = element('textarea', undefined, 'layui-textarea'); textarea.value = textarea.defaultValue = String(value);
            if (dtype === 'htmltext') { textarea.rows = 8; textarea.placeholder = 'HTML 内容'; }
            if (dtype === 'files') textarea.placeholder = '多个文件地址用逗号分隔';
            body.append(textarea); controls.set(field.name, () => textarea.value);
            if (dtype === 'htmltext') {
                const editor = {initialized: true, getContent: () => textarea.value};
                richEditors.set(field.name, editor);
                textarea.value = provenance.load(field.name, String(value), field.__htmltext, textarea, choice => {
                    textarea.value = choice;
                    provenance.baseline(field.name, editor);
                });
                textarea.defaultValue = textarea.value;
                provenance.baseline(field.name, editor);
            }
        } else if (dtype === 'switch') {
            const select = element('select'); select.setAttribute('lay-ignore', '');
            select.append(new Option('关闭', '0', false, String(value) === '0'), new Option('开启', '1', false, String(value) !== '0'));
            body.append(select); controls.set(field.name, () => select.value);
        } else {
            let display = value;
            if (dtype === 'datetime' && Number(value) > 0) {
                const date = new Date(Number(value) * 1000), pad = number => String(number).padStart(2, '0');
                display = date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
            }
            const node = input(display); body.append(node);
            if (dtype === 'img') body.append(uploader(node, true));
            controls.set(field.name, () => node.value);
        }
        if (field.remark) body.append(element('div', field.remark, 'layui-form-mid layui-word-aux'));
        host.append(item);
    }
    window.CategoryFields = {
        async mount(container, requestedTypeId, card) {
            const version = ++requestVersion;
            typeId = Number(requestedTypeId || 0);
            if (card) card.hidden = false;
            host = container; ready = false; controls.clear(); richEditors.clear();
            if (provenance) provenance.reset();
            provenance = window.CmsHtmltextProvenance.create();
            host.replaceChildren(element('p', '正在加载栏目字段…'));
            const controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 10000);
            try {
                const response = await fetch('/web-admin/channelfield/categoryFields?typeid=' + encodeURIComponent(typeId || 0), {signal: controller.signal});
                const result = await response.json();
                if (version !== requestVersion) return;
                if (!response.ok || result.code !== 0 || !Array.isArray(result.data)) throw Error(result.msg || '栏目字段加载失败');
                fields = result.data; host.replaceChildren(); fields.forEach(render);
                if (card) card.hidden = fields.length === 0;
                ready = true;
                return true;
            } catch (error) {
                if (version !== requestVersion) return;
                if (card) card.hidden = false;
                host.replaceChildren(element('p', error.name === 'AbortError' ? '栏目字段读取超时，请重试' : error.message), button('重新加载', () => window.CategoryFields.mount(container, requestedTypeId, card)));
                return false;
            } finally { clearTimeout(timeout); }
        },
        collect() {
            if (!ready) throw Error('栏目字段尚未加载完成，请稍后重试');
            const addonField = Object.create(null);
            fields.forEach(field => {
                const value = controls.get(field.name)();
                if (field.ifrequire == 1 && (Array.isArray(value) ? value.length === 0 : String(value).trim() === '')) throw Error('请填写' + field.title);
                addonField[field.name] = Array.isArray(value) && value.length === 0 ? '' : value;
            });
            if (!provenance.collect(addonField, name => richEditors.get(name))) throw Error('请核对栏目富文本来源后再保存');
            const result = {addonField};
            if (addonField.__htmltext) { result.__htmltext = addonField.__htmltext; delete addonField.__htmltext; }
            if (!typeId) {
                // New fields must be sent even if the default textarea value was unchanged.
                fields.filter(field => field.dtype === 'htmltext').forEach(field => { addonField[field.name] = controls.get(field.name)(); });
                result.__htmltext_format = 'raw-v1';
            }
            return result;
        }
    };
})();
