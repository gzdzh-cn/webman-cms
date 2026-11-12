(function(window,document){
    'use strict';
    const $=id=>document.getElementById(id),query=new URLSearchParams(location.search),edit=$('edit-form'),groupForm=$('group-form');
    const types={0:'单行文本',1:'单行文本',2:'多行文本',3:'图片',4:'开关',5:'多媒体'};
    let deleting=false;
    let lang=query.get('lang')||'',page=Math.max(1,Number(query.get('page'))||1),total=0,records=[],metadata=null,busy=false,loaded=false,deleteAction=null,trigger=null;
    $('q').value=query.get('q')||'';
    function node(tag,text){const item=document.createElement(tag);if(text!==undefined)item.textContent=text;return item;}
    function message(text,error=false){$('status').textContent=text;$('status').dataset.error=error?'1':'0';}
    function field(name){return edit.elements.namedItem(name);}
    function safeImage(value){try{const url=new URL(value,location.origin);return value&&['http:','https:'].includes(url.protocol)?url.href:'';}catch(_){return '';}}
    function pendingRequest(){const owners=Array.from(document.querySelectorAll('dialog[open]'));owners.forEach(owner=>{owner.dataset.cmsSubmitBusy='';owner.setAttribute('aria-busy','true');owner.dataset.cmsPending=String(Number(owner.dataset.cmsPending||0)+1)});return ()=>owners.forEach(owner=>{const remaining=Number(owner.dataset.cmsPending||1)-1;if(remaining>0)owner.dataset.cmsPending=String(remaining);else{delete owner.dataset.cmsPending;delete owner.dataset.cmsSubmitBusy;owner.removeAttribute('aria-busy')}});}
    async function api(...args){return args[2]===true&&['save','groupSave'].includes(args[0])?window.CmsUI.persist(()=>rawSaveRequest(...args)):rawSaveRequest(...args);}
async function rawSaveRequest(action,data={},post=false){const done=post?pendingRequest():()=>{};try{const response=await fetch('/web-admin/globalvariable/'+action+(post?'':'?'+new URLSearchParams(data)),{method:post?'POST':'GET',cache:'no-store',credentials:'same-origin',headers:{Accept:'application/json'},body:post?data:undefined});let result;try{result=await response.json();}catch(_){throw Error('读取失败，请检查登录状态后重试');}if(result.code!==0)throw Error(result.msg||'操作失败');return result;}finally{done();}}
    function permissions(){return metadata?.permissions||{};}
    function canGroupWrite(){return permissions().save&&metadata?.lang===metadata?.main_lang;}
    function controls(){
        $('create').disabled=busy||!loaded||!permissions().save;$('manage-groups').disabled=busy||!loaded||!metadata;
        $('find').disabled=busy;$('reset').disabled=busy;$('lang').disabled=busy||!metadata;$('type_id').disabled=busy||!metadata;
        $('previous').disabled=busy||page<=1;$('next').disabled=busy||page*20>=total;
        edit.querySelectorAll('input,select,textarea,button').forEach(item=>item.disabled=busy||(!permissions().save&&item.type!=='button'));
        $('edit-save').hidden=!permissions().save;
        groupForm.querySelectorAll('input,button').forEach(item=>item.disabled=busy||!canGroupWrite());
        $('delete-execute').disabled=busy||deleting;
        document.querySelectorAll('[data-close]').forEach(button=>button.disabled=busy||deleting);
    }
    function image(value,alt){const img=node('img');img.alt=alt;img.src=safeImage(value);img.addEventListener('error',()=>img.remove());return img;}
    async function copy(row){const value=row.tag;if(!value){message('当前变量没有可用标签',true);return;}try{await navigator.clipboard.writeText(value);message('标签已复制：'+value);}catch(_){message('标签：'+value);}}
    function draw(){
        $('rows').replaceChildren();
        if(!records.length){const row=node('tr'),cell=node('td','暂无自定义变量');cell.colSpan=7;row.append(cell);$('rows').append(row);}
        records.forEach(record=>{
            const row=node('tr'),value=node('td'),text=node('span',String(record.attr_value??record.value??''));text.className='value-text';text.title=text.textContent;
            if(Number(record.attr_input_type)===3&&safeImage(text.textContent))value.append(image(text.textContent,record.attr_name));else value.append(text);
            const variable=node('td');variable.append(node('code',record.attr_var_name));
            const actions=node('td'),editButton=node('button',permissions().save?'编辑':'查看');editButton.type='button';editButton.addEventListener('click',()=>openEditor(record));const label=node('button','复制标签');label.type='button';label.addEventListener('click',()=>copy(record));actions.append(editButton,label);
            if(permissions().delete){const remove=node('button','删除');remove.className='danger';remove.type='button';remove.addEventListener('click',()=>confirmDelete('回收变量：'+record.attr_name+' ('+record.attr_var_name+')\n同名变量的各语言记录一起进入回收站，保留原值。',async()=>{const body=new FormData();body.append('ids[]',record.attr_id);await api('delete',body,true);await load(false);}));actions.append(remove);}
            row.append(node('td',record.attr_name),variable,node('td',types[record.attr_input_type]||'类型 '+record.attr_input_type),value,node('td',metadata.groups.find(item=>String(item.id)===String(record.type_id))?.type_name||'未分组'),node('td',record.sort_order),actions);$('rows').append(row);
        });$('page').textContent='第 '+page+' 页 · 共 '+total+' 条';controls();
    }
    async function load(refreshGroups=true){
        if(busy)return;loaded=false;busy=true;controls();message('正在读取变量…');
        try{
            if(refreshGroups||!metadata){const group=await api('groups',lang?{lang}:{});metadata=group.data;lang=metadata.lang||lang;const selected=$('type_id').value||query.get('type_id')||'';
                $('lang').replaceChildren(...metadata.languages.map(item=>new Option(item.title+' ('+item.mark+')',item.mark,false,item.mark===lang)));
                $('type_id').replaceChildren(new Option('全部分组',''),...metadata.groups.map(item=>new Option(item.type_name,item.id)));$('type_id').value=selected;
            }
            const result=await api('select',{lang,page,limit:20,q:$('q').value,type_id:$('type_id').value});records=result.data;total=result.count;loaded=true;draw();
            window.CmsModuleNavigation.remember({lang,page,q:$('q').value,type_id:$('type_id').value});message('');
        }catch(error){loaded=false;records=[];total=0;draw();message(error.message,true);}finally{busy=false;controls();}
    }
    function valueControl(value){
        const container=$('value-control'),type=Number(field('attr_input_type').value);container.replaceChildren();
        const input=node(type===2?'textarea':type===4?'select':'input');input.name='attr_value';input.setAttribute('aria-label','变量值');
        if(type===4){input.append(new Option('关闭','0'),new Option('开启','1'));input.value=String(value)==='1'?'1':'0';}else{input.value=String(value??'');if(input.tagName==='INPUT')input.type='text';}container.append(input);
        if(type===3||type===5){
            const preview=node('div'),show=()=>{preview.replaceChildren();if(type===3&&safeImage(input.value))preview.append(image(input.value,'变量图片预览'));};show();input.addEventListener('input',show);container.append(preview);
            if(permissions().upload&&permissions().save){const upload=node('input');upload.type='file';upload.setAttribute('aria-label',type===3?'上传变量图片':'上传多媒体');if(type===3)upload.accept='.jpg,.jpeg,.png,.gif,.webp,.bmp';
                upload.addEventListener('change',async()=>{if(busy||!upload.files[0])return;busy=true;const done=pendingRequest();window.CmsUI.dirty(edit);controls();try{const body=new FormData();body.append('file',upload.files[0]);const response=await fetch('/web-admin/upload/file',{method:'POST',credentials:'same-origin',body}),result=await response.json();if(result.code!==0)throw Error(result.msg||'上传失败');input.value=result.data.url;edit.dataset.cmsDirty='1';show();}catch(error){$('edit-error').textContent=error.message;}finally{done();busy=false;upload.value='';controls();}});container.append(upload);}
        }
    }
    function openEditor(record={}){
        edit.reset();window.CmsModuleNavigation.markClean(edit);trigger=document.activeElement;$('edit-title').textContent=record.attr_id?'编辑变量':'新增变量';
        field('type_id').replaceChildren(new Option('未分组','0'),...metadata.groups.map(item=>new Option(item.type_name,item.id)));
        const values={attr_id:'',lang,inc_type:'web',attr_name:'',attr_var_name:'',attr_input_type:'0',type_id:$('type_id').value||'0',sort_order:'100',...record};
        Object.entries(values).forEach(([name,value])=>{const input=field(name);if(input)input.value=value??'';});
        valueControl(record.attr_value??record.value??'');$('edit-error').textContent='';controls();$('edit-dialog').showModal();
    }
    field('attr_input_type').addEventListener('change',()=>valueControl(field('attr_value')?.value||''));
    edit.addEventListener('submit',async event=>{event.preventDefault();if(busy||!permissions().save)return;const body=new FormData(edit);busy=true;controls();$('edit-error').textContent='';try{await api('save',body,true);window.CmsModuleNavigation.markClean(edit);$('edit-dialog').close();busy=false;await load();message('');}catch(error){$('edit-error').textContent=error.message;}finally{busy=false;controls();}});
    function drawGroups(){
        $('group-rows').replaceChildren(...metadata.groups.map(item=>{const row=node('tr'),actions=node('td');if(canGroupWrite()){const edit=node('button','编辑');edit.type='button';edit.addEventListener('click',()=>{groupForm.elements.namedItem('id').value=item.id;groupForm.elements.namedItem('type_name').value=item.type_name;groupForm.elements.namedItem('sort_order').value=item.sort_order;$('group-save').textContent='保存分组';});actions.append(edit);}if(permissions().delete&&metadata.lang===metadata.main_lang){const remove=node('button','删除');remove.className='danger';remove.type='button';remove.addEventListener('click',()=>confirmDelete('永久删除空分组：'+item.type_name+' (#'+item.id+')\n删除后无法通过回收站恢复；含有变量的分组不能删除。',async()=>{const body=new FormData();body.append('ids[]',item.id);body.set('lang',lang);await api('groupDelete',body,true);await reloadGroups();}));actions.append(remove);}row.append(node('td',item.type_name),node('td',item.sort_order),actions);return row;}));controls();
    }
    async function reloadGroups(){const response=await api('groups',{lang});metadata=response.data;drawGroups();const selected=$('type_id').value;$('type_id').replaceChildren(new Option('全部分组',''),...metadata.groups.map(item=>new Option(item.type_name,item.id)));$('type_id').value=selected;}
    $('manage-groups').addEventListener('click',()=>{trigger=document.activeElement;groupForm.reset();window.CmsModuleNavigation.markClean(groupForm);$('group-save').textContent='新增分组';$('group-error').textContent='';drawGroups();$('groups-dialog').showModal();});
    groupForm.addEventListener('submit',async event=>{event.preventDefault();if(busy||!permissions().save)return;const body=new FormData(groupForm);body.set('id',body.get('id')||'0');body.set('lang',lang);busy=true;controls();$('group-error').textContent='';try{await api('groupSave',body,true);groupForm.reset();window.CmsModuleNavigation.markClean(groupForm);$('group-save').textContent='新增分组';await reloadGroups();$('group-error').textContent='';}catch(error){$('group-error').textContent=error.message;}finally{busy=false;controls();}});
    $('group-reset').addEventListener('click',async()=>{if(window.CmsUI.isDirty(groupForm)&&!await window.CmsUI.confirm('放弃当前分组修改并准备新增分组？',{title:'还原分组表单',confirmText:'放弃修改',cancelText:'继续编辑'}))return;groupForm.reset();window.CmsModuleNavigation.markClean(groupForm);$('group-save').textContent='新增分组';});
    function confirmDelete(description,action){deleteAction=action;$('delete-description').textContent=description;$('delete-error').textContent='';$('delete-dialog').showModal();}
    $('delete-execute').addEventListener('click',async()=>{if(busy||deleting||!deleteAction)return;const action=deleteAction;deleting=true;controls();try{await action();$('delete-dialog').close();deleteAction=null;message('操作已完成');}catch(error){$('delete-error').textContent=error.message;}finally{deleting=false;busy=false;controls();}});
    document.querySelectorAll('[data-close]').forEach(button=>button.addEventListener('click',()=>{if(!busy&&!deleting)$(button.dataset.close).close();}));
    document.querySelectorAll('dialog').forEach(dialog=>{dialog.addEventListener('cancel',event=>{if(busy||deleting)event.preventDefault();});dialog.addEventListener('close',()=>{if(trigger?.isConnected)trigger.focus();});});
    $('filters').addEventListener('submit',event=>{event.preventDefault();page=1;load(false);});$('reset').addEventListener('click',()=>{$('q').value='';$('type_id').value='';page=1;load(false);});
    $('lang').addEventListener('change',()=>{lang=$('lang').value;page=1;load();});$('type_id').addEventListener('change',()=>{page=1;load(false);});
    $('previous').addEventListener('click',()=>{page--;load(false);});$('next').addEventListener('click',()=>{page++;load(false);});$('create').addEventListener('click',()=>openEditor());load();
})(window,document);
