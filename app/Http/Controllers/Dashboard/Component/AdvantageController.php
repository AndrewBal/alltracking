<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Components\Advantage;
use App\Models\Components\AdvantageItems;
use App\Models\Components\MenuItems;
use App\Models\Components\FaqItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AdvantageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:advantages_read'
        ]);
        $this->titles['index'] = 'Список преимуществ';
        $this->titles['create'] = 'Добавить преимущество';
        $this->titles['edit'] = 'Редактировать преимущество';
        $this->titles['translate'] = 'Перевод преимущества на :locale';
        $this->baseRoute = 'advantages';
        $this->permissions = [
            'read'   => 'advantages_read',
            'view'   => 'advantages_view',
            'create' => 'advantages_create',
            'update' => 'advantages_update',
            'delete' => 'advantages_delete',
        ];
        $this->entity = new Advantage();
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
                        'label'      => 'Заголовок',
                        'value'      => $entity->title,
                        'attributes' => [
                            'autofocus' => TRUE
                        ],
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    render_field('sub_title', [
                        'label' => 'Под заголовок',
                        'value' => $entity->sub_title,
                        'uikit' => TRUE
                    ]),
                    render_field('body', [
                        'label'      => 'Содержимое',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->body,
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'uikit'      => TRUE
                    ]),

                    '<hr class="uk-divider-icon">',
                    render_field('hidden_title', [
                        'type'     => 'checkbox',
                        'name'     => 'status',
                        'selected' => $entity->exists ? $entity->hidden_title : 0,
                        'values'   => [
                            1 => 'Скрыть заголовок при выводе на страницу',
                        ],
                        'uikit'    => TRUE
                    ]),
                    render_field('status', [
                        'type'     => 'checkbox',
                        'name'     => 'status',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'Опубликовано',
                        ],
                        'uikit'    => TRUE
                    ]),
                ]
            ],
            $this->__form_tab_display_style($entity, 'background')
        ];
        if ($entity->exists) {
            $_form->tabs[] = [
                'title'   => 'Список преимуществ',
                'content' => [
                    view('backend.partials.insert_items', [
                        'entity' => $entity,
                        'route'  => $this->baseRoute,
                        'items'  => $entity->items,
                    ])
                        ->render(function ($view, $content) {
                            return clear_html($content);
                        })
                ],
            ];
        }
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
        $_form->use_multi_language = FALSE;
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
                    'label'    => 'Заголовок',
                    'value'    => $entity->getTranslation('title', $entity->frontLocale, FALSE),
                    'required' => TRUE,
                    'uikit'    => TRUE
                ]),
                render_field('sub_title', [
                    'label' => 'Под заголовок',
                    'value' => $entity->getTranslation('sub_title', $entity->frontLocale, FALSE),
                    'uikit' => TRUE
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
        $_advantages = $item->_advantages;
        if ($_advantages->isNotEmpty()) {
            $_advantages = $_advantages->transform(function ($advantage) {
                $_output = '<div class="uk-card uk-card-default uk-background-default uk-border-rounded uk-margin">';
                $_output .= "<div class=\"uk-card-header uk-text-bold uk-text-uppercase uk-heading-divider\">{$advantage->title}</div>";
                $_output .= '<div class="uk-card-body">';
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Иконка</div>';
                $_output .= "<div class=\"uk-width-2-3\">" . render_image($advantage->_icon, 'thumb_preview_view') . "</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Под заголовок</div>';
                $_output .= "<div class=\"uk-width-2-3\">{$advantage->sub_title}</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Содержимое</div>';
                $_output .= "<div class=\"uk-width-2-3\">{$advantage->body}</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Скрыть заголовок</div>';
                $_output .= "<div class=\"uk-width-2-3\">" . ($advantage->hidden_title ? 'да' : 'нет') . "</div></div>";
                $_output .= '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3 uk-text-bold uk-text-right">Опубликовано</div>';
                $_output .= "<div class=\"uk-width-2-3\">" . ($advantage->status ? 'да' : 'нет') . "</div></div>";
                $_output .= '</div></div>';

                return $_output;
            })->implode('');
        } else {
            $_advantages = NULL;
        }
        $_contents = [
            [
                'Заголовок',
                $item->title,
            ],
            [
                'Описание',
                $item->body,
            ],
            [
                'Скрыть заголовок при выводе на страницу',
                $item->hidden_title ? 'да' : 'нет',
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
            [
                'Список',
                $_advantages,
            ],
        ];
        $_view->contents = $_contents;

        return $_view;
    }

    protected function _items($wrap)
    {
        $_query = Advantage::orderBy('title')
            ->select([
                '*'
            ])
            ->with([
                '_advantages'
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
                'data' => 'Заголовок',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: view_comfy">',
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
                    (string)$item->_advantages->count(),
                    $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>'
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

    public function store(Request $request)
    {
        if ($background_fid = $request->input('background_fid')) {
            $_background_fid = array_shift($background_fid);
            Session::flash('background_fid', json_encode([file_get($_background_fid['id'])]));
        }
        $this->validate($request, [
            'title' => 'required',
        ], [], [
            'title' => 'Заголовок'
        ]);
        $_save = $request->only([
            'title',
            'sub_title',
            'body',
            'style_id',
            'style_class',
            'status',
            'hidden_title',
        ]);
        $_save['background_fid'] = $_background_fid['id'] ?? NULL;
        $_item = Advantage::create($_save);
        Session::forget([
            'background_fid'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Advantage $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $this->validate($request, [
                'title' => 'required'
            ], [], [
                'title' => 'Заголовок'
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'body',

            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            if ($background_fid = $request->input('background_fid')) {
                $_background_fid = array_shift($background_fid);
                Session::flash('background_fid', json_encode([file_get($_background_fid['id'])]));
            }
            $this->validate($request, [
                'title' => 'required',
            ], [], [
                'title' => 'Заголовок'
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'body',
                'style_id',
                'style_class',
                'status',
                'hidden_title',

            ]);
            $_save['background_fid'] = $_background_fid['id'] ?? NULL;
            $item->update($_save);
        }
        Session::forget([
            'background_fid'
        ]);

        return $this->__response_after_update($request, $item);
    }

    public function advantage(Request $request, Advantage $entity, $action, $id = NULL)
    {
        $commands = [];
        switch ($action) {
            case 'add':
            case 'edit':
                $_item = AdvantageItems::findOrNew($id);
                $_form = new Form([
                    'id'     => 'advantage-items-form',
                    'class'  => 'uk-form',
                    'title'  => 'Добавление элемента преимущества',
                    'action' => _r('oleus.advantages.item', [
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
                            render_field('field.advantage_id', [
                                'value' => $entity->id,
                                'type'  => 'hidden',
                            ]),
                            '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                            render_field('field.icon_fid', [
                                'type'   => 'file_drop',
                                'label'  => 'Иконка',
                                'allow'  => 'jpg|jpeg|gif|png|svg',
                                'values' => $_item->icon_fid ? [$_item->_icon] : NULL,
                                'uikit'  => TRUE
                            ]),
                            '</div><div>',
                            render_field("field.title.{$_locale}", [
                                'label'    => 'Заголовок',
                                'value'    => $_item->getTranslation('title', $_locale),
                                'uikit'    => TRUE,
                                'required' => TRUE,
                                'form_id'  => 'advantage-items-form',
                            ]),
                            render_field("field.sub_title.{$_locale}", [
                                'label' => 'Под заголовок',
                                'value' => $_item->getTranslation('sub_title', $_locale),
                                'uikit' => TRUE,
                            ]),
                            '</div></div>',
                            render_field("field.body.{$_locale}", [
                                'label'      => 'Содержимое',
                                'value'      => $_item->getTranslation('body', $_locale),
                                'uikit'      => TRUE,
                                'editor'     => TRUE,
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
                if (isset($_field['icon_fid']) && ($icon = $_field['icon_fid'])) {
                    $_icon = array_shift($icon);
                    Session::flash('field.icon_fid', json_encode([file_get($_icon['id'])]));
                }
                $_locale = DEFAULT_LOCALE;
                $validate_rules = [
                    "field.title.{$_locale}" => 'required',
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    "field.title.{$_locale}" => 'Заголовок',
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#advantage-items-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'advantage-items-form'),
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
                    $_field['icon_fid'] = $_icon['id'] ?? NULL;
                    $_item = AdvantageItems::findOrNew($id);
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
                        'field.icon_fid',
                    ]);
                }
                break;
            case 'destroy':
                $_item = AdvantageItems::find($id);
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

    public function sort(Request $request, Advantage $entity)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                AdvantageItems::where('id', $_id)
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
