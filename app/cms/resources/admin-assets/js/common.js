/** Compact query bars always retain at least one condition on narrow screens. */
function toggleSearchFormShow() {
    const $ = layui.$;
    $('.top-search-from').each(function () {
        const form = $(this), items = form.children('.layui-form-item');
        const fields = items.filter(function () { return !$(this).children('button').length; });
        const actions = items.not(fields), toggle = form.children('.toggle-btn');
        if (!fields.length || !toggle.length) return;
        function draw() {
            fields.show(); actions.show();
            const available = form.innerWidth() - (actions.outerWidth(true) || 0) - 72;
            let used = 0, keep = 0;
            fields.each(function () { const width = $(this).outerWidth(true) + 8; if (!keep || used + width <= available) { keep++; used += width; } });
            keep = Math.max(1, keep);
            const expandable = keep < fields.length;
            const expanded = form.data('cmsSearchExpanded') === true;
            if (expandable && !expanded) fields.slice(keep).hide();
            toggle.toggle(expandable);
            toggle.find('a').eq(0).toggleClass('layui-hide', expanded);
            toggle.find('a').eq(1).toggleClass('layui-hide', !expanded);
        }
        if (!form.data('cmsSearchBound')) {
            form.data('cmsSearchBound', true);
            toggle.on('click.cmsSearch', 'a', function () { form.data('cmsSearchExpanded', form.data('cmsSearchExpanded') !== true); draw(); });
            let scheduled = false;
            $(window).on('resize.cmsSearch', function () { if (scheduled) return; scheduled = true; requestAnimationFrame(function () { scheduled = false; draw(); }); });
        }
        draw();
    });
}
layui.$(function () { toggleSearchFormShow(); });
