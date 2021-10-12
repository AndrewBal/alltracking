<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use App\Models\Seo\UrlAlias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Psy\sh;

class MenuItems extends Model
{
    use BaseModel;

    const DEFAULT_ITEMS = [
        '<front>',
        '<none>'
    ];
    protected $table = 'menu_items';
    protected $fillable = [
        'id',
        'menu_id',
        'parent_id',
        'alias_id',
        'icon_fid',
        'link',
        'anchor',
        'title',
        'sub_title',
        'sort',
        'status',
        'data',
    ];
    protected $attributes = [
        'id'        => NULL,
        'menu_id'   => NULL,
        'parent_id' => NULL,
        'alias_id'  => NULL,
        'icon_fid'  => NULL,
        'link'      => NULL,
        'anchor'    => NULL,
        'title'     => NULL,
        'sub_title' => NULL,
        'data'      => NULL,
        'sort'      => 0,
        'status'    => 1,
    ];
    public $translatable = [
        'title',
        'sub_title',
    ];
    protected $classIndex = 'menu_item';
    public $timestamps = FALSE;
    public $entity;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->with([
                '_children',
                '_alias'
            ])
            ->orderBy('sort');
    }

    public function _alias()
    {
        return $this->hasOne(UrlAlias::class, 'id', 'alias_id')
            ->withDefault();
    }

    /**
     * Attribute
     */
    public function setDataAttribute($value = NULL)
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getDataAttribute()
    {
        return json_decode($this->attributes['data']);
    }

    public function setLinkAttribute($value = NULL)
    {
        if (in_array($value['name'], MenuItems::DEFAULT_ITEMS)) {
            $this->attributes['link'] = $value['name'];
        } elseif ($value['value']) {
            $this->attributes['alias_id'] = $value['value'];
        } else {
            $this->attributes['link'] = $value['name'];
        }
    }

    public function getLinkAttribute()
    {
        if (!is_null($this->attributes['alias_id'])) {
            $_url_alias = UrlAlias::find($this->attributes['alias_id']);
            if ($_url_alias) {
                if ($_related_model = $_url_alias->model) {
                    return (object)[
                        'id'    => $_url_alias->id,
                        'name'  => "{$_related_model->id}::{$_related_model->title}",
                        'alias' => $_url_alias->alias
                    ];
                }
            }
        } elseif ($this->attributes['link']) {
            return (object)[
                'id'    => NULL,
                'name'  => $this->attributes['link'],
                'alias' => NULL
            ];
        }

        return NULL;
    }



    //
    //
    //    public function set($item)
    //    {
    //        if ($this->entity && is_array($item)) {
    //            $_item = isset($item['id']) && is_numeric($item['id']) ? self::find($item['id']) : NULL;
    //            $entity_id = NULL;
    //            $link = NULL;
    //            if (in_array($item['link']['name'], self::DEFAULT_ITEMS)) {
    //                $link = $item['link']['name'];
    //            } elseif ($item['link']['value']) {
    //                $entity_id = $item['link']['value'];
    //            } else {
    //                $link = $item['link']['name'];
    //            }
    //            if (!is_null($entity_id) || !is_null($link)) {
    //                $item['alias_id'] = $entity_id;
    //                $item['link'] = $link;
    //                $item['parent_id'] = isset($item['parent_id']) && $item['parent_id'] ? $item['parent_id'] : NULL;
    //                self::updateOrCreate([
    //                    'id' => is_null($_item) ? NULL : $_item->id
    //                ], $item);
    //            }
    //        }
    //    }
    //
    //
    //    public function _get_url_item()
    //    {
    //        $_language = $this->front_language;
    //        $_location = $this->front_location;
    //        if (is_null($this->alias_id) && $this->link) {
    //            if (in_array($this->link, self::DEFAULT_ITEMS)) {
    //                if ($this->link == '<front>') {
    //                    return (object)[
    //                        'id'     => NULL,
    //                        'name'   => NULL,
    //                        'alias'  => _u('/', [], TRUE),
    //                        'entity' => NULL
    //                    ];
    //                } elseif ($this->link == '<none>') {
    //                    return (object)[
    //                        'id'     => NULL,
    //                        'name'   => NULL,
    //                        'alias'  => NULL,
    //                        'entity' => NULL
    //                    ];
    //                }
    //            } else {
    //                return (object)[
    //                    'id'     => NULL,
    //                    'name'   => NULL,
    //                    'alias'  => $this->link,
    //                    'entity' => NULL
    //                ];
    //            }
    //        } elseif ($this->alias_id) {
    //            $_alias_id = $this->alias_id;
    //            $_url_alias = UrlAlias::from('url_alias as a')
    //                ->leftJoin('nodes as n', 'n.id', '=', 'a.model_id')
    //                ->leftJoin('pages as p', 'p.id', '=', 'a.model_id')
    //                ->leftJoin('shop_categories as sc', 'sc.id', '=', 'a.model_id')
    //                ->where('a.id', $_alias_id)
    //                ->first([
    //                    'a.id',
    //                    'a.model_type',
    //                    'n.id as node_id',
    //                    'p.id as page_id',
    //                    'sc.id as shop_category_id',
    //                ]);
    //            if ($_url_alias) {
    //                $_object = NULL;
    //                switch ($_url_alias->model_type) {
    //                    case 'App\Models\Node':
    //                        $_object = node_load($_url_alias->node_id, $_language);
    //                        $_alias = $_object->_alias;
    //                        $_object_alias = $_alias->language != DEFAULT_LANGUAGE ? "{$_alias->language}/{$_alias->alias}" : $_alias->alias;
    //
    //                        return (object)[
    //                            'id'     => $_alias->id,
    //                            'name'   => $_object->title,
    //                            'alias'  => $_object_alias,
    //                            'entity' => $_object,
    //                        ];
    //                        break;
    //                    case 'App\Models\Page':
    //                        $_object = page_load($_url_alias->page_id, $_language);
    //                        $_alias = $_object->_alias;
    //                        $_object_alias = $_alias->language != DEFAULT_LANGUAGE ? "{$_alias->language}/{$_alias->alias}" : $_alias->alias;
    //
    //                        return (object)[
    //                            'id'     => $_alias->id,
    //                            'name'   => $_object->title,
    //                            'alias'  => $_object_alias,
    //                            'entity' => $_object,
    //                        ];
    //                        break;
    //                    case 'App\Models\ShopCategory':
    //                        $_object = shop_category_load($_url_alias->shop_category_id, $_language);
    //                        $_alias = $_object->_alias;
    //                        $_object_alias = $_alias->language != DEFAULT_LANGUAGE ? "{$_alias->language}/{$_alias->alias}" : $_alias->alias;
    //
    //                        return (object)[
    //                            'id'     => $_alias->id,
    //                            'name'   => $_object->title,
    //                            'alias'  => $_object_alias,
    //                            'entity' => $_object,
    //                        ];
    //                        break;
    //                }
    //                //                    if($_object_id) {
    //                ////                        $_alias_url = $_url_alias->language != DEFAULT_LANGUAGE ? "{$_url_alias->language}/{$_url_alias->alias}" : $_url_alias->alias;
    //                ////                        $_object_alias = $_model::from("{$_model_table} as o")
    //                ////                            ->leftJoin('url_alias as a', 'a.id', '=', 'o.alias_id')
    //                ////                            ->where('o.relation', $_object_id)
    //                ////                            //                            ->where('o.location', $_location)
    //                ////                            ->where('o.language', $_language)
    //                ////                            ->first([
    //                ////                                'a.alias',
    //                ////                                'a.language',
    //                ////                                'a.id',
    //                ////                                'o.title',
    //                ////                            ]);
    //                ////                        if($_object_alias) {
    //                ////                            $_alias_id = $_object_alias->id;
    //                ////                            $_alias_url = $_object_alias->language != DEFAULT_LANGUAGE ? "{$_object_alias->language}/{$_object_alias->alias}" : $_object_alias->alias;
    //                ////                            $_object_title = $_object_alias->title;
    //                ////                        }
    //                //                        return (object)[
    //                //                            'id'     => $_alias_id,
    //                //                            'name'   => $_object_title,
    //                //                            'alias'  => $_object_alias,
    //                //                            'entity' => $_object,
    //                //                        ];
    //                //                    }
    //            }
    //        }
    //
    //        return NULL;
    //    }
    //
    //    public function _sub_items()
    //    {
    //        $items = self::where('parent_id', $this->id)
    //            ->get();
    //
    //        return $items ?? NULL;
    //    }
}
