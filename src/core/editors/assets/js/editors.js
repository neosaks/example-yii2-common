'use strict';

/**
 * @constructor
 * @author Maxim Chichkanov
 */
function Editor(selectors, editors) {
    /**
     * Customizable constants
     */
    const MESSAGES = {
        'withoutEditor': 'Без редактора'
    };

    const DESTROY_EDITOR = '__destroy__';

    /**
     * Private properties
     */
    var _selectors = new Map();
    var _editors = new Map();
    var _runned = new Map();
    var _displayElement;

    /**
     * Run
     */
    registerSelectors(selectors);
    registerEditors(editors);
    renderToolbars();

    /**
     * Description.
     * @param {object} selectors
     * @return {void}
     */
    function registerSelectors(selectors) {
        for (let selector in selectors) {
            if (Array.isArray(selectors[selector])) {
                let editors = selectors[selector].map(function(editor) {
                    return editor;
                });

                _selectors.set(selector, editors);
            }
        }
    }

    /**
     * Description.
     * @param {object} editors
     * @return void
     */
    function registerEditors(editors) {
        for (let name in editors) {
            editors[name]['name'] = name;
            _editors.set(name, editors[name]);
        }
    }

    /**
     * Description.
     * @param {string} selector
     * @param {string} editor
     * @return {void}
     */
    function run(selector, editor) {
        if (_editors.has(editor)) {

            destroy(selector);

            _editors.get(editor).run(selector);
            _runned.set(selector, _editors.get(editor));

            if (_displayElement) {
                _displayElement.innerHTML = editor;
            }
        }
    }

    /**
     * Description.
     * @param {string} selector
     * @return {void}
     */
    function destroy(selector) {
        if (_runned.has(selector)) {
            _runned.get(selector).destroy(selector);
            _runned.delete(selector);

            if (_displayElement) {
                _displayElement.innerHTML = MESSAGES['withoutEditor'];
            }
        }
    }

    /**
     * Description.
     * @return {void}
     */
    function renderToolbars() {
        _selectors.forEach(function(editors, selector) {
            let toolbarContainer = document.createElement('div');
            toolbarContainer.classList.add('editors-toolbar', 'my-2');
            toolbarContainer.appendChild(renderDropdown(selector));
            let editorElement = document.querySelector(selector);
            if (editorElement) {
                let parentElement = editorElement.parentElement;
                parentElement.insertBefore(toolbarContainer, editorElement);
            }
        });
    }

    /**
     * Description.
     * @param {string} selector
     * @return {void}
     */
    function renderDropdown(selector) {
        let btnGroup = document.createElement('div');
        btnGroup.classList.add('btn-group');

        let displayBtn = document.createElement('button');
        displayBtn.type = 'button';
        displayBtn.classList.add('btn', 'btn-secondary');
        displayBtn.innerHTML = _runned.has(selector)
            ? _runned.get(selector)['name'] : MESSAGES['withoutEditor'];
        btnGroup.appendChild(displayBtn);

        let dropdownBtn = document.createElement('button');
        dropdownBtn.dataset.toggle = 'dropdown';
        dropdownBtn.type = 'button';
        dropdownBtn.classList.add(
            'btn', 'btn-secondary',
            'dropdown-toggle',
            'dropdown-toggle-split'
        );
        btnGroup.appendChild(dropdownBtn);

        let dropdownMenu = document.createElement('div');
        dropdownMenu.classList.add('dropdown-menu');
        btnGroup.appendChild(dropdownMenu);

        let editors = _selectors.get(selector);
        if (editors.includes('*')) {
            editors = getAllEditorNames();
        }

        editors.forEach(function(editor) {
            let dropdownItem = document.createElement('button');
            dropdownItem.classList.add('dropdown-item');
            dropdownItem.dataset.editor = editor;
            dropdownItem.dataset.selector = selector;
            dropdownItem.type = 'button';
            dropdownItem.innerHTML = editor;
            dropdownMenu.appendChild(dropdownItem);
        });

        // @todo (task:224)
        // if (true) {
            let dropdownDivider = document.createElement('div');
            dropdownDivider.classList.add('dropdown-divider');
            let dropdownItemDestroy = document.createElement('button');
            dropdownItemDestroy.classList.add('dropdown-item');
            dropdownItemDestroy.dataset.editor = DESTROY_EDITOR;
            dropdownItemDestroy.dataset.selector = selector;
            dropdownItemDestroy.type = 'button';
            dropdownItemDestroy.innerHTML = MESSAGES['withoutEditor'];
            dropdownMenu.appendChild(dropdownDivider);
            dropdownMenu.appendChild(dropdownItemDestroy);
        // }

        // handler
        dropdownMenu.addEventListener('click', function(event) {
            if (_selectors.has(event.target.dataset.selector)
                && _editors.has(event.target.dataset.editor)) {
                run(event.target.dataset.selector,
                    event.target.dataset.editor);
            } else if (event.target.dataset.editor === DESTROY_EDITOR) {
                destroy(event.target.dataset.selector);
            }
        });
        _displayElement = displayBtn;

        return btnGroup;
    }

    /**
     * Description.
     * @return {void}
     */
    function getAllEditorNames() {
        return Array.from(_editors.keys());
    }
};
