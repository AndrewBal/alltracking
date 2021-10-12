<?php

namespace App\Models\Seo;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UrlAlias extends Model
{
    use BaseModel;

    protected $table = 'url_alias';
    protected $fillable = [
        'alias',
        'locale',
        'model_type',
        'model_id',
        'model_title',
        'sitemap',
        'robots',
        'changefreq',
        'priority',
    ];
    protected $attributes = [
        'id'          => NULL,
        'alias'       => NULL,
        'locale'      => NULL,
        'model_id'    => NULL,
        'model_type'  => NULL,
        'model_title' => NULL,
        'sitemap'     => 1,
        'robots'      => 'index, follow',
        'changefreq'  => 'monthly',
        'priority'    => 0.5,
    ];
    public $founder = [];
    public $entity;
    public $translatable;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Others
     */

    public static function generate_alias($alias, $founder = [])
    {
        $_response = NULL;
        if ($founder) {
            $founder[] = Str::slug($alias);
            $_response = implode('/', $founder);
        } else {
            $_alias = explode('/', $alias);
            $alias = [];
            foreach ($_alias as $data) $alias[] = Str::slug($data);
            $_response = implode('/', $alias);
        }

        return $_response;
    }

    public function _items_for_menu($search_string = NULL, $search_id = NULL)
    {
        $_response = [];
        if ($search_string) {
            $_items = self::from('url_alias as a')
                ->with([
                    'model'
                ])
                ->where('a.model_title', 'like', "%{$search_string}%")
                ->when((is_numeric($search_id) && $search_id > 0), function ($query) use ($search_id) {
                    $query->orWhere('id', $search_id);
                })
                ->where('a.locale', DEFAULT_LOCALE)
                ->limit(30)
                ->get([
                    'a.*',
                ]);
            if ($_items->count()) {
                $_items->each(function ($_item) use (&$_response) {
                    if ($_model = $_item->model) {
                        $_item_row = [
                            'name' => "{$_model->id}::{$_model->title}",
                            'view' => NULL,
                            'data' => $_item->id
                        ];
                        $_related_model_class_basename = class_basename($_model->getMorphClass());
                        switch ($_related_model_class_basename) {
                            case 'Node':
                                $_item_row['view'] = $_model->_page->title;
                                break;
                            case 'Page':
                                $_item_row['view'] = $_model->types[$_model->type];
                                break;
                            case 'Tag':
                                $_item_row['view'] = 'Страница тега';
                                break;
                            case 'Brand':
                                $_item_row['view'] = 'Страница брэнда';
                                break;
                            case 'Category':
                                $_item_row['view'] = 'Категория магазина';
                                break;
                            case 'Product':
                                $_item_row['view'] = 'Товар магазина';
                                break;
                        }
                        $_response[] = $_item_row;
                    }
                });
            }
        }

        return $_response;
    }

    public function setAlias()
    {
        $_url = request()->input('url');
        $_re_render = $_url['re_render'] ?? 0;
        $_request_alias = $_url['alias'] && !$_re_render ? $_url['alias'] : NULL;
        $_locale = $this->entity->frontLocale ? : DEFAULT_LOCALE;
        $_url_alias = $this->entity->_alias;
        if ($_url_alias->exists) {
            $_generate_alias = $_request_alias;
            if ($_request_alias && ($_request_alias != $_url_alias->alias)) {
                $_generate_alias = self::generate_alias($_request_alias);
            } elseif (!$_request_alias) {
                $_generate_alias = self::generate_alias(str_replace('/', '-', $this->entity->getTranslation('title', $_locale)), $this->founder);
            }
            $_url_alias->update([
                'alias'       => render_unique_value($_generate_alias, 'url_alias', 'alias', $_locale, $_url_alias->id),
                'locale'      => $_locale,
                'sitemap'     => $_url['sitemap'] ?? 0,
                'changefreq'  => $_url['changefreq'] ?? 'monthly',
                'priority'    => $_url['priority'] ?? .5,
                'robots'      => $_url['robots'] ?? 'index, follow',
                'model_title' => $this->entity->getTranslation('title', $_locale),
            ]);
        } else {
            $_alias = is_null($_request_alias) ? Str::lower(str_replace('/', '-', $this->entity->getTranslation('title', $_locale))) : $_request_alias;
            $_generate_alias = self::generate_alias($_alias, $this->founder);
            $_save = self::fill([
                'alias'       => render_unique_value($_generate_alias, 'url_alias', 'alias', $_locale),
                'locale'      => $_locale,
                'sitemap'     => $_url['sitemap'] ?? 0,
                'changefreq'  => $_url['changefreq'] ?? 'monthly',
                'priority'    => $_url['priority'] ?? .5,
                'robots'      => $_url['robots'] ?? 'index, follow',
                'model_title' => $this->entity->getTranslation('title', $_locale),
            ]);
            $_url_alias = $this->entity->_alias()->save($_save);
        }

        return $_url_alias;
    }

    /**
     * Relationships
     */
    public function model()
    {
        return $this->morphTo()
            ->with([
                '_alias'
            ]);
    }

    //    /**
    //     * ?
    //     */
    //    public function _redirect()
    //    {
    //        return $this->hasOne(Redirect::class, 'alias_id')
    //            ->withDefault();
    //    }
}
