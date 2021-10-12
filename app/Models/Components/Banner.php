<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class Banner extends Model
{
    use BaseModel;

    protected $table = 'banners';
    protected $fillable = [
        'title',
        'body',
        'link',
        'link_attributes',
        'background_fid',
        'preset',
        'hidden_title',
        'status',
        'style_id',
        'style_class'
    ];
    public $timestamps = FALSE;
    public $translatable = [
        'title',
        'link',
        'body',
        'background_fid'
    ];
    protected $attributes = [
        'id'              => NULL,
        'title'           => NULL,
        'body'            => NULL,
        'link'            => NULL,
        'link_attributes' => NULL,
        'background_fid'  => NULL,
        'preset'          => NULL,
        'status'          => 1,
        'hidden_title'    => 0,
        'style_id'        => NULL,
        'style_class'     => NULL
    ];
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Attributes
     */
    public function setPresetAttribute($value = NULL)
    {
        $this->attributes['preset'] = $value ? : NULL;
    }

    /**
     * Relationships
     */
    public function _background()
    {
        $_locale = $this->frontLocale ?? DEFAULT_LOCALE;
        $this->background = $this->getTranslation('background_fid', $_locale);

        return $this->hasOne(File::class, 'id', 'background')
            ->remember(REMEMBER_LIFETIME * 24 * 7);
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
            'class' => 'banner-body' . ($this->style_class ? " {$this->style_class}" : NULL),
        ];
        if ($wrap['user'] && $wrap['user']->can('banners_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';

        return $this;
    }

    public function _render($options = [])
    {
        $this->_load($options);
        $_template = [
            "frontend.{$this->deviceTemplate}.banners.banner_{$this->id}",
            "frontend.{$this->deviceTemplate}.banners.banner",
            "frontend.default.banners.banner_{$this->id}",
            "frontend.default.banners.banner",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.banners.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.banners.{$options['view']}");
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
        $_item = $this;
        $_template = [
            "frontend.{$this->deviceTemplate}.shortcuts.banner_{$this->id}",
            "frontend.{$this->deviceTemplate}.shortcuts.banner",
            "frontend.default.shortcuts.banner_{$this->id}",
            "frontend.default.shortcuts.banner",
            "frontend.{$this->deviceTemplate}.banners.banner_{$this->id}",
            "frontend.{$this->deviceTemplate}.banners.banner",
            "frontend.default.banners.banner_{$this->id}",
            "frontend.default.banners.banner",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.shortcuts.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.shortcuts.{$options['view']}");
        }

        return View::first($_template, compact('_item'))
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }
}
