<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Psr\Log\NullLogger;

class Faq extends Model
{
    use BaseModel;

    protected $table = 'faqs';
    protected $fillable = [
        'title',
        'body',
        'style_class',
        'style_id',
        'status',
        'visible_title',
    ];
    public $translatable = [
        'title',
        'body',
    ];
    protected $attributes = [
        'id'            => NULL,
        'title'         => NULL,
        'body'          => NULL,
        'style_class'   => NULL,
        'style_id'      => NULL,
        'status'        => 0,
        'visible_title' => 0,
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _faqs()
    {
        return $this->hasMany(FaqItems::class)
            ->orderByDesc('status')
            ->orderBy('sort');
    }

    /**
     * Attribute
     */
    public function getItemsAttribute()
    {
        $_response = collect([]);
        $_faqs = $this->_faqs;
        if ($_faqs->isNotEmpty()) {
            $_faq = $this;
            $_response->put('headers', [
                [
                    'data' => 'Вопрос',
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 50px;',
                    'data'  => '<span uk-icon="icon: sort_by_alpha">'
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data'  => '<span uk-icon="icon: visibility">'
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data'  => '<span uk-icon="icon: edit">'
                ],
                [
                    'class' => 'uk-text-center',
                    'style' => 'width: 34px;',
                    'data'  => '<span uk-icon="icon: delete_forever">'
                ]
            ]);
            $_response->put('items', $_faqs->transform(function ($faq) use ($_faq) {
                return (object)[
                    Str::limit(strip_tags($faq->question), 125),
                    '<input type="number"
                           class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item"
                           name="items_sort[]"
                           data-id="' . $faq->id . '"
                           value="' . $faq->sort . '">',
                    $faq->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                    _l('', 'oleus.faqs.item', [
                        'p'          => [
                            $_faq,
                            'edit',
                            $faq->id
                        ],
                        'attributes' => [
                            'class'   => 'uk-button uk-button-success uk-button-xsmall uk-button-icon use-ajax',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]),
                    _l('', 'oleus.faqs.item', [
                        'p'          => [
                            $_faq,
                            'destroy',
                            $faq->id
                        ],
                        'attributes' => [
                            'class'   => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                            'uk-icon' => 'icon: delete_forever'
                        ]
                    ])
                ];
            }));
        }

        return $_response;
    }

    /**
     * Others
     */
    public function _render_block($options)
    {
        $options = array_merge([
            'view' => NULL,
        ], $options);
        $_items = self::active()
            ->visibleOnBlock()
            ->orderBy('sort')
            ->remember(REMEMBER_LIFETIME)
            ->get();
        if ($_items->isNotEmpty()) {
            $_template = [
                "frontend.{$this->deviceTemplate}.faq.block",
                'frontend.default.faq.block',
            ];
            if (isset($options['view']) && $options['view']) {
                array_unshift($_template, "frontend.default.faq.{$options['view']}");
                array_unshift($_template, "frontend.{$this->deviceTemplate}.faq.{$options['view']}");
            }

            return View::first($_template, compact('_items'))
                ->render(function ($view, $content) {
                    return minimize_html($content);
                });
        }

        return NULL;
    }

    public function _load(&$options = [])
    {
        global $wrap;
        $options = array_merge([
            'view'  => NULL,
            'index' => NULL,
        ], $options);
        $this->body = content_render($this);
        if (isset($options['index']) && $options['index']) $this->renderIndex = $options['index'];
        if ($this->renderIndex && $this->style_id) {
            $this->style_id .= "-{$this->renderIndex}";
            $this->style_class .= $this->style_id ? " {$this->style_id}" : $this->style_id;
        }
        $this->styleAttributes = [
            'id'    => $this->style_id ? : FALSE,
            'class' => 'faq-body' . ($this->style_class ? " {$this->style_class}" : NULL),
        ];
        if ($wrap['user'] && $wrap['user']->can('faqs_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';
        $this->faqs = $this->_faqs()
            ->active('faq_items')
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->get();

        return $this;
    }

    public function _render($options)
    {
        $this->_load($options);
        $_template = [
            "frontend.{$this->deviceTemplate}.faqs.faq_{$this->id}",
            "frontend.{$this->deviceTemplate}.faqs.faq",
            "frontend.default.faqs.faq_{$this->id}",
            "frontend.default.faqs.faq",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.faqs.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.faqs.{$options['view']}");
        }
        $_item = $this;

        return View::first($_template, compact('_item'))
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }


    public function getShortcut($options = [])
    {
        if (!$this->status) return NULL;
        $this->_load($options);
        if ($this->faqs->isEmpty()) return NULL;
        $_template = [
            "frontend.{$this->deviceTemplate}.shortcuts.faq_{$this->id}",
            "frontend.{$this->deviceTemplate}.shortcuts.faq",
            "frontend.default.shortcuts.faq_{$this->id}",
            'frontend.default.shortcuts.faq',
            "frontend.{$this->deviceTemplate}.faqs.faq_{$this->id}",
            "frontend.{$this->deviceTemplate}.faqs.faq",
            "frontend.default.faqs.faq_{$this->id}",
            'frontend.default.faqs.faq',
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.shortcuts.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.shortcuts.{$options['view']}");
        }
        $_item = $this;

        return View::first($_template, compact('_item'))
            ->render(function ($view, $content) {
                return clear_html($content);
            });

        return $view;
    }
}
