(function (window) {
    'use strict';
    function candidates(metadata) { return (metadata.modules || []).filter(function (module) { return module.sidebar !== false; }); }
    function normalize(metadata, values) {
        var allowed = new Set(candidates(metadata).map(function (module) { return module.id; }));
        return Array.from(new Set(values.filter(function (id) { return typeof id === 'string' && allowed.has(id); })));
    }
    function defaults(metadata) { return normalize(metadata, metadata.sidebar_defaults || []); }
    function key(metadata) { var scope = String(metadata.preference_scope || ''); return /^[1-9]\d*$/.test(scope) ? 'cms-shortcuts-v1:account-' + scope : null; }
    function read(metadata) {
        var storageKey = key(metadata);
        if (storageKey) try { var saved = JSON.parse(window.localStorage.getItem(storageKey)); if (Array.isArray(saved)) return normalize(metadata, saved); } catch (_) {}
        return defaults(metadata);
    }
    function save(metadata, values) {
        var storageKey = key(metadata);
        if (!storageKey) throw new Error('暂时无法识别当前账号，请刷新后台后重试');
        var result = normalize(metadata, values);
        try { window.localStorage.setItem(storageKey, JSON.stringify(result)); } catch (_) { throw new Error('浏览器未能保存常用导航，请检查本地存储设置'); }
        return result;
    }
    function leaf(module) {
        var page = module.entry;
        return {id:page.id,pid:0,key:page.key,title:module.title,name:module.title,value:page.id,href:page.href,type:1,icon:'layui-icon '+module.icon,module:module.id,weight:module.weight};
    }
    function custom(metadata) { return (metadata.menus || []).filter(function (item) { return !String(item.id).startsWith('cms-nav-group-'); }); }
    window.CmsShortcuts = {candidates:candidates,normalize:normalize,defaults:defaults,key:key,read:read,save:save,leaf:leaf,custom:custom};
})(window);
