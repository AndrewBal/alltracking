<?php

namespace App\Libraries;

use App\Models\Components\File;
use App\Models\Seo\SearchIndex;
use App\Models\Seo\TmpMetaTags;
use App\Models\Seo\UrlAlias;
use App\Models\Structure\NodeTag;
use App\Models\Structure\Page;
use App\Models\Structure\Tag;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;

trait BaseModel
{
    use Rememberable;
    use HasTranslations;

    public $template = [];
    public $frontLocale;
    public $styleAttributes;
    public $entityEventSave = TRUE;
    public $entityEventDelete = TRUE;

    /**
     * Attributes
     */

    public function hasAttribute($attribute)
    {
        return array_key_exists($attribute, $this->attributes);
    }

    public function hasProperty($property)
    {
        return property_exists($this, $property);
    }

    public function getDashboardAttribute()
    {
        global $wrap;

        return $wrap['dashboard'] ?? FALSE;
    }

    public function getDeviceAttribute()
    {
        global $wrap;

        return $wrap['device']['type'] ?? 'pc';
    }

    public function getFrontLocaleAttribute()
    {
        global $wrap;

        return $wrap['locale'] ?? DEFAULT_LOCALE;
    }

    public function getDeviceTemplateAttribute()
    {
        global $wrap;

        return $wrap['device']['template'] ?? 'default';
    }

    public function getLastModifiedAttribute()
    {
        if ($this instanceof Page && $this->type == 'front') {
            $_last_mod = config('seo.last_modified_timestamp');
            if (!$_last_mod) $_last_mod = time();

            return Carbon::parse((int)$_last_mod)->format('D, d M Y H:i:s \G\M\T');
        } elseif (isset($this->updated_at)) return $this->updated_at->format('D, d M Y H:i:s \G\M\T');

        return Carbon::parse(config('seo.last_modified_timestamp'))->format('D, d M Y H:i:s \G\M\T');
    }

    public function getGenerateUrlAttribute()
    {
        if (array_key_exists('generate_url', $this->attributes) && $this->attributes['generate_url']) return $this->attributes['generate_url'];
        $_alias = $this->_alias;
        if (!$_alias->exists && ($this->frontLocale != DEFAULT_LOCALE)) $_alias = $this->_base_alias;
        if ($_alias->exists) {
            return _u(LaravelLocalization::getLocalizedURL($this->frontLocale, $_alias->alias));
        } else {
            return _u(LaravelLocalization::getLocalizedURL($this->frontLocale, '/'));
        }
    }

    public function getSchemaAttribute()
    {
        return NULL;
    }

    public function getHrefLangAttribute()
    {
        $_aliases = $this->_aliases()
            ->remember(REMEMBER_LIFETIME * 24)
            ->get();
        $_base_url = config('app.url');
        $_default_locale = config('app.default_locale');
        $_languages = config('laravellocalization.supportedLocales');
        if ($_aliases->isNotEmpty()) {
            $_response = collect([]);
            $_aliases->each(function ($alias) use (&$_response, $_base_url, $_default_locale, $_languages) {
                $_output = NULL;
                $_visible = $_languages[$alias->locale]['visible'] ?? 0;
                if ($_visible) {
                    if ($alias->locale == $_default_locale) {
                        if (isset($_languages[$alias->locale]['hrefLang'])) {
                            foreach ($_languages[$alias->locale]['hrefLang'] as $_code) {
                                $_output .= '<link rel="alternate" xml:lang="' . $_code . '" hreflang="' . $_code . '" href="' . $_base_url . '/' . $alias->alias . '" />';
                            }
                        }
                    } else {
                        if (isset($_languages[$alias->locale]['hrefLang'])) {
                            foreach ($_languages[$alias->locale]['hrefLang'] as $_code) {
                                $_output .= '<link rel="alternate" xml:lang="' . $_code . '" hreflang="' . $_code . '" href="' . $_base_url . '/' . $alias->locale . '/' . $alias->alias . '" />';
                            }
                        }
                    }
                    $_response->push($_output);
                }
            });
            if ($_response) return $_response->implode('');
        } elseif ($this instanceof Page && $this->type == 'front') {
            $_output = NULL;
            foreach ($_languages as $_locale => $_data) {
                $_visible = $_data['visible'] ?? 0;
                if ($_visible) {
                    if ($_locale == $_default_locale) {
                        foreach ($_data['hrefLang'] as $_code) $_output .= '<link rel="alternate" hreflang="' . $_code . '" href="' . $_base_url . '" />';
                    } else {
                        foreach ($_data['hrefLang'] as $_code) $_output .= '<link rel="alternate" hreflang="' . $_code . '" href="' . $_base_url . '/' . $_locale . '" />';
                    }
                }
            }

            return $_output;
        }

        return NULL;
    }

