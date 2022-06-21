import $ from 'jquery';
import Sortable from 'sortablejs';

let body = $('body');

class Template {
    constructor(container) {
        this.container = $(container);

        if (this.getName() === undefined) {
            this.container = this.container.closest('[data-lev-array-name]');
        }
    }

    getName() {
        return this.container.data('lev-array-name') || '';
    }

    getKeyPlaceholder() {
        return this.container.data('lev-array-keyname') || 'Key';
    }

    getValuePlaceholder() {
        return this.container.data('lev-array-valuename') || 'Value';
    }

    isValueOnly() {
        return this.container.find('[data-lev-array-mode="value_only"]:first').length || false;
    }

    isTextArea() {
        return this.container.data('lev-array-textarea') || false;
    }

    shouldBeDisabled() {
        // check for toggleables, if field is toggleable and it's not enabled, render disabled
        let toggle = this.container.closest('.form-field').find('[data-lev-field="toggleable"] input[type="checkbox"]');
        return toggle.length && toggle.is(':not(:checked)');
    }

    getNewRow() {
        let tpl = '';
        const value = this.isTextArea()
            ? `<textarea ${this.shouldBeDisabled() ? 'disabled="disabled"' : ''} data-lev-array-type="value" name="" placeholder="${this.getValuePlaceholder()}"></textarea>`
            : `<input ${this.shouldBeDisabled() ? 'disabled="disabled"' : ''} data-lev-array-type="value" type="text" name="" value=""  placeholder="${this.getValuePlaceholder()}" />`;

        if (this.isValueOnly()) {
            tpl += `
            <div class="form-row array-field-value_only" data-lev-array-type="row">
                <span data-lev-array-action="sort" class="fa fa-bars"></span>
                ${value}
            `;
        } else {
            tpl += `
            <div class="form-row" data-lev-array-type="row">
                <span data-lev-array-action="sort" class="fa fa-bars"></span>
                <input ${this.shouldBeDisabled() ? 'disabled="disabled"' : ''} data-lev-array-type="key" type="text" value="" placeholder="${this.getKeyPlaceholder()}" />
                ${value}
            `;
        }

        tpl += `
            <span data-lev-array-action="rem" class="fa fa-minus"></span>
            <span data-lev-array-action="add" class="fa fa-plus"></span>
        </div>`;

        return tpl;
    }
}

export default class ArrayField {
    constructor() {
        body.on('input', '[data-lev-array-type="key"], [data-lev-array-type="value"]', (event) => this.actionInput(event));
        body.on('click touch', '[data-lev-array-action]:not([data-lev-array-action="sort"])', (event) => this.actionEvent(event));

        this.arrays = $();

        $('[data-lev-field="array"]').each((index, list) => this.addArray(list));
        $('body').on('mutation._lev', this._onAddedNodes.bind(this));
    }

    addArray(list) {
        list = $(list);

        list.find('[data-lev-array-type="container"]').each((index, container) => {
            container = $(container);
            if (container.data('array-sort') || container[0].hasAttribute('data-array-nosort')) { return; }

            container.data('array-sort', new Sortable(container.get(0), {
                handle: '.fa-bars',
                animation: 150
            }));
        });
    }

    actionInput(event) {
        let element = $(event.target);
        let type = element.data('lev-array-type');

        this._setTemplate(element);

        let template = element.data('array-template');
        let keyElement = type === 'key' ? element : element.siblings('[data-lev-array-type="key"]:first');
        let valueElement = type === 'value' ? element : element.siblings('[data-lev-array-type="value"]:first');

        let escaped_name = !template.isValueOnly() ? keyElement.val() : this.getIndexFor(element);
        escaped_name = escaped_name.toString().replace(/\[/g, '%5B').replace(/]/g, '%5D');
        let name = `${template.getName()}[${escaped_name}]`;

        if (!template.isValueOnly() && (!keyElement.val() && !valueElement.val())) {
            valueElement.attr('name', '');
        } else {
            // valueElement.attr('name', !valueElement.val() ? template.getName() : name);
            valueElement.attr('name', name);
        }

        this.refreshNames(template);
    }

    actionEvent(event) {
        event && event.preventDefault();
        let element = $(event.target);
        let action = element.data('lev-array-action');
        let container = element.parents('[data-lev-array-type="container"]');

        this._setTemplate(element);

        this[`${action}Action`](element);

        let siblings = container.find('> div');
        container[siblings.length > 1 ? 'removeClass' : 'addClass']('one-child');
    }

    addAction(element) {
        let template = element.data('array-template');
        let row = element.closest('[data-lev-array-type="row"]');

        row.after(template.getNewRow());
    }

    remAction(element) {
        let template = element.data('array-template');
        let row = element.closest('[data-lev-array-type="row"]');
        let isLast = !row.siblings().length;

        if (isLast) {
            let newRow = $(template.getNewRow());
            row.after(newRow);
            newRow.find('[data-lev-array-type="value"]:last').attr('name', template.getName());
        }

        row.remove();
        this.refreshNames(template);
    }

    refreshNames(template) {
        if (!template.isValueOnly()) { return; }

        let row = template.container.find('> div > [data-lev-array-type="row"]');
        let inputs = row.find('[name]:not([name=""])');

        inputs.each((index, input) => {
            input = $(input);
            let name = input.attr('name');
            name = name.replace(/\[\d+\]$/, `[${index}]`);
            input.attr('name', name);
        });

        if (!inputs.length) {
            row.find('[data-lev-array-type="value"]').attr('name', template.getName());
        }
    }

    getIndexFor(element) {
        let template = element.data('array-template');
        let row = element.closest('[data-lev-array-type="row"]');

        return template.container.find(`${template.isValueOnly() ? '> div ' : ''} > [data-lev-array-type="row"]`).index(row);
    }

    _setTemplate(element) {
        if (!element.data('array-template')) {
            element.data('array-template', new Template(element.closest('[data-lev-array-name]')));
        }
    }

    _onAddedNodes(event, target/* , record, instance */) {
        let arrays = $(target).find('[data-lev-field="array"]');
        if (!arrays.length) { return; }

        arrays.each((index, list) => {
            list = $(list);
            if (!~this.arrays.index(list)) {
                this.addArray(list);
            }
        });
    }
}

export let Instance = new ArrayField();
