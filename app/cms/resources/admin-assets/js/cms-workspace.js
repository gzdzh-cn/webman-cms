(function(window,document){"use strict";var loaderScript=document.currentScript;var pageLayoutUrl=loaderScript&&loaderScript.getAttribute("data-page-layout")||"/admin-assets/css/cms-page-layout.css";var metadata,admin,pages=[],modules={},current="0",memory={},storeKey,singleMode=false,favorites=[],menuObserver;var HOME="/web-admin/index/dashboard",MAP="/web-admin/index/featuremap";function read(key,fallback){try{var value=sessionStorage.getItem(key);return value===null?fallback:JSON.parse(value)}catch(_){return fallback}}function write(key,value){try{sessionStorage.setItem(key,JSON.stringify(value))}catch(_){}}function escape(value){var node=document.createElement("span");node.textContent=value==null?"":String(value);return node.innerHTML}function url(value){try{var parsed=new URL(value,window.location.origin);return parsed.origin===window.location.origin&&/^\/web-admin\//.test(parsed.pathname)?parsed:null}catch(_){return null}}function resolve(value){var parsed=url(value);if(!parsed)return null;if(parsed.pathname.replace(/\/$/,"")===HOME)return{id:"0",href:HOME,title:"工作台",module:"0",group:""};if(parsed.pathname.replace(/\/$/,"")===MAP)return metadata&&metadata.map||null;var exact=pages.find(function(page){return new URL(page.href,window.location.origin).pathname===parsed.pathname});if(exact){const section=Array.isArray(exact.sections)&&(exact.sections.find(item=>item.id===parsed.searchParams.get("group"))||exact.sections[0]);return section?{...exact,title:section.title}:exact}var editor=/^\/web-admin\/(arctype|admin|channeltype|channelfield)\/(insert|update)\/?$/.exec(parsed.pathname);if(editor){var base=pages.find(function(page){return new URL(page.href,window.location.origin).pathname==="/web-admin/"+editor[1]+"/index"});if(base)return Object.assign({},base,{editor:true})}return parsed.pathname==="/web-admin/archive/insert"?pages.find(function(page){return page.href==="/web-admin/archive/index"})||null:null}function runtime(){return admin&&admin.navigationRuntime?admin.navigationRuntime():{}}function frames(id){return Array.from(document.querySelectorAll('#content iframe')).filter(function(item){return (item.dataset.cmsModule||item.id)===String(id)})}
function frame(id){return frames(id).find(function(item){return !item.hidden})||null}
var cachedFrameSequence=0,requestedPages={},preloadQueue=[],preloadRunning=0;
function pageIdentity(value){var parsed=url(value);if(!parsed)return '';if(!parsed.searchParams.has("lang")&&window.CmsLanguage?.get()?.edit)parsed.searchParams.set("lang",window.CmsLanguage.get().edit);var page=resolve(value);if(Array.isArray(page?.sections)&&page.sections.length&&!parsed.searchParams.has("group"))parsed.searchParams.set("group",page.sections[0].id);if(parsed.pathname==="/web-admin/filemanager/index"&&!parsed.searchParams.has("section"))parsed.searchParams.set("section","themes");var query=new URLSearchParams;['id','lang','group','section','channel_id','view','record','parent_id','target'].forEach(function(key){if(key==='section'&&parsed.pathname!=='/web-admin/filemanager/index')return;if(parsed.searchParams.has(key))query.set(key,parsed.searchParams.get(key))});return parsed.pathname.replace(/\/$/,'')+'?'+query}
function registerFrame(item,id){item.dataset.cmsModule=String(id);if(!item.dataset.cmsPageIdentity){var href=item.getAttribute('src');try{if(item.contentDocument?.location.pathname.startsWith('/web-admin/'))href=item.contentDocument.location.href}catch(_){}item.dataset.cmsPageIdentity=pageIdentity(href)}}
function hideFrame(item,hidden){
 item.hidden=hidden;item.inert=hidden;
 // All preparing documents keep their viewport, including ordinary menu pages.
 // Opacity and inert prevent unfinished controls from being shown or used.
 var preparing=hidden&&item.dataset.cmsLoading==='1'&&!item.dataset.cmsDormant;
 ['display','position','inset','opacity','pointer-events','visibility'].forEach(function(key){item.style.removeProperty(key)});
 if(preparing){item.style.setProperty('display','block','important');item.style.setProperty('visibility','visible','important');item.style.position='absolute';item.style.inset='0';item.style.opacity='0';item.style.pointerEvents='none'}
 if(hidden)item.setAttribute('aria-hidden','true');else item.removeAttribute('aria-hidden');
}
function createPageFrame(id,target,container,editor){
 var item=document.createElement('iframe');item.id='cms-cached-frame-'+(++cachedFrameSequence);item.setAttribute('scrolling','auto');item.setAttribute('frameborder','0');item.setAttribute('allowfullscreen','true');item.style.cssText='width:100%;height:100%;border:0';item.dataset.cmsModule=String(id);item.dataset.cmsPageIdentity=pageIdentity(target);item.dataset.cmsLoading='1';item.title=modules[id]?.title||'后台页面';if(editor){item.cmsEditorSource=editor.source;item.cmsEditorReturn=editor.href;item.dataset.cmsEditor='1'}hideFrame(item,true);item.src=target;container.append(item);attach(item);return item;
}
function clearPagePlaceholder(container){
 var pending=container.cmsPagePlaceholder;if(!pending)return;
 clearTimeout(pending.deadline);pending.node.remove();delete container.cmsPagePlaceholder;
}
function pagePlaceholder(item){
 var container=item.parentElement;if(container.cmsPagePlaceholder?.frame===item)return;
 clearPagePlaceholder(container);
 var node=document.createElement('div'),label=document.createElement('p'),card=document.createElement('div'),spinner=document.createElement('span'),detail=document.createElement('p');
 node.className='cms-page-placeholder';node.setAttribute('role','status');node.setAttribute('aria-live','polite');node.setAttribute('aria-atomic','true');node.setAttribute('aria-busy','true');
 card.className='cms-page-placeholder-card';spinner.className='cms-page-loading-spinner';spinner.setAttribute('aria-hidden','true');
 label.className='cms-page-loading-title';label.textContent='正在加载';
 detail.className='cms-page-loading-detail';detail.textContent=modules[item.dataset.cmsModule]?.title||'请稍候';
 card.append(spinner,label,detail);node.append(card);container.append(node);
 var pending={frame:item,node:node};container.cmsPagePlaceholder=pending;
 pending.deadline=setTimeout(function(){
  if(container.cmsPagePlaceholder!==pending||!item.isConnected)return;
  node.setAttribute('aria-busy','false');node.classList.add('cms-page-loading-slow');label.textContent='加载时间较长';detail.textContent='请检查连接后重试';
  var retry=document.createElement('button');retry.type='button';retry.textContent='重新加载';
  retry.addEventListener('click',function(){clearPagePlaceholder(container);item.cmsReadyDocument=null;item.dataset.cmsLoading='1';delete item.dataset.cmsLoaded;hideFrame(item,true);pagePlaceholder(item);item.contentWindow.location.reload()});
  card.append(retry);
 },15000);
}
function showPage(id,target,container){
 var siblings=frames(id);siblings.forEach(function(item){registerFrame(item,id)});
 var identity=pageIdentity(target),selected=siblings.find(function(item){return item.dataset.cmsPageIdentity===identity});
 requestedPages[id]=identity;
 if(!selected)selected=createPageFrame(id,target,container);
 if(selected.dataset.cmsDormant){delete selected.dataset.cmsDormant;preloadQueue=preloadQueue.filter(function(job){return job.dormant!==selected});selected.src=target}
 siblings.forEach(function(item){if(item!==selected){hideFrame(item,true);if(item.id===String(id))item.id='cms-cached-frame-'+(++cachedFrameSequence)}});
 if(selected.dataset.cmsLoading==='1'){hideFrame(selected,true);pagePlaceholder(selected);return selected}
 clearPagePlaceholder(container);
 selected.id=String(id);hideFrame(selected,false);
 return selected;
}
function pageFrameReady(item){
 if(!item.isConnected)return;
 if(item.cmsReadyDocument===item.contentDocument)return;
 item.cmsReadyDocument=item.contentDocument;
 delete item.dataset.cmsLoading;
 item.dataset.cmsLoaded='1';
 var id=item.dataset.cmsModule||item.id;
 if(item.cmsEditorSource){displayEditor(item);return}
 if(item.cmsEditorReturnComplete){var complete=item.cmsEditorReturnComplete;delete item.cmsEditorReturnComplete;complete();return}
 if(requestedPages[id]===item.dataset.cmsPageIdentity&&(!singleMode||current===id))showPage(id,item.getAttribute('src'),item.parentElement);
 else hideFrame(item,true);
 if(item.cmsPreloadComplete)item.cmsPreloadComplete();
 preloadModule(item);
 drainPreloads();
}
function queuePreload(id,target,container,dormant){
 var identity=pageIdentity(target);
 if(!identity||frames(id).some(function(item){return item!==dormant&&item.dataset.cmsPageIdentity===identity})||preloadQueue.some(function(job){return job.container===container&&job.identity===identity}))return;
 preloadQueue.push({id:id,target:target,identity:identity,container:container,dormant:dormant});setTimeout(drainPreloads,0);
}
function drainPreloads(){
 while(!foregroundEditor&&!frames(current).some(function(item){return item.dataset.cmsLoading==='1'&&!item.dataset.cmsDormant})&&preloadRunning<1&&preloadQueue.length){
  var priority=preloadQueue.findIndex(function(job){return job.id===current});
  var job=preloadQueue.splice(priority>=0?priority:0,1)[0];if(!job.container.isConnected||job.dormant&&!job.dormant.dataset.cmsDormant||frames(job.id).some(function(item){return item!==job.dormant&&item.dataset.cmsPageIdentity===job.identity}))continue;
  var item=job.dormant;preloadRunning++;
  if(item){delete item.dataset.cmsDormant;item.src=job.target}else item=createPageFrame(job.id,job.target,job.container);
  (function(frame){var done=false;function release(){if(done)return;done=true;clearTimeout(deadline);delete frame.cmsPreloadComplete;preloadRunning--;drainPreloads()}frame.cmsPreloadComplete=release;var deadline=setTimeout(release,15000)})(item);
 }
}
function preloadModule(item){
 var id=item.dataset.cmsModule||item.id,module=modules[id],container=item.parentElement;if(!module||!container)return;
 var language=window.CmsLanguage?.get()?.edit;
 function enqueue(href){var target=url(href);if(!target)return;if(language&&module.group!=='system'&&!['cms-nav-assets','cms-nav-filemanager'].includes(id))target.searchParams.set('lang',language);queuePreload(id,target.pathname+target.search,container)}
 module.pages.filter(function(page){return !page.auxiliary}).forEach(function(page){
  if(page.sections?.length)page.sections.forEach(function(section){var target=url(page.href);if(target){target.searchParams.set('group',section.id);enqueue(target.pathname+target.search)}});else enqueue(page.href);
 });
 try{item.contentDocument.querySelectorAll('.cms-module-tabs a,.cms-field-scope a[data-cms-navigation]').forEach(function(anchor){if(resolve(anchor.href)?.module===id)enqueue(anchor.href)})}catch(_){}
}
// Editors are transient documents; the originating list stays mounted.
var foregroundEditor=null;
function cancelPendingEditor(){
 if(!foregroundEditor||!foregroundEditor.hidden)return;
 foregroundEditor.remove();foregroundEditor=null;setTimeout(drainPreloads,0);
}
function openEditor(href,source){
 var target=url(href),sourceFrame=Array.from(document.querySelectorAll('#content iframe')).find(function(item){return item.contentWindow===source});
 if(!target||!/^\/web-admin\/(arctype|admin|channeltype|channelfield)\/(insert|update)\/?$/.test(target.pathname)||!sourceFrame||sourceFrame.hidden)return null;
 var page=resolve(source.location.href),editorPage=resolve(target.href);
 if(!page||!editorPage?.editor||page.module!==editorPage.module||page.module!==current)return null;
 if(!canSwitch(current)||!source.CmsUI?.guard(function(){openEditor(href,source)}))return false;
 cancelPendingEditor();
 var id=page.module,sourceHref=safeHref(source.location.href),returnTarget=url(target.searchParams.get('return'));
 if(!returnTarget||returnTarget.pathname!==url(sourceHref).pathname)target.searchParams.set('return',sourceHref);
 // Set the priority before creating the iframe so no queued preload can start.
 foregroundEditor={hidden:true,remove:function(){}};
 var item=createPageFrame(id,target.pathname+target.search,sourceFrame.parentElement,{source:sourceFrame,href:sourceHref});
 foregroundEditor=item;
 return true;
}
function displayEditor(item){
 if(foregroundEditor!==item||!item.cmsEditorSource?.isConnected)return;
 foregroundEditor=null;
 requestedPages[item.dataset.cmsModule]=item.dataset.cmsPageIdentity;
 frames(item.dataset.cmsModule).forEach(function(other){if(other!==item){hideFrame(other,true);if(other.id===item.dataset.cmsModule)other.id='cms-cached-frame-'+(++cachedFrameSequence)}});
 item.id=item.dataset.cmsModule;hideFrame(item,false);
 onTabChange(item.dataset.cmsModule);drainPreloads();
}
function returnEditor(source,options){
 var item=Array.from(document.querySelectorAll('#content iframe')).find(function(frame){return frame.contentWindow===source&&frame.cmsEditorSource});
 if(!item)return false;
 var previous=item.cmsEditorSource,id=item.dataset.cmsModule,target=url(options?.href||item.cmsEditorReturn);
 if(!previous.isConnected||!target||target.pathname!==url(item.cmsEditorReturn)?.pathname)return false;
 var href=target.pathname+target.search+target.hash;
 function finish(){
  if(!item.isConnected)return;
  item.remove();if(foregroundEditor===item)foregroundEditor=null;
  requestedPages[id]=pageIdentity(href);showPage(id,href,previous.parentElement);
  if(singleMode&&current!==id)hideFrame(previous,true);
  memory[id]=href;runtime().tabs?.rememberTabPage(id,href);
  if(current===id)onTabChange(id);
  drainPreloads();
 }
 if(options?.saved){
  foregroundEditor=item;
  previous.dataset.cmsLoading='1';previous.dataset.cmsPageIdentity=pageIdentity(href);
  previous.cmsEditorReturnComplete=finish;
  previous.src=href;
 }else finish();
 return true;
}

function pageBusy(doc){return !!doc?.querySelector('[data-cms-submit-busy][aria-busy="true"],[data-cms-upload-busy="1"]')}
function canSwitch(id){try{if(frames(id).some(function(item){return pageBusy(item.contentDocument)})){notify('正在处理，请完成后再切换页面');return false}}catch(_){return false}return true}
function hasPendingChanges(){return Array.from(document.querySelectorAll('#content iframe')).some(function(item){try{return pageBusy(item.contentDocument)||item.contentWindow.CmsUI?.isDirty(item.contentDocument)}catch(_){return true}})}
function refreshPage(){if(!canLeave(current,refreshPage))return false;var item=frame(current);if(item)item.contentWindow.location.reload();return true}function notify(message){if(window.layui&&layui.layer)layui.layer.msg(message)}function canLeave(id,retry){
 var group=frames(id||current);
 for(var i=0;i<group.length;i++){try{var child=group[i].contentWindow;if(child.CmsUI){
 if(child.CmsUI.isDirty(child.document)&&(group[i].hidden||String(id||current)!==current)){
  var dirtyFrame=group[i],moduleId=dirtyFrame.dataset.cmsModule||dirtyFrame.id,href=safeHref(child.location.href);
  showPage(moduleId,href,dirtyFrame.parentElement);memory[moduleId]=href;
  if(runtime().tabs){runtime().tabs.rememberTabPage(moduleId,href);runtime().tabs.addTabOnly({id:moduleId,title:modules[moduleId]?.title||'后台页面',url:href,close:moduleId!=='0'},0)}
  else document.querySelectorAll('#content iframe').forEach(function(item){if(item!==dirtyFrame){item.hidden=true;item.setAttribute('aria-hidden','true')}});
  onTabChange(moduleId);
 }
 if(!child.CmsUI.guard(retry))return false;continue
}if(child.CmsModuleNavigation&&!child.CmsModuleNavigation.canLeave(retry))return false;if(child.document.querySelector('dialog[open],form[data-cms-dirty="1"]')){notify('请先完成或关闭当前编辑窗口');return false}}catch(_){notify('当前编辑状态暂时无法确认，请完成或关闭编辑窗口后重试');return false}}
 return true;
}function safeHref(value){var parsed=url(value);return parsed?parsed.pathname+parsed.search+parsed.hash:null}function rememberFrame(id){var iframe=frame(id);if(!iframe||!modules[id])return;try{var href=iframe.cmsEditorReturn||safeHref(iframe.contentWindow.location.href),page=resolve(href);if(page&&page.module===id){memory[id]=href;runtime().tabs?.rememberTabPage(id,href);write(storeKey+"-pages:"+(window.CmsLanguage?.get()?.edit||""),memory)}}catch(_){}}function selectMenu(id){var links=Array.from(document.querySelectorAll("#sideMenu a[menu-id]"));var selected=links.find(function(a){return a.getAttribute("menu-id")===String(id)});if(!selected||selected.parentElement.hidden)selected=links.find(function(a){return a.getAttribute("menu-id")==="cms-feature-map"});if(!selected)return;var menuTree=selected.closest("ul[pear-id]");if(menuTree&&runtime().menu?.option.control){document.querySelectorAll("#sideMenu ul[pear-id]").forEach(function(tree){tree.style.display=tree===menuTree?"block":"none"});document.querySelectorAll("#control [pear-id]").forEach(function(item){item.classList.toggle("layui-this",item.getAttribute("pear-id")===menuTree.getAttribute("pear-id"))});var control=document.querySelector('#control [pear-id="'+menuTree.getAttribute("pear-id")+'"]');document.querySelectorAll("#control .pe-title").forEach(function(item){item.textContent=control?.getAttribute("pear-title")||""})}document.querySelectorAll("#sideMenu .layui-this").forEach(function(node){node.classList.remove("layui-this")});selected.parentElement.classList.add("layui-this");var parent=selected.closest(".layui-nav-child")?.parentElement;if(parent&&!document.body.classList.contains("pear-mini")){document.querySelectorAll("#sideMenu .layui-nav>li").forEach(function(li){var child=li.querySelector(".layui-nav-child");if(child&&window.layui?.$)layui.$(child).stop(true,true);if(li!==parent)li.classList.remove("layui-nav-itemed");if(child){child.style.height="";child.style.display=""}});parent.classList.add("layui-nav-itemed")}}function onTabChange(id){document.querySelectorAll("#content .layui-tab-content>.layui-tab-item").forEach(function(panel){panel.inert=!panel.classList.contains("layui-show")});if(!id)id=document.querySelector("#content .layui-tab-title>.layui-this")?.getAttribute("lay-id")||"0";rememberFrame(current);current=String(id);var data=runtime().tabs?.getData().find(function(item){return String(item.id)===current});var page=resolve(data&&data.url||(current==="0"?HOME:memory[current]));var module=modules[current];var group=metadata.groups.find(function(item){return item.id===(module&&module.group)});document.querySelector(".cms-crumb-group").textContent=group?group.title:current==="0"?"工作台":current==="cms-feature-map"?"功能地图":"账户";document.querySelector(".cms-breadcrumb strong").textContent=current==="cms-feature-map"?"":module?module.title+(module.pages.length>1&&page&&page.title!==module.title?" / "+page.title:""):"";selectMenu(module?module.entry.id:current);var selectedLink=document.querySelector("#sideMenu .layui-this>a[menu-url]");document.querySelectorAll("#sideMenu a[menu-url]").forEach(function(a){var selected=a===selectedLink;if(selected)a.setAttribute("aria-current","page");else a.removeAttribute("aria-current")})}function open(href,options){
 if(!canSwitch(current))return false;
 var page=resolve(href);if(!page){notify('当前账号没有该页面的访问权限');return false}
 cancelPendingEditor();
 var module=modules[page.module],id=page.module,target=safeHref(href),language=window.CmsLanguage?.get()?.edit||'';
 var parsed=url(target),languageAware=module&&module.group!=='system'&&!['cms-nav-assets','cms-nav-filemanager'].includes(id);
 if(parsed&&languageAware&&language&&!parsed.searchParams.has('lang'))parsed.searchParams.set('lang',language);
 if(options?.menu&&memory[id]&&resolve(memory[id])?.module===id){var remembered=url(memory[id]);if(remembered&&(!languageAware||!language||remembered.searchParams.get('lang')===parsed.searchParams.get('lang')))parsed=remembered}
 var mountedEditor=frames(id).find(function(item){return item.cmsEditorSource&&item.dataset.cmsLoaded==='1'});
 if(options?.menu&&mountedEditor)parsed=url(mountedEditor.getAttribute('src'));
 if(mountedEditor&&!options?.menu&&!page.editor){if(!canLeave(id,function(){open(href,options)}))return false;mountedEditor.remove()}
 target=parsed?parsed.pathname+parsed.search+parsed.hash:target;
 var tab=runtime().tabs,bodyFrame=runtime().frame;if(!target||!tab&&!bodyFrame)return false;
 rememberFrame(current);
 memory[id]=target;write(storeKey+'-pages:'+(window.CmsLanguage?.get()?.edit||''),memory);
 if(singleMode&&bodyFrame){
  var active=frame(current),container=active?.parentElement||document.querySelector('#content .pear-frame');if(!container)return false;
  var selected=showPage(id,target,container);
  current=id;
  var savedTabs=read('content-pear-tab-data',[]);if(!Array.isArray(savedTabs))savedTabs=[];
  var savedIndex=savedTabs.findIndex(function(item){return item.id===id}),savedPage={id:id,title:module?module.title:page.title,url:target,close:id!=='0'};
  if(savedIndex>=0)savedTabs[savedIndex]=savedPage;else savedTabs.push(savedPage);
  write('content-pear-tab-data',savedTabs);try{sessionStorage.setItem('content-pear-tab-data-current',id)}catch(_){}
  document.querySelectorAll('#content iframe').forEach(function(item){if(item.dataset.cmsModule!==String(id))hideFrame(item,true)});
  onTabChange(id);
 }else{
  var existing=tab.getData().find(function(item){return String(item.id)===id}),item=frame(id)||frames(id)[0],selected;
  if(existing&&item){selected=showPage(id,target,item.parentElement);tab.rememberTabPage(id,target,module?module.title:page.title)}
  if(!existing){
   // Select the destination in this task; its stable placeholder covers first load.
   tab.addTabOnly({id:id,title:module?module.title:page.title,url:target,close:id!=='0'},0);
   item=document.getElementById(id);if(item){attach(item);selected=showPage(id,target,item.parentElement)}
  }
  tab.addTabOnly({id:id,title:module?module.title:page.title,url:target,close:id!=='0'},0);
  onTabChange(id);
 }
 rememberFrame(id);
 if(window.innerWidth<992&&!document.body.classList.contains('pear-mini'))admin.collapseSide();return true;
}function prepare(config){var home={id:"0",title:"工作台",href:HOME,type:1,icon:"layui-icon layui-icon-home",weight:2e3};var map={id:"cms-feature-map",title:"功能地图",href:MAP,type:1,icon:"layui-icon layui-icon-app",weight:1400};config.menu.async=false;config.other.keepLoad=0;config.tab.session=true;config.tab.keepState=true;config.tab.preload=false;config.tab.index={id:"0",title:"工作台",href:HOME};function preference(key,fallback){try{var saved=config.theme.allowCustom!==false&&localStorage.getItem(key);return saved==="true"?true:saved==="false"?false:!!fallback}catch(_){return!!fallback}}singleMode=!preference("muilt-tab",config.tab.enable);document.body.classList.toggle("cms-single-view",singleMode);var grouped=preference("control",config.menu.control);document.body.classList.toggle("cms-group-menu",grouped);favorites=window.CmsShortcuts.read(metadata);if(grouped){config.menu.data=[{id:"cms-fixed",title:"常用入口",type:0,icon:"layui-icon layui-icon-app",weight:2e3,children:[home,map]}].concat(metadata.menus.map(function(item){return item.type==1?{id:"cms-custom-group-"+item.id,title:item.title,type:0,icon:item.icon,weight:item.weight,children:[item]}:item}))}else{config.menu.data=[home].concat(window.CmsShortcuts.candidates(metadata).map(function(module,index){var item=window.CmsShortcuts.leaf(module);item.weight=1800-index;return item}),[map],window.CmsShortcuts.custom(metadata))}storeKey="cms-workspace-v1";["pages","collapsed","expanded"].forEach(function(part){if(read(storeKey+"-"+part,null)===null){var previous=read("cms-workspace-20261106-seo-v1-"+part,null);if(previous!==null)write(storeKey+"-"+part,previous)}});if(window.innerWidth>=992)config.menu.collapse=read(storeKey+"-collapsed",!!config.menu.collapse)===true;memory=read(storeKey+"-pages:"+(window.CmsLanguage?.get()?.edit||""),{});if(!memory||typeof memory!=="object"||Array.isArray(memory))memory={};var old=read("content-pear-tab-data",[]),active=read("content-pear-tab-data-current",null);try{active=sessionStorage.getItem("content-pear-tab-data-current")||"0"}catch(_){active="0"}var tabs=[{id:"0",title:"工作台",url:HOME,close:false}],seen=new Set(["0"]),activeId="0";if(Array.isArray(old))old.forEach(function(item){var target=item&&item.url;if(target==="about:blank")target=pages.find(function(page){return String(page.id)===String(item.id)})?.href||memory[item.id];var page=resolve(target);if(!page)return;var id=page.module,module=modules[id],edit=window.CmsLanguage?.get()?.edit,parsed=url(target);if(edit&&module&&module.group!=="system"&&!["cms-nav-assets","cms-nav-filemanager","cms-feature-map"].includes(id)){if(parsed?.searchParams.get("lang")!==edit)parsed=url(module.entry.href);parsed.searchParams.set("lang",edit);target=parsed.pathname+parsed.search}var entry={id:id,title:module?module.title:page.title,url:safeHref(target),close:id!=="0"};if(String(item.id)===String(active))activeId=id;if(!seen.has(id)){tabs.push(entry);seen.add(id)}else if(String(item.id)===String(active))tabs[tabs.findIndex(function(tab){return tab.id===id})]=entry});tabs.forEach(function(item){if(item.id!=="0")memory[item.id]=item.url});write("content-pear-tab-data",tabs);try{sessionStorage.setItem("content-pear-tab-data-current",activeId)}catch(_){}current=activeId;if(singleMode){var activePage=tabs.find(function(item){return item.id===activeId});if(activePage)config.tab.index.href=activePage.url}}function appearanceChanged(){var color,second,autoHead;try{color=localStorage.getItem("theme-color-color");second=localStorage.getItem("theme-color-second");autoHead=localStorage.getItem("auto-head")==="true"}catch(_){}if(/^#[\da-f]{3,8}$/i.test(color||""))document.body.style.setProperty("--cms-blue",color);if(/^#[\da-f]{3,8}$/i.test(second||""))document.body.style.setProperty("--cms-selected",second);document.body.classList.toggle("cms-auto-head",!!autoHead);var header=document.querySelector(".layui-header");if(header)document.body.classList.toggle("cms-dark-header",header.classList.contains("dark-theme")&&!autoHead);document.querySelectorAll("#content iframe").forEach(function(iframe){try{var child=iframe.contentDocument?.body;if(child){child.style.setProperty("--cms-page-blue",color||"#2563eb");child.style.setProperty("--cms-blue",color||"#2563eb")}}catch(_){}})}function applySidebar(){var chosen=new Set(favorites);window.CmsShortcuts.candidates(metadata).forEach(function(module){document.querySelectorAll("#sideMenu a[menu-id]").forEach(function(a){if(a.getAttribute("menu-id")===String(module.entry.id))a.parentElement.hidden=!chosen.has(module.id)})});metadata.groups.forEach(function(group){var visible=metadata.modules.some(function(module){return module.group===group.id&&module.sidebar!==false&&chosen.has(module.id)});var id="cms-nav-group-"+group.id;document.querySelectorAll("#sideMenu ul[pear-id],#control [pear-id]").forEach(function(node){if(node.getAttribute("pear-id")===id)node.hidden=!visible})})}function shortcutState(){return{metadata:metadata,favorites:favorites.slice()}}function saveShortcuts(values){favorites=window.CmsShortcuts.save(metadata,values);applySidebar();onTabChange(current);window.dispatchEvent(new CustomEvent("cms-shortcuts-change"));return favorites.slice()}function menuReady(){var menu=runtime().menu,saved=read(storeKey+"-expanded",null);if(!Array.isArray(saved))saved=null;var groups=Array.from(document.querySelectorAll("#sideMenu .layui-nav>li")).filter(function(li){return li.querySelector('a[menu-type="0"]')});groups.forEach(function(li,index){var a=li.querySelector("a");a.setAttribute("role","button");a.setAttribute("tabindex","0");var isOpen=saved?saved.includes(a.getAttribute("menu-id")):index===0;li.classList.toggle("layui-nav-itemed",isOpen);a.setAttribute("aria-expanded",String(isOpen))});document.querySelectorAll("#sideMenu a[menu-url]").forEach(function(a){a.setAttribute("role","link")});if(window.innerWidth<992&&!document.body.classList.contains("pear-mini"))admin.collapseSide();applySidebar();onTabChange(current);if(menuObserver)menuObserver.disconnect();var observer=menuObserver=new MutationObserver(function(){if(document.body.classList.contains("pear-mini"))return;var expanded=[];groups.forEach(function(li){var a=li.querySelector("a"),state=li.classList.contains("layui-nav-itemed");a.setAttribute("aria-expanded",String(state));if(state)expanded.push(a.getAttribute("menu-id"))});write(storeKey+"-expanded",expanded)});groups.forEach(function(li){observer.observe(li,{attributes:true,attributeFilter:["class"]})});if(menu)menu.isCollapse=document.body.classList.contains("pear-mini");document.querySelectorAll("#control [pear-id]").forEach(function(item){item.setAttribute("tabindex","0");item.setAttribute("role","button")})}function search(){var content=document.createElement("div");content.className="cms-search-dialog";var input=document.createElement("input");input.placeholder="搜索功能，如“广告位置”“栏目”";input.setAttribute("aria-label","搜索后台功能");var list=document.createElement("div");list.className="cms-search-results";content.append(input,list);var layerId=layui.layer.open({type:1,title:"搜索功能",area:[Math.min(560,window.innerWidth-32)+"px","480px"],content:content.outerHTML,success:function(layero){var node=layero[0]||layero,field=node.querySelector("input"),results=node.querySelector(".cms-search-results");function draw(){results.replaceChildren();var value=field.value.trim().toLowerCase();var filtered=pages.filter(function(page){var module=modules[page.module];return(!page.auxiliary||module?.pages.length===1)&&(page.title+" "+(module?module.title:"")).toLowerCase().includes(value)});filtered.forEach(function(page){var button=document.createElement("button"),module=modules[page.module],group=metadata.groups.find(function(item){return item.id===page.group});button.type="button";button.append(document.createTextNode(page.title));var path=document.createElement("small");path.textContent=[group?.title,module?.title].filter(Boolean).join(" / ");button.append(path);button.addEventListener("click",function(){if(open(page.href))layui.layer.close(layerId)});results.append(button)});if(!filtered.length){var empty=document.createElement("p");empty.className="cms-search-empty";empty.textContent="没有匹配的功能";results.append(empty)}}field.addEventListener("input",draw);field.addEventListener("keydown",function(e){if(e.key==="ArrowDown"){e.preventDefault();results.querySelector("button")?.focus()}if(e.key==="Enter")results.querySelector("button")?.click()});draw();field.focus()}})}function attach(iframe){if(iframe.dataset.cmsAttached)return;iframe.dataset.cmsAttached="1";iframe.dataset.cmsLoading="1";if(!iframe.dataset.cmsModule&&(iframe.id||singleMode)){registerFrame(iframe,iframe.id||current);iframe.id=iframe.id||current;}function loaded(){try{var doc=iframe.contentDocument;if(!doc||!doc.body||doc.location.pathname.indexOf("/web-admin/")!==0)return;if(!doc.querySelector("[data-cms-workspace-frame]")){var style=doc.createElement("style");style.dataset.cmsWorkspaceFrame="1";style.textContent="body{box-sizing:border-box;min-height:100vh;background:#f5f7fb!important}body.pear-container{margin:0!important}body.cms-workspace-page>h1,body.cms-workspace-page>main>h1,body.cms-workspace-page>main>header>h1,body.cms-workspace-page .cms-module-navigation>.cms-module-heading{display:none!important}@media(max-width:767px){body.pear-container{padding:12px!important}}";doc.head.append(style)}var page=resolve(doc.location.href),errorPage=doc.querySelector('link[href*="pages/error.css"]'),embeddedIndex=page&&!errorPage&&(doc.location.pathname===HOME||doc.location.pathname===MAP||/\/index\/?$/.test(doc.location.pathname));doc.body.classList.toggle("cms-workspace-page",!!embeddedIndex);if(embeddedIndex){doc.body.dataset.cmsPageKey=doc.location.pathname.split("/")[2];doc.body.dataset.cmsPageKind=doc.body.dataset.cmsPageKey==="archive"?"document":doc.location.pathname===HOME?"dashboard":doc.location.pathname===MAP?"featuremap":doc.querySelector("body>main")?"native":"pear";var sheet=doc.querySelector('[data-cms-page-layout]');if(!sheet){sheet=doc.createElement('link');sheet.rel='stylesheet';sheet.href=pageLayoutUrl;sheet.dataset.cmsPageLayout='1';doc.head.append(sheet)}
function accessibleTables(){doc.querySelectorAll('body>main>.scroll,body>main>.cms-table-scroll,.layui-table-view:not(dialog *, .layui-layer *) .layui-table-main').forEach(function(region){if(!region.hasAttribute('tabindex')){region.tabIndex=0;region.setAttribute('role','region');region.setAttribute('aria-label','表格内容，可横向滚动')}})}
if(!doc.body.dataset.cmsTablesObserved){doc.body.dataset.cmsTablesObserved='1';accessibleTables();new MutationObserver(accessibleTables).observe(doc.body,{childList:true,subtree:true});sheet.addEventListener('load',function(){iframe.contentWindow.dispatchEvent(new Event('resize'));accessibleTables()})}}if(!doc.body.dataset.cmsLinkBridge){doc.body.dataset.cmsLinkBridge="1";doc.addEventListener("click",function(event){if(event.defaultPrevented||event.button||event.altKey)return;var a=event.target.closest("a[href]");if(!a||a.download||a.closest("[data-cms-module-navigation]")||a.target==="_blank"||event.ctrlKey||event.metaKey||event.shiftKey||!resolve(a.href))return;event.preventDefault();open(a.href)})}if(page&&!iframe.dataset.cmsModule){registerFrame(iframe,page.module);iframe.id=page.module;if(singleMode)current=page.module}if(page){iframe.dataset.cmsPageIdentity=pageIdentity(doc.location.href);if(!iframe.hidden&&!requestedPages[iframe.dataset.cmsModule||iframe.id])requestedPages[iframe.dataset.cmsModule||iframe.id]=iframe.dataset.cmsPageIdentity;}if(!iframe.hidden){rememberFrame(iframe.dataset.cmsModule||iframe.id);if((iframe.dataset.cmsModule||iframe.id)===current)onTabChange(current);}if(!doc.documentElement.dataset.cmsFrameReadyBound){doc.documentElement.dataset.cmsFrameReadyBound='1';iframe.contentWindow.addEventListener('cms-page-ready',function(){pageFrameReady(iframe)},{once:true})}if(doc.documentElement.dataset.cmsPageState!=='pending')pageFrameReady(iframe);appearanceChanged()}catch(_){}}iframe.addEventListener("cms-frame-content-ready",loaded);iframe.addEventListener("load",loaded);
if(iframe.getAttribute('src')==='about:blank'){
 var id=iframe.dataset.cmsModule||iframe.id,saved=runtime().tabs?.getData().find(function(item){return String(item.id)===id}),target=saved?.url||memory[id];
 if(target&&url(target)){iframe.dataset.cmsDormant='1';iframe.dataset.cmsPageIdentity=pageIdentity(target);hideFrame(iframe,true);queuePreload(id,target,iframe.parentElement,iframe)}
}else loaded()}function bind(){document.querySelector(".cms-menu-search").addEventListener("click",function(event){event.preventDefault();search()});document.querySelector(".cms-account-link").addEventListener("click",function(event){event.preventDefault();open(this.href)});document.querySelector(".cms-account-link").parentElement.hidden=!resolve("/web-admin/account/index");document.addEventListener("keydown",function(event){if(event.target.matches('#sideMenu a[menu-type="0"],#control [pear-id]')&&(event.key==="Enter"||event.key===" ")){event.preventDefault();event.target.click()}});document.addEventListener("click",function(event){var leaf=event.target.closest("#sideMenu a[menu-url]"),tab=event.target.closest("#content .layui-tab-title>li[lay-id]"),close=event.target.closest("#content .layui-tab-title .layui-tab-close");if(leaf||tab)cancelPendingEditor();
if(tab&&!close&&tab.getAttribute('lay-id')!==current){
 var targetId=tab.getAttribute('lay-id'),saved=runtime().tabs?.getData().find(function(item){return String(item.id)===targetId});
 if(saved){open(saved.url,{menu:true});event.preventDefault();event.stopImmediatePropagation();return;}
}var leaving=leaf||tab&&(tab.getAttribute("lay-id")!==current||close);if(leaving&&!(close?canLeave(tab.getAttribute("lay-id"),function(){close.click()}):canSwitch(current))){event.preventDefault();event.stopImmediatePropagation();onTabChange(current)}},true);var observer=new MutationObserver(function(){document.querySelectorAll("#content iframe").forEach(attach)});observer.observe(document.querySelector("#content"),{childList:true,subtree:true});new MutationObserver(function(records){if(records.some(function(record){return record.target.id==="pear-admin-color"||record.target.parentElement?.id==="pear-admin-color"||Array.from(record.addedNodes).some(function(node){return node.id==="pear-admin-color"})}))appearanceChanged()}).observe(document.head,{childList:true,subtree:true,characterData:true});new MutationObserver(appearanceChanged).observe(document.querySelector(".layui-header"),{attributes:true,attributeFilter:["class"]});new MutationObserver(function(){if(window.innerWidth>=992)write(storeKey+"-collapsed",document.body.classList.contains("pear-mini"))}).observe(document.body,{attributes:true,attributeFilter:["class"]});window.addEventListener("beforeunload",function(event){if(!window.CmsLanguage?.isSwitching()&&hasPendingChanges()){event.preventDefault();event.returnValue=""}});window.addEventListener("pageshow",function(){onTabChange(current)});window.addEventListener("storage",function(event){if(event.key===window.CmsShortcuts.key(metadata)){favorites=window.CmsShortcuts.read(metadata);applySidebar();onTabChange(current);window.dispatchEvent(new CustomEvent("cms-shortcuts-change"))}})}async function start(instance){admin=instance;try{await window.CmsLanguage.ready();var boot=window.CmsWorkspaceBootstrap;if(!boot)boot=Promise.all([fetch("/web-admin/config/get",{cache:"no-store",headers:{Accept:"application/json"}}),fetch("/web-admin/rule/get?navigation=1",{cache:"no-store",headers:{Accept:"application/json"}})].map(async function(pending){var response=await pending;if(!response.ok)throw new Error("读取后台导航失败");return response.json()})).then(function(data){return{data:data}});var initial=await boot;if(initial.error)throw new Error(initial.error);var config=initial.data[0],payload=initial.data[1];metadata=payload.data;if(!metadata||!metadata.version||!Array.isArray(metadata.menus))throw new Error("导航配置尚未就绪");pages=metadata.pages.slice();metadata.modules.forEach(function(module){modules[module.id]=module});modules["cms-feature-map"]={id:"cms-feature-map",title:"功能地图",group:"",entry:metadata.map,pages:[metadata.map]};function custom(items){(items||[]).forEach(function(item){if(item.type==1&&item.href&&!resolve(item.href)&&url(item.href)){var id="cms-nav-custom-"+item.id,page={id:item.id,key:item.key,title:item.title,href:item.href,module:id,group:""};pages.push(page);modules[id]={id:id,title:item.title,entry:page,pages:[page]}}custom(item.children)})}custom(metadata.menus);prepare(config);bind();admin.render(config);if(runtime().tabs)runtime().tabs.refresh=refreshPage;if(runtime().frame)runtime().frame.refresh=refreshPage;appearanceChanged()}catch(error){document.querySelector(".loader-main").style.display="none";var panel=document.createElement("div");panel.className="cms-search-dialog";panel.textContent=error.message+"。";var retry=document.createElement("button");retry.textContent="重新加载";retry.onclick=function(){window.location.reload()};panel.append(retry);document.querySelector("#content").replaceChildren(panel)}}function resetLanguagePages(mark){document.querySelectorAll("#content iframe").forEach(function(item){rememberFrame(item.id)});var saved=read("content-pear-tab-data",[]),targetMemory=read(storeKey+"-pages:"+mark,{});if(Array.isArray(saved)){saved=saved.map(function(item){var module=modules[item.id];if(!module||module.group==="system"||["cms-nav-assets","cms-nav-filemanager","cms-feature-map"].includes(item.id))return item;var base=url(module.entry?.href||HOME),remembered=url(targetMemory[item.id]);if(remembered&&base&&remembered.pathname===base.pathname&&remembered.searchParams.get("lang")===mark)base=remembered;if(base){base.searchParams.set("lang",mark);return Object.assign({},item,{url:base.pathname+base.search})}return item});write("content-pear-tab-data",saved)}}var expandedEditorFrame=null;
function setEditorFullscreen(source,enabled){
 var target=Array.from(document.querySelectorAll('#content iframe')).find(function(item){return item.contentWindow===source});
 if(!target)return false;
 if(enabled&&!source.document.querySelector('dialog[open][data-cms-template-editor]'))return false;
 if(!enabled&&expandedEditorFrame!==target)return false;
 if(expandedEditorFrame)expandedEditorFrame.classList.remove('cms-editor-fullscreen-frame');
 expandedEditorFrame=enabled?target:null;
 document.body.classList.toggle('cms-editor-fullscreen',!!expandedEditorFrame);
 if(expandedEditorFrame)expandedEditorFrame.classList.add('cms-editor-fullscreen-frame');
 return !!expandedEditorFrame;
}
window.CmsNavigation={openEditor:openEditor,returnEditor:returnEditor,setEditorFullscreen:setEditorFullscreen,start:start,resetLanguagePages:resetLanguagePages,open:open,resolve:resolve,getMetadata:function(){return metadata},onTabChange:onTabChange,menuReady:menuReady,canLeave:canLeave,appearanceChanged:appearanceChanged,getFeatureMapState:shortcutState,setShortcut:function(id,enabled){if(!window.CmsShortcuts.candidates(metadata).some(function(module){return module.id===id}))throw new Error("当前功能不可加入导航");return saveShortcuts(enabled?favorites.concat([id]):favorites.filter(function(value){return value!==id}))},resetShortcuts:function(){return saveShortcuts(window.CmsShortcuts.defaults(metadata))}}})(window,document);(function(window,document){"use strict";var state=window.CmsLanguageInitial||null,loading=null,busy=false,switching=false,channel=null;var eventKey="cms-admin-language-context-v1";var originalFetch=window.fetch.bind(window);function notice(message){var host=document.getElementById("cms-language-message");if(host)host.textContent=message||""}async function request(action,values){var options={credentials:"same-origin",cache:"no-store",headers:{Accept:"application/json"}};if(values){options.method="POST";var body=new FormData;Object.entries(values).forEach(function(entry){body.append(entry[0],String(entry[1]))});body.append("csrf",state&&state.csrf||"");options.body=body;options.headers["X-CMS-Language-Revision"]=String(state&&state.revision||"");options.headers["X-CMS-Edit-Language"]=state&&state.edit||""}var response=await originalFetch("/web-admin/language/"+action,options),result=await response.json();if(!response.ok||result.code!==0)throw new Error(result.msg||"读取语言设置失败");return result.data}function closePicker(focus){var picker=document.getElementById("cms-edit-language"),menu=document.getElementById("cms-language-dropdown");if(menu)menu.hidden=true;if(picker){picker.setAttribute("aria-expanded","false");if(focus)picker.focus()}}
function pickerItems(){var menu=document.getElementById("cms-language-dropdown");return menu?Array.from(menu.querySelectorAll('[role="option"]:not(:disabled),a.cms-language-manage')):[]}
function openPicker(edge){var picker=document.getElementById("cms-edit-language"),menu=document.getElementById("cms-language-dropdown");if(!picker||!menu||picker.disabled)return;menu.hidden=false;picker.setAttribute("aria-expanded","true");var items=pickerItems(),current=items.find(function(item){return item.getAttribute("aria-selected")==="true"});var target=edge==="last"?items[items.length-1]:edge==="first"?items[0]:current||items[0];if(target&&edge!=="hover")target.focus()}
function draw(){var picker=document.getElementById("cms-edit-language"),menu=document.getElementById("cms-language-options"),value=document.getElementById("cms-edit-language-value");if(!picker||!menu||!state)return;var languages=state.languages||[],enabled=!!state.enabled;if(!enabled)languages=languages.filter(function(row){return row.mark===state.main});var current=languages.find(function(row){return row.mark===state.edit});value.textContent=current?current.title:"无可用语言";picker.disabled=busy||!languages.length;picker.setAttribute("aria-label","当前编辑语言："+(current?current.title:"无可用语言"));picker.title=!enabled?"多语言已关闭，当前使用系统主语言":"切换后台编辑语言，不改变前台默认语言";menu.replaceChildren();languages.forEach(function(row){var item=document.createElement("button"),title=document.createElement("span"),note=document.createElement("small");item.type="button";item.className="cms-language-option";item.setAttribute("role","option");item.setAttribute("aria-selected",row.mark===state.edit?"true":"false");item.dataset.language=row.mark;item.disabled=busy||(!enabled&&row.mark!==state.main);title.textContent=row.title;note.textContent=row.mark===state.main?"系统主语言":Number(row.status)===0?"未公开":"已公开";item.append(title,note);item.addEventListener("click",function(){closePicker(false);if(row.mark===state.edit){picker.focus();return}switchTo(row.mark).catch(function(){picker.focus()})});menu.append(item)});if(busy)closePicker(false);document.documentElement.dataset.cmsEditLanguage=state.edit||"";document.documentElement.dataset.cmsLanguageRevision=String(state.revision||"")}
function documents(){var found=[document];function visit(doc){doc.querySelectorAll("iframe").forEach(function(frame){try{if(frame.contentDocument&&!found.includes(frame.contentDocument)){found.push(frame.contentDocument);visit(frame.contentDocument)}}catch(_){}})}visit(document);return found}function hasPendingWrite(){return documents().some(function(doc){return !!doc.querySelector('[data-cms-submit-busy]:not([aria-busy="false"]),[data-cms-upload-busy="1"],form[data-busy="1"]')})}function hasUnsaved(){if(hasPendingWrite())return true;return documents().some(function(doc){if(doc.querySelector('form[data-cms-dirty="1"],dialog[open],.layui-layer-page,.layui-layer-iframe'))return true;return false})}async function confirmLeave(){if(hasPendingWrite()){notice("当前页面正在保存、上传或执行任务，请完成后再切换语言。");return false}return!hasUnsaved()||await window.CmsUI.confirm("切换语言会关闭当前编辑窗口，并重新载入相关页面。未保存内容将被放弃，是否继续？")}function discardDialogs(){documents().forEach(function(doc){doc.defaultView.CmsUI?.clean(doc);doc.querySelectorAll("form[data-cms-dirty]").forEach(function(form){delete form.dataset.cmsDirty});doc.querySelectorAll("dialog[open]").forEach(function(dialog){dialog.close()});try{doc.defaultView.layui&&doc.defaultView.layui.layer&&doc.defaultView.layui.layer.closeAll()}catch(_){}})}function broadcast(){var update={revision:state.revision,at:Date.now()};try{localStorage.setItem(eventKey,JSON.stringify(update))}catch(_){}if(channel)channel.postMessage(update)}async function load(){if(loading)return loading;loading=request("state").then(function(value){state=value;draw();notice("");return state}).catch(function(error){notice(error.message);throw error}).finally(function(){loading=null});return loading}async function switchTo(mark){if(!state||busy||mark===state.edit)return state;if(!await confirmLeave()){draw();return state}busy=true;draw();notice("正在切换语言…");try{var next=await request("switch",{lang:mark,revision:state.revision});next=next&&next.languages?next:await request("state");discardDialogs();switching=true;if(window.CmsNavigation&&window.CmsNavigation.resetLanguagePages)window.CmsNavigation.resetLanguagePages(next.edit);state=next;broadcast();window.location.reload();return state}catch(error){notice(error.message);throw error}finally{busy=false;draw()}}async function toggle(enabled){await(state?Promise.resolve(state):load());if(busy)throw new Error("正在保存，请稍后重试");var description=enabled?"开启多语言后，可通过右上角选择需要编辑的语言。":"关闭多语言后，前台和后台编辑使用系统主语言，其他语言旧链接显示页面不存在。已有语言和内容会保留。";if(!await window.CmsUI.confirm(description+"\n是否继续？"))return state;if(hasUnsaved()&&!await confirmLeave())return state;busy=true;draw();try{var next=await request("toggle",{enabled:enabled?1:0});next=next&&next.languages?next:await request("state");discardDialogs();switching=true;if(window.CmsNavigation&&window.CmsNavigation.resetLanguagePages)window.CmsNavigation.resetLanguagePages(next.edit);state=next;broadcast();await window.CmsUI.success("多语言设置已保存").finished;window.location.reload();return state}finally{busy=false;draw()}}async function remoteChange(){if(busy||switching||!state)return;try{var next=await request("state");if(String(next.revision)===String(state.revision))return;if(!await confirmLeave()){notice("语言已在其他窗口更改。当前未保存内容保留，请复制保存后刷新；旧上下文不能继续提交。");return}discardDialogs();switching=true;if(window.CmsNavigation&&window.CmsNavigation.resetLanguagePages)window.CmsNavigation.resetLanguagePages(next.edit);window.location.reload()}catch(error){notice(error.message)}}var picker=document.getElementById("cms-edit-language"),languageMenu=document.getElementById("cms-language-dropdown");if(picker){picker.addEventListener("click",function(event){event.stopPropagation();if(event.pointerType==="mouse")openPicker("hover");else if(picker.getAttribute("aria-expanded")==="true")closePicker(false);else openPicker()});picker.addEventListener("keydown",function(event){if(event.key==="ArrowDown"||event.key==="ArrowUp"){event.preventDefault();openPicker(event.key==="ArrowUp"?"last":"first")}else if(event.key==="Escape")closePicker(true)})}if(languageMenu)languageMenu.addEventListener("keydown",function(event){var items=pickerItems(),index=items.indexOf(document.activeElement),next=null;if(event.key==="ArrowDown")next=items[(index+1)%items.length];else if(event.key==="ArrowUp")next=items[(index-1+items.length)%items.length];else if(event.key==="Home")next=items[0];else if(event.key==="End")next=items[items.length-1];else if(event.key==="Escape"){event.preventDefault();closePicker(true)}else if(event.key==="Tab")closePicker(false);if(next){event.preventDefault();next.focus()}});document.addEventListener("click",function(event){var host=document.getElementById("cms-language-menu");if(host&&!host.contains(event.target))closePicker(false)});document.addEventListener("focusin",function(event){var host=document.getElementById("cms-language-menu");if(host&&!host.contains(event.target))closePicker(false)});window.addEventListener("blur",function(){closePicker(false)});
var languageHost=document.getElementById('cms-language-menu'),hoverTimer;
if(languageHost){
    languageHost.addEventListener('pointerenter',function(event){if(event.pointerType==='mouse'){clearTimeout(hoverTimer);document.getElementById('cms-account-toggle')?.dispatchEvent(new CustomEvent('cms-close-menu'));openPicker('hover')}});
    languageHost.addEventListener('pointerleave',function(event){if(event.pointerType==='mouse')hoverTimer=window.setTimeout(function(){if(!languageHost.contains(document.activeElement))closePicker(false)},140)});
    languageHost.querySelector('.cms-language-manage')?.addEventListener('click',function(event){event.preventDefault();closePicker(false);if(window.CmsNavigation)window.CmsNavigation.open('/web-admin/language/index');else window.location.assign('/web-admin/language/index')});
    picker?.addEventListener('cms-close-menu',function(){closePicker(false)});
}
if(state)draw();window.addEventListener("storage",function(event){if(event.key===eventKey)remoteChange()});try{channel=new BroadcastChannel(eventKey);channel.onmessage=remoteChange}catch(_){}window.CmsLanguage={ready:function(){return state?Promise.resolve(state):load()},refresh:load,get:function(){return state},switchTo:switchTo,toggle:toggle,hasUnsaved:hasUnsaved,isSwitching:function(){return switching}}})(window,document);
/* Workspace tools: hover, touch and keyboard share the same menu state. */
(function(window,document){
    'use strict';
    const host=document.getElementById('cms-account-menu'),toggle=document.getElementById('cms-account-toggle'),menu=document.getElementById('cms-account-options');
    if(!host||!toggle||!menu)return;
    let leaveTimer=null;
    function items(){return Array.from(menu.querySelectorAll('[role=menuitem]')).filter(item=>!item.disabled&&!item.closest('[hidden]'))}
    function close(focus=false){menu.hidden=true;toggle.setAttribute('aria-expanded','false');if(focus)toggle.focus()}
    function open(edge){clearTimeout(leaveTimer);document.getElementById('cms-edit-language')?.dispatchEvent(new CustomEvent('cms-close-menu'));menu.hidden=false;toggle.setAttribute('aria-expanded','true');if(edge){const list=items();(edge==='last'?list[list.length-1]:list[0])?.focus()}}
    toggle.addEventListener('click',event=>{event.preventDefault();if(event.pointerType==='mouse'||menu.hidden)open();else close()});
    toggle.addEventListener('cms-close-menu',()=>close());
    toggle.addEventListener('keydown',event=>{if(['ArrowDown','ArrowUp',' '].includes(event.key)){event.preventDefault();open(event.key==='ArrowUp'?'last':'first')}else if(event.key==='Escape')close(true)});
    host.addEventListener('pointerenter',event=>{if(event.pointerType==='mouse')open()});
    host.addEventListener('pointerleave',event=>{if(event.pointerType==='mouse')leaveTimer=window.setTimeout(()=>{if(!host.contains(document.activeElement))close()},140)});
    host.addEventListener('focusout',()=>window.setTimeout(()=>{if(!host.contains(document.activeElement))close()},0));
    menu.addEventListener('click',event=>{if(event.target.closest('[role=menuitem]'))close()});
    menu.addEventListener('keydown',event=>{const list=items(),index=list.indexOf(document.activeElement);let next=null;if(event.key==='ArrowDown')next=list[(index+1)%list.length];else if(event.key==='ArrowUp')next=list[(index-1+list.length)%list.length];else if(event.key==='Home')next=list[0];else if(event.key==='End')next=list[list.length-1];else if(event.key==='Escape'){event.preventDefault();close(true)}if(next){event.preventDefault();next.focus()}});
    document.addEventListener('click',event=>{if(!host.contains(event.target))close()});window.addEventListener('blur',()=>close());
})(window,document);
/* A restart completes only after the authenticated status endpoint sees a new master. */
(function(window,document){
    'use strict';
    const button=document.getElementById('cms-restart'),item=document.getElementById('cms-restart-item');
    if(!button||!item)return;
    const storageKey='cms-admin-restart-task-v1';
    let settings=null,busy=false;
    function documents(){const found=[document];function visit(doc){doc.querySelectorAll('iframe').forEach(frame=>{try{if(frame.contentDocument&&!found.includes(frame.contentDocument)){found.push(frame.contentDocument);visit(frame.contentDocument)}}catch(_){}})}visit(document);return found}
    function pending(){return documents().some(doc=>!!doc.querySelector('[data-cms-submit-busy][aria-busy="true"],[data-cms-upload-busy="1"],form[data-busy="1"]'))}
    function dirty(){return documents().some(doc=>doc.defaultView.CmsUI?.isDirty(doc)||!!doc.querySelector('dialog[open],.layui-layer-page,.layui-layer-iframe'))}
    async function request(action,values){
        const controller=new AbortController(),timeout=window.setTimeout(()=>controller.abort(),7000);
        try{
            const options={credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},signal:controller.signal};
            let suffix='';if(action==='restartstart'){options.method='POST';const body=new FormData();body.set('csrf',settings.csrf);options.body=body}else if(values)suffix='?'+new URLSearchParams(values);
            const response=await fetch('/web-admin/index/'+action+suffix,options);
            if(response.status===503||response.status===502)throw Error('temporary');
            let result;try{result=await response.json()}catch(_){throw Error('temporary')}
            if(!response.ok||result.code!==0){const error=Error(result.msg||'重启操作失败，请检查服务日志');error.definite=true;throw error}
            return result.data;
        }finally{window.clearTimeout(timeout)}
    }
    function remember(task){try{if(task)sessionStorage.setItem(storageKey,JSON.stringify(task));else sessionStorage.removeItem(storageKey)}catch(_){}}
    async function watch(id,progress){
        const deadline=Date.now()+Math.max(95000,Number(settings?.wait_ms)||0);
        while(Date.now()<deadline){
            await new Promise(resolve=>window.setTimeout(resolve,1000));
            try{
                const task=await request('restartstatus',{id});
                if(task.status==='ready'){
                    remember(null);await progress.finish({title:'重启成功',message:'程序已恢复，正在重新载入后台',kind:'success'});
                    documents().forEach(doc=>doc.defaultView.CmsUI?.clean(doc));window.location.reload();return;
                }
                if(task.status==='failed'){const error=Error(task.message||'重启未完成，请检查服务日志');error.definite=true;throw error}
                progress.update(task.message||'正在等待程序重启…');
            }catch(error){if(error.definite)throw error;progress.update('正在等待程序恢复连接，请稍候…')}
        }
        throw Error('等待服务恢复超时。请检查程序状态和服务日志，确认后刷新后台。');
    }
    async function run(existing){
        if(busy)return;let progress=null;busy=true;button.disabled=true;
        try{
            if(!existing){
                if(pending()){window.CmsUI.toast('当前有保存、上传或任务正在执行，请完成后再重启。',{kind:'error',duration:2000});return}
                const warning=dirty()?'\n当前有未保存内容，重启完成后刷新页面会放弃这些修改。':'';
                if(!await window.CmsUI.confirm('重启期间网站和后台会短暂不可访问，恢复后自动重新载入。'+warning,{title:'确认重启程序',confirmText:'确认重启',cancelText:'取消',icon:'restart'}))return;
            }
            progress=window.CmsUI.progress({title:'正在重启程序',message:existing?'正在检查重启进度…':'正在提交重启任务…'});
            const task=existing||await request('restartstart');remember({id:task.id,at:Date.now()});progress.update(task.message||'正在安排重启…');
            await watch(task.id,progress);
        }catch(error){remember(null);progress?.close();const text=error.definite||error.message!=='temporary'&&error.name!=='AbortError'?error.message:'未收到重启结果，请刷新后台检查服务状态。';window.CmsUI.toast(text,{kind:'error',duration:4000});window.CmsUI.notice(text)}
        finally{busy=false;button.disabled=false}
    }
    button.addEventListener('click',()=>run());
    async function initialize(){try{
        settings=await request('restartinfo');item.hidden=!settings.allowed;
        if(!settings.allowed)return;
        let existing;try{existing=JSON.parse(sessionStorage.getItem(storageKey)||'null')}catch(_){}
        if(existing&&/^[a-f0-9]{32}$/.test(existing.id)&&Date.now()-existing.at<Math.max(95000,Number(settings.wait_ms)||0))run(existing);else remember(null);
    }catch(_){item.hidden=true}}
    initialize();
})(window,document);
