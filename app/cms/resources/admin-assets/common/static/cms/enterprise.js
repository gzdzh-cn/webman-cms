/* Compatibility for enterprise templates shipped with an undefined searchForm handler. */
(() => {
    if (typeof window.searchForm === 'function') return;
    window.searchForm = function (form) {
        const candidate = form instanceof HTMLFormElement ? form :
            (window.event?.currentTarget instanceof HTMLFormElement ? window.event.currentTarget : document.activeElement?.form);
        const input = candidate?.elements.namedItem('keywords') || document.getElementById('keywords');
        if (!(input instanceof HTMLInputElement)) return true;
        const value = input.value.trim();
        if (!value || /^(请输入关键词|请输入关键字|Please enter keywords)$/i.test(value)) {
            if (window.layer?.msg) window.layer.msg('请输入关键词');
            input.focus();
            return false;
        }
        input.value = value;
        return true;
    };
})();
