<?php
/** Enterprise visual editor for the original EyouCMS browser protocol. */
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$json = static fn($value) => json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
$value = (string)($field['info']['value'] ?? '');
$labels = ['text' => '文字', 'html' => '富文本', 'type' => '栏目', 'arclist' => '文档列表', 'channel' => '栏目列表', 'upload' => '图片', 'background' => '背景图片', 'map' => '地图', 'code' => '源代码'];
$selected = explode(',', (string)($field['typeid'] ?? 0));
if ($action === 'upload' && $oldHtml === '') $oldHtml = htmlspecialchars_decode($value, ENT_QUOTES);
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $escape($labels[$action]) ?>编辑</title>
<style>body{font:14px/1.6 system-ui,sans-serif;margin:0;color:#243344;background:#fff}main{max-width:900px;margin:auto;padding:20px}label{display:block;margin:14px 0 5px}input,select,textarea,button{box-sizing:border-box;font:inherit}input:not([type=radio]):not([type=file]),select,textarea{width:100%;border:1px solid #bbc6d1;border-radius:4px;padding:8px}textarea{min-height:260px}button{padding:9px 20px;border:0;border-radius:4px;background:#1681b7;color:#fff;cursor:pointer;margin:16px 8px 0 0}button:disabled{opacity:.5}#status{min-height:24px;white-space:pre-wrap}#status.error{color:#b22020}#status.success{color:#126c3b}.choice{display:inline-block;margin-right:20px}iframe.map{width:100%;height:260px;border:1px solid #d2dce5}#image-preview{max-width:100%;max-height:180px;display:none}</style>
<?php if ($action === 'html'): ?>
<script src="<?= $escape(\app\cms\AssetManifest::url($base . '/public/plugins/Ueditor/ueditor.config.js')) ?>"></script>
<script src="<?= $escape(\app\cms\AssetManifest::url($base . '/public/plugins/Ueditor/ueditor.all.min.js')) ?>"></script>
<script src="<?= $escape(\app\cms\AssetManifest::url($base . '/public/plugins/Ueditor/lang/zh-cn/zh-cn.js')) ?>"></script>
<?php endif ?>
</head><body><main>
<form id="post_form" enctype="multipart/form-data">
<?php foreach (['id' => $field['id'], 'type' => $action, 'page' => $scope['page'], 'lang' => $scope['lang'], 'idcode' => $scope['idcode'], 'v' => $v] as $key => $entry): ?>
<input type="hidden" name="<?= $escape($key) ?>" value="<?= $escape($entry) ?>">
<?php endforeach ?>
<?php if (in_array($action, ['text', 'html', 'code'], true)): ?>
<label for="post_content"><?= $escape($labels[$action]) ?>内容</label>
<textarea id="post_content" name="content" spellcheck="false"><?= $escape($value) ?></textarea>
<?php elseif (in_array($action, ['type', 'arclist', 'channel'], true)): ?>
<label for="typeid">请选择栏目</label><select id="typeid" name="typeid">
<option value="0"<?= in_array('0', $selected, true) ? ' selected' : '' ?>>当前栏目</option>
<?php foreach ($categories as $category): ?>
<option value="<?= (int)$category['id'] ?>"<?= in_array((string)$category['id'], $selected, true) ? ' selected' : '' ?>><?= $escape(str_repeat('　', min(10, $category['depth'])) . $category['typename']) ?></option>
<?php endforeach ?>
</select>
<?php if ($action === 'arclist'): ?><label for="row">显示条数</label><input id="row" name="row" type="number" min="1" max="1000" value="<?= $escape($field['info']['row'] ?? 10) ?>"><?php endif ?>
<?php elseif (in_array($action, ['upload', 'background'], true)): ?>
<label class="choice"><input type="radio" name="is_remote" value="0" checked> 本地图片</label>
<label class="choice"><input type="radio" name="is_remote" value="1"> 远程图片</label>
<label for="litpic_local">选择本地图片</label><input id="litpic_local" name="litpic_local" type="file" accept="image/png,image/jpeg,image/gif,image/webp,image/bmp,image/x-icon">
<label for="litpic_remote">远程图片地址</label><input id="litpic_remote" name="litpic_remote" type="url" placeholder="https://" value="<?= $action === 'background' && preg_match('#^https?://#i', $value) ? $escape($value) : '' ?>">
<input type="hidden" name="oldhtml" value="<?= $escape($oldHtml) ?>"><p><img id="image-preview" alt="当前图片"></p>
<?php elseif ($action === 'map'): ?>
<label for="address">搜索地址</label><input id="address" type="text" autocomplete="off"><button type="button" id="geocode">搜索地址</button>
<label for="location">地图坐标（经度,纬度）</label><input id="location" name="location" value="<?= $escape($value ?: '110.34678620675,20.001944329655') ?>" required>
<label for="zoom">地图缩放级别</label><input id="zoom" name="zoom" type="number" min="1" max="21" value="<?= (int)($field['info']['zoom'] ?? 13) ?>">
<p><iframe class="map" id="map-preview" title="地图预览"></iframe></p>
<?php endif ?>
<button type="submit" id="submitBtn">确认提交</button><div id="status" role="status" aria-live="polite"></div>
</form></main>
<script>
(() => {
 const form=document.getElementById('post_form'), status=document.getElementById('status'), submit=document.getElementById('submitBtn');
 const action=<?= $json($action) ?>, endpoint=<?= $json($endpoint) ?>, base=<?= $json($base) ?>;
 let editor;
 const show=(text,ok=false)=>{status.textContent=text;status.className=ok?'success':'error';};
 const old=()=>{try{return parent!==window&&typeof parent.eyou_getOldHtml==='function'?parent.eyou_getOldHtml():'';}catch{return '';}};
 if(['text','html','code'].includes(action)&&!form.elements.namedItem('content').value) form.elements.namedItem('content').value=old();
 if(action==='html'&&window.UE){
   editor=UE.getEditor('post_content',{serverUrl:base+'/index.php?m=api&c=Uiset&a=editor_config',initialFrameWidth:'100%',initialFrameHeight:320,autoHeightEnabled:false,toolbars:[['source','bold','italic','underline','forecolor','backcolor','removeformat','insertorderedlist','insertunorderedlist','link','unlink','undo','redo']],initialContent:form.elements.namedItem('content').value});
 }
 const updateMap=()=>{const location=document.getElementById('location').value.trim(),zoom=document.getElementById('zoom').value;if(/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(location))document.getElementById('map-preview').src=<?= $json(\app\cms\AssetManifest::url($base . '/public/plugins/Ueditor/dialogs/map/show.html')) ?>+'#center='+encodeURIComponent(location)+'&zoom='+encodeURIComponent(zoom)+'&width=600&height=260&markers='+encodeURIComponent(location)+'&markerStyles=l,A';};
 if(action==='map'){
   updateMap();document.getElementById('location').addEventListener('change',updateMap);document.getElementById('zoom').addEventListener('change',updateMap);
   document.getElementById('geocode').addEventListener('click',async()=>{try{const url=new URL(<?= $json($geocoder) ?>,location.origin);url.searchParams.set('address',document.getElementById('address').value);url.searchParams.set('lang',<?= $json($scope['lang']) ?>);const res=await fetch(url,{credentials:'same-origin'}),data=await res.json();if(data.code!==1)throw Error(data.msg||'请求失败');document.getElementById('location').value=data.data.map;updateMap();show(data.msg,true);}catch(e){show(e.message);}});
 }
 if(['upload','background'].includes(action)){
   const image=document.getElementById('image-preview');let previous=<?= $json($value) ?>;
   if(action==='upload'){const html=new DOMParser().parseFromString(form.elements.namedItem('oldhtml').value||old(),'text/html');const img=html.querySelector('img');if(img)previous=img.getAttribute('src')||'';if(!form.elements.namedItem('oldhtml').value)form.elements.namedItem('oldhtml').value=html.body.innerHTML;}
   if(/^(https?:\/\/|\/)/i.test(previous)){image.src=previous;image.style.display='inline';if(/^https?:\/\//i.test(previous)){form.querySelector('input[name=is_remote][value="1"]').checked=true;form.elements.namedItem('litpic_remote').value=previous;}}
 }
 form.addEventListener('submit',async event=>{
   event.preventDefault();submit.disabled=true;show('正在保存…',true);
   try{
     if(editor)editor.sync();
     const res=await fetch(endpoint,{method:'POST',body:new FormData(form),credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}}),data=await res.json();
     if(data.code!==1)throw Error(data.msg||'保存失败');show(data.msg||'操作成功',true);
     if(parent!==window){
       const workspace=parent.document.getElementById('workspace');
       if(workspace){workspace.src=workspace.src;parent.document.querySelector('.admincp-container-right')?.classList.remove('right-show');}
       else if(parent.layer){const index=parent.layer.getFrameIndex(window.name);parent.location.reload();parent.layer.close(index);}
     }
   }catch(error){show(error.message);}finally{submit.disabled=false;}
 });
})();
</script></body></html>
