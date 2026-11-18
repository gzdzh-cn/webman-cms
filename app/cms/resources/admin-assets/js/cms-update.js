/* Shared update badge, source settings and resumable maintenance viewer. */
(function(window,document){
'use strict';
function badge(value){
    document.querySelectorAll('[data-info-key="admin"] dd').forEach(function(row){
        var button=row.querySelector('[data-cms-update]');
        if(!button){button=document.createElement('button');button.type='button';button.dataset.cmsUpdate='1';button.className='cms-update-badge';row.insertBefore(button,row.querySelector('small'));button.addEventListener('click',function(){window.CmsUpdate.open();});}
        button.textContent=value.checking?'检查中…':value.has_update?'新版本':'检查更新';button.classList.toggle('is-new',!!value.has_update);button.classList.toggle('is-checking',!!value.checking);button.setAttribute('aria-busy',String(!!value.checking));
    });
}
try{if(window.parent!==window&&window.parent.CmsUpdate){window.CmsUpdate=window.parent.CmsUpdate;var dispose=window.CmsUpdate.subscribe(badge);window.addEventListener('pagehide',dispose,{once:true});return;}}catch(_){}
var info={},listeners=[],dialog,sourceDialog,previous,task=null,pollTimer,checkTimer,successTimer,checking=false,starting=false,savingSource=false,sourceSelect,sourceError,successTask=null;
var providers={gitee:'Gitee',github:'GitHub',custom:'自定义源'},handled=new Set(),activeTasks=new Set();
try{var savedTask=sessionStorage.getItem('cms-update-active-task');if(savedTask)activeTasks.add(savedTask);}catch(_){}
function rememberTask(id){activeTasks.add(id);try{sessionStorage.setItem('cms-update-active-task',id);}catch(_){}}
function element(tag,text,cls){var node=document.createElement(tag);if(text!==undefined)node.textContent=text;if(cls)node.className=cls;return node;}
function notify(){listeners.slice().forEach(function(listener){listener(info);});}
function terminal(value){return ['succeeded','failed','rolled_back'].includes(value.stage);}
function isBusy(){return starting||!!(task&&!terminal(task));}
function makeDialog(){
    if(dialog)return;
    dialog=element('dialog',undefined,'cms-update-dialog');dialog.dataset.cmsUiOwned='1';dialog.setAttribute('aria-labelledby','cms-update-title');
    var header=element('header'),title=element('h2','webman-cms 在线升级');title.id='cms-update-title';header.append(title);
    var close=element('button','关闭');close.type='button';close.onclick=function(){dialog.close();};header.append(close);dialog.append(header);
    var body=element('div',undefined,'cms-update-body'),source=element('div',undefined,'cms-update-source');
    var label=element('label','升级来源');sourceSelect=element('select');sourceSelect.id='cms-update-provider';label.htmlFor=sourceSelect.id;
    Object.keys(providers).forEach(function(key){var option=element('option',providers[key]);option.value=key;sourceSelect.append(option);});
    sourceSelect.onchange=function(){if(sourceSelect.value==='custom')editSource();else saveSource({provider:sourceSelect.value});};
    var configure=element('button','配置自定义源');configure.type='button';configure.dataset.updateConfigure='1';configure.onclick=editSource;
    source.append(label,sourceSelect,configure);body.append(source);
    ['versions','date','reason','error','checked','task'].forEach(function(name){var node=element('p');node.dataset.updateField=name;if(['task','error','reason'].includes(name)){node.setAttribute('role','status');node.setAttribute('aria-live','polite');}body.append(node);});
    var notes=element('pre');notes.dataset.updateField='notes';body.append(notes);dialog.append(body);
    var actions=element('footer'),check=element('button','重新检查');check.type='button';check.dataset.updateCheck='1';check.onclick=function(){checkUpdates(true);};
    var start=element('button','立即升级','cms-update-primary');start.type='button';start.dataset.updateStart='1';start.onclick=startUpgrade;actions.append(check,start);dialog.append(actions);
    dialog.addEventListener('close',function(){previous?.focus?.({preventScroll:true});});document.body.append(dialog);
}
function render(){
    if(!dialog)return;
    var busy=checking||!!info.checking,completed=task?.stage==='succeeded'&&successTask===task.id;
    function field(name,text){dialog.querySelector('[data-update-field="'+name+'"]').textContent=text||'';}
    field('versions','当前版本：'+(info.current||'读取中')+'　最新版本：'+(info.latest||(busy?'检查中…':info.checked_at?'暂无正式发布':'尚未获取')));
    field('date',info.published_at?'发布日期：'+new Date(info.published_at).toLocaleString():'');
    field('notes',info.notes||'暂无更新说明');field('error',busy?'':info.error);
    field('checked',info.checked_at?'上次成功检查：'+new Date(info.checked_at*1000).toLocaleString():'');
    field('reason',busy?'正在连接 '+(providers[info.source?.provider]||'升级源')+' 检查正式版本…':info.error?'本次检查未完成，可重新检查或手动切换来源。':info.has_update?(info.upgradeable?(info.allowed?'升级会短暂停止服务，请先保存正在编辑的内容。':'仅超级管理员可以执行升级。'):info.reason):info.checked_at?(info.latest?'当前已是最新正式版本。':'检查完成，暂无正式发布。'):'点击重新检查获取版本信息。');
    field('task',task?(task.message+(task.rollback_error?'；'+task.rollback_error:'')):'');
    sourceSelect.value=info.source?.provider||'gitee';sourceSelect.disabled=!info.allowed||!info.source?.writable||isBusy()||savingSource||busy;
    dialog.querySelector('[data-update-configure]').disabled=!info.allowed||!info.source?.writable||isBusy()||savingSource||busy;
    sourceSelect.title=info.source?.reason||'';
    dialog.querySelector('[data-update-check]').disabled=busy||isBusy()||savingSource;
    dialog.querySelector('[data-update-check]').textContent=busy?'检查中…':'重新检查';
    var start=dialog.querySelector('[data-update-start]');start.textContent=completed?'升级成功':starting?'正在启动…':'立即升级';start.disabled=completed||busy||savingSource||isBusy()||!info.has_update||!info.upgradeable||!info.allowed;
}
function open(){makeDialog();render();if(!dialog.open){previous=document.activeElement;dialog.showModal();}if(!info.current)checkUpdates(false);}
async function request(path,options){var response=await fetch('/web-admin/update/'+path,Object.assign({credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}},options||{}));var result;try{result=await response.json();}catch(_){throw new Error('服务暂时不可用，请稍后重试');}if(!response.ok||result.code)throw new Error(result.msg||'操作失败');return result.data;}
function post(values){return {method:'POST',headers:{Accept:'application/json','Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(Object.assign({},values,{csrf:info.csrf||''}))};}
async function checkUpdates(force){
    if(checking||savingSource)return;clearTimeout(checkTimer);checking=true;info.checking=true;notify();render();
    try{info=await request('check'+(force?'?force=1':''));notify();if(info.checking)checkTimer=setTimeout(function(){checkUpdates(false);},1500);if(info.task_id&&task?.id!==info.task_id)poll(info.task_id);}
    catch(error){info.checking=false;info.error=error.message;notify();}
    finally{checking=false;render();}
}
function makeSourceDialog(){
    if(sourceDialog)return;
    sourceDialog=element('dialog',undefined,'cms-update-dialog');sourceDialog.dataset.cmsUiOwned='1';sourceDialog.setAttribute('aria-label','自定义升级源');
    var header=element('header');header.append(element('h2','自定义升级源'));var close=element('button','关闭');close.type='button';close.onclick=function(){if(!savingSource)sourceDialog.close();};header.append(close);sourceDialog.append(header);
    var form=element('form');form.method='dialog';var body=element('div',undefined,'cms-update-body cms-update-source-form');
    [['custom_url','请求链接','url'],['custom_password','访问密码','password']].forEach(function(item){var label=element('label',item[1]),input=element('input');input.name=item[0];input.type=item[2];input.autocomplete=item[2]==='password'?'new-password':'off';input.maxLength=item[2]==='password'?72:2048;input.required=item[2]==='url';label.append(input);body.append(label);});
    var hint=element('p','仅接受 HTTPS。同一配置中密码留空则保留；密码不会返回页面。');body.append(hint);sourceError=element('p');sourceError.setAttribute('role','alert');body.append(sourceError);form.append(body);
    var footer=element('footer'),reset=element('button','重置'),cancel=element('button','取消'),save=element('button','保存并切换','cms-update-primary');reset.type=cancel.type='button';save.type='submit';reset.onclick=fillSource;cancel.onclick=function(){sourceDialog.close();};footer.append(reset,cancel,save);form.append(footer);sourceDialog.append(form);
    form.onsubmit=async function(event){event.preventDefault();var url=form.elements.custom_url.value.trim(),password=form.elements.custom_password.value;if((!info.source?.password_configured||url!==info.source?.custom_url)&&!password){sourceError.textContent='请为此请求链接填写访问密码';return;}await saveSource({provider:'custom',custom_url:url,custom_password:password});};
    sourceDialog.addEventListener('cancel',function(event){if(savingSource)event.preventDefault();});sourceDialog.addEventListener('close',function(){sourceDialog.querySelector('[name="custom_password"]').value='';render();dialog?.querySelector('[data-update-configure]')?.focus({preventScroll:true});});document.body.append(sourceDialog);
}
function fillSource(){var form=sourceDialog.querySelector('form');form.elements.custom_url.value=info.source?.custom_url||'';form.elements.custom_password.value='';form.elements.custom_password.placeholder=info.source?.password_configured?'已设置，留空保留':'请输入访问密码';sourceError.textContent='';}
async function editSource(){if(!info.allowed||isBusy()||savingSource)return;try{info.source=await request('source');makeSourceDialog();fillSource();if(!sourceDialog.open)sourceDialog.showModal();}catch(error){info.error=error.message;render();}}
async function saveSource(values){
    if(savingSource||isBusy())return;savingSource=true;clearTimeout(checkTimer);render();sourceDialog?.querySelectorAll('button,input').forEach(function(node){node.disabled=true;});
    var saved=false;
    try{info.source=await request('source',post(values));info.has_update=false;info.latest=null;info.release_id=null;info.notes='';info.error='';info.checked_at=null;task=null;saved=true;sourceDialog?.close();notify();}
    catch(error){if(sourceDialog?.open)sourceError.textContent=error.message;else info.error=error.message;}
    finally{savingSource=false;sourceDialog?.querySelectorAll('button,input').forEach(function(node){node.disabled=false;});render();}
    if(saved)checkUpdates(true);
}
async function startUpgrade(){
    if(starting||checking||savingSource||!info.has_update||!info.allowed||!info.upgradeable||isBusy())return;
    var dirty=window.CmsUI?.isDirty?.()||false;
    try{document.querySelectorAll('iframe').forEach(function(frame){if(frame.contentWindow?.CmsUI?.isDirty?.())dirty=true;});}catch(_){dirty=true;}
    if(dirty){info.error='请先保存或关闭正在编辑的页面，再执行升级。';render();return;}
    var confirmed=window.CmsUI?.confirm?await window.CmsUI.confirm('立即升级到 '+info.latest+'？升级期间服务会短暂停止。'):window.confirm('立即升级到 '+info.latest+'？升级期间服务会短暂停止。');
    if(!confirmed)return;starting=true;info.error='';render();
    try{task=await request('start',post({release_id:info.release_id}));rememberTask(task.id);poll(task.id);}
    catch(error){info.error=error.message;}
    finally{starting=false;render();}
}
function newer(a,b){if(!a||!b)return false;var x=String(a).replace(/^v/,'').split('.').map(Number),y=String(b).replace(/^v/,'').split('.').map(Number);for(var i=0;i<3;i++){if(x[i]!==y[i])return x[i]>y[i];}return false;}
function succeeded(value){
    var key='cms-update-success:'+value.id,seen=handled.has(key);try{seen=seen||localStorage.getItem(key)==='1';}catch(_){}
    if(seen)return;
    if(!info.current||!newer(info.current,value.version))info.current=value.version;
    if(!info.latest)info.latest=value.version;
    info.has_update=newer(info.latest,info.current);info.upgradeable=false;info.error='';successTask=value.id;
    value.message='升级成功，2 秒后关闭弹窗并刷新工作台';notify();render();
    clearTimeout(successTimer);successTimer=setTimeout(function(){if(dialog?.open)dialog.close();window.CmsNavigation?.refreshDashboard?.();successTask=null;task=null;checkUpdates(false);},2000);
    handled.add(key);activeTasks.delete(value.id);try{sessionStorage.removeItem('cms-update-active-task');}catch(_){}try{localStorage.setItem(key,'1');}catch(_){}
}async function poll(id){
    clearTimeout(pollTimer);
    try{task=await request('status?id='+encodeURIComponent(id));if(terminal(task)){if(task.stage==='succeeded'&&activeTasks.has(task.id))succeeded(task);render();return;}rememberTask(task.id);render();}
    catch(_){if(!task)task={id:id,stage:'reconnecting',message:'正在恢复升级任务连接，请稍候…'};else task.message='服务正在切换，正在重新连接原升级任务…';render();}
    pollTimer=setTimeout(function(){poll(id);},2000);
}
window.CmsUpdate={open:open,isBusy:isBusy,subscribe:function(listener){listeners.push(listener);listener(info);return function(){listeners=listeners.filter(function(item){return item!==listener;});};}};
window.addEventListener('pagehide',function(){clearTimeout(pollTimer);clearTimeout(checkTimer);clearTimeout(successTimer);});
window.CmsUpdate.subscribe(badge);checkUpdates(false);
})(window,document);
