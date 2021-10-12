<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use App\Models\Seo\UrlAlias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class Menu extends Model
{
    use BaseModel;

    protected $table = 'menus';
    protected $fillable = [
        'id',
        'title',
        'style_class',
        'style_id',
        'status',
    ];
    protected $attributes = [
        'id'          => NULL,
        'title'       => NULL,
        'style_class' => NULL,
        'style_id'    => NULL,
        'status'      => 1,
    ];
    public $translatable;
    public $timestamps = FALSE;
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _menu_items()
    {
        return $this->hasMany(MenuItems::class, 'menu_id')
            ->with([
                '_children',
                '_alias'
            ])
            ->orderBy('sort')
            ->orderBy('title');
    }

    /**
     * Attribute
     */
    public function getItemsAttribute()
    {
        $_response = collect([]);
        $_items = $this->_menu_items()
            ->whereNull('parent_id')
            ->get();
        if ($_items->isNotEmpty()) {
            $_menu = $this;
            $_response->put('headers', [
                [
                    'data' => 'Название пункта меню',
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
            $_output = collect([]);
            $_items->map(function ($item) use (&$_output, $_menu) {
                $this->render_menu_item($_output, $_menu, $item);
            });
            $_response->put('items', $_output);
        }

        return $_response;
    }

    public function render_menu_item(&$output, Menu $menu, MenuItems $item, $level = 0)
    {
        $_padding = $level ? '<span style="margin-left: ' . $level * 10 . 'px;" uk-icon="icon: arrow_right;"></span>' : NULL;
        $output->push((object)[
            "{$_padding}{$item->title}",
            '<input type="number"
                           class="uk-input uk-form-width-xsmall uk-form-small uk-input-number-spin-hide uk-input-sort-item"
                           name="items_sort[]"
                           data-id="' . $item->id . '"
                           value="' . $item->sort . '">',
            $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
            _l('', 'oleus.menus.item', [
                'p'          => [
                    $menu,
                    'edit',
                    $item->id
                ],
                'attributes' => [
                    'class'   => 'uk-button uk-button-success uk-button-xsmall uk-button-icon use-ajax',
                    'uk-icon' => 'icon: edit'
                ]
            ]),
            _l('', 'oleus.menus.item', [
                'p'          => [
                    $menu,
                    'destroy',
                    $item->id
                ],
                'attributes' => [
                    'class'   => 'uk-button uk-button-danger uk-button-xsmall uk-button-icon use-ajax',
                    'uk-icon' => 'icon: delete_forever'
                ]
            ])
        ]);
        if ($item->_children->isNotEmpty()) {
            $item->_children->map(function ($_item) use (&$output, $menu, $level) {
                $this->render_menu_item($output, $menu, $_item, ($level + 1));
            });
        }
    }

    public function _items_tree_render($items, $parent = NULL, $options = NULL)
    {
        if ($items->isNotEmpty()) {
            $parents = collect([]);
            foreach ($items as $_item) {
                if (is_null($parent) && $_item->parent_id) continue;
                $_children = $_item->_children()
                    ->where('status', 1)
                    ->get();
                $_item_url_alias = NULL;
                $_item_path = NULL;
                $_item_url = NULL;
                if ($_item->_alias->exists) {
                    $_item_url = $_item->_alias;
                    $_item_path = $_item->generate_url;
                } elseif (($_item_link = $_item->getRawOriginal('link'))) {
                    switch ($_item_link) {
                        case '<front>':
                            $_item_path = USE_MULTI_LANGUAGE ? _u(LaravelLocalization::getLocalizedURL($this->frontLocale, '/')) : _u('/');
                            break;
                        case '<none>':
                            if ($_item->anchor) {
                                $_item_path = '/' . trim(request()->path(), '/');
                            }
                            break;
                        default:
                            $_item_path = $_item_link;
                            break;
                    }
                }
                if ($_item->anchor && $_item_path) $_item_path .= "#{$_item->anchor}";
                $_options = [
                    'entity_id' => $_item->id,
                    'item'      => [
                        'menu_id'         => $this->id,
                        'url_alias'       => $_item_url,
                        'icon_url'        => $_item->icon_fid ? render_image($_item->_icon, NULL, ['only_way' => TRUE]) : NULL,
                        'icon'            => $_item->icon_fid ? render_image($_item->_icon, NULL, ['attribute' => ['alt' => $_item->title]]) : NULL,
                        'title'           => $_item->title,
                        'description'     => $_item->sub_title,
                        'path'            => $_item_path,
                        'anchor'          => $_item->anchor,
                        'active'          => FALSE,
                        'children_active' => FALSE,
                        'wrapper'         => [
                            'class' => [
                                'menu-item',
                                $_item->data->item_class ? : FALSE,
                                count($_children) ? 'menu-items-parent' : FALSE,
                            ]
                        ],
                        'attributes'      => [
                            'class' => [
                                $_item->data->class ? : FALSE
                            ],
                            'id'    => $_item->data->id ? : FALSE,
                            'href'  => $_item_path,
                            'title' => $_item->title
                        ]
                    ],
                    'children'  => (count($_children) ? collect($this->_items_tree_render($_children, $_item->id, $options)) : collect())
                ];
                if ($_item->data->attributes) $_options['item']['attributes'][] = $_item->data->attributes;
                $parents->put($_item->id, $_options);
            }

            return $parents;
        } else {
            return $items;
        }
    }

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
            'class' => 'menu-body' . ($this->style_class ? " {$this->style_class}" : NULL),
        ];
        if ($wrap['user'] && $wrap['user']->can('menus_update')) $this->styleAttributes['class'] .= ' edit-div';
        $_entity = $this;
        $_cache_key = cache_key('menu', $this);
        $this->menu_items = Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($_entity, $options) {
            $_items = $_entity->_menu_items()
                ->active('menu_items')
                ->get();

            return $_entity->_items_tree_render($_items, NULL, $options);
        });

        if ($this->menu_items->isNotEmpty()) {
            $_canonical = $wrap['seo']['canonical'];
            $this->menu_items->transform(function ($_item) use ($_canonical) {
                $this->_menu_item_state($_item, $_canonical);

                return $_item;
            });
        }

    }

    public function _render($options = [])
    {
        $this->_load($options);
        $_options = array_merge([
            'view'  => NULL,
            'index' => NULL,
        ], $options);
        $this->_load($_options);
        $_template = [
            "frontend.{$this->deviceTemplate}.menus.menu_{$this->id}",
            "frontend.{$this->deviceTemplate}.menus.menu",
            "frontend.default.menus.menu_{$this->id}",
            "frontend.default.menus.menu",
        ];
        if (isset($options['view']) && $options['view']) {
            array_unshift($_template, "frontend.default.menus.{$options['view']}");
            array_unshift($_template, "frontend.{$this->deviceTemplate}.menus.{$options['view']}");
        }
        $_item = $this;

        return View::first($_template, compact('_item'))
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }

    public function _menu_item_state(&$item, $canonical)
    {
        $_item_active = $item['item']['anchor'] ? FALSE : active_path($item['item']['path']);
        $_other_item_active = $canonical == $item['item']['path'] ? TRUE : FALSE;
        $item['item']['active'] = $_item_active;
        if ($_item_active || $_other_item_active) {
            $item['item']['wrapper']['class'][] = 'active';
            $item['item']['attributes']['class'][] = 'active';
        }
        if ($item['children']) {
            $item['children']->transform(function ($_children) use (&$item, $canonical) {
                $this->_menu_item_state($_children, $canonical);
                if ($_children['item']['active']) {
                    $item['item']['children_active'] = TRUE;
                    $item['item']['wrapper']['class'][] = 'children-active';
                    $item['item']['attributes']['class'][] = 'children-active';
                }

                return $_children;
            });
        }
    }

    public function getShortcut($options = [])
    {
        if (!$this->status) return NULL;
        $this->_load($options);
        if ($this->menu_items->isEmpty()) return NULL;
        $_template = [
            "frontend.{$this->deviceTemplate}.shortcuts.menu_{$this->id}",
            "frontend.{$this->deviceTemplate}.shortcuts.menu",
            "frontend.default.shortcuts.menu_{$this->id}",
            'frontend.default.shortcuts.menu',
            "frontend.{$this->deviceTemplate}.menus.menu_{$this->id}",
            "frontend.{$this->deviceTemplate}.menus.menu",
            "frontend.default.menus.menu_{$this->id}",
            "frontend.default.menus.menu",
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
