<?php

namespace App\Models\Seo;

use App\Libraries\BaseModel;
use App\Models\Seo\UrlAlias;
use App\Models\Structure\Node;
use App\Models\Structure\Page;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SiteMap extends Model
{
    use BaseModel;

    protected $languages;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function treeRender()
    {
        $_locale = app('wrap')->getLocale();
        $_response = [
            'output' => '<ul class="uk-list">',
            'items'  => collect([]),
        ];
        if ($_page_front = Page::where('type', 'front')
            ->active()
            ->first()) {
            $_item = [
                'name'  => $_page_front->getTranslation('title', $_locale),
                'url'   => $_page_front->generate_url,
                'items' => collect([])
            ];
            $_response['items']->push($_item);
            $_response['output'] .= "<li><a href='{$_item['url']}' title='{$_item['name']}'>{$_item['name']}</a></li>";
        }
        $_url_items = UrlAlias::where('model_type', 'not like', '%Node')
            ->with([
                'model'
            ])
            ->where('sitemap', 1)
            ->where('locale', $_locale)
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->get([
                'id',
                'model_id',
                'model_type',
            ]);
        if ($_url_items->isNotEmpty()) {
            $_url_items = $_url_items->groupBy('model_type');
            $_url_items->map(function ($_aliases, $_model_type) use (&$_response, $_locale) {
                $_class_name = strtolower(class_basename($_model_type));
                $_aliases->map(function ($_alias) use (&$_response, $_class_name, $_locale) {
                    $_model = $_alias->model;
                    switch ($_class_name) {
                        case 'page':
                        case 'tag':
                            if ($_class_name == 'page' && $_model->no_used || !$_model->status) break;
                            $_model_items = [
                                'output' => NULL,
                                'items'  => collect([]),
                            ];
                            $_nodes = $_model->_nodes()
                                ->get([
                                    'id',
                                    'title',
                                ]);
                            if ($_nodes->isNotEmpty()) {
                                $_model_items['output'] = '<ul class="uk-list">';
                                $_nodes->map(function ($_node) use (&$_model_items, $_locale) {
                                    $_item = [
                                        'name'  => $_node->getTranslation('title', $_locale),
                                        'url'   => $_node->generate_url,
                                        'items' => $_model_items
                                    ];
                                    $_model_items['items']->push($_item);
                                    $_model_items['output'] .= "<li><a href='{$_item['url']}' title='{$_item['name']}'>{$_item['name']}</a></li>";
                                });
                                $_model_items['output'] .= '</ul>';
                            }
                            $_item = [
                                'name'  => $_model->getTranslation('title', $_locale),
                                'url'   => $_model->generate_url,
                                'items' => $_model_items
                            ];
                            $_response['items']->push($_item);
                            $_response['output'] .= "<li><a href='{$_item['url']}' title='{$_item['name']}'>{$_item['name']}</a>{$_model_items['output']}</li>";
                            break;
                        default:
                            if (!$_model->status) break;
                            $_item = [
                                'name'  => $_model->getTranslation('title', $_locale),
                                'url'   => $_model->generate_url,
                                'items' => collect([])
                            ];
                            $_response['items']->push($_item);
                            $_response['output'] .= "<li><a href='{$_item['url']}' title='{$_item['name']}'>{$_item['name']}</a></li>";
                            break;
                    }
                });
            });
        }
        $_response['output'] .= '</ul>';

        return collect($_response);
    }

    public static function services_tree_render($relations, $parent = NULL)
    {
        $_response = collect([]);
        if ($relations->has($parent)) {
            $relations->get($parent)->map(function ($r1) use (&$_response, $relations, $parent) {
                $_response->push([
                    'name'  => $r1->title,
                    'url'   => $r1->generate_url,
                    'items' => self::services_tree_render($relations, $r1->id)
                ]);
            });
        }

        return $_response;
    }

    public static function _list($full = TRUE)
    {
        $_items = collect([]);
        $_default_locale = config('app.default_locale');
        $_languages = config('laravellocalization.supportedLocales');
        $_last_modified_timestamp = (int)config('seo.last_modified_timestamp');
        if (!$_last_modified_timestamp) $_last_modified_timestamp = time();
        $_last_modified_timestamp = Carbon::parse($_last_modified_timestamp)->format('c');
        $_page_fronts = Page::where('type', 'front')
            ->active()
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->first([
                'title',
                'updated_at'
            ]);
        if ($_page_fronts) {
            $_page_fronts->frontLocale = $_default_locale;
            $_items->push([
                'name'          => $_page_fronts->getTranslation('title', $_default_locale),
                'url'           => $_page_fronts->generate_url,
                'last_modified' => $_last_modified_timestamp,
                'items'         => NULL,
                'changefreq'    => 'always',
                'priority'      => 0.5,
            ]);
            if (USE_MULTI_LANGUAGE && count($_languages) > 1) {
                foreach ($_languages as $_locale => $_data) {
                    if ($_locale != $_default_locale && $_data['visible']) {
                        $_page_fronts->frontLocale = $_locale;
                        $_items->push([
                            'name'          => $_page_fronts->getTranslation('title', $_locale),
                            'url'           => $_page_fronts->generate_url,
                            'last_modified' => $_last_modified_timestamp,
                            'items'         => NULL,
                            'changefreq'    => 'always',
                            'priority'      => 0.5,
                        ]);
                    }
                }
            }
        }
        if ($full) {
            $_url_items = UrlAlias::with([
                'model'
            ])
                ->where('sitemap', 1)
                ->remember(REMEMBER_LIFETIME * 24 * 7)
                ->get([
                    'id',
                    'alias',
                    'locale',
                    'model_id',
                    'model_type',
                    'changefreq',
                    'priority',
                ]);
            if ($_url_items->isNotEmpty()) {
                $_url_items = $_url_items->groupBy('model_type');
                $_url_items->map(function ($aliases) use (&$_items, $_default_locale, $_languages) {
                    $aliases->map(function ($alias) use (&$_items, $_default_locale, $_languages) {
                        if (($_model = $alias->model) && (($_model->hasAttribute('status') && $_model->status) || !$_model->hasAttribute('status'))) {
                            if (isset($_languages[$alias->locale])) {
                                $_items->push([
                                    'name'          => $_model->getTranslation('title', $alias->locale),
                                    'url'           => _u(LaravelLocalization::getLocalizedURL($alias->locale, $alias->alias)),
                                    'last_modified' => $_model->updated_at ? $_model->updated_at->format('c') : NULL,
                                    'items'         => NULL,
                                    'changefreq'    => $alias->changefreq,
                                    'priority'      => $alias->priority,
                                ]);
                            }
                        }
                    });
                });
            }
        }

        return $_items;
    }

    public static function _renderXML($index = NULL)
    {
        $_items = collect([]);
        $xmlDom = new \DOMDocument("1.0", "utf-8");
        if ($index) {
            $_parse = explode('-', $index);
            if (isset($_parse[0])) {
                switch ($_parse[0]) {
                    default:
                        $_items = self::_list();
                        break;
                }
            }
            $urlSet = $xmlDom->createElement('urlset');
            $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
            $urlSet = $xmlDom->appendChild($urlSet);
            if ($_items) {
                $_base_url = trim(config('app.url'), '/');
                $_items->map(function ($item) use (&$urlSet, $xmlDom, $_base_url) {
                    $url = $xmlDom->createElement('url');
                    $url = $urlSet->appendChild($url);
                    $loc = $xmlDom->createElement('loc');
                    $loc = $url->appendChild($loc);
                    $loc->appendChild($xmlDom->createTextNode($_base_url . $item['url']));
                    $lastmod = $xmlDom->createElement('lastmod');
                    $lastmod = $url->appendChild($lastmod);
                    $lastmod->appendChild($xmlDom->createTextNode($item['last_modified']));
                    if (isset($item['changefreq'])) {
                        $changefreq = $xmlDom->createElement('changefreq');
                        $changefreq = $url->appendChild($changefreq);
                        $changefreq->appendChild($xmlDom->createTextNode($item['changefreq']));
                    }
                    if (isset($item['priority'])) {
                        $priority = $xmlDom->createElement('priority');
                        $priority = $url->appendChild($priority);
                        $priority->appendChild($xmlDom->createTextNode($item['priority']));
                    }
                });
            }
        } else {
            $_last_modified_timestamp = (int)config('seo.last_modified_timestamp');
            if (!$_last_modified_timestamp) $_last_modified_timestamp = time();
            $_items->push([
                'name'          => NULL,
                'url'           => _u("sitemap-general.xml"),
                'last_modified' => Carbon::parse($_last_modified_timestamp)->format('c'),
                'items'         => NULL
            ]);
            $urlSet = $xmlDom->createElement('sitemapindex');
            $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $urlSet = $xmlDom->appendChild($urlSet);
            if ($_items) {
                $_base_url = trim(config('app.url'), '/');
                $_items->map(function ($item) use (&$urlSet, $xmlDom, $_base_url) {
                    $url = $xmlDom->createElement('sitemap');
                    $url = $urlSet->appendChild($url);
                    $loc = $xmlDom->createElement('loc');
                    $loc = $url->appendChild($loc);
                    $loc->appendChild($xmlDom->createTextNode($_base_url . $item['url']));
                    $lastmod = $xmlDom->createElement('lastmod');
                    $lastmod = $url->appendChild($lastmod);
                    $lastmod->appendChild($xmlDom->createTextNode($item['last_modified']));
                    if (isset($item['changefreq'])) {
                        $changefreq = $xmlDom->createElement('changefreq');
                        $changefreq = $url->appendChild($changefreq);
                        $changefreq->appendChild($xmlDom->createTextNode($item['changefreq']));
                    }
                    if (isset($item['priority'])) {
                        $priority = $xmlDom->createElement('priority');
                        $priority = $url->appendChild($priority);
                        $priority->appendChild($xmlDom->createTextNode($item['priority']));
                    }
                });
            }
        }
        $xmlDom->formatOutput = TRUE;
        $siteMapXML = $xmlDom->saveXML();
        header('Content-Type: text/xml; charset=UTF-8', TRUE, 200);
        echo $siteMapXML;
        exit;
    }
}
