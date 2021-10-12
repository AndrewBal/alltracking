<?php

namespace App\Libraries;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Fields
{
    protected $params;
    protected $errors;
    protected $formId;
    protected $fieldId;
    protected $fieldClass;
    protected $fieldBoxClass;
    protected $fieldType;
    protected $fieldLabel;
    protected $fieldName;
    protected $fieldRequired;
    protected $fieldAttributes;
    protected $uiKit;

    public function __construct($name, $variables = [])
    {
        $variables = collect($variables);
        $this->uiKit = (boolean)$variables->has('uikit');
        $this->errors = session('errors');
        $this->formId = $variables->get('form_id');
        $this->fieldId = $variables->has('id') ? Str::slug($variables->get('id')) : self::render_field_id($name, $this->formId);
        $this->fieldType = $variables->get('type', 'text');
        $this->fieldRequired = $variables->get('required', FALSE);
        $this->fieldAttributes = $variables->get('attributes');
        $this->fieldBoxClass = $variables->get('item_class', []);
        if ($this->uiKit) $this->fieldBoxClass = array_merge($this->fieldBoxClass, ['uk-margin']);
        $_use_placeholder = $variables->get('placeholder', FALSE);
        $_variable_label = $variables->get('label');
        if ($_variable_label) $this->fieldLabel = __($_variable_label);
        if (isset($this->fieldAttributes['placeholder'])) {
            $_variable_label = $this->fieldAttributes['placeholder'];
            $_use_placeholder = TRUE;
            $this->fieldAttributes['placeholder'] = __($this->fieldAttributes['placeholder']) . ($this->fieldRequired ? '*' : NULL);
        } elseif ($_use_placeholder && $_variable_label) {
            $this->fieldAttributes['placeholder'] = $this->fieldLabel . ($this->fieldRequired ? '*' : NULL);
        }
        $_field_options = [
            'form_id'      => $this->formId,
            'type'         => $this->fieldType,
            'id'           => $this->fieldId,
            'box_id'       => "box-{$this->fieldId}",
            'box_class'    => $this->fieldBoxClass,
            'label'        => !$_use_placeholder ? $this->fieldLabel : NULL,
            'base_label'   => $_variable_label,
            'name'         => self::render_field_name($name),
            'old'          => $name,
            'value'        => $variables->get('value'),
            'values'       => $variables->get('values', []),
            'selected'     => old($name, ($variables->has('selected') ? $variables->get('selected') : ($variables->has('value') ? $variables->get('value') : NULL))),
            'attributes'   => (array)$this->fieldAttributes,
            'help'         => $variables->get('help'),
            'error'        => $this->errors && $this->errors->has($name) ? $this->errors->first($name) : NULL,
            'required'     => $this->fieldRequired,
            'prefix'       => $variables->get('prefix'),
            'suffix'       => $variables->get('suffix'),
            'options'      => $variables->get('options', []),
            'multiple'     => (boolean)$variables->has('multiple'),
            'callback_url' => $variables->get('ajax_url', _r('ajax.file.upload')),
            'editor'       => (boolean)$variables->has('editor'),
            'html'         => $variables->get('html'),
            'ui'           => $this->uiKit,
            //            'theme'      => $_field_theme,
        ];
        switch ($this->fieldType) {
            case 'select':
                if ($_field_options['values']) {
                    foreach ($_field_options['values'] as &$_item) {
                        if (is_array($_item)) {
                            $_item[0] = __($_item[0]);
                            if (isset($_item[1])) $_item[1] = __($_item[1]);
                        } else {
                            $_item = __($_item);
                        }
                    }
                }
                if ($_field_options['multiple'] && !Str::is('*\[\]', $_field_options['name'])) {
                    $_field_options['name'] = "{$_field_options['name']}[]";
                }
                break;
            case 'file_drop':
                $_field_options['upload_allow'] = ($_allow = $variables->get('allow')) ? $_allow : 'jpg|jpeg|gif|png';
                $_field_options['upload_view'] = $variables->get('view');
                break;
            case 'autocomplete':
                $_field_name = $_field_options['old'];
                $_field_options['name'] = self::render_field_name("{$_field_name}.name");
                $_field_options['id'] = self::render_field_id("{$_field_name}.name", $this->formId);
                $_field_options['autocomplete_name'] = self::render_field_name("{$_field_name}.value");
                $_selected = $_field_options['selected'];
                if (is_array($_selected)) {
                    $_field_options['selected'] = $_selected['name'];
                    $_field_options['value'] = $_selected['value'];
                }
                if ($this->errors && $this->errors->has("{$_field_name}.value")) {
                    $_field_options['error'] = $this->errors->first("{$_field_name}.value");
                }
                break;
        }
        $this->params = collect($_field_options);
    }

