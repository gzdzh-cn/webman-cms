(function(window,document){
    'use strict';
    const $=id=>document.getElementById(id), form=$('settings'), requested=new URL(window.location.href);
    const groups=['web','core','attachment','image','api'];
    const group=groups.includes(requested.searchParams.get('group'))?requested.searchParams.get('group'):'web';
    let state=null,busy=false,failed=false,sequence=0,selectedLang=requested.searchParams.get('lang')||'';
    function element(tag,text,cls){const node=document.createElement(tag);if(text!==undefined)node.textContent=text;if(cls)node.className=cls;return node;}
    function message(text,error=false){$('status').textContent=text;$('status').dataset.error=error?'1':'0';}
    function clearModeError(){
        const row=form.querySelector('[data-field="web_cmsmode"]');
        if(!row)return;
        row.classList.remove('cms-basic-row-error');
        row.querySelector('.cms-basic-mode-error')?.remove();
        row.querySelector('[role="radiogroup"]')?.removeAttribute('aria-describedby');
    }
    function showModeError(text){
        clearModeError();
        const row=form.querySelector('[data-field="web_cmsmode"]');
        if(!row)return;
        row.classList.add('cms-basic-row-error');
        const error=element('p',text,'cms-basic-mode-error');error.id='cms-mode-error';error.setAttribute('role','alert');
        row.querySelector('.cms-basic-control')?.append(error);
        row.querySelector('[role="radiogroup"]')?.setAttribute('aria-describedby',error.id);
        row.scrollIntoView({behavior:'smooth',block:'center'});
        const radio=row.querySelector('input:checked')||row.querySelector('input');
        radio?.focus({preventScroll:true});
    }
    function safeHref(value){try{const url=new URL(value,location.origin);return url.origin===location.origin&&url.pathname.startsWith('/web-admin/')?url.pathname+url.search:null;}catch(_){return null;}}
    function imageHref(value){try{const url=new URL(value,location.origin);return value&&['http:','https:'].includes(url.protocol)?url.href:'';}catch(_){return '';}}
    function currentGroup(){return state?.groups.find(item=>item.id===group);}
    function writable(field){return !!state?.permissions.save&&!field.readonly&&!field.readOnly&&!(field.global&&state.lang!==state.main_lang);}
    function controls(){
        $('lang').disabled=busy||!state;$('reset').disabled=busy;
        form.querySelectorAll('[data-setting]').forEach(node=>{const field=currentGroup()?.fields.find(item=>item.name===node.dataset.setting);node.disabled=busy||failed||!field||!writable(field);});
        $('save').disabled=busy||failed||!state?.permissions.save;form.setAttribute('aria-busy',String(busy));form.toggleAttribute('data-cms-submit-busy',busy);
    }
    async function api(action,input,post=false){const response=await fetch('/web-admin/websiteconfig/'+action+(post?'':'?'+new URLSearchParams(input)),{method:post?'POST':'GET',credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'},body:post?input:undefined});let result;try{result=await response.json();}catch(_){throw Error('读取失败，请检查登录状态后重试');}if(result.code!==0)throw Error(result.msg||'操作失败');return result;}
    function updateVisibility(){
        const value=name=>form.elements.namedItem(name)?.value;
        form.querySelectorAll('[data-field]').forEach(row=>{
            const name=row.dataset.field;
            if(['web_status_mode','web_status_text','web_status_url','web_status_tpl'].includes(name))row.hidden=value('web_status')!=='1'||(name!=='web_status_mode'&&name!==({0:'web_status_text',1:'web_status_url',2:'web_status_tpl'}[value('web_status_mode')]));
            if(name==='web_mobile_domain')row.hidden=value('web_mobile_domain_open')!=='1';
            if(name==='basic_img_alt_force')row.hidden=value('basic_img_alt')!=='1';
            if(['mark_type','mark_width','mark_height','mark_txt','mark_img','mark_txt_size','mark_txt_color','mark_degree','mark_quality','mark_sel','is_thumb_mark'].includes(name))row.hidden=value('is_mark')!=='1'||(['mark_txt','mark_txt_size','mark_txt_color'].includes(name)&&value('mark_type')!=='text')||(name==='mark_img'&&value('mark_type')!=='img');
            if(['thumb_width','thumb_height','thumb_mode','thumb_color'].includes(name))row.hidden=value('thumb_open')!=='1';
        });
    }
    function createControl(field){
        const wrap=element('div',undefined,'cms-basic-control'),id='basic-'+field.name,value=field.value??'';
        const register=node=>{node.name=field.name;node.id=id;node.dataset.setting=field.name;return node;};
        if(field.type==='radio'){
            const choices=element('div',undefined,'cms-basic-options');choices.setAttribute('role','radiogroup');choices.setAttribute('aria-labelledby','label-'+field.name);
            (field.options||[]).forEach((option,index)=>{const input=register(element('input'));input.type='radio';input.id=id+'-'+index;input.value=option.value;input.checked=String(option.value)===String(value);const label=element('label');label.append(input,document.createTextNode(option.label));choices.append(label);});wrap.append(choices);
        }else if(field.type==='checkbox'){
            const label=element('label',undefined,'cms-basic-options'),input=register(element('input'));input.type='checkbox';input.value='1';input.checked=String(value)==='1';label.append(input,document.createTextNode(field.option_label||'开启'));wrap.append(label);
        }else if(field.type==='readonly'||field.type==='link'){
            const text=element('span',String(value), 'cms-basic-readonly');wrap.append(text);
            const href=safeHref(field.href);if(href){const link=element('a',field.link_label||'前往设置 →');link.href=href;link.dataset.cmsNavigation='';wrap.append(link);}
        }else{
            const input=register(element(field.type==='textarea'?'textarea':field.type==='select'?'select':'input'));
            if(field.type==='select')(field.options||[]).forEach(option=>input.append(new Option(option.label,option.value)));
            else if(input.tagName==='INPUT')input.type=['number','password'].includes(field.type)?field.type:'text';
            input.value=String(value);if(field.placeholder)input.placeholder=field.placeholder;if(field.maxlength)input.maxLength=field.maxlength;
            if(field.type==='number'){if(field.min!==undefined)input.min=field.min;if(field.max!==undefined)input.max=field.max;if(field.step!==undefined)input.step=field.step;}
            if(field.type==='password'){input.autocomplete='new-password';if(field.configured)input.placeholder='已配置；留空保留';}
            if(field.type==='image'){
                const image=element('img');image.alt=field.label+'预览';const preview=()=>{const src=imageHref(input.value);image.hidden=!src;if(src)image.src=src;else image.removeAttribute('src');};image.addEventListener('error',()=>{image.hidden=true;});input.addEventListener('input',preview);preview();
                const line=element('div',undefined,'cms-basic-image');line.append(input,image);
                if(state.permissions.upload){const upload=register(element('input'));upload.type='file';upload.name='';upload.removeAttribute('id');upload.setAttribute('aria-label','上传'+field.label);upload.accept=field.name==='web_ico'?'.ico,.png,.jpg,.jpeg,.gif,.svg':'.jpg,.jpeg,.png,.gif,.webp,.bmp';
                    upload.addEventListener('change',async()=>{if(!upload.files[0]||busy)return;const body=new FormData();body.append('file',upload.files[0]);if(field.name==='web_ico')body.append('purpose','site-icon');busy=true;controls();try{const response=await fetch('/web-admin/upload/file',{method:'POST',credentials:'same-origin',body}),result=await response.json();if(result.code!==0)throw Error(result.msg||'上传失败');input.value=result.data.url;form.dataset.cmsDirty='1';preview();message('上传完成，保存配置后生效');}catch(error){message(error.message,true);}finally{upload.value='';busy=false;controls();}});line.append(upload);}wrap.append(line);
            }else wrap.append(input);
            if(field.name==='web_description'){const count=element('small');const update=()=>{count.textContent='已输入 '+Array.from(input.value).length+' 个字符';};input.addEventListener('input',update);update();wrap.append(count);}
        }
        if(field.help)wrap.append(element('small',field.help));
        if(field.global&&state.lang!==state.main_lang)wrap.append(element('small','共享配置，请切换到主体语言修改。'));
        return wrap;
    }
    function draw(){
        $('lang').replaceChildren(...state.languages.map(item=>new Option(item.title+' ('+item.mark+')',item.mark,false,item.mark===state.lang)));
        const selected=currentGroup();if(!selected)throw Error('当前设置分区不可用');
        form.setAttribute('aria-label',selected.title);$('scope-note').textContent=state.lang===state.main_lang?'按分区保存，保留其他配置':'共享配置只读；网站信息按当前语言保存';
        $('fields').replaceChildren(...(selected.fields||[]).flatMap(field=>{
            const row=element('div',undefined,'cms-basic-row');row.dataset.field=field.name;const label=element('label',field.label,'cms-basic-label');label.id='label-'+field.name;label.htmlFor='basic-'+field.name;row.append(label,createControl(field));
            const tag=element('div',undefined,'cms-basic-tag');
            if(field.tag&&field.copy!==false&&field.type!=='password'&&!['readonly','link'].includes(field.type)){
                const code=element('code',field.name);code.title=field.tag;tag.append(code);
                const copy=element('button','复制标签');copy.type='button';copy.addEventListener('click',async()=>{const text=field.tag;try{await window.navigator.clipboard.writeText(text);message('标签已复制：'+text);}catch(_){message('标签：'+text);}});tag.append(copy);
            }
            row.append(tag);
            const title=group==='web'?({web_title:'首页 TDK 信息',web_thirdcode_pc:'第三方代码'})[field.name]:group==='image'?({is_mark:'新上传图片水印',thumb_open:'模板缩略图'})[field.name]:null;
            return title?[element('h2',title,'cms-basic-section'),row]:[row];
        }));
        const links=selected.links||(group==='api'?state.links:[])||[];
        $('integration-links').replaceChildren(...links.filter(item=>safeHref(item.href)).map(item=>{const link=element('a',undefined,'cms-basic-integration');link.href=safeHref(item.href);link.dataset.cmsNavigation='';const text=element('span',item.title);if(item.help||item.description)text.append(element('small',item.help||item.description));link.append(text,element('span','→'));return link;}));
        $('save').closest('footer').hidden=!(selected.fields||[]).some(field=>writable(field)&&!['readonly','link'].includes(field.type));
        updateVisibility();controls();
    }
    async function load(lang=selectedLang){
        const stamp=++sequence;busy=true;controls();message('正在读取设置…');
        try{const result=await api('settings',{lang,group});if(stamp!==sequence)return;state=result.data;failed=false;selectedLang=state.lang;draw();window.CmsModuleNavigation.remember({lang:state.lang,group});window.CmsModuleNavigation.markClean(form);message(state.permissions.save?'':'当前账号仅可查看设置。');}
        catch(error){if(stamp===sequence){failed=true;message(error.message,true);}}finally{if(stamp===sequence){busy=false;controls();}}
    }
    form.addEventListener('change',event=>{updateVisibility();if(event.target.name==='web_cmsmode')clearModeError();});
    form.addEventListener('submit',async event=>{event.preventDefault();if(busy||failed||!state?.permissions.save)return;
        const body=new FormData();body.set('lang',state.lang);body.set('group',group);
        let changed=0;
        (currentGroup()?.fields||[]).filter(field=>writable(field)&&!['readonly','link'].includes(field.type)).forEach(field=>{
            const control=form.elements.namedItem(field.name);if(!control)return;
            const value=field.type==='checkbox'?(control.checked?'1':'0'):control.value;
            if(field.type==='password'?value!=='':String(value)!==String(field.value??'')){body.set(field.name,value);changed++;}
        });
        if(!changed){window.CmsModuleNavigation.markClean(form);message('当前配置没有修改。');return;}
        const newMode=body.get('web_cmsmode');
        const oldMode=String(currentGroup()?.fields.find(field=>field.name==='web_cmsmode')?.value??'');
        const names={'1':'运营模式','2':'开发模式','3':'Redis 缓存模式'};
        const ui=window.top.CmsUI||window.CmsUI;
        let progress=null,modeError=null;
        // Lock the form before the asynchronous confirmation so repeated submissions cannot race.
        busy=true;controls();
        try{
            if(newMode!==null){
                const resetSession=(newMode==='3')!==(oldMode==='3');
                const detail=resetSession?'切换后需要重新登录。':'保存后网站将按新模式运行。';
                const approved=await ui.confirm('将网站模式从“'+(names[oldMode]||oldMode)+'”切换为“'+(names[newMode]||newMode)+'”。'+detail+'\n确定继续吗？',{
                    title:'确认切换网站模式',confirmText:'确认切换',cancelText:'取消',icon:'switch'
                });
                if(!approved)return;
                clearModeError();
                progress=ui.progress({title:newMode==='3'?'正在验证 Redis 配置':'正在切换网站模式',message:newMode==='3'?'正在检查连接和缓存读写能力，请稍候…':'正在保存设置，请稍候…'});
            }
            message('正在保存…');
            const result=await api('configure',body,true);
            if(group==='web'){
                const name=result.data?.groups?.find(item=>item.id==='web')?.fields?.find(field=>field.name==='web_name')?.value;
                if(typeof name==='string')window.top.CmsBranding?.setName(name);
            }
            window.CmsModuleNavigation.markClean(form);
            if(result.data?.session_reset_required){
                message('配置已保存，缓存模式已切换，请重新登录。');
                if(progress)await progress.finish({title:'模式切换成功',message:'请重新登录后继续使用',kind:'success'});
                window.top.location.href='/web-admin';return;
            }
            if(progress)await progress.finish({title:'模式切换成功',message:'网站模式已保存',kind:'success'});else ui.success(result.msg||'配置已保存');
            busy=false;await load(state.lang);message('');
        }catch(error){
            if(progress)progress.close();
            if(newMode==='3'&&/redis/i.test(error.message)){
                message('');
                modeError=error.message;
                ui.failure('Redis 配置验证失败，请检查网站模式下方的详细提示。');
            }else{
                message(error.message,true);ui.failure(error.message);
            }
        }finally{
            busy=false;controls();
            if(modeError)showModeError(modeError);
        }
    });
    $('lang').addEventListener('change',()=>{const next=$('lang').value;if(!window.CmsModuleNavigation.canLeave()){$('lang').value=selectedLang;return;}load(next);});
    $('reset').addEventListener('click',async()=>{
        if(busy)return;if(!state||failed){load();return;}
        if(window.CmsUI.isDirty(form)&&!await window.CmsUI.confirm('将放弃未保存的修改，恢复最近读取或保存的配置。',{title:'重置配置',confirmText:'确认重置',cancelText:'继续编辑'}))return;
        clearModeError();draw();window.CmsModuleNavigation.markClean(form);message('已恢复最近读取或保存的配置。');
    });load();
})(window,document);
