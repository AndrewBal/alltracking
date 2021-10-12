var obj = {};
CKEDITOR.dialog.add('shortcutDialog', function (editor) {
    function render_opt(j) {
        var select_opt = '<option value="">- Выбрать -</option>';
        if (j != undefined) {
            for (let [key, value] of Object.entries(j)) {
                select_opt += '<option value="' + key + '">' + (value.entity == undefined ? value : value.entity) + '</option>';
            }
        }
        return select_opt;
    }

    var shortCodeData = jQuery.ajax({
        url: '/callback/shortcut',
        data: {},
        method: 'POST',
        success: function (result) {
            obj = result;
            var select_opt = '';
            jQuery('select.shortcut-dialog-select-entity').html(render_opt(result)).prop('disabled', false)
                .change(function (e) {
                    var v = $(this).val();
                    jQuery('select.shortcut-dialog-select-object').prop('multiple', false);
                    if (v) {
                        if (result[v]['multiple'] != undefined && result[v]['multiple']) {
                            jQuery('select.shortcut-dialog-select-object').prop('multiple', true);
                        }
                        jQuery('select.shortcut-dialog-select-object').html(render_opt(result[v]['items'])).prop('disabled', false);
                    } else {
                        jQuery('select.shortcut-dialog-select-object').html('<option value="">- Выбрать -</option>').prop('disabled', true);
                    }
                });

        }
    });
    return {
        title: 'Вставить элемент в тело содержимое',
        minWidth: 450,
        minHeight: 200,
        contents: [
            {
                id: 'Params',
                label: 'Параметры',
                elements: [
                    {
                        type: 'select',
                        id: 'entity',
                        default: '',
                        className: 'shortcut-dialog-select-entity',
                        label: 'Выберите элемент, который хотите вставить в контент',
                        items: [],
                        inputStyle: 'width: 450px !important;',
                        validate: CKEDITOR.dialog.validate.notEmpty("Поле не может быть пустым."),
                    },
                    {
                        type: 'select',
                        id: 'id',
                        default: '',
                        className: 'shortcut-dialog-select-object',
                        label: 'Выберите объект',
                        items: [],
                        inputStyle: 'width: 450px !important;',
                        validate: CKEDITOR.dialog.validate.notEmpty("Поле не может быть пустым."),
                    },
                    {
                        type: 'html',
                        html: '<span class="uk-text-primary">Тонкая настройка.</span>'
                    },
                    {
                        type: 'text',
                        className: 'shortcut-dialog-select-option-view',
                        id: 'option_view',
                        label: 'Файл шаблона для рендерига'
                    },
                    {
                        type: 'html',
                        html: '<span class="uk-text-muted">Путь к шаблону начиная от папка SHORTCUTS "./views/frontend/{DEVICE}/shortcuts/___ВОТ ОТСЮДА___".</span>'
                    },
                    {
                        type: 'text',
                        className: 'shortcut-dialog-select-option-index',
                        id: 'option_index',
                        label: 'ID для добавляемого элемента'
                    },
                    {
                        type: 'html',
                        html: '<span class="uk-text-muted">ID добавляемый в html, чтобы избежать задвоения на элементов на странице, если у него в настройках указан ID.</span>'
                    }
                ]
            }
        ],
        onOk: function () {
            var dialog = this;
            var entity = dialog.getValueOf('Params', 'entity'),
                id = jQuery('select.shortcut-dialog-select-object').val(),
                // wrapperHtml = dialog.getValueOf('Params', 'wrapper'),
                // classHtml = dialog.getValueOf('Params', 'class'),
                option_view = dialog.getValueOf('Params', 'option_view'),
                option_index = dialog.getValueOf('Params', 'option_index'),
                text = null;
            if (Array.isArray(id)) {
                id.join(',');
            }
            text = '@short(' + entity + ';' + id;
            if (option_view) text += ';view:' + option_view;
            if (option_index) text += ',index:' + option_index;
            text += ')';
            editor.insertHtml(text);
        },
        onShow: function () {
            if (Object.keys(obj).length == 0) jQuery('select.shortcut-dialog-select-entity, select.shortcut-dialog-select-object').html('<option value="">- Выбрать -</option>').prop('disabled', true);
        },
        onHide: function () {
            jQuery('select.shortcut-dialog-select-entity, select.shortcut-dialog-select-object, select.shortcut-dialog-select-wrapper, textarea.shortcut-dialog-select-options, input.shortcut-dialog-select-wrapper-class').val('');
            jQuery('select.shortcut-dialog-select-object').html('<option value="">- Выбрать -</option>').prop('disabled', true);
            jQuery('select.shortcut-dialog-select-object').prop('multiple', false);
        }
    };
});