    public function _render()
    {
        $_params = $this->params;
        $_field = NULL;
        if ($_params->has('name') && $_params->get('name')) {
            switch ($_params->get('type')) {
                //                case 'select':
                //                    if ($_params->get('multiple', FALSE) && !str_is('*\[\]', $_params->get('name'))) $_params->put('name', $_params->get('name') . '[]');
                //                    $_field = View::first($_params->get('theme', []))
                //                        ->with('params', $_params)
                //                        ->render();
                //                    break;
                //                case 'autocomplete':
                //                    $_field_name = $_params->get('old');
                //                    $_params->put('name', $this->render_field_name("{$_field_name}.name"));
                //                    $_params->put('autocomplete_name', $this->render_field_name("{$_field_name}.value"));
                //                    $_selected = $_params->get('selected');
                //                    if (is_array($_selected)) {
                //                        $_params->put('selected', $_selected['name']);
                //                        $_params->put('value', $_selected['value']);
                //                    }
                //                    $_errors = session('errors');
                //                    if ($_errors && $_errors->has("{$_field_name}.value")) {
                //                        $_params->put('error', $this->errors->first("{$_field_name}.value"));
                //                    }
                //                    $_field = View::first($_params->get('theme', []))
                //                        ->with('params', $_params)
                //                        ->render();
                //                    break;
                //                case 'table':
                //                    $_options = [
                //                        'cols' => 2,
                //                    ];
                //                    $_params->put('options', array_merge($_options, $_params->get('options', [])));
                //                    $_field = View::first($_params->get('theme', []))
                //                        ->with('params', $_params)
                //                        ->render();
                //                    break;

                default:
                    $_method_name = "render_field_{$this->fieldType}";
                    if (method_exists($this, $_method_name)) {
                        $_field = $this->$_method_name($_params);
                    } else {
                        $_field = $this->render_field_text($_params);
                    }
                    break;
            }
        }

        return $_field;
    }

    public static function render_field_name($name)
    {
        $_name = $name;
        if (str_contains($name, '.')) {
            $name = explode('.', $name);
            $_name = NULL;
            foreach ($name as $_item) {
                $_item = str_replace('*', '', $_item);
                $_name .= is_null($_name) ? (string)$_item : (string)"[{$_item}]";
            }
        }

        return $_name;
    }

    public static function render_field_id($name, $form_id = NULL)
    {
        $_prefix = $form_id ? "{$form_id}-" : 'form-field-';

        return Str::slug($_prefix . str_replace([
                '.',
                '_'
            ], '_', $name), '-');
    }

    public function render_field_wrapper($options)
    {
        $_box_attributes = [];
        $_field_required = $options->get('required');
        if ($_box_id = $options->get('box_id')) $_box_attributes['id'] = $_box_id;
        if ($_box_class = $options->get('box_class')) $_box_attributes['class'] = $_box_class;
        $_box_attributes['class'][] = 'box-form-field';
        if ($_field_required) $_box_attributes['class'][] = 'box-form-field-required';
        if ($options->get('error')) $_box_attributes['class'][] = 'box-form-field-error';
        $_output = '<div' . ($_box_attributes ? ' ' . render_attributes($_box_attributes) : NULL) . '>@field_output</div>';

        return $options->get('prefix') . $_output . $options->get('suffix');
    }