    public function getTranslateLinksAttribute()
    {
        $_response = NULL;
        $_aliases = $this->_aliases()
            ->remember(REMEMBER_LIFETIME)
            ->get();
        $_languages = config('laravellocalization.supportedLocales');
        $_default_locale = config('app.default_locale');
        if ($_aliases->isNotEmpty()) {
            $_response = $_aliases->keyBy('locale')->transform(function ($alias) use ($_languages, $_default_locale) {
                if ($_languages[$alias->locale]['visible']) {
                    $_language = $_languages[$alias->locale]['native'];
                    $_href = $alias->locale == $_default_locale ? "/{$alias->alias}" : "/{$alias->locale}/{$alias->alias}";

                    return [
                        'code'   => $alias->locale,
                        'active' => '<span class="' . $alias->locale . '">' . $_language . '</span>',
                        'link'   => '<a rel="alternate" class="' . $alias->locale . '" hreflang="' . $alias->locale . '" href="' . $_href . '">' . $_language . '</a>'
                    ];
                }

                return NULL;
            })->filter(function ($alias) {
                return $alias;
            });
            $_response = $_response->all();
        } elseif ($this instanceof Page && $this->type == 'front') {
            $_response = collect([]);
            foreach ($_languages as $_locale_code => $_properties) {
                if ($_properties['visible']) {
                    $_language = $_properties['native'];
                    $_href = $_locale_code == $_default_locale ? "/" : "/{$_locale_code}";
                    $_response->push([
                        'code'   => $_locale_code,
                        'active' => '<span class="' . $_locale_code . '">' . $_language . '</span>',
                        'link'   => '<a rel="alternate" class="' . $_locale_code . '" hreflang="' . $_locale_code . '" href="' . $_href . '">' . $_language . '</a>'
                    ]);
                }
            }
        }

        return $_response;
    }

    /**
     * Scope
     */
    public function scopeActive($query, $prefix = NULL, $status = 1)
    {
        $_column = $prefix ? "{$prefix}.status" : 'status';

        return $query->where($_column, $status);
    }

    public function scopeVisibleOnList($query, $prefix = NULL, $status = 1)
    {
        $_column = $prefix ? "{$prefix}.visible_on_list" : 'visible_on_list';

        return $query->where($_column, $status);
    }

    public function scopeVisibleOnBlock($query, $prefix = NULL, $status = 1)
    {
        $_column = $prefix ? "{$prefix}.visible_on_block" : 'visible_on_block';

        return $query->where($_column, $status);
    }

    public function scopeBlocked($query, $prefix = NULL, $blocked = 1)
    {
        $_column = $prefix ? "{$prefix}.blocked" : 'status';

        return $query->where($_column, $blocked);
    }

    public function scopeUsed($query, $prefix = NULL, $used = 1)
    {
        $_column = $prefix ? "{$prefix}.used" : 'status';

        return $query->where($_column, $used);
    }

    /**
     * Relationships
     */
    public function _alias()
    {
        $_front_locale = $this->frontLocale ? : DEFAULT_LOCALE;

        return $this->morphOne(UrlAlias::class, 'model')
            ->where('url_alias.locale', $_front_locale)
            ->withDefault();
    }

