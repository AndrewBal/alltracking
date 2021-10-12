<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class Block extends Model
{
    use BaseModel;

    protected $table = 'blocks';
    protected $fillable = [
        'title',
        'sub_title',
        'body',
        'background_fid',
        'style_id',
        'style_class',
        'status',
        'hidden_title',
    ];
    public $translatable = [
        'title',
        'sub_title',
        'body'
    ];
    protected $attributes = [
        'id'             => NULL,
        'title'          => NULL,
        'sub_title'      => NULL,
        'body'           => NULL,
        'background_fid' => NULL,
        'style_id'       => NULL,
        'style_class'    => NULL,
        'status'         => 1,
        'hidden_title'   => 0,
    ];
    public $timestamps = FALSE;
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
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
            'class' => 'block-body' . ($this->style_class ? " {$this->style_class}" : NULL),
            'style' => ($_background = $this->_background) ? 'background-image: url(' . render_image($_background, NULL, ['only_way' => TRUE]) . ');' : FALSE
        ];
            $this->relatedMedias = $this->_files_related()
                ->wherePivot('type', 'medias')
                ->remember(REMEMBER_LIFETIME * 24 * 7)
                ->get();
            $this->relatedFiles = $this->_files_related()
                ->wherePivot('type', 'files')
                ->remember(REMEMBER_LIFETIME * 24 * 7)
                ->get();

        if ($wrap['user'] && $wrap['user']->can('blocks_update')) $this->styleAttributes['class'] .= ' uk-position-relative edit-div';

        return $this;
    }

    public function _render($options = [])
    {
        $this->_load($options);
        $_template = [
            "frontend.{$this->deviceTemplate}.blocks.block_{$this->id}",
            "frontend.{$this->deviceTemplate}.blocks.block",
            "frontend.default.blocks.block_{$this->id}",
            "frontend.default.blocks.block",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.blocks.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.blocks.{$options['view']}");
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
            "frontend.{$this->deviceTemplate}.shortcuts.block_{$this->id}",
            "frontend.{$this->deviceTemplate}.shortcuts.block",
            "frontend.default.shortcuts.block_{$this->id}",
            'frontend.default.shortcuts.block',
            "frontend.{$this->deviceTemplate}.blocks.block_{$this->id}",
            "frontend.{$this->deviceTemplate}.blocks.block",
            "frontend.default.blocks.block_{$this->id}",
            "frontend.default.blocks.block",
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
