/*! CMS backend interaction primitives; project maintained. */
(function(window, document) {
'use strict'; if(window.CmsUI)return;
const originals=new WeakMap(), baseline=new WeakMap(), busyState=new WeakMap();let confirming=null,lastSuccess=0,lastSuccessPopup=null,currentFeedback=null;
function allForms(root=document){return root.matches?.('form')?[root]:Array.from(root.querySelectorAll('form'));}
function signature(form){const rich=Array.from(form.querySelectorAll('iframe')).flatMap((frame,index)=>{try{const doc=frame.contentDocument,body=doc?.body;return body&&(body.isContentEditable||body.contentEditable==='true'||doc.designMode==='on')?[['rich:'+index,body.innerHTML]]:[];}catch(_){return [];}});return JSON.stringify(Array.from(form.elements||[]).map((e,index)=>({e,key:e.name||e.id||'@'+index})).filter(({e})=>!['submit','button','reset','hidden'].includes(e.type)&&!e.closest('[data-cms-filter]')).map(({e,key})=>[key,e.type==='file'?Array.from(e.files||[]).map(f=>f.name+':'+f.size):e.type==='checkbox'||e.type==='radio'?e.checked:e.value]).concat(rich));}
function filter(form){return form.matches('[data-cms-filter],.cms-filterbar')||!!form.closest('[data-cms-filter]');}
function clean(root=document){if(!root)return;delete root.dataset?.cmsDirty;allForms(root).forEach(form=>{delete form.dataset.cmsDirty;baseline.set(form,signature(form));});}
function dirty(root){if(root&&!filter(root))root.dataset.cmsDirty='1';}
function isBusy(root=document){return !!(root.matches?.('[data-cms-submit-busy][aria-busy="true"],[data-cms-upload-busy="1"]')||root.querySelector?.('[data-cms-submit-busy][aria-busy="true"],[data-cms-upload-busy="1"]'));}
function isDirty(root=document){return !!root.querySelector?.('[data-cms-dirty="1"]')||root.dataset?.cmsDirty==='1';}
function safeUrl(value){try{const u=new URL(value,location.href);return u.origin===location.origin&&/^\/web-admin\//.test(u.pathname)?u:null;}catch(_){return null;}}
function openEditor(href){
 const target=safeUrl(href);if(!target)return false;
 const source=new URL(location.href),language=window.CmsEditContext?.get()?.edit||source.searchParams.get('lang')||window.CmsLanguageInitial?.lang;
 if(language)target.searchParams.set('lang',language);
 if(!target.searchParams.has('return'))target.searchParams.set('return',source.pathname+source.search);
 try{if(parent!==window&&parent.CmsNavigation?.openEditor){const result=parent.CmsNavigation.openEditor(target.href,window);if(result!==null)return result}}catch(error){notice('编辑页面暂时无法打开，请重试。');return false;}
 return leave(()=>location.assign(target.pathname+target.search));
}
function navigateBack(target,saved){
 try{if(parent!==window&&parent.CmsNavigation?.returnEditor(window,{href:target,saved}))return;}catch(_){}
 if(target)location.assign(target);
}
function notice(text){let box=document.querySelector('[data-cms-ui-notice]');if(!box){box=document.createElement('div');box.dataset.cmsUiNotice='';box.className='cms-ui-notice';box.setAttribute('role','status');document.body.append(box);}box.textContent=text;box.hidden=!text;}
async function confirm(message,options={}){
 if(confirming)return false;
 confirming=new Promise(resolve=>{
  const dialog=document.createElement('dialog');dialog.dataset.cmsDialogKind='confirm';dialog.dataset.cmsUiOwned='1';dialog.className='cms-ui-confirm';const emblem=document.createElement('span');emblem.className='cms-ui-emblem';emblem.setAttribute('aria-hidden','true');emblem.textContent=({switch:'⇄',restart:'↻'})[options.icon]||options.icon||'!';dialog.append(emblem);
  const heading=document.createElement('h2');heading.id='cms-ui-confirm-title';heading.textContent=options.title||'确认操作';dialog.setAttribute('aria-labelledby',heading.id);
  const text=document.createElement('p');text.className='cms-ui-confirm-message';text.textContent=message;
  const actions=document.createElement('div');actions.className='cms-ui-actions';const cancel=document.createElement('button');cancel.type='button';cancel.textContent=options.cancelText||'取消';const ok=document.createElement('button');ok.type='button';ok.textContent=options.confirmText||'确认';ok.className=options.danger?'cms-ui-danger':'cms-ui-primary';actions.append(cancel,ok);dialog.append(heading,text,actions);
  const previous=document.activeElement;function finish(value){dialog.close();dialog.remove();confirming=null;previous?.focus?.();resolve(value);}
  cancel.onclick=()=>finish(false);ok.onclick=()=>finish(true);dialog.addEventListener('cancel',event=>{event.preventDefault();finish(false);});document.body.append(dialog);dialog.showModal();cancel.focus();
 });return confirming;
}
async function leave(callback,root=document){
 if(root.matches?.('[data-cms-upload-busy="1"]')||root.querySelector?.('[data-cms-upload-busy="1"]')){notice('文件正在上传，请上传完成后再离开。');return false;}
 if(root.matches?.('[aria-busy="true"][data-cms-submit-busy]')||root.querySelector?.('[aria-busy="true"][data-cms-submit-busy]')){notice('正在保存，请稍候。');return false;}
 if(isDirty(root)&&!await confirm('当前内容尚未保存，放弃修改并离开？',{title:'未保存的修改',confirmText:'放弃修改',cancelText:'继续编辑'}))return false;
 clean(root);if(callback)callback();return true;
}
function discard(root=document){clean(root);root.querySelectorAll('dialog[open]').forEach(dialog=>{const saved=originals.get(dialog);if(saved)saved.close();else dialog.close();});try{root.defaultView?.layui?.layer?.closeAll();}catch(_){} }
function guard(callback,root=document){
 if(isBusy(root)){notice('正在处理，请完成后再离开。');return false;}
 const open=root.querySelector('dialog[open]:not([data-cms-ui-owned]),.layui-layer-page,.layui-layer-iframe,.layui-layer-dialog');
 if(!isDirty(root)){
  if(!open)return true;
  const windows=Array.from(root.querySelectorAll('dialog[open]:not([data-cms-ui-owned]),.layui-layer-page,.layui-layer-iframe,.layui-layer-dialog'));
  if(windows.every(node=>node.matches('dialog[data-cms-editor="page"]'))){discard(root);return true;}
 }
 if(!callback)return false;
 (async()=>{if(isDirty(root)){if(!await leave(null,root))return;}else if(!await confirm('离开页面将关闭当前窗口，是否继续？',{title:'关闭当前窗口',confirmText:'离开页面',cancelText:'继续操作'}))return;discard(root);callback();})();return false;
}
function returnUrl(explicit){const requested=safeUrl(new URL(location.href).searchParams.get('return'));const fallback=safeUrl(explicit||document.body.dataset.cmsReturnUrl||location.pathname.replace(/\/(insert|update)\/?$/,'/index'));const u=requested&&fallback&&requested.pathname===fallback.pathname?requested:fallback;if(!u)return null;const language=new URL(location.href).searchParams.get('lang');if(language)u.searchParams.set('lang',language);return u.pathname+u.search+u.hash;}
function back(explicit){const target=returnUrl(explicit);if(target)return leave(()=>navigateBack(target,false));return Promise.resolve(false);}
function saved(explicit){clean();const target=returnUrl(explicit);if(Date.now()-lastSuccess<4000&&lastSuccessPopup){return lastSuccessPopup.finished.then(()=>{if(target)navigateBack(target,true);});}return success('保存成功',()=>{if(target)navigateBack(target,true);});}
function setBusy(root,value){if(!root)return;root.toggleAttribute('data-cms-submit-busy',!!value);root.setAttribute('aria-busy',String(!!value));const controls=root.querySelectorAll('button,input[type=submit]');if(value){controls.forEach(button=>{if(!busyState.has(button))busyState.set(button,{disabled:button.disabled,text:button.textContent});button.disabled=true;if(button.type==='submit'&&button.tagName==='BUTTON')button.textContent='正在保存…';});}else controls.forEach(button=>{const saved=busyState.get(button);if(saved){button.disabled=saved.disabled;button.textContent=saved.text;busyState.delete(button);}});}
function error(root,message,fields={}){
 root=root||document.body;root.querySelectorAll('.cms-ui-field-error').forEach(e=>e.remove());root.querySelectorAll('[aria-invalid=true]').forEach(e=>e.removeAttribute('aria-invalid'));
 let box=root.querySelector('[data-cms-form-error]');if(!box){box=document.createElement('p');box.dataset.cmsFormError='';box.className='cms-ui-error';box.setAttribute('role','alert');box.tabIndex=-1;root.prepend(box);}box.textContent=message||'操作失败，请重试。';
 let first=null;Object.entries(fields||{}).forEach(([name,value])=>{const control=Array.from(root.querySelectorAll('[name]')).find(e=>e.name===name);if(!control)return;control.setAttribute('aria-invalid','true');const line=document.createElement('span');line.className='cms-ui-field-error';line.textContent=Array.isArray(value)?value.join('；'):String(value);control.after(line);first||=control;});(first||box).focus();failure(box.textContent);
}
function layer(options={}){
 const api=window.layui?.layer||window.layer;if(!api)throw Error('窗口组件尚未就绪，请重试');
 const {kind='edit',...settings}=options;const width=kind==='picker'?1040:kind==='confirm'?520:720,mobile=innerWidth<640;let innerDocument=null,escapeHandler=null,index;
 const temporary=[];if(settings.type===1&&settings.content&&typeof settings.content==='object'){const nodes=settings.content.nodeType?[settings.content]:Array.from(settings.content);for(const node of nodes){if(node?.nodeType===1&&!node.isConnected){temporary.push({node,display:node.style.display});node.style.display='none';document.body.append(node);}}}
 function closeAllowed(layerIndex,node){
  let doc=document;try{doc=node?.find?.('iframe')?.[0]?.contentDocument||document;}catch(_){}
  const ui=doc.defaultView?.CmsUI||window.CmsUI;
  if(isBusy(doc)){notice('正在处理，请完成后再关闭。');return false;}
  const finish=()=>{ui.clean(doc);if(settings.cancel?.(layerIndex,node)===false)return;api.close(layerIndex);};
  if(ui.isDirty(doc)){ui.leave(finish,doc);return false;}
  if(settings.cancel)return settings.cancel(layerIndex,node);return true;
 }
 index=api.open({...settings,shadeClose:false,area:mobile?['calc(100vw - 16px)','calc(100dvh - 16px)']:[Math.min(width,innerWidth-32)+'px',kind==='confirm'?'auto':Math.min(Math.round(innerHeight*.85),850)+'px'],skin:(settings.skin||'')+' cms-ui-layer cms-ui-layer-'+kind,
  success(node,layerIndex){settings.success?.(node,layerIndex);escapeHandler=event=>{const outer=node?.[0];if(document.querySelector('dialog[open]')||innerDocument?.querySelector('dialog[open]'))return;const layers=Array.from(document.querySelectorAll('.layui-layer')).filter(layer=>getComputedStyle(layer).display!=='none').sort((a,b)=>(parseInt(getComputedStyle(b).zIndex)||0)-(parseInt(getComputedStyle(a).zIndex)||0));if(outer&&layers[0]!==outer)return;if(event.key==='Escape'){event.preventDefault();event.stopPropagation();if(closeAllowed(layerIndex,node)!==false)api.close(layerIndex);}else if(event.key==='Tab'&&innerDocument){const controls=Array.from(innerDocument.querySelectorAll('button,a[href],input:not([type=hidden]),select,textarea,[tabindex]')).filter(control=>!control.disabled&&control.tabIndex>=0&&control.getClientRects().length);if(!controls.length)return;const active=innerDocument.activeElement;if(event.shiftKey&&active===controls[0]){event.preventDefault();controls[controls.length-1].focus();}else if(!event.shiftKey&&active===controls[controls.length-1]){event.preventDefault();controls[0].focus();}}};document.addEventListener('keydown',escapeHandler);try{innerDocument=node.find('iframe')[0]?.contentDocument;innerDocument?.addEventListener('keydown',escapeHandler);}catch(_){}},
  cancel:closeAllowed,end(){if(escapeHandler){document.removeEventListener('keydown',escapeHandler);innerDocument?.removeEventListener('keydown',escapeHandler);}settings.end?.();temporary.forEach(({node,display})=>{node.remove();node.style.display=display;});}
 });return index;
}
function editorMode(dialog){return dialog.dataset.cmsEditor==='page'||dialog.dataset.cmsPageWhen==='textarea'&&!!dialog.querySelector('textarea,[contenteditable=true]');}
function layoutDialog(dialog){
 if(dialog.dataset.cmsUiOwned||!dialog.open)return;
 const page=editorMode(dialog);dialog.classList.toggle('cms-ui-page-editor',page);
 let header=dialog.querySelector('[data-cms-editor-heading]');
 if(page&&!header){header=document.createElement('header');header.dataset.cmsEditorHeading='';header.className='cms-ui-editor-heading';const button=document.createElement('button');button.type='button';button.textContent='← 返回列表';button.onclick=()=>dialog.close();header.append(button);dialog.prepend(header);}
 if(header)header.hidden=!page;
 document.body.classList.toggle('cms-ui-editing',!!document.querySelector('dialog.cms-ui-page-editor[open]'));
}
function prepareDialog(dialog){
 if(originals.has(dialog)||dialog.dataset.cmsUiOwned)return;
 const show=dialog.showModal.bind(dialog),close=dialog.close.bind(dialog);originals.set(dialog,{show,close});
 dialog.showModal=function(){
  dialog.dataset.cmsDialogKind||='edit';clean(dialog);
  show();layoutDialog(dialog);dialog.setAttribute('aria-modal','true');
  if(!dialog.getAttribute('aria-label')&&!dialog.getAttribute('aria-labelledby')){const title=dialog.querySelector('h1,h2,h3');if(title){title.id||='cms-dialog-'+Math.random().toString(36).slice(2);dialog.setAttribute('aria-labelledby',title.id);}}
 };
 dialog.close=function(value){if(isBusy(dialog)){notice('正在处理，请稍候。');return;}if(isDirty(dialog))return leave(()=>close(value),dialog);close(value);};
 dialog.addEventListener('cancel',event=>{event.preventDefault();dialog.close();});
 dialog.addEventListener('close',()=>{clean(dialog);if(!document.querySelector('dialog.cms-ui-page-editor[open]'))document.body.classList.remove('cms-ui-editing');});
}
function setup(root=document){
 root.querySelectorAll?.('dialog').forEach(prepareDialog);
 const openDialog=root.closest?.('dialog[open]');if(openDialog)layoutDialog(openDialog);
 allForms(root).forEach(form=>{if(filter(form)){delete form.dataset.cmsDirty;return;}if(!baseline.has(form))baseline.set(form,signature(form));});
}
function invalid(event){const form=event.target.form;if(!form||filter(form))return;event.preventDefault();const fields={};Array.from(form.elements).filter(control=>control.willValidate&&!control.validity.valid&&control.name).forEach(control=>{fields[control.name]=control.validationMessage||'请检查此项';});error(form,'请检查标记的项目后再保存。',fields);}
function start(){setup();window.addEventListener('beforeunload',event=>{if(isDirty()||isBusy()){event.preventDefault();event.returnValue='';}});document.addEventListener('input',track);document.addEventListener('change',track);document.addEventListener('invalid',invalid,true);document.addEventListener('click',event=>{const backButton=event.target.closest('[data-cms-back]');if(backButton){event.preventDefault();back();}});new MutationObserver(records=>{for(const record of records)for(const node of record.addedNodes)if(node.nodeType===1){if(node.matches('dialog'))prepareDialog(node);setup(node);}}).observe(document.body,{childList:true,subtree:true});}
function track(event){const form=event.target.form||event.target.closest('form');if(!form)return;const control=event.target;if(control.getAttribute('aria-invalid')==='true'&&(!control.validity||control.validity.valid)){control.removeAttribute('aria-invalid');if(control.nextElementSibling?.classList.contains('cms-ui-field-error'))control.nextElementSibling.remove();if(!form.querySelector('[aria-invalid=true]'))form.querySelector('[data-cms-form-error]')?.remove();}if(filter(form)){delete form.dataset.cmsDirty;return;}if(event.target.closest('[data-cms-filter]'))return;if(!baseline.has(form))baseline.set(form,signature(form));form.dataset.cmsDirty=signature(form)===baseline.get(form)?'0':'1';}
function toast(message,options={}){
        const box=document.createElement('div');box.className='cms-ui-toast';box.dataset.kind=options.kind||'error';
        box.setAttribute('role',options.kind==='success'?'status':'alert');
        const icon=document.createElement('span');icon.className='cms-ui-toast-icon';icon.setAttribute('aria-hidden','true');icon.textContent=options.kind==='success'?'✓':'!';
        const text=document.createElement('span');text.className='cms-ui-toast-message';text.textContent=message;box.append(icon,text);feedbackLifetime(box,options.duration??2000);document.body.append(box);
        const close=()=>box.remove();window.setTimeout(close,options.duration??2000);return {close};
    }
    function feedbackLifetime(box,duration){
        box.style.setProperty('--cms-feedback-duration',Math.max(0,Number(duration)||0)+'ms');
        const lifetime=document.createElement('span');lifetime.className='cms-ui-feedback-lifetime';lifetime.setAttribute('aria-hidden','true');box.append(lifetime);
    }
    // Keep feedback in the workspace so closing an editor frame cannot hide it.
    function feedback(message,options={}){
        try{if(window.parent!==window&&window.parent.CmsUI)return window.parent.CmsUI.feedback(message,options);}catch(_){}
        if(currentFeedback?.message===String(message)&&currentFeedback?.kind===(options.kind||'success'))return currentFeedback;
        currentFeedback?.close();
        const previous=document.activeElement,dialog=document.createElement('dialog');dialog.className='cms-ui-feedback';dialog.dataset.cmsUiOwned='1';dialog.dataset.kind=options.kind||'success';
        const emblem=document.createElement('span');emblem.className='cms-ui-emblem';emblem.setAttribute('aria-hidden','true');emblem.textContent=options.kind==='error'?'!':options.kind==='warning'?'!':'✓';
        const title=document.createElement('h2');title.id='cms-feedback-'+Math.random().toString(36).slice(2);title.textContent=options.title||(options.kind==='error'?'操作未完成':options.kind==='warning'?'请注意':'操作成功');dialog.setAttribute('aria-labelledby',title.id);
        const text=document.createElement('p');text.className='cms-ui-confirm-message';text.id=title.id+'-message';text.textContent=String(message||'已保存');text.setAttribute('role',options.kind==='error'?'alert':'status');dialog.setAttribute('aria-describedby',text.id);
        const content=document.createElement('div');content.className='cms-ui-feedback-content';content.append(title,text);dialog.append(emblem,content);feedbackLifetime(dialog,options.duration??2000);
        let timer,closed=false,resolve;const finished=new Promise(done=>{resolve=done;});const handle={finished,message:String(message),kind:options.kind||'success',close(){if(closed)return;closed=true;clearTimeout(timer);dialog.close();dialog.remove();if(currentFeedback===handle)currentFeedback=null;if(previous?.isConnected)previous.focus?.({preventScroll:true});resolve();}};
        dialog.addEventListener('cancel',event=>{event.preventDefault();handle.close();});document.body.append(dialog);dialog.showModal();currentFeedback=handle;
        timer=setTimeout(handle.close,options.duration??2000);return handle;
    }
    function success(message,callback){lastSuccess=Date.now();const popup=feedback(message,{kind:'success'});lastSuccessPopup=popup;popup.finished.then(()=>{lastSuccess=Date.now();callback?.();});return popup;}
    function failure(message,callback){const popup=feedback(message,{kind:'error'});popup.finished.then(()=>callback?.());return popup;}
    function warning(message,callback){const popup=feedback(message,{kind:'warning'});popup.finished.then(()=>callback?.());return popup;}
    // Explicit save requests opt in; reads, uploads and background tasks stay silent.
    async function persist(run,options={}){try{const result=await run();success(options.message||result?.message||result?.msg||'保存成功');return result;}catch(error){failure(error.message||'保存失败，请稍后重试');throw error;}}
    function progress(options={}){
        const previous=document.activeElement,dialog=document.createElement('dialog');dialog.className='cms-ui-progress';dialog.dataset.cmsUiOwned='1';dialog.dataset.cmsDialogKind='confirm';dialog.setAttribute('aria-modal','true');
        const emblem=document.createElement('span');emblem.className='cms-ui-emblem cms-ui-spinner';emblem.setAttribute('aria-hidden','true');
        const title=document.createElement('h2');title.id='cms-ui-progress-'+Math.random().toString(36).slice(2);title.textContent=options.title||'正在处理';dialog.setAttribute('aria-labelledby',title.id);
        const message=document.createElement('p');message.className='cms-ui-confirm-message';message.setAttribute('role','status');message.setAttribute('aria-live','polite');message.textContent=options.message||'请稍候…';
        const track=document.createElement('div');track.className='cms-ui-progress-track';track.setAttribute('aria-hidden','true');track.append(document.createElement('span'));
        dialog.append(emblem,title,message,track);dialog.addEventListener('cancel',event=>event.preventDefault());document.body.append(dialog);dialog.showModal();
        let closed=false;function close(){if(closed)return;closed=true;dialog.close();dialog.remove();previous?.focus?.()}
        return {update(text){if(!closed)message.textContent=text},close,async finish(result={}){if(closed)return;dialog.dataset.kind=result.kind||'success';emblem.classList.remove('cms-ui-spinner');emblem.textContent=result.kind==='error'?'!':'✓';title.textContent=result.title||'操作完成';message.textContent=result.message||'已完成';track.hidden=true;await new Promise(resolve=>window.setTimeout(resolve,result.duration??1000));close()}};
    }
    window.CmsUI={openEditor,toast,feedback,success,failure,warning,persist,progress,confirm,leave,guard,discard,back,saved,clean,dirty,isDirty,layer,setBusy,error,notice,returnUrl,setup};
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})(window,document);