    public function render_field_text($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_attributes = array_merge([
            'type'         => $options->get('type'),
            'id'           => $options->get('id'),
            'name'         => $options->get('name'),
            'value'        => $options->get('selected'),
            'autocomplete' => 'off'
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-input';
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        if ($_field_label = $options->get('label')) {
            $_field = "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
            $_field .= '<div><input ' . render_attributes($_field_attributes) . '>';
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";
            $_field .= '</div>';
        } else {
            $_field = $_field = '<input ' . render_attributes($_field_attributes) . '>';
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        }

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_textarea($options)
    {
        $_field_selected = $options->get('selected');
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_attributes = array_merge([
            'type'         => $options->get('type'),
            'id'           => $options->get('id'),
            'name'         => $options->get('name'),
            'autocomplete' => 'off'
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-textarea';
        if ($options->get('editor')) $_field_attributes['class'][] = 'ckEditor';
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        if ($_field_label = $options->get('label')) {
            $_field = "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
            $_field .= '<div><textarea ' . render_attributes($_field_attributes) . ">{$_field_selected}</textarea>";
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";
            $_field .= '</div>';
        } else {
            $_field = $_field = '<textarea ' . render_attributes($_field_attributes) . ">{$_field_selected}</textarea>";
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        }

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_hidden($options)
    {
        return "<input type=\"hidden\" name=\"{$options->get('name')}\" value=\"{$options->get('selected')}\">";
    }

    public function render_field_markup($options)
    {
        $_field = nl2br($options->get('html'));

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_checkbox($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_values = $options->get('values', []);
        $_field_name = $options->get('name');
        $_field_multiple = count($_field_values) > 1 ? TRUE : FALSE;
        $_field_selected = $options->get('selected');
        $_field_attributes = array_merge([
            'type'         => $options->get('type'),
            'id'           => $options->get('id'),
            'autocomplete' => 'off'
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-checkbox';
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        foreach ($_field_values as &$_item) {
            if (is_array($_item)) {
                $_item[0] = __($_item[0]);
                if (isset($_item[1])) $_item[1] = __($_item[1]);
            } else {
                $_item = __($_item);
            }
        }
        $_field = NULL;
        if ($_field_label = $options->get('label')) {
            $_field = "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
        }
        $_field .= "<div id=\"{$_field_attributes['id']}\">";
        $_t = 0;
        foreach ($_field_values as $_value => $_label) {
            $_item_attributes = array_merge($_field_attributes, [
                'id'      => "{$_field_attributes['id']}-{$_t}",
                'name'    => $_field_multiple ? "{$_field_name}[{$_t}]" : $_field_name,
                'value'   => $_value,
                'checked' => !is_null($_field_selected) && ((is_array($_field_selected) && in_array($_value, $_field_selected)) || $_field_selected == $_value) ? TRUE : FALSE,
            ]);
            $_field .= "<div><label for=\"{$_item_attributes['id']}\" class=\"uk-display-inline-block\">";
            if (!$_field_multiple) {
                $_field .= "<input " . render_attributes([
                        'type'  => 'hidden',
                        'name'  => $_item_attributes['name'],
                        'value' => $_value == 1 ? 0 : NULL
                    ]) . ">";
            }
            $_field .= "<input " . render_attributes($_item_attributes) . ">";
            if (is_array($_label)) {
                $_field .= "<span class=\"form-field-checkbox-label\">{$_label[0]}";
                if (isset($_label[1])) $_field .= "<span class=\"form-field-checkbox-label-help\">{$_label[1]}</span>";
                $_field .= "</span>";
            } else {
                $_field .= "<span class=\"form-field-checkbox-label\">{$_label}</span>";
            }
            $_field .= '</div>';
            $_t++;
        }
        $_field .= '</div>';
        if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_radio($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_values = $options->get('values', []);
        $_field_name = $options->get('name');
        $_field_selected = $options->get('selected', array_key_first($_field_values));
        $_field_attributes = array_merge([
            'type'         => $options->get('type'),
            'id'           => $options->get('id'),
            'autocomplete' => 'off'
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-radio';
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        foreach ($_field_values as &$_item) {
            if (is_array($_item)) {
                $_item[0] = __($_item[0]);
                if (isset($_item[1])) $_item[1] = __($_item[1]);
            } else {
                $_item = __($_item);
            }
        }
        $_field = NULL;
        if ($_field_label = $options->get('label')) {
            $_field = "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
        }
        $_field .= "<div id=\"{$_field_attributes['id']}\">";
        $_t = 0;
        foreach ($_field_values as $_value => $_label) {
            $_item_attributes = array_merge($_field_attributes, [
                'id'      => "{$_field_attributes['id']}-{$_t}",
                'name'    => $_field_name,
                'value'   => $_value,
                'checked' => $_field_selected == $_value ? TRUE : FALSE,
            ]);
            $_field .= "<div><label for=\"{$_item_attributes['id']}\" class=\"uk-display-inline-block\"><input " . render_attributes($_item_attributes) . ">";
            if (is_array($_label)) {
                $_field .= "<span class=\"form-field-checkbox-label\">{$_label[0]}";
                if (isset($_label[1])) $_field .= "<span class=\"form-field-checkbox-label-help\">{$_label[1]}</span>";
                $_field .= "</span>";
            } else {
                $_field .= "<span class=\"form-field-checkbox-label\">{$_label}</span>";
            }
            $_field .= '</div>';
            $_t++;
        }
        $_field .= '</div>';
        if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_table($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_selected = $options->get('selected', []);
        $_field_options = array_merge([
            'cols'  => 2,
            'thead' => NULL
        ], $options->get('options', []));
        $_field_name = $options->get('name');
        $_field_attributes = array_merge([
            'type'         => $options->get('type'),
            'id'           => $options->get('id'),
            'autocomplete' => 'off'
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        $_field = NULL;
        if ($_field_label = $options->get('label')) {
            $_field = "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
        }
        $_field .= "<div>";
        $_field .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
        if ($_field_options['thead']) {
            $_field .= '<thead><tr>';
            for ($_i = 0; $_i < $_field_options['cols']; $_i++) {
                $_field .= '<th>' . (isset($_field_options['thead'][$_i]) ? $_field_options['thead'][$_i] : NULL) . '</th>';
            }
            $_field .= '</tr></thead>';
        }
        $_field .= "<tbody id=\"{$_field_attributes['id']}\">";
        if ($_field_selected) {
            foreach ($_field_selected as $_td) {

            }
        }
        $_field .= '</tbody>';
        $_t = 0;
        $_field .= '</table>';
        $_field .= '<div>';
        $_field .= 'Добавить строку в таблицу';
        $_field .= '</div></div>';
        if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_select($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_multiple = (bool)$options->get('multiple', FALSE);
        $_field_values = $options->get('values', []);
        $_field_selected = $options->get('selected');
        $_field_attributes = array_merge([
            'id'   => $options->get('id'),
            'name' => $options->get('name'),
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-select';
        $_field_attributes['class'][] = 'form-field-select';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_multiple === TRUE) $_field_attributes['multiple'] = TRUE;
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        if ($_field_label = $options->get('label')) {
            $_field = "<div><label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
            $_field .= '<select ' . render_attributes($_field_attributes) . '>';
            foreach ($_field_values as $key => $value) {
                if (is_array($_field_selected) || is_object($_field_selected)) {
                    if (is_object($_field_selected)) $selected = $_field_selected->toArray();
                    $_selected = in_array($key, $_field_selected) ? ' selected' : '';
                    $_field .= "<option value=\"" . ($key ? : NULL) . "\" {$_selected}>{$value}</option>";
                } else {
                    $_selected = !is_null($_field_selected) ? ((string)$_field_selected == (string)$key ? ' selected' : '') : '';
                    $_field .= "<option value=\"" . (!is_null($key) ? $key : NULL) . "\" {$_selected}>{$value}</option>";
                }
            }
            $_field .= '</select>';
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";
            $_field .= '</div>';
        } else {
            $_field = '<select ' . render_attributes($_field_attributes) . '>';
            foreach ($_field_values as $key => $value) {
                if (is_array($_field_selected) || is_object($_field_selected)) {
                    if (is_object($_field_selected)) $selected = $_field_selected->toArray();
                    $_selected = in_array($key, $_field_selected) ? ' selected' : '';
                    $_field .= "<option value=\"" . ($key ? : NULL) . "\" {$_selected}>{$value}</option>";
                } else {
                    $_selected = !is_null($_field_selected) ? ((string)$_field_selected == (string)$key ? ' selected' : '') : '';
                    $_field .= "<option value=\"" . (!is_null($key) ? $key : NULL) . "\" {$_selected}>{$value}</option>";
                }
            }
            $_field .= '</select>';
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";
        }

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_file($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_multiple = (bool)$options->get('multiple', FALSE);
        $_field_attributes = array_merge([
            'type' => $options->get('type'),
            'id'   => $options->get('id'),
            'name' => $options->get('name'),
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-input';
        if ($_field_multiple) $_field_attributes['multiple'] = TRUE;
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        $_field = NULL;
        if ($_field_label = $options->get('label')) {
            $_field .= "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
        }
        if ($this->uiKit) {
            $_field .= '<div uk-form-custom="target: true" class="uk-form-controls uk-form-controls-file uk-width-1-1">';
            $_field .= '<input ' . render_attributes($_field_attributes) . '>';
            $_field .= '<input class="uk-input uk-width-1-1" type="text" placeholder="' . trans('forms.fields.file_upload_placeholder') . '" disabled>';
            $_field .= '</div>';
        } else {
            $_field .= '<input ' . render_attributes($_field_attributes) . '>';
        }
        if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_file_drop($options)
    {
        $_field_files = ($_files = session($options->get('old'))) ? json_decode($_files) : (($_files = $options->get('values')) ? $_files : NULL);
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_multiple = (bool)$options->get('multiple', FALSE);
        $_field_upload_allow = $options->get('upload_allow');
        $_field_attributes = array_merge([
            'type'  => $options->get('type'),
            'id'    => $options->get('id'),
            'name'  => $options->get('name'),
            'value' => $options->get('selected'),
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        $_field_attributes['class'][] = 'form-field-input';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        $_field = NULL;
        if ($_field_label = $options->get('label')) {
            $_field .= "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
        }
        $_field .= '<div class="uk-form-controls uk-form-controls-file ' . ($_field_multiple ? 'uk-multiple-file' : 'uk-one-file') . (!$_field_multiple && $_field_files ? ' loaded-file' : NULL) . '" data-view="' . $options->get('upload_view') . '"><div class="uk-width-1-1 uk-position-relative">';
        $_field .= '<input type="hidden" name="' . $options->get('name') . '">';
        $_field .= '<div class="uk-preview">';
        if ($options->get('upload_view') == 'gallery') {
            $_field .= '<div class="sortable-list uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s"
data-path="' . _r('ajax.file.sort') . '">';
        }
        if ($_field_files) {
            foreach ($_field_files as $_f) {
                $_field .= render_preview_file($_f, [
                    'field' => $options->get('name'),
                    'view'  => $options->get('upload_view')
                ]);
            }
        }
        if ($options->get('upload_view') == 'gallery') {
            $_field .= '</div>';
        }
        $_field .= '</div>';
        $_field .= '<div class="uk-field uk-text-right">';
        $_field .= '<div class="js-upload uk-placeholder uk-text-center uk-border-rounded' . ($_field_error ? ' uk-form-danger' : '') . '" id="' . $options->get('id') . '">';
        $_field .= '<span uk-icon="icon: cloud_upload" class="uk-text-muted uk-text-primary"></span>&nbsp;<span class="uk-text-middle">' . __('forms.fields.file_dropdown_start') . '</span>';
        $_field .= '<div data-url="' . $options->get('callback_url') . '" data-allow="*.(' . $_field_upload_allow . ')" data-field="' . $options->get('name') . '" data-multiple="' . ($_field_multiple ? 1 : 0) . '" data-view="' . $options->get('upload_view') . '" class="uk-field file-upload-field" uk-form-custom><input type="file"' . ($_field_multiple ? ' multiple' : NULL) . '> <span class="uk-link uk-text-lowercase">' . __('forms.fields.file_dropdown_finish') . '</span></div>';
        $_field .= '<div class="uk-text-small uk-text-muted">' . __('forms.fields.file_allow_mime_type', ['mime_type' => str_replace('|', ', ', $_field_upload_allow)]);
        if ($_field_help = $options->get('help')) {
            $_field .= '<span class="uk-help-block uk-display-block" >' . $_field_help . '</span >';
        }
        $_field .= '</div>';
        $_field .= '</div><progress class="uk-progress js-progressbar" value="0" max="100" hidden></progress>';
        $_field .= '</div>';
        $_field .= '</div>';
        $_field .= '</div>';

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }

    public function render_field_autocomplete($options)
    {
        $_field_required = (bool)$options->get('required', FALSE);
        $_field_attributes = array_merge([
            'type'         => 'text',
            'id'           => $options->get('id'),
            'name'         => $options->get('name'),
            'value'        => $options->get('selected'),
            'autocomplete' => 'off'
        ], $options->get('attributes'));
        if (isset($_field_attributes['class'])) $_field_attributes['class'] = (array)$_field_attributes['class'];
        if ($this->uiKit) $_field_attributes['class'][] = 'uk-input';
        $_field_attributes['class'][] = 'form-field-input';
        $_field_attributes['class'][] = 'uk-autocomplete';
        if ($_field_required === TRUE) $_field_attributes['class'][] = 'form-field-required';
        if ($_field_error = $options->get('error')) $_field_attributes['class'][] = 'form-field-error';
        if ($_field_label = $options->get('label')) {
            $_field = "<label for=\"{$_field_attributes['id']}\">{$_field_label}" . ($_field_required === TRUE ? '<span class="form-field-required-mark">*</span>' : NULL) . "</label>";
            $_field .= '<div class="uk-form-controls-autocomplete"><input ' . render_attributes($_field_attributes) . '>';
            $_field .= '<input type="hidden" value="' . $options->get('value') . '" name="' . $options->get('autocomplete_name') . '">';
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";
            $_field .= '</div>';
        } else {
            $_field = $_field = '<input ' . render_attributes($_field_attributes) . '>';
            if ($_field_help = $options->get('help')) $_field .= "<div class=\"form-field-help\">{$_field_help}</div>";

        }

        return clear_html(str_replace('@field_output', $_field, $this->render_field_wrapper($options)));
    }
}

