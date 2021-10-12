<?php

namespace App\Models\Structure;

use App\Libraries\BaseModel;
use App\Models\Seo\SearchIndex;
use App\Models\Seo\UrlAlias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class Tag extends Model
{
    use BaseModel;

    protected $table = 'tags';
    protected $fillable = [
        'title',
        'sub_title',
        'breadcrumb_title',
        'body',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'parent_id',
        'style_id',
        'style_class',
        'status',
        'sort',
    ];
    protected $attributes = [
        'id'               => NULL,
        'title'            => NULL,
        'parent_id'        => NULL,
        'sub_title'        => NULL,
        'breadcrumb_title' => NULL,
        'body'             => NULL,
        'meta_title'       => NULL,
        'meta_description' => NULL,
        'meta_keywords'    => NULL,
        'style_class'      => NULL,
        'style_id'         => NULL,
        'status'           => 1,
        'sort'             => 0,
    ];
    public $entity;
    public $translatable = [
        'title',
        'sub_title',
        'breadcrumb_title',
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
    public function _nodes()
    {
        return $this->morphedByMany(Node::class, 'model', 'taggables')
            ->visibleOnList('nodes')
            ->with([
                '_alias',
                '_page',
                '_user',
                '_preview'
            ]);
    }

    public function _parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function _children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with([
                '_parent',
                '_children'
            ]);
    }

    /**
     * Attributes
     */
    public function getSchemaAttribute()
    {
        global $wrap;
        $_response = [
            "@context"    => "https://schema.org",
            "@type"       => "WebPage",
            "name"        => $this->getTranslation('title', $wrap['locale']),
            "description" => "",
            "publisher"   => [
                "@type" => "Organization",
                "name"  => $wrap['page']['site_name'],
                "logo"  => [
                    "@type"  => "ImageObject",
                    "url"    => "{$wrap['seo']['base_url']}/template/logotypes/logotype.png",
                    "width"  => NULL,
                    "height" => NULL
                ]
            ],
        ];

        return json_encode($_response);
    }

    /**
     * Others
     */
    public function setTag()
    {
        $_response = NULL;
        $_tags = request()->input('tags');
        if ($this->entity) {
            $this->entity->_tags()->detach();
            if ($_tags) {
                $_attach = [];
                foreach ($_tags as $_tag) {
                    if (ctype_digit($_tag)) {
                        $_attach[] = $_tag;
                    } else {
                        $_item = new self;
                        $_item->fill([
                            'title' => $_tag
                        ]);
                        $_item->save();
                        $_alias = new UrlAlias([
                            'alias'       => UrlAlias::generate_alias($_item->title),
                            'changefreq'  => 'monthly',
                            'sitemap'     => 1,
                            'priority'    => 0.5,
                            'model_title' => $_item->getTranslation('title', DEFAULT_LOCALE),
                            'locale'      => DEFAULT_LOCALE
                        ]);
                        $_item->_alias()->save($_alias);
                        $_search_index = new SearchIndex([
                            'locale' => DEFAULT_LOCALE,
                            'title'  => $_item->title,
                            'body'   => strip_tags($_item->body),
                            'status' => $_item->status ?? 1,
                        ]);
                        $_item->_search_index()->save($_search_index);
                        $_attach[] = $_item->id;
                    }
                }
                $this->entity->_tags()->attach($_attach);
            }
        }
    }

    public function _last_nodes($take = 5, $exclude = [])
    {
        $_items = $this->_nodes();
        if (is_array($exclude)) $_items->whereNotIn('id', $exclude);
        $_items = $_items->active()
            ->visibleOnBlock()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take($take)
            ->get();
        if ($_items->isNotEmpty()) {
            $_items = $_items->map(function ($_item) {
                if (method_exists($_item, '_load')) $_item->_load('teaser');

                return $_item;
            });
        }

        return $_items;
    }

    public function _load(&$options = [])
    {
        $options = array_merge([
            'view'      => NULL,
            'view_mode' => 'full',
            'index'     => NULL,
        ], $options);
        if (isset($options['index']) && $options['index']) $this->renderIndex = $options['index'];
        if ($this->renderIndex && $this->style_id) {
            $this->style_id .= "-{$this->renderIndex}";
            $this->style_class .= $this->style_id ? " {$this->style_id}" : $this->style_id;
        }
        $this->styleAttributes = [
            'id'    => $this->style_id ? : FALSE,
            'class' => $this->style_class ? : FALSE,
            'style' => ($_background = $this->_background) ? 'background-image: url(' . image_render($_background, NULL, ['only_way' => TRUE]) . ');' : FALSE
        ];
        switch ($options['view_mode']) {
            default:
                $this->body = content_render($this);
                $this->relatedMedias = $this->_files_related()->wherePivot('type', 'medias')->get();
                $this->relatedFiles = $this->_files_related()->wherePivot('type', 'files')->get();
                break;
        }
    }

    public function _render($options = [])
    {
        global $wrap;
        $this->_load($options);
        $_set_wrap = [
            'seo.title'         => $this->meta_title ? : $this->title,
            'seo.keywords'      => $this->meta_keywords,
            'seo.description'   => $this->meta_description,
            'seo.robots'        => $this->meta_robots,
            'seo.last_modified' => $this->last_modified,
            'page.title'        => $this->title,
            'page.style_id'     => $this->style_id,
            'page.style_class'  => $this->style_class ? [$this->style_class] : NULL,
            'page.breadcrumb'   => render_breadcrumb([
                'entity' => $this
            ]),
        ];
        $_page_number = $wrap['seo']['page_number'];
        if ($_page_number) {
            Paginator::currentPageResolver(function () use ($_page_number) {
                return $_page_number;
            });
        }
        $this->_items = $this->_nodes()
            ->select([
                'id',
                'title',
                'page_id',
                'preview_fid',
                'sort',
                'published_at',
                'teaser',
                'body'
            ])
            ->active()
            ->visibleOnList()
            ->orderBy('sort')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->remember(REMEMBER_LIFETIME)
            ->paginate(4);
        $this->_items->getCollection()->transform(function ($_entity) {
            $_options = ['view_mode' => 'teaser'];
            if (method_exists($_entity, '_load')) $_entity->_load($_options);

            return $_entity;
        });
        if ($_page_number && $this->_items->isEmpty()) abort(404);
        if ($_page_number) $_set_wrap['seo.robots'] = 'noindex, follow';
        if ($this->_items->isNotEmpty() && $this->_items->hasMorePages()) {
            $_page_number = $_page_number ? : 1;
            $_page_number++;
            $_current_url = $wrap['seo']['url_alias'];
            $_current_url_query = $wrap['seo']['url_query'];
            $_url = trim($_current_url, '/') . "/page-{$_page_number}";
            $_next_page_link = _u($_url) . $_current_url_query;
            $_set_wrap['seo.link_next'] = $_next_page_link;
        }
        $this->setWrap($_set_wrap);
        $_template = [
            "frontend.{$this->deviceTemplate}.tags.tag_{$this->id}",
            "frontend.{$this->deviceTemplate}.tags.tag",
            "frontend.default.tags.tag_{$this->id}",
            "frontend.default.tags.tag"
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.tags.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.tags.{$options['view']}");
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
        $_items = $this->_nodes()
            ->select([
                'id',
                'title',
                'page_id',
                'preview_fid',
                'sort',
                'published_at',
                'teaser',
                'body'
            ])
            ->active()
            ->visibleOnList()
            ->orderBy('sort')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->remember(REMEMBER_LIFETIME)
            ->paginate(4);
        if ($_items->isNotEmpty()) {
            if ($_items->hasMorePages()) {
                $_page_number = $_page_number ? : 1;
                $_page_number++;
                $_current_url = $wrap['seo']['url_alias'];
                $_current_url_query = $wrap['seo']['url_query'];
                $_url = trim($_current_url, '/') . "/page-{$_page_number}";
                $_next_page_link = _u($_url) . $_current_url_query;
                wrap()->set('seo.link_next', $_next_page_link);
            }
            $_items->getCollection()->transform(function ($_entity) {
                $_options = ['view_mode' => 'teaser'];
                if (method_exists($_entity, '_load')) $_entity->_load($_options);

                return $_entity;
            });
            $_items_output = NULL;
            foreach ($_items as $_item) {
                $_items_output .= View::first([
                    "frontend.{$this->deviceTemplate}.nodes.teaser_{$this->id}",
                    "frontend.{$this->deviceTemplate}.nodes.teaser",
                    "frontend.default.nodes.teaser_{$this->id}",
                    "frontend.default.nodes.teaser",
                ], compact('_item'))
                    ->render(function ($view, $content) {
                        return clear_html($content);
                    });
            }
            $commands['commands'][] = [
                'command' => 'html',
                'options' => [
                    'target' => '#page-items-list-pagination',
                    'data'   => clear_html($_items->links("frontend.{$this->deviceTemplate}.partials.pagination"))
                ]
            ];
            $commands['commands'][] = [
                'command' => 'append',
                'options' => [
                    'target' => '#page-items-list > div',
                    'data'   => $_items_output
                ]
            ];
            $commands['commands'][] = [
                'command' => 'replaceWith',
                'options' => [
                    'target' => '#page-breadcrumbs',
                    'data'   => View::first([
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
                    'title' => $this->getTranslation('meta_title', $wrap['locale'], FALSE) ? : $this->getTranslation('title', $wrap['locale']) . " {$wrap['seo']['title_suffix']}{$wrap['seo']['page_number_suffix']}"
                ]
            ];
        }

        return $commands;
    }


}
