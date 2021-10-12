<?php

namespace App\Models\Form;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class FormFields extends Model
{
    use BaseModel;

    protected $table = 'form_fields';
    protected $fillable = [
        'form_id',
        'title',
        'help',
        'value',
        'type',
        'data',
        'options',
        'multiple',
        'markup',
        'sort',
        'required',
        'other_rules',
        'status',
        'hidden_label',
        'placeholder_label',
    ];
    public $timestamps = FALSE;
    public $translatable = [
        'title',
        'help',
        'options',
        'markup',
    ];
    protected $attributes = [
        'id'                => NULL,
        'form_id'           => NULL,
        'title'             => NULL,
        'help'              => NULL,
        'value'             => NULL,
        'type'              => NULL,
        'data'              => NULL,
        'options'           => NULL,
        'multiple'          => 0,
        'markup'            => NULL,
        'sort'              => 0,
        'required'          => 0,
        'other_rules'       => NULL,
        'status'            => 1,
        'hidden_label'      => 0,
        'placeholder_label' => 0,
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Attribute
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data']);
    }

    public function setDataAttribute($value = NULL)
    {
        $this->attributes['data'] = json_encode($value);
    }

    /**
     * Others
     */
    public function field_data($var = NULL)
    {
        $_fields = [
            'text'     => [
                'name' => 'Текстовое поле',
            ],
            'number'   => [
                'name' => 'Числовое поле',
            ],
            'textarea' => [
                'name' => 'Текстовая область',
            ],
            'hidden'   => [
                'name' => 'Скрытое поле',
            ],
            'select'   => [
                'name' => 'Элементы списка'
            ],
            'checkbox' => [
                'name' => 'Флажки'
            ],
            'radio'    => [
                'name' => 'Переключатели'
            ],
            'file'     => [
                'name' => 'Выбор файла',
            ],
            'mockup'   => [
                'name' => 'Разметка'
            ],
            //                'break'    => [
            //                    'name' => 'Шаг формы'
            //                ],
        ];

        return $var ? ($_fields[$this->type][$var] ?? NULL) : ($_fields[$this->type] ?? ['name' => NULL]);
    }

