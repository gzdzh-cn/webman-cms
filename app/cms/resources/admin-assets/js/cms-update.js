/* Shared login-scoped update prompt and resumable maintenance task viewer. */
(function(window,document){
'use strict';
function badge(info){
    document.querySelectorAll('[data-info-key="admin"] dd').forEach(function(row){
        var button=row.querySelector('[data-cms-update]');
        if(!button){button=document.createElement('button');button.type='button';button.dataset.cmsUpdate='1';button.className='cms-update-badge';row.insertBefore(button,row.querySelector('small'));button.addEventListener('click',function(){window.CmsUpdate.open(true);});}
        button.textContent=info.checking?'检查中…':info.has_update?'新版本':'检查更新';button.classList.toggle('is-new',!!info.has_update);button.classList.toggle('is-checking',!!info.checking);button.setAttribute('aria-busy',String(!!info.checking));
    });
}
try{if(window.parent!==window&&window.parent.CmsUpdate){window.CmsUpdate=window.parent.CmsUpdate;window.CmsUpdate.subscribe(badge);return;}}catch(_){}
var info={},listeners=[],dialog,previous,task=null,pollTimer,checking=false,starting=false;
function element(tag,text,cls){var node=document.createElement(tag);if(text!==undefined)node.textContent=text;if(cls)node.className=cls;return node;}
function notify(){listeners.forEach(function(listener){listener(info);});}
function makeDialog(){
    if(dialog)return;
    dialog=element('dialog',undefined,'cms-update-dialog');dialog.dataset.cmsUiOwned='1';dialog.setAttribute('aria-labelledby','cms-update-title');
    var header=element('header');var title=element('h2','webman-cms 在线升级');title.id='cms-update-title';header.appendChild(title);
    var close=element('button','关闭');close.type='button';close.addEventListener('click',function(){dialog.close();});header.appendChild(close);dialog.appendChild(header);
    var body=element('div',undefined,'cms-update-body');
    ['versions','date','reason','error','checked','task'].forEach(function(name){var node=element('p');node.dataset.updateField=name;if(name==='task'||name==='error'||name==='reason'){node.setAttribute('role','status');node.setAttribute('aria-live','polite');}body.appendChild(node);});
    var notes=element('pre');notes.dataset.updateField='notes';body.appendChild(notes);dialog.appendChild(body);
    var actions=element('footer');var check=element('button','重新检查');check.type='button';check.dataset.updateCheck='1';check.addEventListener('click',function(){checkUpdates(true);});
    var start=element('button','立即升级','cms-update-primary');start.type='button';start.dataset.updateStart='1';start.addEventListener('click',startUpgrade);actions.appendChild(check);actions.appendChild(start);dialog.appendChild(actions);
    dialog.addEventListener('close',function(){previous?.focus?.({preventScroll:true});});document.body.appendChild(dialog);
}
function render(){
    if(!dialog)return;
    var busy=checking||!!info.checking;
    function field(name,text){dialog.querySelector('[data-update-field="'+name+'"]').textContent=text||'';}
    field('versions','当前版本：'+(info.current||'读取中')+'　最新版本：'+(info.latest||(busy?'检查中…':info.checked_at?'暂无正式发布':'尚未获取')));
    field('date',info.published_at?'发布日期：'+new Date(info.published_at).toLocaleString():'');
    field('notes',info.notes||'暂无更新说明');field('error',busy?'':info.error);
    field('checked',info.checked_at?'上次成功检查：'+new Date(info.checked_at*1000).toLocaleString():'');
    field('reason',busy?'正在连接 Gitee 检查正式版本，请稍候…':info.error?'本次检查未完成，请重新检查。以下保留上次成功获取的信息。':info.has_update?(info.upgradeable?(info.allowed?'升级会短暂停止服务，请先保存正在编辑的内容。':'仅超级管理员可以执行升级。'):info.reason):info.checked_at?(info.latest?'当前已是最新正式版本。':'检查完成，官方仓库暂无正式发布。'):'点击重新检查获取版本信息。');
    field('task',task?(task.message+(task.rollback_error?'；'+task.rollback_error:'')):'');
    dialog.querySelector('[data-update-check]').disabled=busy||starting;
    dialog.querySelector('[data-update-check]').textContent=busy?'检查中…':'重新检查';
    dialog.querySelector('[data-update-start]').disabled=starting||!!(task&&!terminal(task))||!info.has_update||!info.upgradeable||!info.allowed;
}
function open(refresh){makeDialog();render();if(!dialog.open){previous=document.activeElement;dialog.showModal();}if(refresh||!info.current)checkUpdates(!!refresh);}
function terminal(value){return ['succeeded','failed','rolled_back'].indexOf(value.stage)!==-1;}
async function request(path,options){var response=await fetch('/web-admin/update/'+path,Object.assign({credentials:'same-origin',headers:{Accept:'application/json'}},options||{}));if(!response.ok)throw new Error('服务暂时不可用，请稍后重试');var result=await response.json();if(result.code)throw new Error(result.msg||'操作失败');return result.data;}
async function checkUpdates(force){
    if(checking)return;checking=true;info.checking=true;notify();render();
    try{
        info=await request('check'+(force?'?force=1':''));notify();if(info.checking)setTimeout(function(){checkUpdates(false);},1500);
        if(info.task_id&&!task)poll(info.task_id);
        if(info.allowed&&info.has_update){var key='cms-update-login:'+info.login_key;var seen=false;try{seen=localStorage.getItem(key)==='1';if(!seen)localStorage.setItem(key,'1');}catch(_){seen=sessionStorage.getItem(key)==='1';sessionStorage.setItem(key,'1');}if(!seen)open();}
    }catch(error){info.checking=false;info.error=error.message;notify();}
    finally{checking=false;render();}
}
async function startUpgrade(){
    if(starting||!info.allowed||!info.upgradeable)return;
    var dirty=window.CmsUI?.isDirty?.()||false;
    try{document.querySelectorAll('iframe').forEach(function(frame){if(frame.contentWindow?.CmsUI?.isDirty?.())dirty=true;});}catch(_){}
    if(dirty){info.error='请先保存或关闭正在编辑的页面，再执行升级。';render();return;}
    var confirmed=window.CmsUI?.confirm?await window.CmsUI.confirm('立即升级到 '+info.latest+'？升级期间服务会短暂停止。'):window.confirm('立即升级到 '+info.latest+'？升级期间服务会短暂停止。');
    if(!confirmed)return;starting=true;render();
    try{task=await request('start',{method:'POST',headers:{Accept:'application/json','Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({release_id:info.release_id,csrf:info.csrf})});poll(task.id);}
    catch(error){info.error=error.message;}
    finally{starting=false;render();}
}
async function poll(id){
    clearTimeout(pollTimer);
    try{task=await request('status?id='+encodeURIComponent(id));render();if(terminal(task)){if(task.stage==='succeeded'){info.current=task.version;info.has_update=false;notify();}return;}}
    catch(_){if(!task)task={id:id,stage:'reconnecting',message:'正在恢复升级任务连接，请稍候…'};else task.message='服务正在切换，正在重新连接原升级任务…';render();}
    pollTimer=setTimeout(function(){poll(id);},2000);
}
window.CmsUpdate={open:open,subscribe:function(listener){listeners.push(listener);listener(info);}};
window.CmsUpdate.subscribe(badge);checkUpdates(false);
})(window,document);
