<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class Slider extends Model
{
    use BaseModel;

    protected $table = 'sliders';
    protected $fillable = [
        'title',
        'options',
        'preset',
        'style_id',
        'style_class',
        'status',
    ];
    protected $attributes = [
        'id'          => NULL,
        'title'       => NULL,
        'options'     => NULL,
        'preset'      => NULL,
        'style_id'    => NULL,
        'style_class' => NULL,
        'status'      => 1,
    ];
    public $timestamps = FALSE;
    public $translatable;
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
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

    public function getItemsAttribute()
    {
        $_response = collect([]);
        $_sliders = $this->_slides;
        if ($_sliders->isNotEmpty()) {
            $_slider = $this;
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
            $_response->put('items', $_sliders->transform(function ($slider) use ($_slider) {
                return (object)[
                    $slider->title,
                    '<input type="number"
                           class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item"
                           name="items_sort[]"
                           data-id="' . $slider->id . '"
                           value="' . $slider->sort . '">',
                    $slider->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                    _l('', 'oleus.sliders.item', [
                        'p'          => [
                            $_slider,
                            'edit',
                            $slider->id
                        ],
                        'attributes' => [
                            'class'   => 'uk-button uk-button-success uk-button-xsmall uk-button-icon use-ajax',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]),
                    _l('', 'oleus.sliders.item', [
                        'p'          => [
                            $_slider,
                            'destroy',
                            $slider->id
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

    public function getSlideShowAttribute()
    {
        $_options = $this->attributes['options'] ?? NULL;
        if ($_options && is_json($_options)) {
            return collect(json_decode($_options))
                ->except([
                    'slidenav',
                    'dotnav'
                ])
                ->map(function ($value, $key) {
                    return $value ? (str_replace('_', '-', $key) . ':' . ($value == 0 ? 'false' : ($value == 1 ? 'true' : $value))) : NULL;
                })
                ->filter(function ($option) {
                    return $option;
                })
                ->implode(';');
        }

        return NULL;
    }

    /**
     * Relationships
     */
    public function _slides()
    {
        return $this->hasMany(SliderItems::class, 'slider_id')
            ->orderByDesc('status')
            ->orderBy('sort');
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
        if (isset($options['index']) && $options['index']) $this->renderIndex = $options['index'];
        if ($this->renderIndex && $this->style_id) {
            $this->style_id .= "-{$this->renderIndex}";
            $this->style_class .= $this->style_id ? " {$this->style_id}" : $this->style_id;
        }
        $this->styleAttributes = [
            'id'           => $this->style_id ? : FALSE,
            'class'        => 'slider-body uk-position-relative' . ($this->style_class ? " {$this->style_class}" : NULL),
            'uk-slideshow' => $this->slide_show
        ];
        if ($wrap['user'] && $wrap['user']->can('sliders_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';
        $this->slides = $this->_slides()
            ->active('slider_items')
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->get();

        return $this;
    }

    public function _render($options = NULL)
    {
        $this->_load($options);
        $_template = [
            "frontend.{$this->deviceTemplate}.sliders.slider_{$this->id}",
            "frontend.{$this->deviceTemplate}.sliders.slider",
            "frontend.default.sliders.slider_{$this->id}",
            "frontend.default.sliders.slider",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.sliders.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.sliders.{$options['view']}");
        }
        $_item = $this;

        debug($this->options);

        return View::first($_template, compact('_item'))
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }

    public function getShortcut($options = [])
    {
        if (!$this->status) return NULL;
        $this->_load($options);
        if ($this->slides->isEmpty()) return NULL;
        $_template = [
            "frontend.{$this->device_template}.shortcuts.slider_{$this->id}",
            "frontend.{$this->device_template}.shortcuts.slider",
            "frontend.default.shortcuts.slider_{$this->id}",
            "frontend.default.shortcuts.slider",
            "frontend.{$this->deviceTemplate}.sliders.slider_{$this->id}",
            "frontend.{$this->deviceTemplate}.sliders.slider",
            "frontend.default.sliders.slider_{$this->id}",
            "frontend.default.sliders.slider",
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
    }
}
