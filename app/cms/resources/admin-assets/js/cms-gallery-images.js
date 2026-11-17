/* EyouCMS gallery captions use intro; title is independent image metadata. */
(function (window) {
    'use strict';
    var metadata = ['title', 'width', 'height', 'filesize', 'mime', 'add_time'];
    function row(image, url, intro) {
        var result = {image_url:String(url || '').trim(), intro:intro == null ? '' : String(intro)};
        metadata.forEach(function (name) {
            if (image && Object.prototype.hasOwnProperty.call(image, name)) result[name] = image[name];
        });
        return result;
    }
    function parse(value) {
        if (!value) return [];
        if (!Array.isArray(value)) {
            var text = String(value).trim();
            try { var decoded = JSON.parse(text); if (Array.isArray(decoded)) return parse(decoded); } catch (ignored) {}
            value = text.split(',');
        }
        return value.slice().sort(function (a, b) {
            return (parseInt(a && a.sort_order, 10) || 0) - (parseInt(b && b.sort_order, 10) || 0);
        }).map(function (image) {
            var url = typeof image === 'string' ? image : image && (image.image_url || image.url || image.path);
            if (!url || !String(url).trim()) return null;
            // Never infer a caption from title: imported original images and
            // old candidate title-only captions cannot be distinguished in DB.
            var result = row(image, url, image && typeof image === 'object' ? image.intro : '');
            result.url = result.image_url;
            return result;
        }).filter(function (image) { return image !== null; });
    }
    window.cmsGalleryImages = {parse:parse, row:row};
})(window);
