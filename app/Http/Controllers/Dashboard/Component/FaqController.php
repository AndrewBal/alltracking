<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Components\Faq;
use App\Models\Components\FaqItems;
use App\Models\Structure\NodeDataField;
use App\Models\Structure\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class FaqController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Список вопросов';
        $this->titles['create'] = 'Добавить список';
        $this->titles['edit'] = 'Редактировать список';
        $this->titles['translate'] = 'Перевод списка на :locale';
        $this->middleware([
            'permission:faqs_read'
        ]);
        $this->baseRoute = 'faqs';
        $this->permissions = [
            'read'   => 'faqs_read',
            'view'   => 'faqs_view',
            'create' => 'faqs_create',
            'update' => 'faqs_update',
            'delete' => 'faqs_delete'
        ];
        $this->entity = new Faq();

    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    render_field('title', [
                        'label'      => 'Название списка',
                        'value'      => $entity->title,
                        'required'   => TRUE,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('body', [
                        'label'      => 'Описание',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->body,
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<hr class="uk-divider-icon">',
                    render_field('visible_title', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->visible_title : 1,
                        'values'   => [
                            1 => 'Показывать заголовок'
                        ],
                        'uikit'    => TRUE
                    ]),
                    render_field('status', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'Опубликовано'
                        ],
                        'uikit'    => TRUE
                    ]),
                ],
            ],
        ];
        if ($entity->exists) {
            $_form->tabs[] = [
                'title'   => 'Список',
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
        $_form->tabs[] = $this->__form_tab_translate($entity, $this->baseRoute, 'title');

        return $_form;
    }

    protected function _form_translate($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, [
            'translate' => $this->permissions['update']
        ]);
        $_form->tabs[] = [
            'title'   => 'Параметры перевода',
            'content' => [
                render_field('locale', [
                    'type'  => 'hidden',
                    'value' => $entity->frontLocale
                ]),
                render_field('translate', [
                    'type'  => 'hidden',
                    'value' => 1
                ]),
                render_field('title', [
                    'label'      => 'Заголовок',
                    'value'      => $entity->getTranslation('title', $entity->frontLocale, FALSE),
                    'required'   => TRUE,
                    'attributes' => [
                        'autofocus' => TRUE,
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('body', [
                    'label'      => 'Содержимое',
                    'type'       => 'textarea',
                    'editor'     => TRUE,
                    'value'      => $entity->getTranslation('body', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'rows' => 8,
                    ],
                    'uikit'      => TRUE
                ]),
            ]
        ];

        return $_form;
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_faqs = $item->_faqs;
        if ($_faqs->isNotEmpty()) {
            $_faqs = $_faqs->transform(function ($faq) {
                $_output = '<div class="uk-card uk-card-default uk-background-default uk-border-rounded uk-margin">';
                $_output .= "<div class=\"uk-card-header uk-text-bold uk-text-uppercase uk-heading-divider\">{$faq->question}";
                if ($faq->status) {
                    $_output .= "<span class=\"uk-float-right uk-text-success\" uk-icon=\"icon:visibility\"></span>";
                } else {
                    $_output .= "<span class=\"uk-float-right uk-text-danger\" uk-icon=\"icon:visibility_off\"></span>";
                }
                $_output .= '</div>';
                $_output .= "<div class=\"uk-card-body\">{$faq->answer}</div>";
                $_output .= '</div>';

                return $_output;
            })->implode('');
        } else {
            $_faqs = NULL;
        }
        $_contents = [
            [
                'Название списка',
                $item->title,
            ],
            [
                'Описание',
                $item->body,
            ],
            [
                'Список',
                $_faqs,
            ],
        ];
        $_view->contents = $_contents;

        return $_view;
    }

    protected function _items($wrap)
    {
        $this->__filter();
        $_filter = $this->filter;
        if ($this->filterClear) {
            return redirect()
                ->route("oleus.{$this->baseRoute}");
        }
        $_filters = [];
        $_query = Faq::when($_filter, function ($query) use ($_filter) {
            if ($_filter['title']) $query->where('title', 'like', "%{$_filter['title']}%");
        })
            ->orderByDesc('status')
            ->select([
                '*'
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
                'data' => 'Список',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: reorder">',
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
                    (string)$item->items->count(),
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
        $_filters[] = [
            'class' => 'uk-width-large',
            'data'  => render_field('title', [
                'value'      => $_filter['title'] ?? NULL,
                'attributes' => [
                    'placeholder' => 'Название списка',
                    'class'       => [
                        'uk-form-small'
                    ]
                ],
                'item_class' => [
                    'uk-margin-small-top uk-width-medium'
                ],
                'uikit'      => TRUE
            ])
        ];
        $_items = $this->__items([
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'items'       => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ], [], [
            'title' => 'Название списка',
        ]);
        $_save = $request->only([
            'title',
            'body',
            'body',
            'style_class',
            'style_id',
            'visible_title',
            'status',
        ]);
        $_item = Faq::create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Faq $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $_save = $request->only([
                'title',
                'body',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            $this->validate($request, [
                'title' => 'required',
            ], [], [
                'title' => 'Название списка',
            ]);
            $_save = $request->only([
                'title',
                'body',
                'visible_title',
                'style_class',
                'style_id',
                'status',
            ]);
            $item->update($_save);
        }

        return $this->__response_after_update($request, $item);
    }

    public function faqs(Request $request, Faq $entity, $action, $id = NULL)
    {
        $commands = [];
        switch ($action) {
            case 'add':
            case 'edit':
                $_item = FaqItems::findOrNew($id);
                $_form = new Form([
                    'id'     => 'faq-items-form',
                    'class'  => 'uk-form',
                    'title'  => 'Добавление элемента списка',
                    'action' => _r('oleus.faqs.item', [
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
                            render_field('field.faq_id', [
                                'value' => $entity->id,
                                'type'  => 'hidden',
                            ]),
                            render_field("field.question.{$_locale}", [
                                'label'      => 'Вопрос',
                                'value'      => $_item->getTranslation('question', $_locale),
                                'uikit'      => TRUE,
                                'required'   => TRUE,
                                'type'       => 'textarea',
                                'attributes' => [
                                    'rows' => 5,
                                ],
                                'form_id'    => 'faq-items-form',
                            ]),
                            render_field("field.answer.{$_locale}", [
                                'label'      => 'Ответ ',
                                'value'      => $_item->getTranslation('answer', $_locale),
                                'uikit'      => TRUE,
                                'form_id'    => 'faq-items-form',
                                'required'   => TRUE,
                                'attributes' => [
                                    'rows' => 5,
                                ],
                                'type'       => 'textarea',
                            ]),
                            '<hr class="uk-divider-icon">',
                            render_field('field.sort', [
                                'type'  => 'number',
                                'label' => 'Порядок сортировки',
                                'value' => $_item->sort ? : 0,
                                'uikit' => TRUE
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
                                    render_field("field.question.{$_locale}", [
                                        'label'      => 'Вопрос',
                                        'value'      => $_item->getTranslation('question', $_locale, FALSE),
                                        'uikit'      => TRUE,
                                        'type'       => 'textarea',
                                        'attributes' => [
                                            'rows' => 5,
                                        ],
                                        'form_id'    => 'faq-items-form',
                                    ]),
                                    render_field("field.answer.{$_locale}", [
                                        'label'      => 'Ответ ',
                                        'value'      => $_item->getTranslation('answer', $_locale, FALSE),
                                        'uikit'      => TRUE,
                                        'form_id'    => 'faq-items-form',
                                        'attributes' => [
                                            'rows' => 5,
                                        ],
                                        'type'       => 'textarea',
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
                $_locale = DEFAULT_LOCALE;
                $validate_rules = [
                    "field.question.{$_locale}" => 'required',
                    "field.answer.{$_locale}"   => 'required',
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    "field.question.{$_locale}" => 'Вопрос',
                    "field.answer.{$_locale}"   => 'Ответ',
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#faq-items-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'faq-items-form'),
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
                    $_item = FaqItems::findOrNew($id);
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
                }
                break;
            case 'destroy':
                $_item = FaqItems::find($id);
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

        return response($commands, 200);
    }

    public function sort(Request $request, Faq $entity)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                FaqItems::where('id', $_id)
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
