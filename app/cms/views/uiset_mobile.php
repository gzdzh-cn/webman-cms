<?php
$escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$json = static fn($value) => json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><title>移动网站可视化编辑</title><meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{margin:0;background:#eef1f5;font:14px system-ui,sans-serif}header{padding:12px;background:white;border-bottom:1px solid #ccd5df;display:flex;gap:15px;align-items:center}a{color:#146d9a}button{font:inherit;padding:6px 12px;cursor:pointer}.preview{margin:20px;width:390px;max-width:calc(100vw - 40px)}#workspace{border:1px solid #b2bec9;background:white;width:100%;height:calc(100vh - 110px)}.admincp-container-right{display:none;position:fixed;top:70px;left:430px;right:20px;bottom:20px;background:white}.right-show{display:block}#workspace_right{border:0;width:100%;height:100%}@media(max-width:750px){.admincp-container-right{left:10px;right:10px;z-index:2}}</style>
<script>var __root_dir__=<?= $json($base) ?>,__lang__=<?= $json($lang) ?>,v='mobile',__urltypeid__=<?= $tid ?>,__urlaid__=<?= $aid ?>,__iframe__=1,admin_basefile=<?= $json($base . '/app/admin') ?>;</script>
<script src="<?= $escape(\app\cms\AssetManifest::url($base . '/public/static/common/js/jquery.min.js')) ?>"></script><script src="<?= $escape(\app\cms\AssetManifest::url($base . '/public/plugins/layer-v3.1.0/layer.js')) ?>"></script><script src="<?= $escape(\app\cms\AssetManifest::url($base . '/public/static/common/js/eyou_mobile_tpl.js')) ?>"></script>
</head><body><header><a href="<?= $escape($base) ?>/app/admin">返回后台</a><span>移动网站可视化编辑</span><button type="button" onclick="document.querySelector('.admincp-container-right').classList.remove('right-show')">关闭编辑面板</button></header>
<main class="preview"><iframe id="workspace" name="workspace" src="<?= $escape($murl) ?>" title="移动网站预览"></iframe></main><aside class="admincp-container-right"><iframe id="workspace_right" name="workspace_right" title="可视化编辑面板"></iframe></aside>
</body></html>
