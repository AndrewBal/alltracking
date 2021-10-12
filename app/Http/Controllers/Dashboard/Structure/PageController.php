<?php

namespace App\Http\Controllers\Dashboard\Structure;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Components\MenuItems;
use App\Models\Structure\NodeDataField;
use App\Models\Structure\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Permission;

class PageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Страницы';
        $this->titles['create'] = 'Добавить страницу';
        $this->titles['edit'] = 'Редактировать страницу';
        $this->titles['translate'] = 'Перевод страницы на :locale';
        $this->middleware([
            'permission:pages_read'
        ]);
        $this->baseRoute = 'pages';
        $this->permissions = [
            'read'   => 'pages_read',
            'view'   => 'pages_view',
            'create' => 'pages_create',
            'update' => 'pages_update',
            'delete' => 'pages_delete'
        ];
        $this->entity = new Page();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_field_type = NULL;
        if ($entity->exists && $entity->_alias->id) {
            $_form->buttons[] = _l('', $entity->_alias->alias, [
                'attributes' => [
                    'class'   => 'uk-button uk-button-success uk-margin-xsmall-right uk-button-small',
                    'uk-icon' => 'icon: link',
                    'target'  => '_blank'
                ]
            ]);
        }
        if (!$entity->exists) {
            $_field_type = render_field('type', [
                'type'   => 'select',
                'label'  => 'Тип',
                'values' => [
                    'normal'     => 'Обычная страница',
                    'list_nodes' => 'Страница со списком материалов'
                ],
                'uikit'  => TRUE
            ]);
        }
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    render_field('title', [
                        'label'      => 'Заголовок',
                        'value'      => $entity->title,
                        'required'   => TRUE,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<div class="uk-grid uk-child-width-1-2 uk-grid-small"><div>',
                    render_field('sub_title', [
                        'label' => 'Под заголовок',
                        'value' => $entity->sub_title,
                        'uikit' => TRUE
                    ]),
                    '</div><div>',
                    render_field('breadcrumb_title', [
                        'label' => 'Заголовок в "Хлебных крошках"',
                        'value' => $entity->breadcrumb_title,
                        'uikit' => TRUE
                    ]),
                    '</div></div>',
                    $_field_type,
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
        ];
        if ($entity->exists && in_array($entity->type, $entity::TYPES_USING_DEFAULT_TAGS)) {
            if($entity->type == 'list_nodes') {
                $_form->tabs[] = [
                    'title'   => 'Дополнительные поля',
                    'content' => [
                        '<div class="uk-border-rounded uk-box-shadow-small-inset uk-padding-small uk-background-default uk-text-small">На данной вкладке Вы можете указать дополнительные поля, если они необходимы для уникализации типа материалов. Данные из этих полей будут доступны для вывода в шаблонах материалов в виде переменных.</div>',
                        view('backend.partials.insert_items', [
                            'entity' => $entity,
                            'route'  => $this->baseRoute,
                            'items'  => $entity->fields,
                        ])
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            })
                    ]
                ];
            }
            $_form->tabs[] = [
                'title'   => 'Настройки SEO для материалов',
                'content' => [
                    '<div class="uk-border-rounded uk-box-shadow-small-inset uk-padding-small uk-background-default uk-text-small"><h4 class="uk-margin-remove-top uk-text-primary uk-text-light uk-margin-small-bottom">Метки подстановки для применения</h4><ul class="uk-list uk-list-small uk-margin-remove"><li><strong>[:title]</strong> - заголовка материала.</li></ul></div>',
                    '<h3 class="uk-heading-line uk-text-uppercase"><span>Настройка страницы списка материалов</span></h3>',
                    render_field('options.per_page', [
                        'type'   => 'select',
                        'label'  => 'Количество выводимых элементов на страницу',
                        'value'  => $entity->options->per_page ?? 'all',
                        'class'  => 'uk-select2',
                        'values' => $entity::PER_PAGE_OPTIONS,
                        'uikit'  => TRUE
                    ]),
                    '<h3 class="uk-heading-line uk-text-uppercase"><span>Шаблоны SEO по умолчанию</span></h3>',
                    render_field('tmp_meta_tags.meta_title', [
                        'label'      => 'Title',
                        'value'      => $entity->_tmp_meta_tags->meta_title,
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('tmp_meta_tags.meta_description', [
                        'type'       => 'textarea',
                        'label'      => 'Description',
                        'value'      => $entity->_tmp_meta_tags->meta_description,
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('tmp_meta_tags.meta_keywords', [
                        'type'       => 'textarea',
                        'label'      => 'Keywords',
                        'value'      => $entity->_tmp_meta_tags->meta_keywords,
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ])
                ]
            ];
        }elseif ($entity->type == 'search'){
            $_form->tabs[] = [
                'title'   => 'Настройки страницы результатов',
                'content' => [
                    render_field('options.per_page', [
                        'type'   => 'select',
                        'label'  => 'Количество выводимых элементов на страницу',
                        'value'  => $entity->options->per_page ?? 'all',
                        'class'  => 'uk-select2',
                        'values' => $entity::PER_PAGE_OPTIONS,
                        'uikit'  => TRUE
                    ]),
                ]
            ];
        }
        $_form->tabs[] = $this->__form_tab_seo($entity);
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
        if (in_array($entity->type, $entity::TYPES_USING_DEFAULT_TAGS)) {
            $_form->tabs[] = [
                'title'   => 'Настройки SEO для материалов',
                'content' => [
                    '<div class="uk-border-rounded uk-box-shadow-small-inset uk-padding-small uk-background-default uk-text-small"><h4 class="uk-margin-remove-top uk-text-primary uk-text-light uk-margin-small-bottom">Метки подстановки для применения</h4><ul class="uk-list uk-list-small uk-margin-remove"><li><strong>[:title]</strong> - заголовка материала.</li></ul></div>',
                    '<h3 class="uk-heading-line uk-text-uppercase"><span>Шаблоны SEO по умолчанию</span></h3>',
                    render_field('tmp_meta_tags.meta_title', [
                        'label'      => 'Title',
                        'value'      => $entity->_tmp_meta_tags->getTranslation('meta_title', $entity->frontLocale, FALSE),
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('tmp_meta_tags.meta_description', [
                        'type'       => 'textarea',
                        'label'      => 'Description',
                        'value'      => $entity->_tmp_meta_tags->getTranslation('meta_description', $entity->frontLocale, FALSE),
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('tmp_meta_tags.meta_keywords', [
                        'type'       => 'textarea',
                        'label'      => 'Keywords',
                        'value'      => $entity->_tmp_meta_tags->getTranslation('meta_keywords', $entity->frontLocale, FALSE),
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ])
                ]
            ];
        }
        $_form->tabs[] = $this->__form_tab_seo_for_translation($entity);

        return $_form;
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_contents = [
            [
                'Тип',
                $item->view_type,
            ],
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
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
        ];
        if (in_array($item->type, $item::TYPES_USING_DEFAULT_TAGS)) {
            $_per_page = $item->options->per_page ?? 'all';
            $_contents[] = [
                'Кол-во материалов на странице',
                $item::PER_PAGE_OPTIONS[$_per_page],
            ];
        }
        $_contents[] = [
            '<h3 class="uk-heading-line uk-text-uppercase"><span>SEO</span></h3>'
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
        if (in_array($item->type, $item::TYPES_USING_DEFAULT_TAGS)) {
            $_contents[] = [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Шаблоны SEO для материалов</span></h3>',
            ];
            $_contents[] = [
                'Title',
                $item->_tmp_meta_tags->meta_title,
            ];
            $_contents[] = [
                'Description',
                $item->_tmp_meta_tags->meta_description,
            ];
            $_contents[] = [
                'Keywords',
                $item->_tmp_meta_tags->meta_keywords,
            ];
            $_fields = collect($item->options->fields ?? []);
            if ($_fields->isNotEmpty()) {
                $_contents[] = [
                    '<h3 class="uk-heading-line uk-text-uppercase"><span>Дополнительные поля</span></h3>',
                ];
                $_fields->map(function ($field) use (&$_contents) {
                    $_type = 'Текстовое поле';
                    switch ($field->type) {
                        case 'textarea':
                            $_type = 'Текстовая область';
                            break;
                        case 'file_drop':
                            if ($field->multiple) {
                                $_type = 'Множественный выбор файлов';
                            } else {
                                $_type = 'Выбор файла';
                            }
                            break;
                    }
                    $_contents[] = [
                        $field->label,
                        $_type,
                    ];
                });
            }
        }
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
        $_buttons = [];
        $_query = Page::from('pages as p')
            ->leftJoin('url_alias as a', function ($join) {
                $join->on('a.model_id', '=', 'p.id')
                    ->where('a.locale', $this->defaultLocale)
                    ->where('a.model_type', Page::class);
            })
            ->when($_filter, function ($query) use ($_filter) {
                if ($_filter['title']) $query->where('a.model_title', 'like', "%{$_filter['title']}%");
                if ($_filter['alias']) $query->where('a.alias', 'like', "%{$_filter['alias']}%");
            })
            ->where('p.no_used', 0)
            ->orderByDesc('p.status')
            ->orderBy('p.id')
            ->distinct()
            ->select([
                'p.*'
            ])
            ->with([
                '_alias'
            ])
            ->paginate($this->entity->getPerPage(), ['p.id']);
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
                'data'  => 'Тип',
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
                    $item->view_type,
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
        $_items = $this->__items([
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'actions'     => [
                'publish'    => 'Опубликовать',
                'no_publish' => 'Снять с публикации',
                'delete'     => 'Удалить',
            ],
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'items'       => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
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
            'title' => 'required'
        ], [], [
            'title' => 'Заголовок'
        ]);
        $_save = $request->only([
            'title',
            'sub_title',
            'breadcrumb_title',
            'type',
            'body',
            'style_id',
            'style_class',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'style_id',
            'style_class',
            'status',
            'options',
        ]);
        $_item = Page::create($_save);
        Session::forget([
            'medias',
            'files'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Page $item)
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
                'title' => 'required'
            ], [], [
                'title' => 'Заголовок'
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'breadcrumb_title',
                'body',
                'style_id',
                'style_class',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'options',
                'status',
            ]);
            $item->update($_save);
        }
        Session::forget([
            'background_fid',
            'medias',
            'files'
        ]);

        return $this->__response_after_update($request, $item);
    }

    public function destroy(Request $request, Page $item)
    {
        $item->delete();

        return $this->__response_after_destroy($request, $item);
    }

    public function fields(Request $request, Page $page, $action, $key = NULL)
    {
        $commands = [];
        switch ($action) {
            case 'add':
            case 'edit':
                $_item = NULL;
                $_relation_pages = Page::where('type', 'list_nodes')
                    ->where('id', '<>', $page->id)
                    ->pluck('title', 'id');
                $_form = new Form([
                    'id'     => 'page-fields-form',
                    'class'  => 'uk-form',
                    'title'  => 'Добавление поля',
                    'action' => _r('oleus.pages.item', [
                        $page,
                        'save'
                    ]),
                    'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                    'suffix' => '</div>',
                ]);
                $_form->setAjax();
                $_form->setFields([
                    '<div class="uk-border-rounded uk-box-shadow-small-inset uk-padding-small uk-background-default uk-border-danger uk-text-small uk-text-danger uk-text-center">ВНИМАНИЕ!!! В дальнейшем поле нельзя будет отредактировать. Только удалить и создать заново.</div>',
                    render_field('field.name', [
                        'label'    => 'Машинное имя',
                        'value'    => NULL,
                        'uikit'    => TRUE,
                        'required' => TRUE,
                        'form_id'  => 'page-fields-form',
                        'help'     => 'Имя должно быть уникальным в пределах этой страницы! При заполнении можно использовать символы латиского алфавита в нижнем регистре, цифры и знак подчеркивания.',
                    ]),
                    render_field('field.label', [
                        'label'    => 'Название поля',
                        'value'    => NULL,
                        'uikit'    => TRUE,
                        'form_id'  => 'page-fields-form',
                        'required' => TRUE,
                    ]),
                    render_field('field.type', [
                        'type'       => 'select',
                        'label'      => 'Тип поля',
                        'value'      => NULL,
                        'values'     => [
                            'text'      => 'Текстовое поле',
                            'textarea'  => 'Текстовая область',
                            'file_drop' => 'Выбор файла',
                            'relation'  => 'Связанный материал',
                            'table'     => 'Таблица'
                        ],
                        'attributes' => [
                            'data-minimum-results-for-search' => 20
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<div id="page-fields-form-file-settings-box" class="uk-hidden">',
                    '<hr class="uk-divider-icon">',
                    render_field('field.multiple', [
                        'type'   => 'checkbox',
                        'value'  => NULL,
                        'values' => [
                            1 => 'Множественный выбор'
                        ],
                        'uikit'  => TRUE
                    ]),
                    render_field('field.allow', [
                        'value'    => NULL,
                        'label'    => 'Расширения файлов для загрузки',
                        'help'     => 'Перечислите форматы через вертикальную черту. Пример: jpg|jpeg|gif|png',
                        'uikit'    => TRUE,
                        'required' => TRUE,
                        'form_id'  => 'page-fields-form',
                    ]),
                    '</div>',
                    '<div id="page-fields-form-relation-settings-box" class="uk-hidden">',
                    '<hr class="uk-divider-icon">',
                    render_field('field.relation', [
                        'type'   => 'radio',
                        'label'  => 'Тип материалов для связи',
                        'values' => $_relation_pages->toArray(),
                        'uikit'  => TRUE,
                    ]),
                    '</div>',
                    '<div id="page-fields-form-table-settings-box" class="uk-hidden">',
                    '<hr class="uk-divider-icon">',
                    render_field('field.cols', [
                        'type'       => 'number',
                        'label'      => 'Количество колонок в таблице',
                        'uikit'      => TRUE,
                        'value'      => 2,
                        'form_id'    => 'page-fields-form',
                        'attributes' => [
                            'min'  => 2,
                            'step' => 1
                        ],
                        'help'       => 'Минимальное количество колонок в таблице 2'
                    ]),
                    '</div>'
                ]);
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
                $_page_fields = collect($page->options->fields ?? []);
                $_page_fields_keys = $_page_fields->keys()->implode(',');
                $_field = $request->get('field');
                $_type = $request->input('field.type');
                $validate_rules = [
                    'field.name'  => "required|regex:/^[a-z_0-9]+$/u|uniqueInArray:{$_page_fields_keys}",
                    'field.label' => 'required',
                    'field.allow' => 'required_if:field.type,file_drop',
                    'field.cols'  => 'required_if:field.type,table|numeric|min:2',
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    'field.name'  => 'Машинное имя',
                    'field.label' => 'Название поля',
                    'field.allow' => 'Расширения файлов',
                    'field.cols'  => 'Количество колонок',
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#page-fields-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'page-fields-form'),
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
                    if ($_type != 'relation') {
                        $_field['relation'] = NULL;
                    }
                    $_page_options = $page->options;
                    $_page_fields->put($_field['name'], $_field);
                    $_page_options->fields = json_decode($_page_fields->toJson());
                    $page->options = $_page_options;
                    $page->save();
                    $items = $page->fields;
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
                }
                break;
            case 'destroy':
                $_page_options = $page->options;
                $_page_fields = collect($page->options->fields ?? []);
                if ($_page_fields->has($key)) {
                    $_page_fields->forget($key);
                    NodeDataField::from('node_data_fields as nd')
                        ->join('nodes as n', 'n.id', '=', 'nd.node_id')
                        ->where('nd.field', $key)
                        ->where('n.page_id', $page->id)
                        ->delete();
                }
                if ($_page_fields->isNotEmpty()) {
                    $_page_options->fields = json_decode($_page_fields->toJson());
                } else {
                    $_page_options->fields = [];
                }
                $page->options = $_page_options;
                $page->save();
                $items = $page->fields;
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
                break;
        }

        return response($commands, 200);
    }
}
