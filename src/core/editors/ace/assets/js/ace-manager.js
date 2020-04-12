'use strict';

/**
 * @author Maxim Chichkanov
 */
const aceManager = new (function() {
    /**
     * Private properties
     */
    var instances = new Map();

    /**
     * Public methods
     */
    this.init = init;
    this.destroy = destroy;

    /**
     *
     */
    function init(selector, options) {
        let formControl = document.querySelector(selector);
        if (formControl.name) {
            if (instances.has(selector)) {
                destroy(selector);
            }

            let parentElement = formControl.parentElement;
            let container = document.createElement('div');

            container.id = formControl.name;
            container.style.height = '300px';
            formControl.style.display = 'none';

            parentElement.insertBefore(container, formControl);

            let editor = ace.edit(container.id, {
                mode: "ace/mode/html",
                selectionStyle: "text"
            });

            editor.setShowPrintMargin(false);

            editor.getSession().setValue(formControl.value);
            editor.getSession().on('change', function() {
                formControl.value = editor.getSession().getValue();
                formControl.dispatchEvent(new Event('change'));
            });

            instances.set(selector, editor);
        }
    }

    /**
     *
     */
    function destroy(selector) {
        if (instances.has(selector)) {
            instances.get(selector).destroy()
            instances.get(selector).container.remove();
            instances.delete(selector);

            let formControl = document.querySelector(selector);
            if (formControl) formControl.style.display = null;
        }
    }
})();