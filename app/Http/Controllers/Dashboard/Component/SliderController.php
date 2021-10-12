<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Libraries\Form;
use App\Models\Components\AdvantageItems;
use App\Models\Components\Slider;
use App\Models\Components\SliderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Fields;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SliderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Список слайд-шоу';
        $this->titles['create'] = 'Добавить слайд-шоу';
        $this->titles['edit'] = 'Редактировать слайд-шоу';
        $this->middleware([
            'permission:sliders_read'
        ]);
        $this->baseRoute = 'sliders';
        $this->permissions = [
            'read'   => 'sliders_read',
            'view'   => 'sliders_view',
            'create' => 'sliders_create',
            'update' => 'sliders_update',
            'delete' => 'sliders_delete'
        ];
        $this->entity = new Slider();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_field_preset = NULL;
        if ($_presets = config('preset_images')) {
            $_preset_values = [];
            foreach ($_presets as $_preset_key => $_preset_value) {
                if (Str::is('slider_*', $_preset_key)) {
                    $_label = $_preset_key;
                    if (isset($_preset_value['width']) && isset($_preset_value['height'])) {
                        $_label = "{$_preset_value['width']}px * {$_preset_value['height']}px";
                    } elseif (isset($_preset_value['width'])) {
                        $_label = "{$_preset_value['width']}px * auto";
                    } elseif (isset($_preset_value['height'])) {
                        $_label = "auto * {$_preset_value['height']}px";
                    }
                    $_preset_values[$_preset_key] = "{$_preset_key} ({$_label})";
                }
            }
            if (count($_preset_values)) {
                array_unshift($_preset_values, '-- Выбрать --');
                $_field_preset = render_field('preset', [
                    'label'    => 'Формат отображаения',
                    'type'     => 'select',
                    'selected' => $entity->preset,
                    'values'   => $_preset_values,
                    'uikit'    => TRUE,
                    'help'     => 'Первое значение ширина, второе высота. Auto - не учитывается значение параметра.'
                ]);
            }
        }
        $_form->tabs[] = [
            'title'   => 'Основное',
            'content' => [
                render_field('title', [
                    'label'    => 'Название',
                    'value'    => $entity->title,
                    'required' => TRUE,
                    'uikit'    => TRUE
                ]),
                $_field_preset,
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Настройки</span></h3>',
                render_field('options.animation', [
                    'label'    => 'Тип анимации',
                    'type'     => 'radio',
                    'selected' => $entity->options->animation ?? 'slide',
                    'values'   => [
                        'slide' => 'Слайды сдвигаются бок о бок',
                        'fade'  => 'Слайды исчезают',
                        'scale' => 'Слайды увеличиваются и исчезают',
                    ],
                    'uikit'    => TRUE,
                ]),
                render_field('options.autoplay', [
                    'type'     => 'checkbox',
                    'selected' => $entity->options->autoplay ?? 0,
                    'values'   => [
                        1 => 'Автозапуск слайд-шоу'
                    ],
                    'uikit'    => TRUE,
                ]),
                render_field('options.autoplay_interval', [
                    'label'    => 'Интервал смены слайдов',
                    'type'     => 'number',
                    'selected' => $entity->options->autoplay_interval ?? 7000,
                    'uikit'    => TRUE,
                    'help'     => '1000 = 1 секунде.',
                ]),
                render_field('options.pause_on_hover', [
                    'type'     => 'checkbox',
                    'selected' => $entity->options->pause_on_hover ?? 0,
                    'values'   => [
                        1 => 'Приостановить режим автовоспроизведения при наведении курсора'
                    ],
                    'uikit'    => TRUE,
                ]),
                render_field('options.draggable', [
                    'type'     => 'checkbox',
                    'selected' => $entity->options->draggable ?? 0,
                    'values'   => [
                        1 => 'Перетаскивание слайдов'
                    ],
                    'uikit'    => TRUE,
                ]),
                render_field('options.finite', [
                    'type'     => 'checkbox',
                    'selected' => $entity->options->finite ?? 0,
                    'values'   => [
                        1 => 'Отключить бесконечное воспроизведение'
                    ],
                    'uikit'    => TRUE,
                ]),
                render_field('options.slidenav', [
                    'type'     => 'checkbox',
                    'selected' => $entity->options->slidenav ?? 0,
                    'values'   => [
                        1 => 'Стрелки навигации'
                    ],
                    'uikit'    => TRUE,
                ]),
                '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                render_field('options.min_height', [
                    'value' => $entity->options->min_height ?? NULL,
                    'label' => 'Минимальная высота',
                    'uikit' => TRUE,
                ]),
                '</div><div>',
                render_field('options.max_height', [
                    'label' => 'Максимальная высота',
                    'value' => $entity->options->min_height ?? NULL,
                    'uikit' => TRUE,
                ]),
                '</div></div>',
                '<hr class="uk-divider-icon">',
                render_field('status', [
                    'type'     => 'checkbox',
                    'selected' => $entity->exists ? $entity->status : 1,
                    'values'   => [
                        1 => 'Опубликовано'
                    ],
                    'uikit'    => TRUE,
                ])
            ]
        ];
        if ($entity->exists) {
            $_form->tabs[] = [
                'title'   => 'Слайды',
                'content' => [
                    view('backend.partials.insert_items', [
                        'entity' => $entity,
                        'route'  => $this->baseRoute,
                        'items'  => $entity->items,
                    ])
                        ->render(function ($view, $content) {
                            return clear_html($content);
                        })
                ]
            ];
        }
        $_form->tabs[] = $this->__form_tab_display_style($entity);

        return $_form;
    }

    protected function _items($wrap)
    {
        $_query = Slider::orderByDesc('status')
            ->orderBy('title')
            ->select([
                '*'
            ])
            ->with([
                '_slides'
            ])
            ->paginate($this->entity->getPerPage(), ['id']);
        $_buttons = [];
        if ($this->__can_permission('create')) {
            $_buttons[] = _l('Добавить', "oleus.{$this->baseRoute}.create", [
                'attributes' => [
                    'class' => 'uk-button uk-button-success uk-button-small',
                ]
            ]);
        }
        $_headers = [
            [
                'class' => 'uk-width-xsmall uk-text-center',
                'data'  => 'ID',
            ],
            [
                'data' => 'Название',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: photo_library">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: visibility">',
            ]
        ];
        if ($this->__can_permission('view')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: desktop">',
            ];
        }
        if ($this->__can_permission('update')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: edit">',
            ];
        }
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$item->id}</div>",
                    $item->title,
                    (string)$item->_slides->count(),
                    $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                ];
                if ($this->__can_permission('view')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.show", [
                        'p'          => [
                            $item
                        ],
                        'attributes' => [
                            'class'   => 'uk-button-icon uk-button uk-button-success uk-button-xsmall',
                            'uk-icon' => 'icon: desktop'
                        ]
                    ]);
                }
                if ($this->__can_permission('update')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.edit", [
                        'p'          => [
                            $item
                        ],
                        'attributes' => [
                            'class'   => 'uk-button-icon uk-button uk-button-primary uk-button-xsmall',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]);
                }

                return $_response;
            });
        }
        $_items = $this->__items([
            'buttons' => $_buttons,
            'headers' => $_headers,
            'items'   => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_slides = $item->_slides;
        if ($_slides->isNotEmpty()) {
            $_slides = $_slides->transform(function ($slide) {
                $_output = '<div class="uk-card uk-card-default uk-background-default uk-border-rounded uk-margin">';
                $_output .= "<div class=\"uk-card-header uk-text-bold uk-text-uppercase uk-heading-divider\">{$slide->title}</div>";
                $_output .= '<div class="uk-card-body">';
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Фон слайда</div>';
                $_output .= "<div class=\"uk-width-2-3\">" . render_image($slide->_background, 'thumb_preview_view') . "</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Под заголовок</div>';
                $_output .= "<div class=\"uk-width-2-3\">{$slide->sub_title}</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Содержимое</div>';
                $_output .= "<div class=\"uk-width-2-3\">{$slide->body}</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Ссылка</div>';
                $_output .= "<div class=\"uk-width-2-3\">{$slide->link}</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Скрыть заголовок</div>';
                $_output .= "<div class=\"uk-width-2-3\">" . ($slide->hidden_title ? 'да' : 'нет') . "</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Опубликовано</div>';
                $_output .= "<div class=\"uk-width-2-3\">" . ($slide->status ? 'да' : 'нет') . "</div></div>";
                $_output .= '</div></div>';

                return $_output;
            })->implode('');
        } else {
            $_slides = NULL;
        }
        $_contents = [
            [
                'Название',
                $item->title,
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
            [
                'Слайды',
                $_slides,
            ],
        ];
        $_view->contents = $_contents;

        return $_view;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required'
        ], [], [
            'title' => 'Заголовок'
        ]);
        $_save = $request->only([
            'title',
            'preset',
            'style_id',
            'style_class',
            'options',
            'status',
        ]);
        $_item = Slider::create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Slider $item)
    {
        $this->validate($request, [
            'title' => 'required'
        ], [], [
            'title' => 'Заголовок'
        ]);
        $_save = $request->only([
            'title',
            'preset',
            'style_id',
            'style_class',
            'options',
            'status',
        ]);
        $item->update($_save);

        return $this->__response_after_update($request, $item);
    }

    public function slider(Request $request, Slider $entity, $action, $id = NULL)
    {
        $commands = [];
        switch ($action) {
            case 'add':
            case 'edit':
                $_item = SliderItems::findOrNew($id);
                $_form = new Form([
                    'id'     => 'slide-items-form',
                    'class'  => 'uk-form',
                    'title'  => 'Добавление слайда',
                    'action' => _r('oleus.sliders.item', [
                        $entity,
                        'save',
                        $id
                    ]),
                    'tabs'   => TRUE,
                    'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                    'suffix' => '</div>',
                ]);
                $_locale = DEFAULT_LOCALE;
                $_tabs = [
                    [
                        'title'   => 'По умолчанию',
                        'content' => [
                            render_field('field.slider_id', [
                                'value' => $entity->id,
                                'type'  => 'hidden',
                            ]),
                            render_field("field.title.{$_locale}", [
                                'label'    => 'Заголовок',
                                'value'    => $_item->getTranslation('title', $_locale),
                                'uikit'    => TRUE,
                                'required' => TRUE,
                                'form_id'  => 'slide-items-form',
                            ]),
                            render_field("field.sub_title.{$_locale}", [
                                'label' => 'Под заголовок',
                                'value' => $_item->getTranslation('sub_title', $_locale),
                                'uikit' => TRUE,
                            ]),
                            render_field('field.background_fid', [
                                'type'     => 'file_drop',
                                'label'    => 'Фон слайда',
                                'allow'    => 'jpg|jpeg|gif|png|svg',
                                'values'   => $_item->background_fid ? [$_item->_background] : NULL,
                                'uikit'    => TRUE,
                                'form_id'  => 'slide-items-form',
                                'required' => TRUE,
                            ]),
                            render_field("field.body.{$_locale}", [
                                'label'      => 'Содержимое',
                                'value'      => $_item->getTranslation('body', $_locale),
                                'uikit'      => TRUE,
                                'attributes' => [
                                    'rows' => 5,
                                ],
                                'type'       => 'textarea',
                            ]),
                            render_field("field.link.{$_locale}", [
                                'label' => 'Ссылка',
                                'value' => $_item->getTranslation('link', $_locale),
                                'uikit' => TRUE,
                            ]),
                            render_field('field.link_attributes', [
                                'label'      => 'Дополнительные аттрибуты ссылки',
                                'type'       => 'textarea',
                                'value'      => $_item->link_attributes,
                                'uikit'      => TRUE,
                                'attributes' => [
                                    'rows' => 2
                                ]
                            ]),
                            '<hr class="uk-divider-icon">',
                            render_field('field.sort', [
                                'type'  => 'number',
                                'label' => 'Порядок сортировки',
                                'value' => $_item->sort ? : 0,
                                'uikit' => TRUE
                            ]),
                            render_field('field.hidden_title', [
                                'type'   => 'checkbox',
                                'value'  => $_item->exists ? $_item->hidden_title : 0,
                                'values' => [
                                    1 => 'Скрыть заголовок'
                                ],
                                'uikit'  => TRUE
                            ]),
                            render_field('field.status', [
                                'type'   => 'checkbox',
                                'value'  => $_item->exists ? $_item->status : 1,
                                'values' => [
                                    1 => 'Опубликовано'
                                ],
                                'uikit'  => TRUE
                            ]),
                        ]
                    ]
                ];
                if (USE_MULTI_LANGUAGE) {
                    $_languages = config('laravellocalization.supportedLocales');
                    foreach ($_languages as $_locale => $_data) {
                        if ($_locale != DEFAULT_LOCALE) {
                            $_tabs[] = [
                                'title'   => $_data['native'],
                                'content' => [
                                    render_field("field.title.{$_locale}", [
                                        'label' => 'Заголовок',
                                        'value' => $_item->getTranslation('title', $_locale, FALSE),
                                        'uikit' => TRUE,
                                    ]),
                                    render_field("field.sub_title.{$_locale}", [
                                        'label' => 'Под заголовок',
                                        'value' => $_item->getTranslation('sub_title', $_locale, FALSE),
                                        'uikit' => TRUE,
                                    ]),
                                    render_field("field.body.{$_locale}", [
                                        'label'      => 'Содержимое',
                                        'value'      => $_item->getTranslation('body', $_locale, FALSE),
                                        'uikit'      => TRUE,
                                        'attributes' => [
                                            'rows' => 5,
                                        ],
                                        'type'       => 'textarea',
                                    ]),
                                    render_field("field.link.{$_locale}", [
                                        'label' => 'Ссылка',
                                        'value' => $_item->getTranslation('link', $_locale, FALSE),
                                        'uikit' => TRUE,
                                    ]),
                                ]
                            ];
                        } else {
                            $_tabs[0]['title'] = "По умолчанию ({$_data['native']})";
                        }
                    }
                }
                $_form->setAjax();
                $_form->setFields($_tabs);
                $_form->setButtonSubmitText('Сохранить');
                $_form->setButtonSubmitClass('uk-button uk-button-success');
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content' => $_form->_render()
                    ]
                ];
                break;
            case 'save':
                $_field = $request->get('field');
                if (isset($_field['background_fid']) && ($background = $_field['background_fid'])) {
                    $_background = array_shift($background);
                    Session::flash('field.background_fid', json_encode([file_get($_background['id'])]));
                }
                $_locale = DEFAULT_LOCALE;
                $validate_rules = [
                    "field.title.{$_locale}" => 'required',
                    'field.background_fid'   => 'required',
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    "field.title.{$_locale}" => 'Заголовок',
                    'field.background_fid'   => 'Фон слайда',
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#slide-items-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'slide-items-form'),
                                'data'   => 'form-field-error'
                            ]
                        ];
                        $_notification .= "<li class='uk-margin-remove'>{$message[0]}</li>";
                    }
                    $_notification .= '</ul>';
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'status' => 'danger',
                            'text'   => $_notification
                        ]
                    ];
                } else {
                    $_field['background_fid'] = $_background['id'] ?? NULL;
                    $_item = SliderItems::findOrNew($id);
                    $_item->fill($_field);
                    $_item->save();
                    $items = $entity->items;
                    $commands['commands'][] = [
                        'command' => 'html',
                        'options' => [
                            'target' => '#list-insert-items',
                            'data'   => view('backend.partials.insert_items_table', compact('items'))
                                ->render(function ($view, $content) {
                                    return clear_html($content);
                                })
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'text'   => 'Элемент сохранен',
                            'status' => 'success',
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'UK_modalClose',
                        'options' => []
                    ];
                    $commands['commands'][] = [
                        'command' => 'removeClass',
                        'options' => [
                            'target' => '#uk-tab-body a.uk-button-save-sorting',
                            'data'   => 'uk-hidden'
                        ]
                    ];
                    Session::forget([
                        'field.background_fid',
                    ]);
                }
                break;
            case 'destroy':
                $_item = SliderItems::find($id);
                $_item->delete();
                $items = $entity->items;
                $commands['commands'][] = [
                    'command' => 'html',
                    'options' => [
                        'target' => '#list-insert-items',
                        'data'   => view('backend.partials.insert_items_table', compact('items'))
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            })
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text'   => 'Элемент удален',
                        'status' => 'success',
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_modalClose',
                    'options' => []
                ];
                if ($items->isEmpty()) {
                    $commands['commands'][] = [
                        'command' => 'addClass',
                        'options' => [
                            'target' => '#uk-tab-body a.uk-button-save-sorting',
                            'data'   => 'uk-hidden'
                        ]
                    ];
                }
                break;
        }
        update_last_modified_timestamp();

        return response($commands, 200);
    }

    public function sort(Request $request, Slider $entity)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                SliderItems::where('id', $_id)
                    ->update([
                        'sort' => $_sort
                    ]);
            }
        }
        $items = $entity->items;
        $commands['commands'][] = [
            'command' => 'html',
            'options' => [
                'target' => '#list-insert-items',
                'data'   => view('backend.partials.insert_items_table', compact('items'))
                    ->render(function ($view, $content) {
                        return clear_html($content);
                    })
            ]
        ];
        $commands['commands'][] = [
            'command' => 'UK_notification',
            'options' => [
                'text'   => 'Сортировка сохранена',
                'status' => 'success',
            ]
        ];

        return response($commands, 200);
    }
}
