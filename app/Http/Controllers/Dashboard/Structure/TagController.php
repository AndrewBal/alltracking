<?php

namespace App\Http\Controllers\Dashboard\Structure;

use App\Libraries\BaseController;
use App\Models\Structure\Page;
use App\Models\Structure\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TagController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Страницы тегов';
        $this->titles['create'] = 'Добавить страницу';
        $this->titles['edit'] = 'Редактировать страницу';
        $this->titles['translate'] = 'Перевод страницы на :locale';
        $this->middleware([
            'permission:tags_read'
        ]);
        $this->baseRoute = 'tags';
        $this->permissions = [
            'read'   => 'tags_read',
            'view'   => 'tags_view',
            'create' => 'tags_create',
            'update' => 'tags_update',
            'delete' => 'tags_delete',
        ];
        $this->entity = new Tag();
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
        $_query = Tag::from('tags as t')
            ->leftJoin('url_alias as a', function ($join) {
                $join->on('a.model_id', '=', 't.id')
                    ->where('a.locale', $this->defaultLocale)
                    ->where('a.model_type', Tag::class);
            })
            ->when($_filter, function ($query) use ($_filter) {
                if ($_filter['title']) $query->where('a.model_default_title', 'like', "%{$_filter['title']}%");
                if ($_filter['parent'] != 'all') $query->where('t.parent_id', $_filter['parent']);
                if ($_filter['alias']) $query->where('a.alias', 'like', "%{$_filter['alias']}%");
                if ($_filter['status'] != 'all') $query->where('t.status', $_filter['status']);
            })
            ->orderByDesc('t.status')
            ->orderBy('t.id')
            ->with([
                '_nodes',
                '_alias',
                '_parent'
            ])
            ->select([
                't.*'
            ])
            ->paginate($this->entity->getPerPage(), ['t.id']);
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
                'class' => 'uk-width-medium',
                'data'  => 'Родительсткий тег',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: description">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: visibility">',
            ],
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
            array_unshift($_headers, [
                'class' => 'uk-text-center',
                'style' => 'width: 18px;',
                'data'  => "<input type='checkbox' name='items_all' class='uk-checkbox uk-margin-remove'>",
            ]);
        }
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$item->id}</div>",
                    $item->_alias->id ? _l($item->title, $item->_alias->alias, ['attributes' => ['target' => '_blank']]) : $item->title,
                    $item->_parent ? $item->_parent->title : '-//-',
                    (string)$item->_nodes->count(),
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
                    array_unshift($_response, "<input type='checkbox' name='items[{$item->id}]' class='uk-checkbox uk-margin-remove'>");
                }

                return $_response;
            });
        }
        $_filters = [
            [
                'class' => 'uk-width-large',
                'data'  => render_field('title', [
                    'value'      => $_filter['title'] ?? NULL,
                    'attributes' => [
                        'placeholder' => 'Заголовок',
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
                'class' => 'uk-width-large',
                'data'  => render_field('alias', [
                    'value'      => $_filter['alias'] ?? NULL,
                    'attributes' => [
                        'placeholder' => 'Путь страницы',
                        'class'       => [
                            'uk-form-small'
                        ]
                    ],
                    'item_class' => [
                        'uk-margin-small-top uk-width-medium'
                    ],
                    'uikit'      => TRUE
                ])
            ]
        ];
        $_parents = $this->entity->tree_parents();
        if ($_parents->isNotEmpty()) {
            $_filters[] = [
                'data' => render_field('parent', [
                    'value'      => $_filter['parent'] ?? NULL,
                    'type'       => 'select',
                    'values'     => $_parents->pluck('title_option', 'id')->prepend('- Выбрать -', 'all'),
                    'uikit'      => TRUE,
                    'attributes' => [
                        'class' => [
                            'uk-form-small'
                        ]
                    ],
                    'item_class' => [
                        'uk-margin-small-top uk-width-small'
                    ],
                ])
            ];
        }
        $_filters[] = [
            'class' => 'uk-width-medium',
            'data'  => render_field('status', [
                'type'       => 'select',
                'value'      => $_filter['status'] ?? 'all',
                'values'     => [
                    'all' => 'Любой статус',
                    0     => 'Снять с публикации',
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
        ];
        $_items = $this->__items([
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'filters'     => $_filters,
            'actions'     => [
                'publish'    => 'Опубликовать',
                'no_publish' => 'Снять с публикации',
                'delete'     => 'Удалить',
            ],
            'use_filters' => $_filter ? TRUE : FALSE,
            'items'       => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'))
            ->render();
    }

    public function store(Request $request)
    {

        if ($medias = $request->input('medias')) {
            $_media = file_get(array_keys($medias));
            Session::flash('medias', json_encode($_media->toArray()));
        }
        if ($files = $request->input('files')) {
            $_files = file_get(array_keys($files));
            Session::flash('files', json_encode($_files->toArray()));
        }
        $this->validate($request, [
            'title' => 'required',
        ], [], [
            'title' => 'Заголовок ',
        ]);
        $_save = $request->only([
            'title',
            'sub_title',
            'breadcrumb_title',
            'body',
            'style_id',
            'style_class',
            'sort',
            'parent_id',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'meta_robots',
            'status',
        ]);
        $_item = Tag::create($_save);
        Session::forget([
            'medias',
            'files'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Tag $item)
    {
        if ($request->has('translate')) {
            $this->validate($request, [
                'title' => 'required',
            ], [], [
                'title' => 'Заголовок ',
            ]);
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $_save = $request->only([
                'title',
                'sub_title',
                'breadcrumb_title',
                'body',
                'meta_title',
                'meta_keywords',
                'meta_description',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            if ($medias = $request->input('medias')) {
                $_media = file_get(array_keys($medias));
                Session::flash('medias', json_encode($_media->toArray()));
            }
            if ($files = $request->input('files')) {
                $_files = file_get(array_keys($files));
                Session::flash('files', json_encode($_files->toArray()));
            }
            $this->validate($request, [
                'title' => 'required',
            ], [], [
                'title' => 'Заголовок ',
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'breadcrumb_title',
                'body',
                'style_id',
                'style_class',
                'sort',
                'parent_id',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'meta_robots',
            ]);
            $_save['background_fid'] = $_background_fid['id'] ?? NULL;
            $_save['status'] = $request->input('status', 0);
            $item->update($_save);
        }
        Session::forget([
            'medias',
            'files'
        ]);

        return $this->__response_after_update($request, $item);
    }
}
