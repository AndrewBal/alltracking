<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Advantage extends Model
{
    use BaseModel;

    protected $table = 'advantages';
    protected $fillable = [
        'title',
        'sub_title',
        'background_fid',
        'style_id',
        'style_class',
        'body',
        'status',
        'hidden_title',

    ];
    public $timestamps = FALSE;
    public $translatable = [
        'title',
        'sub_title',
        'body',

    ];
    protected $attributes = [
        'id'             => NULL,
        'title'          => NULL,

        'sub_title'      => NULL,
        'background_fid' => NULL,
        'style_id'       => NULL,
        'style_class'    => NULL,
        'body'           => NULL,
        'status'         => 1,
        'hidden_title'   => 0,
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _advantages()
    {
        return $this->hasMany(AdvantageItems::class, 'advantage_id')
            ->orderByDesc('status')
            ->orderBy('sort');
    }

    /**
     * Attribute
     */
    public function getItemsAttribute()
    {
        $_response = collect([]);
        $_advantages = $this->_advantages;
        if ($_advantages->isNotEmpty()) {
            $_advantage = $this;
            $_response->put('headers', [
                [
                    'data' => 'Заголовок',
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
            $_response->put('items', $_advantages->transform(function ($advantage) use ($_advantage) {
                return (object)[
                    $advantage->title,
                    '<input type="number"
                           class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item"
                           name="items_sort[]"
                           data-id="' . $advantage->id . '"
                           value="' . $advantage->sort . '">',
                    $advantage->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                    _l('', 'oleus.advantages.item', [
                        'p'          => [
                            $_advantage,
                            'edit',
                            $advantage->id
                        ],
                        'attributes' => [
                            'class'   => 'uk-button uk-button-success uk-button-xsmall uk-button-icon use-ajax',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]),
                    _l('', 'oleus.advantages.item', [
                        'p'          => [
                            $_advantage,
                            'destroy',
                            $advantage->id
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
            'class' => 'advantage-body' . ($this->style_class ? " {$this->style_class}" : NULL),
            'style' => ($_background = $this->_background) ? 'background-image: url(' . image_render($_background, NULL, ['only_way' => TRUE]) . ');' : FALSE
        ];
        if ($wrap['user'] && $wrap['user']->can('advantages_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';
        $this->advantages = $this->_advantages()
            ->active('advantage_items')
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->get();

        return $this;
    }

    public function _render($options)
    {
        $this->_load($options);
        $_template = [
            "frontend.{$this->deviceTemplate}.advantages.advantage_{$this->id}",
            "frontend.{$this->deviceTemplate}.advantages.advantage",
            "frontend.default.advantages.advantage_{$this->id}",
            "frontend.default.advantages.advantage",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.advantages.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.advantages.{$options['view']}");
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
        if ($this->advantages->isEmpty()) return NULL;
        $_template = [
            "frontend.{$this->deviceTemplate}.shortcuts.advantage_{$this->id}",
            "frontend.{$this->deviceTemplate}.shortcuts.advantage",
            "frontend.default.shortcuts.advantage_{$this->id}",
            'frontend.default.shortcuts.advantage',
            "frontend.{$this->deviceTemplate}.advantages.advantage_{$this->id}",
            "frontend.{$this->deviceTemplate}.advantages.advantage",
            "frontend.default.advantages.advantage_{$this->id}",
            'frontend.default.advantages.advantage',
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
