<?php

namespace App\Http\Controllers\Dashboard\Structure;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Components\MenuItems;
use App\Models\Structure\NodeTag;
use App\Models\Structure\Page;
use App\Models\Structure\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class NodeTagController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Теги материалов';
        $this->middleware([
            'permission:tags_read'
        ]);
        $this->baseRoute = 'node_tags';
        $this->permissions = [
            'read'   => 'tags_read',
            'view'   => 'tags_view',
            'create' => 'tags_create',
            'update' => 'tags_update',
            'delete' => 'tags_delete',
        ];
        $this->entity = new NodeTag();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->seo = TRUE;
        $_field_parent = NULL;
        $_parents = $entity->tree_parents(TRUE);
        if ($_parents->isNotEmpty()) {
            $_parents = $_parents->map(function ($_item) {
                return $_item['title_option'];
            });
            if ($_parents->isNotEmpty()) $_parents->prepend('-- Выбрать --', '');
            $_field_parent = render_field('parent_id', [
                'type'   => 'select',
                'label'  => 'Родительский тег',
                'value'  => $entity->parent_id,
                'values' => $_parents,
                'uikit'  => TRUE
            ]);
        }
        $_form->permission = array_merge($_form->permission, $this->permissions);
        if ($entity->exists && $entity->_alias->id) {
            $_form->buttons[] = _l('', $entity->_alias->alias, [
                'attributes' => [
                    'class'   => 'uk-button uk-button-success uk-margin-xsmall-right uk-button-small',
                    'uk-icon' => 'icon: link',
                    'target'  => '_blank'
                ]
            ]);
        }
        $_form->tabs = [
            [
                'title'   => 'Основные параметры',
                'content' => [
                    render_field('locale', [
                        'type'  => 'hidden',
                        'value' => config('app.default_locale'),
                    ]),
                    render_field('title', [
                        'label'      => 'Заголовок',
                        'value'      => $entity->getTranslation('title', DEFAULT_LOCALE),
                        'required'   => TRUE,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('sub_title', [
                        'label' => 'Под заголовок',
                        'value' => $entity->getTranslation('sub_title', DEFAULT_LOCALE),
                        'uikit' => TRUE
                    ]),
                    render_field('breadcrumb_title', [
                        'label' => 'Заголовок в "Хлебных крошках"',
                        'value' => $entity->getTranslation('breadcrumb_title', DEFAULT_LOCALE),
                        'uikit' => TRUE
                    ]),
                    $_field_parent,
                    render_field('body', [
                        'label'      => 'Содержимое',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->getTranslation('body', DEFAULT_LOCALE),
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<hr class="uk-divider-icon">',
                    render_field('sort', [
                        'type'  => 'number',
                        'label' => 'Порядок сортировки',
                        'value' => $entity->exists ? $entity->sort : 0,
                        'uikit' => TRUE
                    ]),
                    render_field('status', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'Опубликовано'
                        ],
                        'uikit'    => TRUE
                    ])
                ],
            ],
            $this->__form_tab_display_style($entity),
            $this->__form_tab_seo($entity),
            $this->__form_tab_translate($entity, $this->baseRoute, 'title')
        ];

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
                    'label'      => 'Заголовок',
                    'value'      => $entity->getTranslation('title', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'autofocus' => TRUE,
                    ],
                    'required'   => TRUE,
                    'uikit'      => TRUE
                ]),
                render_field('sub_title', [
                    'label' => 'Под заголовок',
                    'value' => $entity->getTranslation('sub_title', $entity->frontLocale, FALSE),
                    'uikit' => TRUE
                ]),
                render_field('breadcrumb_title', [
                    'label' => 'Заголовок в "Хлебных крошках"',
                    'value' => $entity->getTranslation('breadcrumb_title', $entity->frontLocale, FALSE),
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
        $_form->tabs[] = $this->__form_tab_seo_for_translation($entity);

        return $_form;
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_contents = [
            [
                'Заголовок',
                $item->title,
            ],
            [
                'Под заголовок',
                $item->sub_title,
            ],
            [
                'Заголовок в "Хлебных крошках"',
                $item->breadcrumb_title,
            ],
            [
                'Содержимое',
                $item->body,
            ],
            [
                'Кол-во связанных материалов',
                $item->_nodes->count(),
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
            [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>SEO</span></h3>'
            ]
        ];
        if ($item->_alias->id) {
            $_contents[] = [
                'URL',
                $item->_alias->alias,
            ];
        }
        $_contents[] = [
            'Title',
            $item->meta_title,
        ];
        $_contents[] = [
            'Description',
            $item->meta_description,
        ];
        $_contents[] = [
            'Keywords',
            $item->meta_keywords,
        ];
        if ($item->_alias->id) {
            $_contents[] = [
                'Robots',
                $item->_alias->robots,
            ];
            $_contents[] = [
                'Опубликовать в карте сайта',
                $item->_alias->sitemap ? 'да' : 'нет',
            ];
            $_contents[] = [
                'Частота изменения',
                $item->_alias->changefreq,
            ];
            $_contents[] = [
                'Приоритет',
                $item->_alias->priority,
            ];
        }
        $_view->contents = $_contents;

        return $_view;
    }

    protected function _items($wrap)
    {
        $this->__filter();
        if ($this->filterClear) {
            return redirect()
                ->route("oleus.{$this->baseRoute}");
        }
        $_filter = $this->filter;
        $_buttons = [];
        $_query = NodeTag::from('node_tags as t')
            ->when($_filter, function ($query) use ($_filter) {
                if ($_filter['title']) $query->where('t.default_title', 'like', "%{$_filter['title']}%");
                if ($_filter['status'] != 'all') $query->where('t.status', $_filter['status']);
            })
            ->orderByDesc('t.status')
            ->orderBy('t.id')
            ->with([
                '_nodes',
            ])
            ->select([
                't.*'
            ])
            ->paginate($this->entity->getPerPage(), ['t.id']);
        if ($this->__can_permission('update')) {
            if ($_query->isNotEmpty()) {
                $_buttons[] = _l('Сохранить сортировку', "oleus.{$this->baseRoute}.sort", [
                    'attributes' => [
                        'class' => 'uk-button uk-button-primary uk-button-small uk-button-save-sorting',
                    ]
                ]);
            }
        }
        $_headers = [
            [
                'class' => 'uk-width-xsmall uk-text-center',
                'data'  => 'ID',
            ],
            [
                'data' => 'Тег',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: description">',
            ],
            [
                'class' => 'uk-width-xsmall uk-text-center',
                'data'  => '<span uk-icon="icon: sort_by_alpha"></span>',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: visibility">',
            ],
        ];
        if ($this->__can_permission('update')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: edit">',
            ];
        }
        if ($this->__can_permission('delete')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: delete_forever">',
            ];
        }
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$item->id}</div>",
                    [
                        'data'  => $item->default_title,
                        'class' => 'tag-name'
                    ],
                    (string)$item->_nodes->count(),
                    '<input type="number" class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item" name="items_sort[]" data-id="' . $item->id . '" value="' . $item->sort . '">',
                    $item->status ? '<span class="uk-text-success status" uk-icon="icon: done"></span>' : '<span class="uk-text-danger status" uk-icon="icon: close"></span>',
                ];
                if ($this->__can_permission('update')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.item", [
                        'p'          => [
                            $item,
                        ],
                        'attributes' => [
                            'class'   => 'uk-button-icon uk-button uk-button-primary uk-button-xsmall use-ajax',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]);
                }
                if ($this->__can_permission('delete')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.item", [
                        'p'          => [
                            $item,
                            'remove'
                        ],
                        'attributes' => [
                            'class'   => 'uk-button-icon uk-button uk-button-danger uk-button-xsmall use-ajax',
                            'uk-icon' => 'icon: delete_forever'
                        ]
                    ]);
                }

                return [
                    'id'   => "table-item-row-{$item->id}",
                    'data' => $_response,
                ];
            });
        }
        $_filters = [
            [
                'class' => 'uk-width-large',
                'data'  => render_field('title', [
                    'value'      => $_filter['title'] ?? NULL,
                    'attributes' => [
                        'placeholder' => 'Тег',
                        'class'       => [
                            'uk-form-small'
                        ]
                    ],
                    'item_class' => [
                        'uk-margin-small-top uk-width-medium'
                    ],
                    'uikit'      => TRUE
                ])
            ],
            [
                'class' => 'uk-width-medium',
                'data'  => render_field('status', [
                    'type'       => 'select',
                    'value'      => $_filter['status'] ?? 'all',
                    'values'     => [
                        'all' => 'Любой статус',
                        0     => 'Снят с публикации',
                        1     => 'Опубликован',
                    ],
                    'attributes' => [
                        'class' => [
                            'uk-form-small'
                        ]
                    ],
                    'item_class' => [
                        'uk-margin-small-top uk-width-small'
                    ],
                    'uikit'      => TRUE
                ])
            ]
        ];
        $_items = $this->__items([
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'items'       => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'))
            ->render();
    }

    public function save_sort(Request $request)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                NodeTag::where('id', $_id)
                    ->update([
                        'sort' => $_sort
                    ]);
            }
        }
        $commands['commands'][] = [
            'command' => 'UK_notification',
            'options' => [
                'status' => 'success',
                'text'   => 'Порядок обновлен.'
            ]
        ];

        return response($commands, 200);
    }

    public function tag(Request $request, NodeTag $entity, $action = 'edit')
    {
        $commands = [];
        switch ($action) {
            case 'edit':
                $_form = new Form([
                    'id'     => 'node-tags-items-form',
                    'class'  => 'uk-form',
                    'title'  => 'Редактирование тега материала',
                    'action' => _r('oleus.node_tags.item', [
                        $entity,
                        'save'
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
                            render_field("field.title.{$_locale}", [
                                'label'    => 'Название тега',
                                'value'    => $entity->getTranslation('title', $_locale),
                                'uikit'    => TRUE,
                                'required' => TRUE,
                                'form_id'  => 'node-tags-items-form',
                            ]),
                            render_field('field.sort', [
                                'type'  => 'number',
                                'label' => 'Порядок сортировки',
                                'value' => $entity->sort ? : 0,
                                'uikit' => TRUE
                            ]),
                            render_field('field.status', [
                                'type'       => 'checkbox',
                                'value'      => $entity->status,
                                'values'     => [
                                    1 => 'Опубликовано'
                                ],
                                'attributes' => [
                                ],
                                'uikit'      => TRUE
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
                                        'value' => $entity->getTranslation('title', $_locale, FALSE),
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
                        'content' => $_form->_render(),
                    ]
                ];
                break;
            case 'save':
                $_field = $request->get('field');
                $_locale = DEFAULT_LOCALE;
                $validate_rules = [
                    "field.title.{$_locale}" => 'required'
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    "field.title.{$_locale}" => 'Название тега',
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#node-tags-items-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'node-tags-items-form'),
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
                    $_field['default_title'] = $_field['title'][$_locale];
                    $entity->fill($_field);
                    $entity->save();
                    $commands['commands'][] = [
                        'command' => 'text',
                        'options' => [
                            'target' => "#table-item-row-{$entity->id} .tag-name",
                            'data'   => $_field['default_title']
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'val',
                        'options' => [
                            'target' => "#table-item-row-{$entity->id} input",
                            'data'   => $_field['sort']
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'replaceWith',
                        'options' => [
                            'target' => "#table-item-row-{$entity->id} .status",
                            'data'   => '<span class="uk-text-' . ($entity->status ? 'success' : 'danger') . ' status" uk-icon="icon: ' . ($entity->status ? 'done' : 'close') . '"></span>'
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
                }
                break;
            case 'remove':
                $_form = new Form([
                    'id'     => 'node-tags-items-form',
                    'class'  => 'uk-form',
                    'title'  => 'Удалить тег',
                    'action' => _r('oleus.node_tags.item', [
                        $entity,
                        'destroy'
                    ]),
                    'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                    'suffix' => '</div>',
                ]);
                $_form->setAjax();
                $_form->setFields([
                    render_field('field.transfer', [
                        'label'   => 'ID тега к которому перевести все привязанные материалы',
                        'uikit'   => TRUE,
                        'form_id' => 'node-tags-items-form',
                    ]),
                ]);
                $_form->setButtonSubmitText('Удалить');
                $_form->setButtonSubmitClass('uk-button uk-button-danger');
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content' => $_form->_render(),
                    ]
                ];
                break;
            case 'destroy':
                $_transfer = $request->input('field.transfer');
                if ($_transfer) {
                    $_transfer = NodeTag::find($_transfer);
                    $_transfer_existing_ids = $_transfer->_nodes()
                        ->pluck('model_id');
                    $_entity_existing_ids = $entity->_nodes()
                        ->pluck('model_id');
                    $_diff = $_entity_existing_ids->diff($_transfer_existing_ids);
                    if ($_diff->isNotEmpty()) {
                        $_transfer->_nodes()->attach($_diff);
                    }
                }
                $entity->delete();
                $commands['commands'][] = [
                    'command' => 'addClass',
                    'options' => [
                        'target' => "#table-item-row-{$entity->id}",
                        'data'   => 'uk-background-danger-light'
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
                break;
        }
        update_last_modified_timestamp();

        return response($commands, 200);
    }
}
