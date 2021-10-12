<?php

namespace App\Models\Form;

use App\Libraries\BaseModel;
use App\Libraries\Fields;
use App\Models\Components\Menu;
use App\Models\Components\MenuItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Forms extends Model
{
    use BaseModel;

    protected $table = 'forms';
    protected $fillable = [
        'title',
        'sub_title',
        'body',
        'style_id',
        'style_class',
        'prefix',
        'suffix',
        'attributes',
        'settings',
        'completion_type',
        'completion_modal_text',
        'completion_page_id',
        'button_send',
        'button_open_form',
        'email_to_receive',
        'email_subject',
        'send_to_user',
        'user_email_field_id',
        'status',
        'hidden_title',
    ];
    public $timestamps = FALSE;
    public $translatable = [
        'title',
        'sub_title',
        'body',
        'button_send',
        'button_open_form',
        'completion_modal_text'
    ];
    protected $attributes = [
        'id'                    => NULL,
        'title'                 => NULL,
        'sub_title'             => NULL,
        'body'                  => NULL,
        'attributes'            => NULL,
        'style_id'              => NULL,
        'style_class'           => NULL,
        'prefix'                => NULL,
        'suffix'                => NULL,
        'settings'              => NULL,
        'completion_type'       => 1,
        'completion_modal_text' => NULL,
        'completion_page_id'    => NULL,
        'button_send'           => NULL,
        'button_open_form'      => NULL,
        'email_to_receive'      => NULL,
        'email_subject'         => NULL,
        'send_to_user'          => 0,
        'user_email_field_id'   => NULL,
        'status'                => 1,
        'hidden_title'          => 0,
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Attribute
     */
    public function getSettingsAttribute()
    {
        return json_decode($this->attributes['settings']);
    }

    public function setSettingsAttribute($value = NULL)
    {
        $this->attributes['settings'] = json_encode($value);
    }

    public function getItemsAttribute()
    {
        $_response = collect([]);
        $_items = $this->_fields;
        if ($_items->isNotEmpty()) {
            $_form = $this;
            $_response->put('headers', [
                [
                    'data' => 'Заголовок поля',
                ],
                [
                    'data'  => 'Тип поля',
                    'class' => 'uk-width-medium',
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 50px;',
                    'data'  => '<span uk-icon="icon: sort_by_alpha">'
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data'  => '<span uk-icon="icon: visibility">'
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data'  => '<span uk-icon="icon: edit">'
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data'  => '<span uk-icon="icon: delete_forever">'
                ]
            ]);
            $_output = collect([]);
            $_items->map(function ($item) use (&$_output, $_form) {
                $this->render_field_item($_output, $_form, $item);
            });
            $_response->put('items', $_output);
        }

        return $_response;
    }

    /**
     * Relationships
     */
    public function _fields()
    {
        return $this->hasMany(FormFields::class, 'form_id')
            ->active('form_fields')
            ->orderBy('sort')
            ->orderBy('title');
    }

    /**
     * Others
     */
    public function render_field_item(&$output, self $form, FormFields $item)
    {
        $output->push((object)[
            $item->title,
            $item->type,
            '<input type="number"
                           class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item"
                           name="items_sort[]"
                           data-id="' . $item->id . '"
                           value="' . $item->sort . '">',
            $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
            _l('', 'oleus.forms.item', [
                'p'          => [
                    $form,
                    'edit',
                    $item->id
                ],
                'attributes' => [
                    'class'   => 'uk-button uk-button-success uk-button-xsmall uk-button-icon use-ajax',
                    'uk-icon' => 'icon: edit'
                ]
            ]),
            _l('', 'oleus.forms.item', [
                'p'          => [
                    $form,
                    'destroy',
                    $item->id
                ],
                'attributes' => [
                    'class'   => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                    'uk-icon' => 'icon: delete_forever'
                ]
            ])
        ]);
    }

    public function _render($options = [])
    {
        global $wrap;
        $options = array_merge([
            'view'  => NULL,
            'index' => NULL,
            'with'  => [],
        ], $options);
        $this->body = content_render($this);
        if (isset($options['index']) && $options['index']) $this->renderIndex = $options['index'];
        if ($this->renderIndex && $this->style_id) {
            $this->style_id .= "-{$this->renderIndex}";
            $this->style_class .= $this->style_id ? " {$this->style_id}" : $this->style_id;
        }
        $this->styleAttributes = [
            'id'    => $this->style_id ? : FALSE,
            'class' => "use-ajax {$this->style_class}" ? : 'use-ajax',
        ];
        if ($wrap['user'] && $wrap['user']->can('banners_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';
        $_form_data = $this->formatted_data();
        $_template = [
            "frontend.{$this->deviceTemplate}.forms.form_{$this->id}",
            "frontend.{$this->deviceTemplate}.forms.form",
            "frontend.default.forms.form_{$this->id}",
            "frontend.default.forms.form",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.forms.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.forms.{$options['view']}");
        }
        $_item = $this;

        return View::first($_template, compact('_item', '_form_data'))
            ->with($options['with'])
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }

    public function formatted_data($index = NULL)
    {
        global $wrap;
        $_renderIndex = !is_null($index) ? "-{$index}" : ($this->renderIndex ? "-{$this->renderIndex}" : NULL);
        $_form_id = ($this->style_id ? $this->style_id : "form-entity-{$this->id}") . ($_renderIndex ? "-{$_renderIndex}" : NULL);
        $_attributes = [
            'id'    => $_form_id ? : FALSE,
            'class' => "use-ajax {$this->style_class}" ? : 'use-ajax',
        ];
        if ($this->hasAttribute('attributes')) {
            $_attributes[] = $this->getAttribute('attributes');
        }
        $_response = [
            'form_id'        => $_form_id,
            'attributes'     => $_attributes,
            'options_fields' => [],
            'render_fields'  => [],
            'validation'     => []
        ];
        $this->_fields->map(function ($field) use (&$_response, $_form_id) {
            $_field_name = "fields.field_{$field->id}" . ($field->multiple ? '.*' : NULL);
            $_field_id = "{$_response['form_id']}-field-{$field->id}";
            $_field_label = NULL;
            $_field_attributes = [];
            $_field_required = FALSE;
            $_field_value = NULL;
            $_field_values = NULL;
            $_field_validation_rules = NULL;
            if (isset($field->data->attributes) && $field->data->attributes) $_field_attributes[] = $field->data->attributes;
            if (isset($field->data->class) && $field->data->class) $_field_attributes['class'] = $field->data->class;
            if (!$field->hidden_label && !$field->placeholder_label) {
                $_field_label = $field->title;
            } elseif (!$field->hidden_label && $field->placeholder_label) {
                $_field_attributes['placeholder'] = $field->title;
            }
            //            if ($field->type == 'file') {
            //                $_option['attributes']['placeholder'] = 'Select file';
            //                $_option['ajax_url'] = FALSE;
            //            }
            if ($field->other_rules) {
                if (Str::is('*required*', $field->other_rules)) $_field_required = TRUE;
                $_field_validation_rules = [
                    'id'       => $_field_id,
                    'name'     => $_field_name,
                    'title'    => $field->title,
                    'rule'     => $field->other_rules,
                    'multiple' => $field->multiple ? TRUE : FALSE,
                ];
            } elseif ($field->required) {
                $_field_required = TRUE;
                $_field_validation_rules = [
                    'id'       => $_field_id,
                    'name'     => $_field_name,
                    'title'    => $field->title,
                    'rule'     => 'required',
                    'multiple' => $field->multiple ? TRUE : FALSE,
                ];
            }
            if ($field->type == 'select' || $field->type == 'checkbox' || $field->type == 'radio') {
                $_field_options = $field->options ? explode(PHP_EOL, $field->options) : NULL;
                if ($_field_options) {
                    foreach ($_field_options as $field_option) {
                        $_field_option = explode('|', $field_option);
                        if (isset($_field_option[1]) && $_field_option[1]) {
                            $_field_values[$_field_option[0]] = $_field_option[1];
                        } elseif (isset($_field_option[0]) && $_field_option[0]) {
                            $_field_values[$_field_option[0]] = $_field_option[0];
                        }
                    }
                    if (!$_field_values) {
                        return FALSE;
                    }
                } else {
                    return FALSE;
                }
                if ($field->value && ($field->type == 'select' || $field->type == 'checkbox')) $_field_value = explode('|', $field->value);
                if ($field->type == 'radio') {
                    $_field_value = $field->value ? $field->value : array_key_first($_field_values);
                }
            } elseif ($field->value) {
                $_field_value = $field->value;
            }
            $_field_options = [
                'type'        => $field->type,
                'field_name'  => "field_{$field->id}",
                'field_label' => $field->title,
                'label'       => $_field_label,
                'form_id'     => $_form_id,
                'value'       => $_field_value,
                'values'      => $_field_values,
                'attributes'  => $_field_attributes,
                'prefix'      => $field->data->prefix ?? NULL,
                'suffix'      => $field->data->suffix ?? NULL,
                'item_class'  => isset($field->data->item_class) ? [$field->data->item_class] : NULL,
                'required'    => $_field_required,
                'help'        => $field->help,
                'html'        => ($field->type == 'markup' ? $field->markup : NULL)
            ];
            if ($field->multiple) $_field_options['multiple'] = TRUE;
            $_field_render = new Fields($_field_name, $_field_options);
            $_option = [
                'options' => $_field_options,
                'output'  => $_field_render->_render(),
                'sort'    => $field->sort,
            ];
            $_response['options_fields'][$field->id] = $_option;
            $_response['render_fields'][$field->id] = $_option['output'];
            if ($_field_validation_rules) $_response['validation'][$field->id] = $_field_validation_rules;
        });
        if ($_response['options_fields']) $_response['options_fields'] = collect($_response['options_fields'])->sortBy('sort');

        return (object)$_response;
    }

    public function getShortcut($options = [])
    {
        if (!$this->status) return NULL;
        $_options = array_merge([
            'type'  => 'form',
            'view'  => NULL,
            'index' => NULL,
        ], $options);
        if ($_options['type'] == 'form') {
            $_template = [];
            if (isset($_options['index']) && $_options['index']) $this->renderIndex = $_options['index'];
            if ($this->renderIndex && $this->style_id) $this->style_id .= "-{$this->renderIndex}";
            if ($this->renderIndex && $this->style_id) {
                $this->style_id .= "-{$this->renderIndex}";
                $this->style_class .= $this->style_id ? " {$this->style_id}" : $this->style_id;
            }
            $this->styleAttributes = [
                'id'    => $this->style_id ? : FALSE,
                'class' => 'banner-body' . ($this->style_class ? " {$this->style_class}" : NULL),
            ];
            $_form_data = $this->formatted_data();
            $_template = array_merge($_template, [
                "frontend.{$this->deviceTemplate}.shortcuts.form_{$this->id}",
                "frontend.{$this->deviceTemplate}.shortcuts.form",
                "frontend.default.shortcuts.form_{$this->id}",
                'frontend.default.shortcuts.form',
                "frontend.{$this->deviceTemplate}.forms.form_{$this->id}",
                "frontend.{$this->deviceTemplate}.forms.form",
                "frontend.default.forms.form_{$this->id}",
                "frontend.default.forms.form",
            ]);
            if (isset($options['view']) && $options['view']) {
                array_unshift($_template, "frontend.default.shortcuts.{$options['view']}");
                array_unshift($_template, "frontend.{$this->deviceTemplate}.shortcuts.{$options['view']}");
            }
            $_item = $this;
            if ($_item) {
                return View::first($_template, compact('_item', '_form_data'))
                    ->render(function ($view, $content) {
                        return clear_html($content);
                    });
            }
        } elseif ($_options['type'] == 'form_button') {
            $_form_button_name = $this->button_open_form ? : trans('forms.constructor_form.buttons.open');
            $_form_button_class = isset($this->settings->button_open_form->class) && $this->settings->button_open_form->class ? " {$this->settings->button_open_form->class}" : NULL;
            $_form_button_path = _r('ajax.open_form', [$this]);

            return "<button type=\"button\" data-path=\"{$_form_button_path}\" data-index=\"{$_options['index']}\" data-view=\"{$_options['view']}\" class=\"use-ajax{$_form_button_class}\">{$_form_button_name}</button>";
        }

        return NULL;
    }

    public static function getButtonOpenForm($form, $options = [])
    {
        $_options = array_merge([
            'name'  => trans('forms.buttons.constructor_form.open'),
            'class' => NULL,
            'view'  => NULL,
            'index' => NULL,
        ], $options);
        $_form_button_path = _r('ajax.open_form', [$form]);

        return "<button type=\"button\" data-path=\"{$_form_button_path}\" data-index=\"{$_options['index']}\" data-view=\"{$_options['view']}\" class=\"use-ajax {$_options['class']}\">{$_options['name']}</button>";
    }
}