    public static function getFormField($form, $item, $locale)
    {
        $_response = [
            [
                'title'   => 'По умолчанию',
                'content' => [
                    render_field('field.form_id', [
                        'value' => $form->id,
                        'type'  => 'hidden',
                    ]),
                    render_field('field.type', [
                        'value' => $item->type,
                        'type'  => 'hidden',
                    ]),
                    render_field("field.title.{$locale}", [
                        'label'    => 'Заголовок',
                        'value'    => $item->getTranslation('title', $locale),
                        'uikit'    => TRUE,
                        'required' => TRUE,
                        'form_id'  => 'form-items-form',
                    ]),

                ]
            ]
        ];
        switch ($item->type) {
            case 'text':
            case 'number':
            case 'textarea':
                $_response[0]['content'][] = render_field("field.help.{$locale}", [
                    'label'      => 'Описание',
                    'attributes' => [
                        'rows' => 2,
                    ],
                    'type'       => 'textarea',
                    'value'      => $item->getTranslation('help', $locale),
                    'uikit'      => TRUE,
                ]);
                $_response[0]['content'][] = '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>';
                $_response[0]['content'][] = render_field('field.value', [
                    'label' => 'Значение поля по умолчанию',
                    'value' => $item->value,
                    'uikit' => TRUE,
                ]);
                $_response[0]['content'][] = render_field('field.data.attributes', [
                    'label'      => 'Дополнительные атрибуты',
                    'attributes' => [
                        'rows' => 2,
                    ],
                    'type'       => 'textarea',
                    'value'      => $item->data->attributes ?? NULL,
                    'uikit'      => TRUE,
                ]);
                $_response[0]['content'][] = '</div><div>';
                $_response[0]['content'][] = render_field('field.sort', [
                    'type'  => 'number',
                    'label' => 'Порядок сортировки',
                    'value' => $item->sort ? : 0,
                    'uikit' => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.hidden_label', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->hidden_label : 0,
                    'values' => [
                        1 => 'Скрыть название поля'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.placeholder_label', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->placeholder_label : 0,
                    'values' => [
                        1 => 'Использовать заголовок как PLACEHOLDER'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.status', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->status : 1,
                    'values' => [
                        1 => 'Отображать поле в форме'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = '</div></div>';
                break;
            case 'hidden':
                $_response[0]['content'][] = '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>';
                $_response[0]['content'][] = render_field('field.value', [
                    'label' => 'Значение поля по умолчанию',
                    'value' => $item->value,
                    'uikit' => TRUE,
                    'help'  => 'Множественное значение разделять вертикальной чертой.'
                ]);
                $_response[0]['content'][] = '</div><div>';
                $_response[0]['content'][] = render_field('field.sort', [
                    'type'  => 'number',
                    'label' => 'Порядок сортировки',
                    'value' => $item->sort ? : 0,
                    'uikit' => TRUE
                ]);
                $_response[0]['content'][] = '</div></div>';
                break;
            case 'select':
            case 'radio':
            case 'checkbox':
                $_response[0]['content'][] = render_field("field.help.{$locale}", [
                    'label'      => 'Описание',
                    'attributes' => [
                        'rows' => 2,
                    ],
                    'type'       => 'textarea',
                    'value'      => $item->getTranslation('help', $locale),
                    'uikit'      => TRUE,
                ]);
                $_response[0]['content'][] = '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>';
                $_response[0]['content'][] = render_field('field.value', [
                    'label' => 'Значение поля по умолчанию',
                    'value' => $item->value,
                    'uikit' => TRUE,
                    'help'  => 'Множественное значение разделять вертикальной чертой.'
                ]);
                $_response[0]['content'][] = render_field('field.data.attributes', [
                    'label'      => 'Дополнительные атрибуты',
                    'attributes' => [
                        'rows' => 2,
                    ],
                    'type'       => 'textarea',
                    'value'      => $item->data->attributes ?? NULL,
                    'uikit'      => TRUE,
                    'form_id'    => 'form-items-form',
                ]);
                $_response[0]['content'][] = '</div><div>';
                $_response[0]['content'][] = render_field('field.sort', [
                    'type'  => 'number',
                    'label' => 'Порядок сортировки',
                    'value' => $item->sort ? : 0,
                    'uikit' => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.hidden_label', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->hidden_label : 0,
                    'values' => [
                        1 => 'Скрыть название поля'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.placeholder_label', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->placeholder_label : 0,
                    'values' => [
                        1 => 'Использовать заголовок как PLACEHOLDER'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.status', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->status : 1,
                    'values' => [
                        1 => 'Отображать поле в форме'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = '</div></div>';
                $_response[0]['content'][] = '<h3 class="uk-heading-line"><span>Дополнительные настройки</span></h3>';
                $_response[0]['content'][] = render_field("field.options.{$locale}", [
                    'label'      => 'Список пунктов',
                    'attributes' => [
                        'rows' => 8,
                    ],
                    'type'       => 'textarea',
                    'value'      => $item->options,
                    'uikit'      => TRUE,
                    'help'       => 'Вписываются данные по принципу ключ|значение. Каждый параметр с новой строки.'
                ]);
                $_response[0]['content'][] = render_field('field.multiple', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->multiple : 1,
                    'values' => [
                        1 => 'Множественный выбор'
                    ],
                    'uikit'  => TRUE
                ]);
                break;
            case 'file':
                $_response[0]['content'][] = render_field("field.help.{$locale}", [
                    'label'      => 'Описание',
                    'attributes' => [
                        'rows' => 2,
                    ],
                    'type'       => 'textarea',
                    'value'      => $item->getTranslation('help', $locale),
                    'uikit'      => TRUE,
                ]);
                $_response[0]['content'][] = '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>';
                $_response[0]['content'][] = render_field('field.sort', [
                    'type'  => 'number',
                    'label' => 'Порядок сортировки',
                    'value' => $item->sort ? : 0,
                    'uikit' => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.multiple', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->multiple : 0,
                    'values' => [
                        1 => 'Множественный выбор'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = '</div><div>';
                $_response[0]['content'][] = render_field('field.hidden_label', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->hidden_label : 0,
                    'values' => [
                        1 => 'Скрыть название поля'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.placeholder_label', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->placeholder_label : 0,
                    'values' => [
                        1 => 'Использовать заголовок как PLACEHOLDER'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = render_field('field.status', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->status : 1,
                    'values' => [
                        1 => 'Отображать поле в форме'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = '</div></div>';
                break;
            case 'markup':
                $_response[0]['content'][] = render_field("field.markup.{$locale}", [
                    'label'      => 'Значение поля',
                    'attributes' => [
                        'rows' => 10,
                    ],
                    'editor'     => TRUE,
                    'type'       => 'textarea',
                    'value'      => $item->getTranslation('markup', $locale),
                    'uikit'      => TRUE,
                ]);
                $_response[0]['content'][] = '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>';
                $_response[0]['content'][] = render_field('field.sort', [
                    'type'  => 'number',
                    'label' => 'Порядок сортировки',
                    'value' => $item->sort ? : 0,
                    'uikit' => TRUE
                ]);
                $_response[0]['content'][] = '</div><div class="uk-padding-top">';
                $_response[0]['content'][] = render_field('field.status', [
                    'type'   => 'checkbox',
                    'value'  => $item->exists ? $item->status : 1,
                    'values' => [
                        1 => 'Отображать поле в форме'
                    ],
                    'uikit'  => TRUE
                ]);
                $_response[0]['content'][] = '</div></div>';
                break;
        }
        if (!in_array($item->type, [
            'hidden',
            'markup'
        ])) {
            $_response[0]['content'][] = '<h3 class="uk-heading-line"><span>Проверка поля</span></h3>';
            $_response[0]['content'][] = render_field('field.required', [
                'type'   => 'checkbox',
                'value'  => $item->exists ? $item->required : 0,
                'values' => [
                    1 => 'Обязательно для заполнения'
                ],
                'uikit'  => TRUE,
                'help'   => 'Игнорируется, если указаны собственные правила проверки.'
            ]);
            $_response[0]['content'][] = render_field('field.other_rules', [
                'label'      => 'Свои правила проверки',
                'attributes' => [
                    'rows' => 2,
                ],
                'type'       => 'textarea',
                'value'      => $item->other_rules,
                'uikit'      => TRUE,
                'help'       => 'Тут можну указать вручном режиме правило проверки для поля. При этом выше указанное правило становится не действительным.'
            ]);
            $_response[0]['content'][] = '<h3 class="uk-heading-line"><span>Стиль оформления</span></h3>';
            $_response[0]['content'][] = '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3">';
            $_response[0]['content'][] = render_field('field.data.item_class', [
                'label' => "&lt;div class='...'",
                'value' => $item->data->item_class ?? NULL,
                'uikit' => TRUE,
            ]);
            $_response[0]['content'][] = render_field('field.data.class', [
                'label' => "&lt;input class='...'",
                'value' => $item->data->class ?? NULL,
                'uikit' => TRUE,
            ]);
            $_response[0]['content'][] = '</div><div class="uk-width-2-3">';
            $_response[0]['content'][] = render_field('field.data.prefix', [
                'label' => 'Prefix code',
                'value' => $item->data->prefix ?? NULL,
                'uikit' => TRUE,
            ]);
            $_response[0]['content'][] = render_field('field.data.suffix', [
                'label' => 'Suffix code',
                'value' => $item->data->suffix ?? NULL,
                'uikit' => TRUE,
            ]);
            $_response[0]['content'][] = '</div></div>';
        }
        if (USE_MULTI_LANGUAGE) {
            $_languages = config('laravellocalization.supportedLocales');
            foreach ($_languages as $_locale => $_data) {
                $_content = [];
                if ($_locale != DEFAULT_LOCALE) {
                    $_content[] = render_field("field.title.{$_locale}", [
                        'label' => 'Заголовок',
                        'value' => $item->getTranslation('title', $_locale, FALSE),
                        'uikit' => TRUE,
                    ]);
                    if ($item->type == 'markup') {
                        $_content[] = render_field("field.markup.{$_locale}", [
                            'label'      => 'Значение поля',
                            'value'      => $item->getTranslation('markup', $_locale, FALSE),
                            'uikit'      => TRUE,
                            'type'       => 'textarea',
                            'attributes' => [
                                'rows' => 10,
                            ],
                        ]);
                    } else {
                        $_content[] = render_field("field.help.{$_locale}", [
                            'label'      => 'Описание',
                            'value'      => $item->getTranslation('help', $_locale, FALSE),
                            'uikit'      => TRUE,
                            'type'       => 'textarea',
                            'attributes' => [
                                'rows' => 2,
                            ],
                        ]);
                    }
                    $_response[] = [
                        'title'   => $_data['native'],
                        'content' => $_content
                    ];
                } else {
                    $_tabs[0]['title'] = "По умолчанию ({$_data['native']})";
                }
            }
        }

        return $_response;
    }
}
