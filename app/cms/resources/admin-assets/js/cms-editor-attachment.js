(function (global) {
    'use strict';

    // TinyMCE calls a file picker with the originating editor as `this`.
    global.cmsAttachmentPicker = function (callback, value, meta) {
        if (!meta || meta.filetype !== 'file') return;
        var editor = this;
        var input = document.createElement('input');
        input.type = 'file';
        input.hidden = true;
        document.body.appendChild(input);
        input.addEventListener('cancel', function () { input.remove(); });
        input.addEventListener('change', async function () {
            var file = input.files && input.files[0];
            if (!file) { input.remove(); return; }
            try {
                editor.setProgressState(true);
                var body = new FormData();
                body.append('file', file);
                var response = await fetch('/web-admin/upload/file', {
                    method: 'POST', body: body, credentials: 'same-origin',
                    headers: { Accept: 'application/json' }
                });
                var result;
                try { result = await response.json(); }
                catch (error) { throw new Error('上传接口响应异常（' + response.status + '）'); }
                if (!response.ok || result.code !== 0) throw new Error(result.msg || '上传失败');
                if (!result.data || typeof result.data.url !== 'string' || !result.data.url) {
                    throw new Error('上传接口未返回附件地址');
                }
                var name = result.data.name || file.name;
                callback(result.data.url, { text: name, title: name });
            } catch (error) {
                editor.windowManager.alert('附件上传失败：' + error.message);
            } finally {
                editor.setProgressState(false);
                input.remove();
            }
        });
        input.click();
    };
})(window);
