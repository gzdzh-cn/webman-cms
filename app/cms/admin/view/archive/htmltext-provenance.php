<style>
.cms-htmltext-origin{margin:8px 0;padding:10px 12px;border:1px solid #e6d5a3;border-radius:6px;background:#fffcf3;color:#62522e;font-size:13px;line-height:1.6}.cms-htmltext-origin details{margin:6px 0}.cms-htmltext-origin pre{white-space:pre-wrap;overflow-wrap:anywhere;max-height:140px;overflow:auto;margin:5px 0;padding:8px;background:#fff;color:#333;border:1px solid #e7e7e7}.cms-htmltext-origin-actions{display:flex;flex-wrap:wrap;gap:8px}.cms-htmltext-origin button{border:1px solid #c8cdd5;border-radius:4px;background:#fff;color:#34445d;padding:5px 10px;cursor:pointer}.cms-htmltext-origin button[aria-pressed=true]{border-color:#1677ff;color:#1677ff}.cms-htmltext-origin-error{color:#c52f31;margin-top:5px}
</style>
<script>
(function () {
    'use strict';
    window.CmsHtmltextProvenance = {
        create: function () {
            var entries = {};
            function remove(name) {var node=document.getElementById('cms-htmltext-origin-'+name);if(node)node.remove();}
            function markDirty(node) {var form=node.closest('form');if(form){form.dataset.cmsDirty='1';form.dispatchEvent(new Event('input',{bubbles:true}));}}
            function load(name, original, metadata, element, applyChoice) {
                remove(name);
                var info = metadata || {source:'unknown',sha256:'',revision:0};
                var entry = entries[name] = {original:String(original == null?'':original),info:info,normalized:null,choice:null,element:element};
                entry.value = String(info.editor_value == null ? entry.original : info.editor_value);
                if (info.source !== 'unknown' || !element) return entry.value;
                var panel=document.createElement('div');panel.id='cms-htmltext-origin-'+name;panel.className='cms-htmltext-origin';
                var heading=document.createElement('div');heading.textContent='这段历史正文的编码来源尚未确认。仅修改标题等其他信息会保留原正文。';panel.appendChild(heading);
                var details=document.createElement('details'),summary=document.createElement('summary');summary.textContent='查看两种解读，编辑正文前请选择';details.appendChild(summary);
                [['按原始 HTML 解读',info.raw_preview],['按 EyouCMS 编码解读（解码一次）',info.encoded_preview]].forEach(function (item) {
                    var label=document.createElement('div');label.textContent=item[0];var pre=document.createElement('pre');pre.textContent=String(item[1] == null ? entry.original : item[1]);details.appendChild(label);details.appendChild(pre);
                });panel.appendChild(details);
                var actions=document.createElement('div');actions.className='cms-htmltext-origin-actions';
                [['raw','按原始 HTML 编辑'],['encoded','按 EyouCMS 编码编辑']].forEach(function (choice) {
                    var button=document.createElement('button');button.type='button';button.textContent=choice[1];button.setAttribute('aria-pressed','false');
                    button.addEventListener('click',async function () {
                        if (button.disabled) return;
                        var editor=entry.editor;
                        if (!editor || !editor.initialized) {entry.error.textContent='编辑器尚未就绪，请稍候';return;}
                        var message='确认'+choice[1]+'？编辑器将载入该预览；尚未保存的正文修改将被替换。保存时只提交实际修改的正文。';
                        button.disabled=true;
                        try {
                            var approved=window.CmsUI && typeof window.CmsUI.confirm==='function' ? await window.CmsUI.confirm(message,{title:'确认正文来源',confirmText:'确认并载入',cancelText:'继续核对'}) : window.confirm(message);
                            if (!approved || entries[name] !== entry) return;
                            entry.choice=choice[0];entry.error.textContent='';
                            applyChoice(choice[0]==='encoded'?String(info.encoded_preview):entry.original);
                            actions.querySelectorAll('button').forEach(function(b){b.setAttribute('aria-pressed',String(b===button));});
                            heading.textContent='已选择'+choice[1]+'。只有实际修改正文并保存后才会写入新内容及来源。';
                            markDirty(panel);
                        } finally {button.disabled=false;}
                    });actions.appendChild(button);
                });panel.appendChild(actions);
                entry.error=document.createElement('div');entry.error.className='cms-htmltext-origin-error';entry.error.setAttribute('role','alert');panel.appendChild(entry.error);
                element.parentNode.insertBefore(panel,element);
                return entry.value;
            }
            return {
                reset:function(){Object.keys(entries).forEach(remove);entries={};},
                load:load,
                baseline:function(name,editor){var entry=entries[name];if(entry){entry.editor=editor;entry.normalized=String(editor.getContent()||'');}},
                collect:function(formData,getEditor){
                    var metadata={},valid=true;
                    Object.keys(entries).forEach(function(name){
                        var entry=entries[name],editor=getEditor(name);delete formData[name];
                        if (!editor || entry.normalized===null) {valid=false;return;}
                        var value=String(editor.getContent()||'');if(value===entry.normalized)return;
                        if(entry.info.source==='unknown'&&!entry.choice){
                            entry.error.textContent='请先查看两种预览，并确认原正文来源后再编辑保存。';entry.error.closest('.cms-htmltext-origin').querySelector('details').open=true;
                            entry.error.scrollIntoView({block:'center',behavior:'auto'});valid=false;return;
                        }
                        formData[name]=value;
                        if(entry.info.source==='new')return;
                        metadata[name]={format:'raw',source:entry.info.source,sha256:entry.info.sha256,revision:entry.info.revision};
                        if(entry.info.source==='unknown'){metadata[name].interpretation=entry.choice;metadata[name].confirmed=true;}
                    });
                    if(Object.keys(metadata).length)formData.__htmltext=JSON.stringify(metadata);else delete formData.__htmltext;
                    return valid;
                }
            };
        }
    };
})();
</script>
