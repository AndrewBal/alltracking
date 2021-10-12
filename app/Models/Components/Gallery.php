<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class Gallery extends Model
{
    use BaseModel;

    protected $table = 'galleries';
    protected $fillable = [
        'title',
        'preset_preview',
        'preset_full',
        'style_id',
        'style_class',
        'status',
        'hidden_title'
    ];
    protected $attributes = [
        'id'             => NULL,
        'title'          => NULL,
        'preset_preview' => NULL,
        'preset_full'    => NULL,
        'style_id'       => NULL,
        'style_class'    => NULL,
        'status'         => 1,
        'hidden_title'   => 0,
    ];
    public $timestamps = FALSE;
    public $translatable = [
        'title'
    ];
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
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
            'id'    => $this->style_id ? : FALSE,
            'class' => 'gallery-body' . ($this->style_class ? " {$this->style_class}" : NULL),
        ];
        if ($wrap['user'] && $wrap['user']->can('galleries_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';
        $this->photos = $this->_files_related()
            ->wherePivot('type', 'medias')
            ->orderBy('sort')
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->get();

        return $this;
    }

    public function _render($options = NULL)
    {
        $this->_load($options);
        $_template = [
            "frontend.{$this->deviceTemplate}.galleries.gallery_{$this->id}",
            "frontend.{$this->deviceTemplate}.galleries.gallery",
            "frontend.default.galleries.gallery_{$this->id}",
            "frontend.default.galleries.gallery",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.galleries.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.galleries.{$options['view']}");
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
        if ($this->photos->isEmpty()) return NULL;
        $_template = [
            "frontend.{$this->device_template}.shortcuts.gallery_{$this->id}",
            "frontend.{$this->device_template}.shortcuts.gallery",
            "frontend.default.shortcuts.gallery_{$this->id}",
            "frontend.default.shortcuts.gallery",
            "frontend.{$this->deviceTemplate}.galleries.gallery_{$this->id}",
            "frontend.{$this->deviceTemplate}.galleries.gallery",
            "frontend.default.galleries.gallery_{$this->id}",
            "frontend.default.galleries.gallery",
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
