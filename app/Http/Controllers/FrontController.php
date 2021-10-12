<?php

namespace App\Http\Controllers;

use App\Models\Seo\UrlAlias;
use App\Models\Structure\Node;
use App\Models\Structure\Page;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewClass;

class FrontController extends Controller
{
    use Authorizable;

    public function __construct()
    {
        parent::__construct();
    }

    public function home(Request $request)
    {
        global $wrap;
        $_locale = $wrap['locale'] ?? DEFAULT_LOCALE;
        $_locale = config("laravellocalization.supportedLocales.{$_locale}");
        $_others = NULL;
        $_response = NULL;
        $_item = Page::where('type', 'front')
            ->with([
                '_alias'
            ])
            ->remember(REMEMBER_LIFETIME * 24 * 7)
            ->first();
        $_item = $_item->_render();
        $_deliveries = Node::getDeliveries();
        $_others['deliveries'] = json_encode($_deliveries['deliveries']);
        $_others['alphabet'] = json_encode($_deliveries['alphabet']);
        if ($_item) {
            $_response = View::first($_item->template, compact('_item'));
        }

        return $this->response($_response, $_others);

    }

    public function path(Request $request, $path)
    {
        $_item = NULL;
        $_others = NULL;
        $_response = NULL;
        $_alias = format_alias($request);
        $_locale = app()->getLocale();
        $_current_alias = UrlAlias::with([
            'model'
        ])
            ->where(function ($query) use ($_locale, $_alias) {
                $query->where('alias', $_alias)
                    ->where('locale', $_locale);
            })
            ->when(($_locale != DEFAULT_LOCALE), function ($query) use ($_locale, $_alias) {
                $query->orWhere(function ($query) use ($_alias) {
                    $query->where('alias', $_alias)
                        ->where('locale', DEFAULT_LOCALE);
                })
                    ->orderByRaw('CASE WHEN locale = \'' . $_locale . '\' THEN 0 ELSE 1 END');
            })
            ->first();
        if ($_current_alias) {
            if ($_current_alias->model) {
                wrap()->set('seo.model_alias', $_current_alias);
                wrap()->set('eloquent', $_current_alias->model);
                wrap()->set('page.style_class', 'not-front');
                if ($request->isMethod('GET')) {
                    $_item = $_current_alias->model->_render();
                    if ($_item && $_item->redirect) return redirect()->to($_item->redirect);
                    if ($_item) $_response = View::first($_item->template, compact('_item'));
                } elseif ($request->ajax()) {
                    $_response = $_current_alias->model->_render_ajax($request);
                }
            }
        }

        return $this->response($_response, $_others);
    }

    public function response($entity, $others = NULL)
    {
        $wrap = app('wrap')->render();
        $_response = NULL;
        if ($entity) {
            $_others = $others;
            if (is_a($entity, ViewClass::class)) {
                $_response = $entity->with(compact('_others', 'wrap'))
                    ->render(function ($view, $content) {
                        return USE_COMPRESS ? clear_html($content) : $content;
                    });
            } else {
                $_response = response($entity, 200);
            }

            return $_response;
        }
        abort(404);
    }
}
