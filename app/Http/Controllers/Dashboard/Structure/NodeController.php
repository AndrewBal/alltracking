<?php

namespace App\Http\Controllers\Dashboard\Structure;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Structure\Node;
use App\Models\Structure\NodeRelation;
use App\Models\Structure\NodeRelationField;
use App\Models\Structure\Page;
use App\Models\User\Group;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use function Couchbase\defaultDecoder;

class NodeController extends BaseController
{
    protected $types;
    protected $authors;

    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Материалы';
        $this->titles['create'] = 'Добавить страницу';
        $this->titles['edit'] = 'Редактировать страницу';
        $this->titles['translate'] = 'Перевод страницы на :locale';
        $this->middleware([
            'permission:nodes_read'
        ]);
        $this->types = Node::nodeTypes();
        $this->tags = Node::tags();
        $this->nodeTags = Node::nodeTags();
        $this->authors = Node::authors();
        $this->baseRoute = 'nodes';
        $this->permissions = [
            'read' => 'nodes_read',
            'view' => 'nodes_view',
            'create' => 'nodes_create',
            'update' => 'nodes_update',
            'delete' => 'nodes_delete',
        ];
        $this->entity = new Node();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->seo = TRUE;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_tags = [];
        $_node_tags = [];
        $_field_type = NULL;
        $_field_tags = NULL;
        $_field_node_tags = NULL;
        if ($entity->exists && $entity->_alias->id) {
            $_form->buttons[] = _l('', $entity->_alias->alias, [
                'attributes' => [
                    'class' => 'uk-button uk-button-success uk-margin-xsmall-right uk-button-small',
                    'uk-icon' => 'icon: link',
                    'target' => '_blank'
                ]
            ]);
        }
        if ($this->tags) {
            $_tags = $this->tags->keyBy('id')->map(function ($_type) {
                return $_type->title;
            })->toArray();
        }
        if ($this->nodeTags) {
            $_node_tags = $this->nodeTags->keyBy('id')->map(function ($_type) {
                return $_type->title;
            })->toArray();
        }
        $_field_additional_fields = '<div class="uk-alert uk-alert-warning uk-border-rounded" uk-alert>У данного типа материалов нет дополнительных полей</div>';
        $_page_old_id = old('page_id');
        if (($entity->exists && $entity->_page) || $_page_old_id) {
            $_fields = $_page_old_id ? Page::find($_page_old_id)->render_fields($entity) : $entity->_page->render_fields($entity);
            if ($_fields) $_field_additional_fields = $_fields;
        }
        $_field_tags = render_field('tags', [
            'type' => 'select',
            'label' => 'Страницы тегов',
            'selected' => $entity->_tags->isNotEmpty() ? $entity->_tags->pluck('id')->toArray() : [],
            'values' => $_tags,
            'multiple' => TRUE,
            'attributes' => [
                'data-minimum-results-for-search' => 5,
                'data-tags' => 1
            ],
            'uikit' => TRUE,
            'help' => 'Странцы, на которых будут выводиться материалы сгруппированные по указанным тегам.'
        ]);
        $_field_node_tags = render_field('node_tags', [
            'type' => 'select',
            'label' => 'Теги',
            'selected' => $entity->_node_tags->isNotEmpty() ? $entity->_node_tags->pluck('id')->toArray() : [],
            'values' => $_node_tags,
            'multiple' => TRUE,
            'attributes' => [
                'data-minimum-results-for-search' => 5,
                'data-tags' => 1
            ],
            'uikit' => TRUE,
            'help' => 'Маркеры, по которым можно группировать материалы. Не имеют отдельной страницы.'
        ]);
        if ($this->types) {
            $_types = $this->types->keyBy('id')->map(function ($_type) {
                return $_type->title;
            })->prepend('-Выбрать-', '');
            $_field_type = render_field('page_id', [
                'type' => 'select',
                'label' => 'Тип (Связанная страница)',
                'value' => $entity->page_id,
                'values' => $_types,
                'attributes' => [
                    'data-path' => route('oleus.nodes.fields'),
                    'class' => 'use-ajax'
                ],
                'required' => TRUE,
                'help' => 'Определяет к какому типу будет относится материал',
                'uikit' => TRUE
            ]);
        }
        $_form->tabs = [
            [
                'title' => 'Основное',
                'content' => [
                    render_field('title', [
                        'label' => 'Заголовок',
                        'value' => $entity->title,
                        'required' => TRUE,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'uikit' => TRUE
                    ]),
                    '<div class="uk-grid"><div class="uk-width-1-3">',
                    render_field('preview_fid', [
                        'type' => 'file_drop',
                        'label' => 'Изображение в списке',
                        'allow' => 'jpg|jpeg|gif|png|svg',
                        'values' => $entity->exists && $entity->_preview ? [$entity->_preview] : NULL,
                        'uikit' => TRUE
                    ]),
                    '</div><div class="uk-width-2-3">',
                    render_field('sub_title', [
                        'label' => 'Под заголовок',
                        'value' => $entity->sub_title,
                        'uikit' => TRUE
                    ]),
                    render_field('breadcrumb_title', [
                        'label' => 'Заголовок в "Хлебных крошках"',
                        'value' => $entity->breadcrumb_title,
                        'uikit' => TRUE
                    ]),
                    $_field_type,
                    $_field_tags,
                    $_field_node_tags,
                    '</div></div>',
                    render_field('teaser', [
                        'label' => 'Тизер материала (краткое описание)',
                        'type' => 'textarea',
                        'editor' => TRUE,
                        'value' => $entity->teaser,
                        'attributes' => [
                            'rows' => 4,
                            'class' => 'editor-short',
                        ],
                        'uikit' => TRUE
                    ]),
                    render_field('body', [
                        'label' => 'Содержимое',
                        'type' => 'textarea',
                        'editor' => TRUE,
                        'value' => $entity->body,
                        'attributes' => [
                            'rows' => 8,
                        ],
                        //                        'required'   => TRUE,
                        'uikit' => TRUE
                    ]),
                    '<hr class="uk-divider-icon">',
                    '<div class="uk-grid uk-child-width-1-3"><div>',
                    render_field('published_at', [
                        'label' => 'Дата публикации',
                        'value' => $entity->exists && $entity->published_at ? $entity->published_at->format('d.m.Y') : Carbon::now()->format('d.m.Y'),
                        'attributes' => [
                            'data-position' => 'top left',
                            'class' => 'uk-datepicker',
                        ],
                        'uikit' => TRUE
                    ]),
                    '</div><div>',
                    render_field('user_id', [
                        'type' => 'select',
                        'label' => 'Автор',
                        'value' => $entity->user_id,
                        'values' => $this->authors->pluck('full_name', 'id'),
                        'uikit' => TRUE
                    ]),
                    '</div><div>',
                    render_field('sort', [
                        'type' => 'number',
                        'label' => 'Порядок сортировки',
                        'value' => $entity->exists ? $entity->sort : 0,
                        'uikit' => TRUE
                    ]),
                    '</div></div>',
                    render_field('visible_on_list', [
                        'type' => 'checkbox',
                        'value' => $entity->exists ? $entity->visible_on_list : 1,
                        'values' => [
                            1 => 'Выводить в списке материалов'
                        ],
                        'uikit' => TRUE
                    ]),
                    render_field('visible_on_block', [
                        'type' => 'checkbox',
                        'value' => $entity->exists ? $entity->visible_on_block : 1,
                        'values' => [
                            1 => 'Выводить в блок последних материалов'
                        ],
                        'uikit' => TRUE
                    ]),
                    render_field('status', [
                        'type' => 'checkbox',
                        'value' => $entity->exists ? $entity->status : 1,
                        'values' => [
                            1 => 'Опубликовано'
                        ],
                        'uikit' => TRUE
                    ])
                ],
            ],
            [
                'title' => 'Дополнительные поля',
                'content' => [
                    "<div id=\"additional_fields\">{$_field_additional_fields}</div>",
                ],
            ],
            $this->__form_tab_display_style($entity),
            $this->__form_tab_media_files($entity),
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
        $_field_additional_fields = '<div class="uk-alert uk-alert-warning uk-border-rounded" uk-alert>У данного типа материалов нет дополнительных полей</div>';
        $_fields = $entity->_page->render_fields($entity);
        if ($_fields) $_field_additional_fields = $_fields;
        $_form->tabs[] = [
            'title' => 'Параметры перевода',
            'content' => [
                render_field('locale', [
                    'type' => 'hidden',
                    'value' => $entity->frontLocale
                ]),
                render_field('translate', [
                    'type' => 'hidden',
                    'value' => 1
                ]),
                render_field('title', [
                    'label' => 'Заголовок',
                    'value' => $entity->getTranslation('title', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'autofocus' => TRUE,
                    ],
                    'required' => TRUE,
                    'uikit' => TRUE
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
                render_field('teaser', [
                    'label' => 'Тизер материала (краткое описание)',
                    'type' => 'textarea',
                    'editor' => TRUE,
                    'value' => $entity->getTranslation('teaser', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'rows' => 4,
                        'class' => 'editor-short',
                    ],
                    'uikit' => TRUE
                ]),
                render_field('body', [
                    'label' => 'Содержимое',
                    'type' => 'textarea',
                    'editor' => TRUE,
                    'value' => $entity->getTranslation('body', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'rows' => 8,
                    ],
                    'uikit' => TRUE,
                    'required' => TRUE,
                ]),
            ]
        ];
        $_form->tabs[] = [
            'title' => 'Дополнительные поля',
            'content' => [
                $_field_additional_fields
            ]
        ];
        $_form->tabs[] = $this->__form_tab_seo_for_translation($entity);

        return $_form;
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
        $_buttons = [];
        $_pages = Page::where('type', 'list_nodes')
            ->pluck('id');

        $_query = Node::from('nodes as n')
            ->leftJoin('url_alias as a', function ($join) {
                $join->on('a.model_id', '=', 'n.id')
                    ->where('a.locale', $this->defaultLocale)
                    ->where('a.model_type', Node::class);
            })
            ->when($_filter, function ($query) use ($_filter) {
                if ($_filter['page_id'] != 'all') $query->where('n.page_id', $_filter['page_id']);
                if ($_filter['title']) $query->where('a.model_title', 'like', "%{$_filter['title']}%");
                if ($_filter['alias']) $query->where('a.alias', 'like', "%{$_filter['alias']}%");
                if ($_filter['status'] != 'all') $query->where('n.status', $_filter['status']);
            })
            ->orderByDesc('n.status')
            ->orderByDesc('n.published_at')
            ->orderByDesc('n.updated_at')
            ->orderBy('n.title')
            ->with([
                '_page',
                '_tags'
            ])
            ->select([
                'n.*'
            ])
            ->paginate($this->entity->getPerPage(), ['n.id']);
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
                'data' => 'ID',
            ],
            [
                'data' => 'Заголовок',
            ],
            [
                'class' => 'uk-width-medium',
                'data' => 'Тип',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 120px',
                'data' => '<span uk-icon="icon: calendar">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data' => '<span uk-icon="icon: visibility">',
            ]
        ];
        if ($this->__can_permission('view')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data' => '<span uk-icon="icon: desktop">',
            ];
        }
        if ($this->__can_permission('update')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data' => '<span uk-icon="icon: edit">',
            ];
            array_unshift($_headers, [
                'class' => 'uk-text-center',
                'style' => 'width: 18px;',
                'data' => "<input type='checkbox' name='items_all' class='uk-checkbox uk-margin-remove'>",
            ]);
        }
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$item->id}</div>",
                    $item->_alias->id ? _l($item->title, $item->_alias->alias, ['attributes' => ['target' => '_blank']]) : $item->title,
                    $item->_page->title,
                    $item->published_at ? $item->published_at->format('d.m.Y') : $item->updated_at->format('d.m.Y'),
                    $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                ];
                if ($this->__can_permission('view')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.show", [
                        'p' => [
                            $item
                        ],
                        'attributes' => [
                            'class' => 'uk-button-icon uk-button uk-button-success uk-button-xsmall',
                            'uk-icon' => 'icon: desktop'
                        ]
                    ]);
                }
                if ($this->__can_permission('update')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.edit", [
                        'p' => [
                            $item
                        ],
                        'attributes' => [
                            'class' => 'uk-button-icon uk-button uk-button-primary uk-button-xsmall',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]);
                    array_unshift($_response, "<input type='checkbox' name='items[{$item->id}]' class='uk-checkbox uk-margin-remove'>");
                }

                return $_response;
            });
        }
        if ($_pages) {
            $this->types = $this->types->filter(function ($p) use ($_pages) {
                return in_array($p->id, $_pages->toArray());
            });
        }
        if ($this->types->isNotEmpty()) {
            $_filters = [
                [
                    'class' => 'uk-width-large',
                    'data' => render_field('title', [
                        'value' => $_filter['title'] ?? NULL,
                        'attributes' => [
                            'placeholder' => 'Заголовок',
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-medium'
                        ],
                        'uikit' => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-large',
                    'data' => render_field('alias', [
                        'value' => $_filter['alias'] ?? NULL,
                        'attributes' => [
                            'placeholder' => 'Путь страницы',
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-medium'
                        ],
                        'uikit' => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-medium',
                    'data' => render_field('page_id', [
                        'type' => 'select',
                        'value' => $_filter['page_id'] ?? 'all',
                        'values' => $this->types->pluck('title', 'id')->prepend('- Выбрать -', 'all'),
                        'attributes' => [
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-small'
                        ],
                        'uikit' => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-medium',
                    'data' => render_field('status', [
                        'type' => 'select',
                        'value' => $_filter['status'] ?? 'all',
                        'values' => [
                            'all' => 'Любой статус',
                            0 => 'Снять с публикации',
                            1 => 'Опубликован',
                        ],
                        'attributes' => [
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-small'
                        ],
                        'uikit' => TRUE
                    ])
                ]
            ];
        }
        $_items = $this->__items([
            'filters' => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'actions' => [
                'publish' => 'Опубликовать',
                'no_publish' => 'Снять с публикации',
                'delete' => 'Удалить',
            ],
            'buttons' => $_buttons,
            'headers' => $_headers,
            'items' => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_contents = [
            [
                'Изображение в списке',
                render_image($item->_preview, 'thumb_preview_view')
            ],
            [
                'Тип (Связанная страница)',
                $item->_page->title,
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
                'Тизер материала (краткое описание)',
                $item->teaser,
            ],
            [
                'Содержимое',
                $item->body,
            ],
            [
                'Теги материала',
                $item->_tags->isNotEmpty() ? $item->_tags->pluck('title')->implode(', ') : NULL,
            ],
            [
                'Автор',
                $item->author,
            ],
            [
                'Порядок сортировки',
                $item->sort,
            ],
            [
                'Дата публикации',
                $item->published_at,
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
            [
                'Выводить в списке материалов',
                $item->visible_on_list ? 'да' : 'нет',
            ],
            [
                'Выводить в блок последних материалов',
                $item->visible_on_block ? 'да' : 'нет',
            ],
            [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>SEO</span></h3>'
            ],
            [
                'URL',
                $item->_alias->alias,
            ],
            [
                'Title',
                $item->meta_title,
            ],
            [
                'Description',
                $item->meta_description,
            ],
            [
                'Keywords',
                $item->meta_keywords,
            ],
            [
                'Robots',
                $item->_alias->robots,
            ],
            [
                'Опубликовать в карте сайта',
                $item->_alias->sitemap ? 'да' : 'нет',
            ],
            [
                'Частота изменения',
                $item->_alias->changefreq,
            ],
            [
                'Приоритет',
                $item->_alias->priority,
            ],
            [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Медиа файлы</span></h3>'
            ]
        ];
        if ($_medias = $item->_files_related()->wherePivot('type', 'medias')->orderBy('sort')->get()) {
            $_output = '<div class="uk-preview uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s">';
            $_output .= $_medias->map(function ($file) {
                return render_preview_file($file, [
                    'field' => 'file',
                    'view' => 'view'
                ]);
            })->implode('');
            $_output .= '</div>';
            $_contents[] = [
                'Вложенные изображения',
                $_output,
            ];
        }
        if ($_files = $item->_files_related()->wherePivot('type', 'files')->orderBy('sort')->get()) {
            $_output = '<div class="uk-preview uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s">';
            $_output .= $_files->map(function ($file) {
                return render_preview_file($file, [
                    'field' => 'file',
                    'view' => 'view'
                ]);
            })->implode('');
            $_output .= '</div>';
            $_contents[] = [
                'Вложенные файлы',
                $_output,
            ];
        }
        $_fields = collect($item->_page->options->fields ?? []);
        if ($_fields->isNotEmpty()) {
            $_contents[] = [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Дополнительные поля</span></h3>',
            ];
            $_data = $item->_data_fields->keyBy('field');
            $_fields->map(function ($field) use (&$_contents, $_data, $item) {
                $_response = NULL;
                switch ($field->type) {
                    case 'file_drop':
                        $_node_field_data = $_data->get($field->name)->getTranslation('data', DEFAULT_LOCALE);
                        if ($field->multiple) {
                            $_files = $_node_field_data ? file_get($_node_field_data) : NULL;
                        } else {
                            $_files = $_node_field_data ? [file_get($_node_field_data)] : NULL;
                        }
                        if ($_files) {
                            $_response = '<div class="uk-preview uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s">';
                            foreach ($_files as $_file) {
                                $_response .= render_preview_file($_file, [
                                    'field' => 'file',
                                    'view' => 'view'
                                ]);
                            }
                            $_response .= '</div>';
                        }
                        break;
                    default:
                        if ($item->_page->id == 13 && $field->name == 'type') {
                            $_response = 1;
                        } else {
                            $_response = $_data->get($field->name)->getTranslation('data', DEFAULT_LOCALE);
                        }
                        break;
                }
                $_contents[] = [
                    $field->label,
                    $_response,
                ];
            });
        }
        $_view->contents = $_contents;

        return $_view;
    }

    public function create(Node $item)
    {
        if ($this->types->isEmpty()) {
            return redirect()
                ->route('oleus.pages')
                ->with('notices', [
                    [
                        'message' => 'Для начала добавьте страницу вывода материаллов в разделе \"Страниц\"',
                        'status' => 'warning'
                    ]
                ]);
        }
        $_dashboard = new Page();
        $_dashboard->fill([
            'title' => 'Панель управления',
            'generate_url' => _r('oleus')
        ]);
        $_page = new Page();
        $_page->fill([
            'title' => $this->titles['create'],
        ]);
        $_parent = new Page();
        $_parent->fill([
            'title' => $this->titles['index'],
            'generate_url' => _r("oleus.{$this->baseRoute}")
        ]);
        $_form = $this->_form($item);
        $_wrap = $this->render([
            'page.title' => $this->titles['index'],
            'page.callback_route' => $_parent->generate_url,
            'seo.title' => "{$_parent->title}. {$this->titles['create']}",
            'breadcrumbs' => render_breadcrumb([
                'parent' => [
                    $_dashboard,
                    $_parent
                ],
                'entity' => $_page,
            ]),
        ]);
        $_item = $item;

        return view($_form->theme, compact('_form', '_item', '_wrap'));
    }

    public function store(Request $request)
    {
        if ($preview_fid = $request->input('preview_fid')) {
            $_preview_fid = array_shift($preview_fid);
            Session::flash('preview_fid', json_encode([file_get($_preview_fid['id'])]));
        }
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
            //            'body'    => 'required',
            'page_id' => 'sometimes|required',
        ], [], [
            'title' => 'Заголовок ',
            'body' => 'Содержимое',
            'page_id' => 'Тип (Связанная страница)',
        ]);
        $_save = $request->only([
            'title',
            'sub_title',
            'breadcrumb_title',
            'page_id',
            'user_id',
            'teaser',
            'body',
            'style_id',
            'style_class',
            'published_at',
            'sort',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'status',
            'visible_on_block',
            'visible_on_list',
        ]);
        $_save['preview_fid'] = $_preview_fid['id'] ?? NULL;
        $_save['published_at'] = $_save['published_at'] ? Carbon::parse($_save['published_at']) : Carbon::now();
        $_item = Node::create($_save);
        $_item->setFields();
        Session::forget([
            'preview_fid',
            'medias',
            'files'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Node $item)
    {
        if ($request->has('translate')) {
            $this->validate($request, [
                'title' => 'required',
                //                'body'  => 'required',
            ], [], [
                'title' => 'Заголовок ',
                'body' => 'Содержимое',
            ]);
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $_save = $request->only([
                'title',
                'sub_title',
                'breadcrumb_title',
                'teaser',
                'body',
                'body_mobile',
                'meta_title',
                'meta_keywords',
                'meta_description',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
            $item->setFields($_locale);

        } else {
            if ($preview_fid = $request->input('preview_fid')) {
                $_preview_fid = array_shift($preview_fid);
                Session::flash('preview_fid', json_encode([file_get($_preview_fid['id'])]));
            }
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
//                'body'    => 'required',
                'page_id' => 'sometimes|required',
            ], [], [
                'title' => 'Заголовок ',
                'body' => 'Содержимое',
                'page_id' => 'Тип (Связанная страница)',
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'breadcrumb_title',
                'page_id',
                'user_id',
                'teaser',
                'body',
                'style_id',
                'style_class',
                'published_at',
                'sort',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'visible_on_block',
                'visible_on_list',
                'status',
            ]);
            $_save['preview_fid'] = $_preview_fid['id'] ?? NULL;
            $_save['published_at'] = $_save['published_at'] ? Carbon::parse($_save['published_at']) : Carbon::now();
            $item->update($_save);
            $item->setFields();
        }
        Session::forget([
            'preview_fid',
            'medias',
            'files'
        ]);

        return $this->__response_after_update($request, $item);
    }

    public function fields(Request $request)
    {
        $_commands = [];
        $_option = $request->input('option');
        if ($_option) {
            $_page = Page::find($_option);
            $_fields = $_page->render_fields();
            if ($_fields) {
                $_commands[] = [
                    'command' => 'html',
                    'options' => [
                        'data' => $_fields,
                        'target' => '#additional_fields',
                    ]
                ];
            }
        }
        if (!count($_commands)) {
            $_commands[] = [
                'command' => 'html',
                'options' => [
                    'data' => '<div class="uk-alert uk-alert-warning uk-border-rounded" uk-alert>У данного типа материалов нет дополнительных полей</div>',
                    'target' => '#additional_fields',
                ]
            ];
        }

        return response(['commands' => $_commands], 200);
    }

    public function relation(Request $request, Node $node, $field, $action, $key = NULL)
    {
        $commands = [];
        $_user = Auth::user();
        switch ($action) {
            case 'add':
                $_item = NULL;
                $_page_relation = collect($node->_page->options->fields);
                $_page_relation = $_page_relation->get($field);
                $_form = new Form([
                    'id' => 'page-relation-form',
                    'class' => 'uk-form',
                    'title' => 'Связанные материалы',
                    'action' => _r('oleus.nodes.relation', [
                        $node,
                        $field,
                        'save'
                    ]),
                    'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                    'suffix' => '</div>',
                ]);
                $_form->setAjax();
                $_form->setFields([
                    render_field('field.node', [
                        'label' => 'Материал',
                        'type' => 'autocomplete',
                        'selected' => [
                            'name' => NULL,
                            'value' => NULL,
                        ],
                        'uikit' => TRUE,
                        'required' => TRUE,
                        'form_id' => 'page-relation-form',
                        'attributes' => [
                            'data-path' => _r('oleus.nodes.relation_node'),
                            'data-value' => 'name',
                            'data-page' => $_page_relation->relation
                        ],
                        'help' => 'Начните вводить название материала и выберите из списка нужный.'
                    ]),
                ]);
                $_form->setButtonSubmitText('Сохранить');
                $_form->setButtonSubmitClass('uk-button uk-button-success');
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content' => $_form->_render()
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'easyAutocomplete',
                    'options' => []
                ];
                break;
            case 'save':
                $_node = $request->input('field.node.value');
                if ($node->id != $_node) {
                    NodeRelationField::updateOrCreate([
                        'id' => $node->id,
                        'node_id' => $_node,
                    ], [
                        'id' => $node->id,
                        'node_id' => $_node,
                        'field' => $field,
                    ]);
                    $commands['commands'][] = [
                        'command' => 'html',
                        'options' => [
                            'target' => '#' . Str::slug("list-insert-fields-items-{$field}"),
                            'data' => view('backend.fields.relation_items_table', [
                                'items' => $node->_relation_fields()
                                    ->where('field', $field)
                                    ->orderBy('sort')
                                    ->get()
                                    ->transform(function ($item) use ($_user, $node, $field) {
                                        return [
                                            $item->_node->id,
                                            $_user->can('nodes_update') ? _l($item->_node->title, 'oleus.nodes.update', [
                                                'p' => [$item->_node],
                                                'attributes' => ['_target' => 'blank']
                                            ]) : _l($item->_node->title, $item->_node->generate_url, ['attributes' => ['_target' => 'blank']]),
                                            '<input type="number" class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item" name="items_sort[' . $field . '][]" data-id="' . $item->node_id . '" value="' . $item->sort . '">',
                                            $item->_node->status ? '<span class="uk-text-success status" uk-icon="icon: done"></span>' : '<span class="uk-text-danger status" uk-icon="icon: close"></span>',
                                            _l('', 'oleus.nodes.relation', [
                                                'p' => [
                                                    $node,
                                                    $field,
                                                    'destroy',
                                                    $item->_node->id
                                                ],
                                                'attributes' => [
                                                    'class' => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                                                    'uk-icon' => 'icon: delete_forever'
                                                ]
                                            ])
                                        ];
                                    }),
                            ])
                                ->render(function ($view, $content) {
                                    return clear_html($content);
                                })
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'text' => 'Элемент сохранен.',
                            'status' => 'success',
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'UK_modalClose',
                        'options' => []
                    ];
                } else {
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'text' => 'Связь не может быть сохранена по причине, что материал не может ссылаться на себя же.',
                            'status' => 'warning',
                        ]
                    ];
                }
                break;
            case 'destroy':
                NodeRelationField::where('id', $node->id)
                    ->where('node_id', $key)
                    ->delete();
                $commands['commands'][] = [
                    'command' => 'html',
                    'options' => [
                        'target' => '#' . Str::slug("list-insert-fields-items-{$field}"),
                        'data' => view('backend.fields.relation_items_table', [
                            'items' => $node->_relation_fields()
                                ->where('field', $field)
                                ->orderBy('sort')
                                ->get()
                                ->transform(function ($item) use ($_user, $node, $field) {
                                    return [
                                        $item->_node->id,
                                        $_user->can('nodes_update') ? _l($item->_node->title, 'oleus.nodes.update', [
                                            'p' => [$item->_node],
                                            'attributes' => ['_target' => 'blank']
                                        ]) : _l($item->_node->title, $item->_node->generate_url, ['attributes' => ['_target' => 'blank']]),
                                        '<input type="number" class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item" name="items_sort[' . $field . '][]" data-id="' . $item->node_id . '" value="' . $item->sort . '">',
                                        $item->_node->status ? '<span class="uk-text-success status" uk-icon="icon: done"></span>' : '<span class="uk-text-danger status" uk-icon="icon: close"></span>',
                                        _l('', 'oleus.nodes.relation', [
                                            'p' => [
                                                $node,
                                                $field,
                                                'destroy',
                                                $item->_node->id
                                            ],
                                            'attributes' => [
                                                'class' => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                                                'uk-icon' => 'icon: delete_forever'
                                            ]
                                        ])
                                    ];
                                }),
                        ])
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            })
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text' => 'Элемент удален.',
                        'status' => 'success',
                    ]
                ];
                break;
            case 'sort':
                $_items = $request->except('captcha');
                foreach ($_items as $_node_id => $_sort) {
                    NodeRelationField::where('id', $node->id)
                        ->where('node_id', $_node_id)
                        ->update([
                            'sort' => $_sort
                        ]);
                }
                $commands['commands'][] = [
                    'command' => 'html',
                    'options' => [
                        'target' => '#' . Str::slug("list-insert-fields-items-{$field}"),
                        'data' => view('backend.fields.relation_items_table', [
                            'items' => $node->_relation_fields()
                                ->where('field', $field)
                                ->orderBy('sort')
                                ->get()
                                ->transform(function ($item) use ($_user, $node, $field) {
                                    return [
                                        $item->_node->id,
                                        $_user->can('nodes_update') ? _l($item->_node->title, 'oleus.nodes.update', [
                                            'p' => [$item->_node],
                                            'attributes' => ['_target' => 'blank']
                                        ]) : _l($item->_node->title, $item->_node->generate_url, ['attributes' => ['_target' => 'blank']]),
                                        '<input type="number" class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item" name="items_sort[' . $field . '][]" data-id="' . $item->node_id . '" value="' . $item->sort . '">',
                                        $item->_node->status ? '<span class="uk-text-success status" uk-icon="icon: done"></span>' : '<span class="uk-text-danger status" uk-icon="icon: close"></span>',
                                        _l('', 'oleus.nodes.relation', [
                                            'p' => [
                                                $node,
                                                $field,
                                                'destroy',
                                                $item->_node->id
                                            ],
                                            'attributes' => [
                                                'class' => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                                                'uk-icon' => 'icon: delete_forever'
                                            ]
                                        ])
                                    ];
                                }),
                        ])
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            })
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text' => 'Сортировка сохранена.',
                        'status' => 'success',
                    ]
                ];
                break;
        }

        return response($commands, 200);
    }

    public function relation_node(Request $request)
    {
        $_response = NULL;
        $_page = $request->input('page');
        $_search = $request->input('search');
        if ($_search && $_page) {
            $_str = substr(strstr($_search, '::'), 2, strlen($_search));
            if ($_str) $_search = $_str;
            $_nodes = Node::from('nodes as n')
                ->leftJoin('url_alias as a', function ($join) {
                    $join->on('a.model_id', '=', 'n.id')
                        ->where('a.locale', $this->defaultLocale)
                        ->where('a.model_type', Node::class);
                })
                ->where('page_id', $_page)
                ->where('a.model_title', 'like', "%{$_search}%")
                ->limit(30)
                ->get([
                    'n.id',
                    'n.title',
                ])->pluck('title', 'id');
            if ($_nodes->isNotEmpty()) {
                $_nodes->each(function ($title, $id) use (&$_response) {
                    $_response[] = [
                        'name' => "{$id}::{$title}",
                        'data' => $id
                    ];
                });
            }
        }

        return response($_response, 200);
    }
}
