<?php

namespace App\Models\Structure;

use App\Libraries\BaseModel;
use App\Models\Seo\TmpMetaTags;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class Node extends Model
{
    use BaseModel;

    protected $table = 'nodes';
    protected $fillable = [
        'id',
        'user_id',
        'page_id',
        'title',
        'sub_title',
        'breadcrumb_title',
        'teaser',
        'body',
        'preview_fid',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'style_class',
        'style_id',
        'status',
        'sort',
        'visible_on_list',
        'visible_on_block',
        'published_at'
    ];
    protected $casts = [
        'published_at' => 'datetime'
    ];
    protected $attributes = [
        'id' => NULL,
        'user_id' => NULL,
        'page_id' => NULL,
        'title' => NULL,
        'sub_title' => NULL,
        'breadcrumb_title' => NULL,
        'teaser' => NULL,
        'body' => NULL,
        'preview_fid' => NULL,
        'meta_title' => NULL,
        'meta_description' => NULL,
        'meta_keywords' => NULL,
        'style_class' => NULL,
        'style_id' => NULL,
        'status' => 1,
        'sort' => 0,
        'visible_on_list' => 0,
        'visible_on_block' => 0,
        'published_at' => NULL
    ];
    public $translatable = [
        'title',
        'sub_title',
        'breadcrumb_title',
        'teaser',
        'body',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _page()
    {
        return $this->belongsTo(Page::class, 'page_id')
            ->with([
                '_tmp_meta_tags'
            ]);
    }

    public function _data_fields()
    {
        return $this->hasMany(NodeDataField::class, 'node_id');
    }

    public function _relation_fields()
    {
        return $this->hasMany(NodeRelationField::class, 'id')
            ->with([
                '_node'
            ]);
    }

    /**
     * Scope
     */
    public function scopeNodeTypes()
    {
        return Page::where('type', 'list_nodes')
            ->orderBy('title')
            ->get();
    }

    public function scopeTags()
    {
        return Tag::all();
    }

    public function scopeNodeTags()
    {
        return NodeTag::all();
    }

    public function scopeAuthors()
    {
        return User::permission([
            'nodes_create',
            'nodes_update'
        ])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'surname',
                'patronymic',
                'email'
            ]);
    }

    /**
     * Attribute
     */
    public function getSchemaAttribute()
    {
        global $wrap;
        $_response = [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => "{$wrap['seo']['base_url']}{$this->generate_url}"
            ],
            "headline" => $this->getTranslation('title', $wrap['locale']),
            "image" => $this->preview_fid ? $wrap['seo']['base_url'] . render_image($this->_preview, 'thumb_preview_view', [
                    'only_way' => TRUE,
                    'no_last_modify' => TRUE
                ]) : NULL,
            "datePublished" => $this->published_at->format('Y-m-d') ?: $this->created_at->format('Y-m-d'),
            "dateModified" => $this->updated_at->format('Y-m-d'),
            "author" => [
                "@type" => "Organization",
                "name" => $wrap['page']['site_name']
            ],
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

    public function getAuthorAttribute()
    {
        return $this->_user->full_name;
    }

    /**
     * Others
     */
    public function _load(&$options = [])
    {
        $options = array_merge([
            'view' => NULL,
            'view_mode' => 'full',
            'index' => NULL,
        ], $options);
        if (isset($options['index']) && $options['index']) $this->renderIndex = $options['index'];
        if ($this->renderIndex && $this->style_id) {
            $this->style_id .= "-{$this->renderIndex}";
            $this->style_class .= $this->style_id ? " {$this->style_id}" : $this->style_id;
        }
        $this->styleAttributes = [
            'id' => $this->style_id ?: FALSE,
            'class' => $this->style_class ?: FALSE,
            'style' => $this->background_fid ? 'background-image: url(' . render_image($this->_background, NULL, ['only_way' => TRUE]) . ');' : FALSE
        ];
        $this->additionalFields = $this->_data_fields->pluck('value', 'field');
        switch ($options['view_mode']) {
            case 'teaser':
                $this->teaser = teaser_render($this, 320);
                $this->node_tags = $this->_node_tags->transform(function ($t) {
                    return $t->status ? [
                        'id' => $t->id,
                        'title' => $t->title,
                        'sort' => $t->sort,
                    ] : NULL;
                })->filter(function ($t) {
                    return $t;
                });
                break;
            default:
                $this->body = content_render($this);
                $_files_related = $this->_files_related()
                    ->remember(REMEMBER_LIFETIME * 24 * 7)
                    ->get();
                $this->relatedMedias = $_files_related->filter(function ($f) {
                    return $f->pivot->type == 'medias';
                });
                $this->relatedFiles = $_files_related->filter(function ($f) {
                    return $f->pivot->type == 'files';
                });
                $this->additionalFields = $this->getAdditionalFields();
                break;
        }
    }

    public function _render($options = [])
    {
        global $wrap;
        $this->_load($options);
        $_page = $this->_page;
        $_page_entity_tmp_meta = [
            'title' => shortcut($_page->_tmp_meta_tags->meta_title, $this),
            'description' => shortcut($_page->_tmp_meta_tags->meta_description, $this),
            'keywords' => shortcut($_page->_tmp_meta_tags->meta_keywords, $this),
        ];
        $_set_wrap = [
            'seo.title' => $this->getTranslation('meta_title', $wrap['locale'], FALSE) ? $this->meta_title : ($_page_entity_tmp_meta['title'] ?: $this->title),
            'seo.keywords' => $this->getTranslation('meta_keywords', $wrap['locale'], FALSE) ? $this->meta_keywords : $_page_entity_tmp_meta['keywords'],
            'seo.description' => $this->getTranslation('meta_description', $wrap['locale'], FALSE) ? $this->meta_description : $_page_entity_tmp_meta['description'],
            'seo.robots' => $this->_alias->robots ?? 'index, follow',
            'seo.last_modified' => $this->last_modified,
            'page.title' => $this->title,
            'page.style_id' => $this->style_id,
            'page.style_class' => $this->style_class,
            'page.breadcrumb' => render_breadcrumb([
                'entity' => $this,
                'parent' => [$_page]
            ]),
            'page.translate_links' => $this->translate_links,
            'seo.href_lang' => $this->href_lang,
        ];
        $this->setWrap($_set_wrap);
        $_template = [
            "frontend.{$this->deviceTemplate}.nodes.node_page_{$_page->id}",
            "frontend.{$this->deviceTemplate}.nodes.node_{$this->id}",
            "frontend.{$this->deviceTemplate}.nodes.node",
            "frontend.default.nodes.node_page_{$_page->id}",
            "frontend.default.nodes.node_{$this->id}",
            "frontend.default.nodes.node",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.nodes.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.nodes.{$options['view']}");
        }
        $this->template = $_template;

        return $this;
    }

    public function setFields($locale = DEFAULT_LOCALE)
    {
        $_data_fields = request()->input('data_fields');
        if ($_data_fields) {
            $_node_page_fields = collect($this->_page->options->fields ?? []);
            foreach ($_node_page_fields as $_field) {
                $_exists = NodeDataField::where('field', $_field->name)
                    ->where('node_id', $this->id)
                    ->first();
                if (!$_exists) {
                    $_exists = new NodeDataField([
                        'field' => $_field->name,
                        'node_id' => $this->id,
                    ]);
                }
                $_save_value = $_data_fields[$_field->name] ?? NULL;
                if ($_field->type == 'file_drop' && $_field->multiple) {
                    if (is_array($_save_value)) {
                        $_exists->setTranslation('data', $locale, array_keys($_save_value));
                    }
                } elseif ($_field->type == 'file_drop') {
                    if (is_array($_save_value)) {
                        $_exists->setTranslation('data', $locale, array_key_first($_save_value));
                    }
                } else {
                    $_exists->setTranslation('data', $locale, $_save_value);
                }
                $_exists->save();
            }
        }
    }

    public function getAdditionalFields()
    {
        $_response = [];
        $_fields_data = $this->_data_fields
            ->keyBy('field');
        $_fields_relation = $this->_relation_fields
            ->groupBy('field');
        $_page_fields = $this->_page->options->fields ?? NULL;
        if ($_page_fields) {
            foreach ($_page_fields as $_field => $_data) {
                switch ($_data->type) {
                    case 'file_drop':
                        $_field_data = $_fields_data->get($_field);
                        if ($_field_data->data) {
                            $_response[$_field] = [
                                'type' => $_data->multiple ? 'file_collection' : 'file',
                                'data' => file_get($_field_data->data),
                            ];
                        }
                        break;
                    case 'table':
                        $_field_data = $_fields_data->get($_field);
                        if ($_field_data->data && is_json($_field_data->data)) {
                            $_response[$_field] = [
                                'type' => 'table',
                                'data' => json_decode($_field_data->data)
                            ];
                        }
                        break;
                    case 'relation':
                        $_field_data = $_fields_relation->get($_field);
                        if ($_field_data->isNotEmpty()) {
                            $_field_data->transform(function ($relation) {
                                return $relation->_node;
                            });
                            $_response[$_field] =
                                [
                                    'type' => 'related_collection',
                                    'data' => $_field_data
                                ];
                        }
                        break;
                    default:
                        $_field_data = $_fields_data->get($_field);
                        if ($_field_data->data) {
                            $_response[$_field] = [
                                'type' => 'data',
                                'data' => $_field_data->data
                            ];
                        }
                        break;
                }
            }
        }

        return $_response;
    }

    public static function getFrontProjectBlock()
    {
        $_projects_page = Page::where('id', 8)
            ->select([
                'id',
                'title'
            ])
            ->first();
        $_nodes = $_projects_page->_nodes()
            ->visibleOnBlock()
            ->active()
            ->orderBy('sort')
            ->select([
                'id',
                'title',
                'sub_title',
                'teaser',
                'preview_fid',
                'sort',
            ])
            ->remember(REMEMBER_LIFETIME)
            ->get();
        $_response = [
            'tags' => collect([]),
            'nodes' => collect([]),
        ];
        if ($_nodes->isNotEmpty()) {
            $_response['nodes'] = $_nodes->map(function ($entity) use (&$_response) {
                if (method_exists($entity, '_load')) {
                    $_options = ['view_mode' => 'teaser'];
                    $entity->_load($_options);
                    if ($entity->node_tags->isNotEmpty()) {
                        $entity->node_tags->map(function ($t) use (&$_response) {
                            $_response['tags']->put($t['id'], $t);
                        });
                    }
                }

                return $entity;
            });
            if ($_response['tags']->isNotEmpty()) {
                $_response['tags'] = $_response['tags']->sortBy('sort');
            }
        }

        return $_response;
    }

    public static function getDeliveries()
    {
        $_deliveries = self::where('page_id', 13)
            ->where('status', 1)
            ->with([
                '_data_fields',
                '_relation_fields',
                '_preview'
            ])
            ->get()
            ->transform(function ($d) {
                $_data_fields = $d->_data_fields->keyBy('field');
                $_relation_fields = $d->_relation_fields;
                $_country = $_data_fields->has('country') ? $_data_fields->get('country')->getTranslation('data', app()->getLocale()) : null;
                $_name = $_data_fields->has('name') ? $_data_fields->get('name')->getTranslation('data', app()->getLocale()) : null;
                $_type = [];
                if ($_relation_fields->isNotEmpty()) {
                    $_type = $_relation_fields->map(function ($r) {
                        return $r->node_id;
                    })
                        ->toArray();
                }
                return [
                    'id' => $d->id,
                    'country' => $_country,
                    'first_letter' => $_country ? mb_strtoupper(mb_substr($_country, 0, 1)) : null,
                    'name' => $_name,
                    'type' => $_type,
                    'search' => "{$_country} {$_name}",
                    "image" => render_image($d->_preview, 'thumb_preview_view', [
                        'only_way' => TRUE,
                        'no_last_modify' => TRUE
                    ])
                ];
            })
            ->filter(function ($d) {
                return $d['country'];
            });
        $_deliveries_datum = [
            'types' => [
                'type_1' => [],
                'type_2' => [],
                'type_3' => [],
            ],
            'all' => $_deliveries->toArray()
        ];
        $_mark_type = [
            12 => 1,
            10 => 2,
            11 => 3,
        ];
        $_deliveries->map(function ($d) use (&$_deliveries_datum, $_mark_type) {
            $_types = $d['type'] ?: [];
            if ($_types) {
                foreach ($_types as $type) {
                    $_deliveries_datum['types']["type_{$_mark_type[$type]}"][] = $d;
                }
            }
        });
        global $wrap;
        $_locale = $wrap['locale'] ?? DEFAULT_LOCALE;
        $_locale = config("laravellocalization.supportedLocales.{$_locale}");
        return [
            'deliveries' => $_deliveries_datum,
            'alphabet' => $_locale['alphabet']
        ];
    }
}
