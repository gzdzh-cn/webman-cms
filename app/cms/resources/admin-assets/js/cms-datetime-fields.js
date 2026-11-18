/* Display local calendar time; persist epoch seconds independently of PHP/browser timezone. */
(function (window) {
    'use strict';
    var epochAttribute = 'data-cms-datetime-epoch', originalAttribute = 'data-cms-datetime-original';
    function restore(input, value) {
        if (!input) return;
        input.step = '1';
        input.removeAttribute(epochAttribute); input.removeAttribute(originalAttribute);
        var numeric = value !== null && value !== '' && /^[+-]?\d+$/.test(String(value));
        if (numeric) {
            var epoch = Number(value), date = new Date(epoch * 1000);
            var pad = function (part) { return String(part).padStart(2, '0'); };
            input.value = epoch && !isNaN(date.getTime()) ? String(date.getFullYear()).padStart(4, '0') + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds()) : '';
            input.setAttribute(epochAttribute, String(epoch));
            // Read the actual control after assignment: browsers normalize its string format.
            input.setAttribute(originalAttribute, input.value);
        } else {
            input.value = value == null ? '' : String(value).replace(' ', 'T');
        }
        input.defaultValue = input.value;
    }
    function render(field) {
        var input = window.document.createElement('input');
        input.type = 'datetime-local'; input.name = field.name; input.className = 'layui-input';
        if (field.ifrequire == 1) input.setAttribute('lay-verify', 'required');
        restore(input, field.dfvalue == null ? '' : field.dfvalue);
        return input.outerHTML;
    }
    function collect(formData, container) {
        container = container || window.document;
        Array.prototype.forEach.call(container.querySelectorAll('input[data-cms-datetime], [data-field-type="datetime"] input[type="datetime-local"]'), function (input) {
            if (!input.name) return;
            if (input.disabled) { delete formData[input.name]; return; }
            if (input.hasAttribute(epochAttribute) && input.value === input.getAttribute(originalAttribute)) {
                // A DST fold may have two epochs with this same visible local calendar time.
                formData[input.name] = Number(input.getAttribute(epochAttribute));
            } else if (!input.value) {
                formData[input.name] = '';
            } else {
                // Native Date defines edited DST gaps consistently with the browser's timezone.
                var date = new Date(input.value);
                formData[input.name] = isNaN(date.getTime()) ? '' : Math.floor(date.getTime() / 1000);
            }
        });
        return formData;
    }
    window.cmsDatetimeFields = {render:render,restore:restore,collect:collect};
})(window);
