<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Components\Menu;
use App\Models\Components\MenuItems;
use App\Models\Components\SliderItems;
use App\Models\Seo\UrlAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class MenuController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Список меню';
        $this->titles['create'] = 'Добавить меню';
        $this->titles['edit'] = 'Редактировать меню';
        $this->titles['translate'] = 'Перевод меню на :locale';
        $this->middleware([
            'permission:menus_read'
        ]);
        $this->baseRoute = 'menus';
        $this->permissions = [
            'read'   => 'menus_read',
            'view'   => 'menus_view',
            'create' => 'menus_create',
            'update' => 'menus_update',
            'delete' => 'menus_delete',
        ];
        $this->entity = new Menu();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->tabs[] = [
            'title'   => 'Основное',
            'content' => [
                render_field('title', [
                    'label'    => 'Название меню',
                    'value'    => $entity->title,
                    'required' => TRUE,
                    'uikit'    => TRUE
                ]),
                '<hr class="uk-divider-icon">',
                render_field('status', [
                    'type'     => 'checkbox',
                    'selected' => $entity->exists ? $entity->status : 1,
                    'values'   => [
                        1 => 'Опубликовано',
                    ],
                    'uikit'    => TRUE
                ]),
            ]
        ];
        if ($entity->exists) {
            $_form->tabs[] = [
                'title'   => 'Пункты меню',
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
        $_query = Menu::with([
            '_menu_items'
        ])
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
                'data' => 'Название меню',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: format_list_bulleted"></span>',
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
                    (string)$item->_menu_items->count(),
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
        $_menu_items = $item->items;
        if ($_menu_items->isNotEmpty()) {
            $_menu_items = $_menu_items->get('items')->transform(function ($item) {
                return '<li>' . $item->{0} . '</li>';
            })->implode('');
            $_menu_items = "<ul class='uk-list'>{$_menu_items}</ul>";
        } else {
            $_menu_items = NULL;
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
                'Пункты меню',
                $_menu_items,
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
            'title' => 'Название меню'
        ]);
        $_save = $request->only([
            'title',
            'style_id',
            'style_class',
            'status',
        ]);
        $_item = Menu::create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Menu $item)
    {
        $this->validate($request, [
            'title' => 'required',
        ], [], [
            'title' => 'Название меню'
        ]);
        $_save = $request->only([
            'title',
            'style_id',
            'style_class',
            'status',
        ]);
        $item->update($_save);

        return $this->__response_after_update($request, $item);

    }

    public function item(Request $request, Menu $entity, $action, $id = NULL)
    {
        $commands = [];
        $_parents = MenuItems::where('menu_id', $entity->id)
            ->when($id, function ($_query) use ($id) {
                $_query->where('id', '<>', $id);
            })
            ->orderBy('sort')
            ->pluck('title', 'id');
        if ($_parents->isNotEmpty()) $_parents->prepend('-- Выбрать --', 0);
        switch ($action) {
            case 'add':
            case 'edit':
                $_item = MenuItems::findOrNew($id);
                $_form = new Form([
                    'id'     => 'menu-items-form',
                    'class'  => 'uk-form',
                    'title'  => $_item->exists ? 'Редактирование пункта меню' : 'Добавление пункта меню',
                    'action' => _r('oleus.menus.item', [
                        $entity,
                        'save',
                        $id
                    ]),
                    'tabs'   => TRUE,
                    'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                    'suffix' => '</div>',
                ]);
                $_field_parents = NULL;
                if ($_parents->isNotEmpty()) {
                    $_field_parents = render_field('field.parent_id', [
                        'type'   => 'select',
                        'label'  => 'Родительский пункт',
                        'value'  => $_item->parent_id,
                        'uikit'  => TRUE,
                        'values' => $_parents->prepend('-Выбрать-', '')->toArray()
                    ]);
                }
                $_locale = DEFAULT_LOCALE;
                $_tabs = [
                    [
                        'title'   => 'По умолчанию',
                        'content' => [
                            render_field('field.menu_id', [
                                'value' => $entity->id,
                                'type'  => 'hidden',
                            ]),
                            '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                            render_field("field.title.{$_locale}", [
                                'label'    => 'Заголовок',
                                'value'    => $_item->getTranslation('title', $_locale),
                                'uikit'    => TRUE,
                                'required' => TRUE,
                                'form_id'  => 'menu-items-form',
                            ]),
                            render_field('field.link', [
                                'label'      => 'Ссылка на материал',
                                'type'       => 'autocomplete',
                                'selected'   => [
                                    'name'  => $_item->link->name ?? NULL,
                                    'value' => $_item->link->id ?? NULL,
                                ],
                                'uikit'      => TRUE,
                                'required'   => TRUE,
                                'form_id'    => 'menu-items-form',
                                'attributes' => [
                                    'data-path'  => _r('oleus.menus.link'),
                                    'data-value' => 'name'
                                ],
                                'help'       => '<span class="uk-text-bold">&lt;front&gt;</span> - ссылка на главную страницу, <span class="uk-text-bold">&lt;none&gt;</span> - пустая ссылка<br>Либо вручную вписать путь (https://domain/path/... или /path/...)'
                            ]),
                            $_field_parents,
                            '</div><div>',
                            render_field("field.sub_title.{$_locale}", [
                                'label' => 'Под заголовок',
                                'value' => $_item->getTranslation('sub_title', $_locale),
                                'uikit' => TRUE,
                            ]),
                            render_field('field.anchor', [
                                'label' => 'Якорь',
                                'value' => $_item->anchor,
                                'uikit' => TRUE,
                            ]),
                            '</div></div>',
                            '<hr class="uk-divider-icon">',
                            '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                            render_field('field.sort', [
                                'type'  => 'number',
                                'label' => 'Порядок сортировки',
                                'value' => $_item->sort ? : 0,
                                'uikit' => TRUE
                            ]),
                            '</div><div class="uk-padding-top">',
                            render_field('field.status', [
                                'type'       => 'checkbox',
                                'value'      => $_item->exists ? $_item->status : 1,
                                'values'     => [
                                    1 => 'Опубликовано'
                                ],
                                'attributes' => [
                                ],
                                'uikit'      => TRUE
                            ]),
                            '</div></div>',
                            '<hr class="uk-divider-icon">',
                            '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3">',
                            render_field('field.icon_fid', [
                                'type'   => 'file_drop',
                                'label'  => 'Иконка',
                                'allow'  => 'jpg|jpeg|gif|png|svg',
                                'values' => $_item->icon_fid ? [$_item->_icon] : NULL,
                                'uikit'  => TRUE,
                            ]),
                            '</div><div class="uk-width-2-3">',
                            render_field('field.data.item_class', [
                                'label' => '&lt;li class="..."',
                                'value' => $_item->data->item_class ?? NULL,
                                'uikit' => TRUE,
                            ]),
                            render_field('field.data.id', [
                                'label' => '&lt;a id="..."',
                                'value' => $_item->data->id ?? NULL,
                                'uikit' => TRUE,
                            ]),
                            render_field('field.data.class', [
                                'label' => '&lt;a class="..."',
                                'value' => $_item->data->class ?? NULL,
                                'uikit' => TRUE,
                            ]),
                            '</div></div>',
                            render_field('field.data.attributes', [
                                'label'      => 'Дополнительные атрибуты для ссылки',
                                'value'      => $_item->data->attributes ?? NULL,
                                'uikit'      => TRUE,
                                'attributes' => [
                                    'rows' => 3,
                                ],
                                'type'       => 'textarea',
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
                                    ])
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
                        'content'     => $_form->_render(),
                        'classDialog' => 'uk-width-1-2@l'
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'easyAutocomplete',
                    'options' => []
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
                    'field.link.name'        => 'required'
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    "field.title.{$_locale}" => 'Название пункта меню',
                    'field.link.name'        => 'Ссылка на материал'
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#menu-items-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'menu-items-form'),
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
                    $_item = MenuItems::findOrNew($id);
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
                        'field.icon_fid'
                    ]);
                }
                break;
            case 'destroy':
                $_item = MenuItems::find($id);
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

    public function link(Request $request)
    {
        $_items = [];
        if ($_search = $request->input('search')) {
            $_str = substr(strstr($_search, '::'), 2, strlen($_search));
            if ($_str) $_search = $_str;
            $_url = new UrlAlias();
            $_items = $_url->_items_for_menu($_search);
        }

        return response($_items, 200);
    }

    public function sort(Request $request, Menu $entity)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                MenuItems::where('id', $_id)
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
