<?php

namespace App\Models\Structure;

use App\Libraries\BaseModel;
use App\Models\Seo\SearchIndex;
use App\Models\Seo\SiteMap;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Page extends Model
{
    use BaseModel;

    protected $table = 'pages';
    protected $fillable = [
        'id',
        'type',
        'title',
        'sub_title',
        'breadcrumb_title',
        'body',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'style_class',
        'style_id',
        'status',
        'options',
        'no_used',
        'generate_url'
    ];
    public $types;
    protected $perPage = 100;
    protected $attributes = [
        'id' => NULL,
        'type' => NULL,
        'title' => NULL,
        'sub_title' => NULL,
        'breadcrumb_title' => NULL,
        'body' => NULL,
        'meta_title' => NULL,
        'meta_description' => NULL,
        'meta_keywords' => NULL,
        'style_class' => NULL,
        'style_id' => NULL,
        'status' => 1,
        'no_used' => 0,
        'options' => NULL
    ];
    public $translatable = [
        'title',
        'sub_title',
        'breadcrumb_title',
        'body',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];
    const TYPES_USING_DEFAULT_TAGS = [
        'list_nodes',
        'galleries',
        'reviews',
    ];
    const PER_PAGE_OPTIONS = [
        'all' => 'Все материалы',
        4 => '4 материала',
        8 => '8 материалов',
        9 => '9 материалов',
        12 => '12 материалов',
        24 => '24 материала',
        36 => '36 материалов',
        48 => '48 материалов',
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->types = [
            'normal' => 'Обычная страница',
            'list_nodes' => 'Список материалов',
            'front' => 'Главная страница',
            'sitemap' => 'Карта сайта',
            'search' => 'Поиск',
            'reviews' => 'Отзывы',
            'galleries' => 'Галерея',
            'contacts' => 'Контакты',
            'faq' => 'Вопрос/Ответ',
        ];
    }

    /**
     * Attributes
     */
    public function setOptionsAttribute($value = NULL)
    {
        $_page_options = collect($this->options);
        foreach ($value as $_key => $_data) {
            $_page_options->put($_key, $_data);
        }
        $this->attributes['options'] = json_encode(json_decode($_page_options->toJson()));
    }

    public function getOptionsAttribute()
    {
        $_options = $this->attributes['options'] ?? NULL;
        if ($_options && is_json($_options)) {
            return json_decode($_options);
        }

        return new \stdClass();
    }

    public function getViewTypeAttribute()
    {
        return $this->types[$this->type] ?? NULL;
    }

    public function getFieldsAttribute()
    {
        $_response = collect([]);
        $_fields = collect($this->options->fields ?? []);
        if ($_fields->isNotEmpty()) {
            $_page = $this;
            $_response->put('headers', [
                [
                    'class' => 'uk-width-medium',
                    'data' => 'Имя',
                ],
                [
                    'data' => 'Название поля',
                ],
                [
                    'class' => 'uk-width-medium',
                    'data' => 'Тип поля',
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data' => '<span uk-icon="icon: delete_forever">'
                ]
            ]);
            $_response->put('items', $_fields->transform(function ($field) use ($_page) {
                $_type = 'Текстовое поле';
                switch ($field->type) {
                    case 'relation':
                        $_type = 'Связанный материал';
                        break;
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

                return (object)[
                    $field->name,
                    $field->label,
                    $_type,
                    _l('', 'oleus.pages.item', [
                        'p' => [
                            $_page,
                            'destroy',
                            $field->name
                        ],
                        'attributes' => [
                            'class' => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                            'uk-icon' => 'icon: delete_forever'
                        ]
                    ])
                ];
            }));
        }

        return $_response;
    }

    public function getSchemaAttribute()
    {
        global $wrap;
        $_response = [
            "@context" => "https://schema.org",
            "@type" => "WebPage",
            "name" => $this->getTranslation('title', $wrap['locale']),
            "description" => "",
            "publisher" => [
                "@type" => "Organization",
                "name" => $wrap['page']['site_name'],
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => "{$wrap['seo']['base_url']}/template/logotypes/logotype.png",
                    "width" => NULL,
                    "height" => NULL
                ]
            ],
        ];

        return json_encode($_response);
    }

    /**
     * Relationships
     */
    public function _nodes()
    {
        return $this->hasMany(Node::class, 'page_id')
            ->with([
                '_alias',
                '_page',
                '_user',
                '_preview',
                '_data_fields',
                '_tags',
                '_node_tags',
            ]);
    }

    /**
     * Others
     */
    public function render_fields(Node $node = NULL)
    {
        $_response = NULL;
        $_fields = collect($this->options->fields ?? []);
        if ($_fields->isNotEmpty()) {
            $_data = $node ? $node->_data_fields->keyBy('field') : collect([]);
            $_locale = $node->frontLocale ?? DEFAULT_LOCALE;
            $_fields->map(function ($field) use (&$_response, $_data, $node, $_locale) {
                if ($field->type == 'text') {
                    $_node_field_data = $_data->has($field->name) ? $_data->get($field->name)->getTranslation('data', $_locale, FALSE) : NULL;
                    $_response .= "<h4 class=\"uk-heading-line uk-text-uppercase\"><span>{$field->label}</span></h4>";
                    $_response .= render_field("data_fields.{$field->name}", [
                        'value' => old("data_fields.{$field->name}", $_node_field_data),
                        'uikit' => TRUE
                    ]);
                } elseif ($field->type == 'relation') {
                    $_response .= "<h4 class=\"uk-heading-line uk-text-uppercase\"><span>{$field->label}</span></h4>";
                    if ($node) {
                        $_user = Auth::user();
                        $_response .= view('backend.fields.relation_items', [
                            'items_id' => Str::slug("list-insert-fields-items-{$field->name}"),
                            'entity' => $node,
                            'field' => $field->name,
                            'items' => $node->_relation_fields()
                                ->where('field', $field->name)
                                ->get()
                                ->transform(function ($item) use ($_user, $node, $field) {
                                    return [
                                        $item->_node->id,
                                        $_user->can('nodes_update') ? _l($item->_node->title, 'oleus.nodes.update', [
                                            'p' => [$item->_node],
                                            'attributes' => ['_target' => 'blank']
                                        ]) : _l($item->_node->title, $item->_node->generate_url, ['attributes' => ['_target' => 'blank']]),
                                        '<input type="number" class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item" name="items_sort[' . $field->name . '][]" data-id="' . $item->node_id . '" value="' . $item->sort . '">',
                                        $item->_node->status ? '<span class="uk-text-success status" uk-icon="icon: done"></span>' : '<span class="uk-text-danger status" uk-icon="icon: close"></span>',
                                        _l('', 'oleus.nodes.relation', [
                                            'p' => [
                                                $node,
                                                $field->name,
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
                            });
                    } else {
                        $_response .= '<div class="uk-alert uk-alert-warning uk-border-rounded" uk-alert>Связать материал можно только после добавления.</div>';
                    }
                } elseif ($field->type == 'textarea') {
                    $_node_field_data = $_data->has($field->name) ? $_data->get($field->name)->getTranslation('data', $_locale, FALSE) : NULL;
                    $_response .= "<h4 class=\"uk-heading-line uk-text-uppercase\"><span>{$field->label}</span></h4>";
                    $_response .= render_field("data_fields.{$field->name}", [
                        'type' => 'textarea',
                        'value' => old("data_fields.{$field->name}", $_node_field_data),
                        'attributes' => [
                            'rows' => 5
                        ],
                        'uikit' => TRUE
                    ]);
                } elseif ($field->type == 'file_drop') {
                    $_node_field_data = $_data->has($field->name) ? $_data->get($field->name)->getTranslation('data', $_locale) : NULL;
                    $_file = old("data_fields.{$field->name}");
                    $_response .= "<h4 class=\"uk-heading-line uk-text-uppercase\"><span>{$field->label}</span></h4>";
                    if ($field->multiple) {
                        if (is_array($_file)) {
                            $files = file_get(array_keys($_file));
                            Session::flash("data_fields.{$field->name}", json_encode($files->toArray()));
                        }
                        $_response .= render_field("data_fields.{$field->name}", [
                            'type' => 'file_drop',
                            'allow' => $field->allow,
                            'values' => $_node_field_data ? file_get($_node_field_data) : NULL,
                            'view' => 'gallery',
                            'multiple' => TRUE,
                            'uikit' => TRUE
                        ]);
                    } else {
                        if (is_array($_file)) {
                            $file = array_shift($_file);
                            Session::flash("data_fields.{$field->name}", json_encode([file_get($file['id'])]));
                        }
                        $_response .= render_field("data_fields.{$field->name}", [
                            'type' => 'file_drop',
                            'allow' => $field->allow,
                            'values' => $_node_field_data ? [file_get($_node_field_data)] : NULL,
                            'uikit' => TRUE
                        ]);
                    }
                }
            });
        }

        return $_response;
    }

    public function _load(&$options = [])
    {
        $wrap = app('wrap');
        $options = array_merge([
            'view' => NULL,
            'view_mode' => 'full',
            'index' => NULL,
        ], $options);
        if (isset($options['index']) && $options['index']) $this->renderIndex = $options['index'];
        if ($this->renderIndex && $this->style_id) {
            $this->style_id .= "-{$this->renderIndex}";
        }
        $this->styleAttributes = [
            'id' => $this->style_id ?: FALSE,
            'class' => $this->style_class ?: FALSE,
            'style' => $this->background_fid ? 'background-image: url(' . render_image($this->_background, NULL, ['only_way' => TRUE]) . ');' : FALSE
        ];
        switch ($options['view_mode']) {
            default:
                $this->body = content_render($this, 'body');
                $this->relatedMedias = $this->_files_related()
                    ->wherePivot('type', 'medias')
                    ->remember(REMEMBER_LIFETIME * 24 * 7)
                    ->get();
                $this->relatedFiles = $this->_files_related()
                    ->wherePivot('type', 'files')
                    ->remember(REMEMBER_LIFETIME * 24 * 7)
                    ->get();
                break;
        }

        return $this;
    }

    public function _render($options = [])
    {
        global $wrap;
        $this->_load($options);
        $_set_wrap = [
            'eloquent' => $this,
            'seo.title' => $this->getTranslation('meta_title', $wrap['locale'], FALSE) ?: $this->getTranslation('title', $wrap['locale']),
            'seo.keywords' => $this->getTranslation('meta_keywords', $wrap['locale'], FALSE),
            'seo.description' => $this->getTranslation('meta_description', $wrap['locale'], FALSE),
            'seo.robots' => $this->_alias->robots ?? 'index, follow',
            'seo.last_modified' => $this->last_modified,
            'seo.open_graph.title' => $this->title,
            'seo.open_graph.url' => $this->generate_url,
            'page.title' => $this->title,
            'page.style_id' => $this->style_id,
            'page.style_class' => $this->style_class ?: NULL,
            'page.breadcrumb' => render_breadcrumb([
                'entity' => $this
            ]),
            'page.translate_links' => $this->translate_links,
            'seo.href_lang' => $this->href_lang,
        ];
        $this->_items = collect([]);
        $_page_number = $wrap['seo']['page_number'];
        switch ($this->type) {
            case 'list_nodes':
                $_per_page = $this->options->per_page ?? 'all';
                $_items = $this->_nodes()
                    ->select([
                        'id',
                        'title',
                        'page_id',
                        'preview_fid',
                        'sort',
                        'published_at',
                        'teaser',
                        'body',
                        'user_id'
                    ])
                    ->active('nodes')
                    ->visibleOnList('nodes')
                    ->orderBy('sort')
                    ->orderByDesc('published_at')
                    ->orderByDesc('created_at')
                    ->remember(REMEMBER_LIFETIME);
                if ($_per_page != 'all') {
                    if ($_page_number) {
                        Paginator::currentPageResolver(function () use ($_page_number) {
                            return $_page_number;
                        });
                    }
                    $this->_items = $_items->paginate($_per_page);
                } else {
                    $this->_items = $_items->get();
                }
                break;
            case 'sitemap':
                $this->_items = SiteMap::treeRender();
                break;
            case 'search':
                $_per_page = $this->options->per_page ?? 12;
                $_query_string = trim(request()->get('search-string'));
                $_search_model = new SearchIndex();
                $this->_items = $_search_model->query_search($_query_string, TRUE, $_per_page);
                break;
            case 'front':
                $_set_wrap['page.is_front'] = TRUE;
                break;
        }
        if ($this->type == 'list_nodes' || $this->type == 'search') {
            if ($this->_items->isNotEmpty()) {
                $this->_items->transform(function (&$_entity) {
                    $_options = ['view_mode' => 'teaser'];
                    if (method_exists($_entity, '_load')) $_entity->_load($_options);

                    return $_entity;
                });
                if ($this->id == 8) {
                    $_tags = collect([]);
                    $this->_items->map(function ($entity) use (&$_tags) {
                        if ($entity->node_tags->isNotEmpty()) {
                            $entity->node_tags->map(function ($t) use (&$_tags) {
                                $_tags->put($t['id'], $t);
                            });
                        }
                    });
                    $this->_tags = $_tags->sortBy('sort');
                }
            }
            if ($_page_number && $this->_items->isEmpty()) abort(404);
            if ($_page_number) $_set_wrap['seo.robots'] = 'noindex, follow';
            if ($this->_items->isNotEmpty() && method_exists($this->_items, 'hasMorePages') && $this->_items->hasMorePages()) {
                $_page_number = $_page_number ?: 1;
                $_page_number++;
                $_current_url = $wrap['seo']['url_alias'];
                $_current_url_query = $wrap['seo']['url_query'];
                $_url = trim($_current_url, '/') . "/page-{$_page_number}";
                $_next_page_link = _u($_url) . $_current_url_query;
                $_set_wrap['seo.link_next'] = $_next_page_link;
            }
        }
        $this->setWrap($_set_wrap);
        $_template = [
            "frontend.{$this->device_template}.pages.page_{$this->id}",
            "frontend.{$this->device_template}.pages.{$this->type}",
            "frontend.{$this->device_template}.pages.page",
            "frontend.default.pages.page_{$this->id}",
            "frontend.default.pages.{$this->type}",
            "frontend.default.pages.page",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.pages.{$options['view']}");
            array_unshift($_template, "frontend.{$this->device_template}.pages.{$options['view']}");
        }
        $this->template = $_template;

        return $this;
    }

    public function _render_ajax(Request $request)
    {
        global $wrap;
        $this->_load();
        $_page_number = $request->has('load_more') ? $wrap['seo']['page_number'] : NULL;
        if ($_page_number) {
            Paginator::currentPageResolver(function () use ($_page_number) {
                return $_page_number;
            });
        }
        $this->_items = collect([]);
        $commands = NULL;
        $_item_template = [];
        switch ($this->type) {
            case 'list_nodes':
                $_per_page = $this->options->per_page ?? 'all';
                $_items = $this->_nodes()
                    ->select([
                        'id',
                        'title',
                        'page_id',
                        'preview_fid',
                        'sort',
                        'published_at',
                        'teaser',
                        'body',
                        'user_id'
                    ])
                    ->with([
                        '_alias',
                        '_page',
                        '_user',
                        '_preview',
                        '_tags',
                        '_data_fields'
                    ])
                    ->active('nodes')
                    ->visibleOnList('nodes')
                    ->orderBy('sort')
                    ->orderByDesc('published_at')
                    ->orderByDesc('created_at')
                    ->remember(REMEMBER_LIFETIME);
                if ($_per_page != 'all') {
                    $this->_items = $_items->paginate($_per_page);
                } else {
                    $this->_items = $_items->get();
                }
                $_item_template = [
                    "frontend.{$this->deviceTemplate}.nodes.teaser_{$this->id}",
                    "frontend.{$this->deviceTemplate}.nodes.teaser",
                    "frontend.default.nodes.teaser_{$this->id}",
                    "frontend.default.nodes.teaser",
                ];
                break;
            case 'faq':
                $_item_template = [
                    "frontend.{$this->deviceTemplate}.nodes.teaser_5",
                    "frontend.default.nodes.teaser_5",
                ];
                $_items = $this->_faqs();
                break;
            case 'search':
                $_per_page = $this->options->per_page ?? 12;
                $_query_string = trim(request()->get('search-string'));
                $_search_model = new SearchIndex();
                $this->_items = $_search_model->query_search($_query_string, TRUE, $_per_page);
                if ($_query_string) {
                    wrap()->set('seo.url_query', "?search-string={$_query_string}");
                    $wrap['seo']['url_query'] = "?search-string={$_query_string}";
                }

                break;
        }
        if ($this->options && $this->_items->isNotEmpty() && $this->_items->hasMorePages()) {
            $_page_number = $_page_number ?: 1;
            $_page_number++;
            $_current_url = $wrap['seo']['url_alias'];
            $_current_url_query = $wrap['seo']['url_query'];
            $_url = trim($_current_url, '/') . "/page-{$_page_number}";
            $_next_page_link = _u($_url) . $_current_url_query;
            wrap()->set('seo.link_next', $_next_page_link);
        }
        if ($this->_items->isNotEmpty()) {
            if ($this->type == 'list_nodes' || $this->type == 'search') {
                $_items_output = NULL;
                $this->_items->getCollection()->transform(function ($_entity) use (&$_items_output, $_item_template) {
                    $_options = ['view_mode' => 'teaser'];
                    if (method_exists($_entity, '_load')) $_entity->_load($_options);
                    if ($this->type == 'search') {
                        $_items_output .= $_entity->output;
                    } else {
                        $_items_output .= View::first($_item_template, ['_item' => $_entity])
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            });
                    }

                    return $_entity;
                });
                $commands['commands'][] = [
                    'command' => 'html',
                    'options' => [
                        'target' => '#page-items-list-pagination',
                        'data' => clear_html($this->_items->links("frontend.{$this->deviceTemplate}.partials.pagination"))
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'append',
                    'options' => [
                        'target' => '#page-items-list > div',
                        'data' => $_items_output
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'replaceWith',
                    'options' => [
                        'target' => '#page-breadcrumbs',
                        'data' => View::first([
                            "frontend.{$this->deviceTemplate}.partials.breadcrumbs",
                            'frontend.default.partials.breadcrumbs'
                        ], ['breadcrumb' => render_breadcrumb(['entity' => $this])])
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            })
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'changeUrl',
                    'options' => [
                        'url' => _u($request->fullUrl())
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'changeTitle',
                    'options' => [
                        'title' => $this->getTranslation('meta_title', $wrap['locale'], FALSE) ?: $this->getTranslation('title', $wrap['locale']) . " {$wrap['seo']['title_suffix']}{$wrap['seo']['page_number_suffix']}"
                    ]
                ];
            } else {
                $this->_items->transform(function (&$_entity) {
                    $_options = ['view_mode' => 'teaser'];
                    if (method_exists($_entity, '_load')) $_entity->_load($_options);

                    return $_entity;
                });
            }
        }
        $commands['commands'][] = [
            'command' => 'html',
            'options' => [
                'target' => '#page-body',
                'data' => ''
            ]
        ];

        return $commands;
    }
}