    public function _aliases()
    {
        return $this->morphMany(UrlAlias::class, 'model')
            ->select([
                'id',
                'model_type',
                'model_id',
                'alias',
                'locale',
            ]);
    }

    public function _base_alias()
    {
        return $this->morphOne(UrlAlias::class, 'model')
            ->where('url_alias.locale', DEFAULT_LOCALE)
            ->withDefault();
    }

    public function _preview()
    {
        return $this->hasOne(File::class, 'id', 'preview_fid')
            ->remember(REMEMBER_LIFETIME * 24 * 7);
    }

    public function _icon()
    {
        return $this->hasOne(File::class, 'id', 'icon_fid')
            ->remember(REMEMBER_LIFETIME * 24 * 7);
    }

    public function _background()
    {
        return $this->hasOne(File::class, 'id', 'background_fid')
            ->remember(REMEMBER_LIFETIME * 24 * 7);
    }

    public function _tmp_meta_tags()
    {
        return $this->morphOne(TmpMetaTags::class, 'model')
            ->withDefault();
    }

    public function _files_related()
    {
        return $this->morphToMany(File::class, 'model', 'files_related')
            ->withPivot('type');
    }

    public function _user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->withDefault();
    }

    public function _search_index()
    {
        return $this->morphMany(SearchIndex::class, 'model');
    }

    public function _tags()
    {
        return $this->morphToMany(Tag::class, 'model', 'taggables');
    }

    public function _node_tags()
    {
        return $this->morphToMany(NodeTag::class, 'model', 'node_taggables');
    }

    /**
     * Others
     */
    public function tree_parents($exclude = FALSE)
    {
        $_response = collect([]);
        if ($this->hasAttribute('parent_id')) {
            $_exclude_id = $this->id;
            $_items = self::orderBy('parent_id')
                ->when($exclude, function ($query) use ($_exclude_id) {
                    $query->where('id', '<>', $_exclude_id);
                })
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                    'parent_id'
                ])
                ->keyBy('id');
            if ($_items->isNotEmpty()) {
                $_response = collect([]);
                $_locale = App::getLocale();
                $_items->each(function ($_item) use (&$_response, $_items, $_locale) {
                    if ($_item->parent_id) return FALSE;
                    $_data = [
                        'id'         => $_item->id,
                        'parents'    => [],
                        'parents_id' => [],
                        'title'      => $_item->getTranslation('title', $_locale),
                        'entity'     => $_item
                    ];
                    $_response->put($_item->id, $_data);
                    self::tree_parents_item($_response, $_items, $_data, $_locale);
                });
                $_response = $_response->map(function ($_item) {
                    $_item['title_parent'] = $_item['parents'] ? implode(' / ', $_item['parents']) : NULL;
                    $_item['title_option'] = $_item['title_parent'] ? "{$_item['title_parent']} / {$_item['title']}" : $_item['title'];

                    return $_item;
                });
            }
        }

        return $_response;
    }

    public static function tree_parents_item(&$_response, $categories, $parent = NULL, $locale = DEFAULT_LOCALE)
    {
        $categories->each(function ($_item) use (&$_response, $categories, $parent, $locale) {
            if ($_item->parent_id == $parent['id']) {
                $_data = [
                    'id'         => $_item->id,
                    'parents'    => array_merge($parent['parents'], [
                        $parent['id'] => $parent['title']
                    ]),
                    'parents_id' => array_merge($parent['parents_id'], [$parent['id']]),
                    'title'      => $_item->getTranslation('title', $locale)
                ];
                $_response->put($_item->id, $_data);
                self::tree_parents_item($_response, $categories, $_data, $locale);
            }
        });
    }

    public function setWrap($variables = NULL)
    {
        $_wrap = app('wrap');
        if (is_array($variables) && $variables) {
            foreach ($variables as $_variable => $_data) {
                if ($_data) $_wrap->set($_variable, $_data);
            }
        }
        if($this->schema) $_wrap->setMicrodata($this->schema);
    }
}
