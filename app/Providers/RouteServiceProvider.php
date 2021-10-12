<?php

namespace App\Providers;

use App\Models\Form\Forms;
use App\Models\Components\Advantage;
use App\Models\Components\Banner;
use App\Models\Components\Block;
use App\Models\Components\File;
use App\Models\Components\Gallery;
use App\Models\Components\Menu;
use App\Models\Components\Slider;
use App\Models\Components\Variable;
use App\Models\Components\Faq;
use App\Models\Form\FormsData;
use App\Models\Structure\Node;
use App\Models\Structure\NodeTag;
use App\Models\Structure\Page;
use App\Models\Structure\Tag;
use App\Models\User\Group;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';
    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        $this->configureRateLimiting();
        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
            Route::prefix('callback')
                ->middleware([
                    'web',
                    'ajaxVariables',
                ])
                ->namespace("{$this->namespace}\Callbacks")
                ->group(base_path('routes/callback.php'));
            Route::prefix('oleus')
                ->middleware([
                    'web',
                    'auth',
                    'permission:access_dashboard'
                ])
                ->namespace("{$this->namespace}\Dashboard")
                ->group(base_path('routes/dashboard.php'));
            Route::middleware([
                'web',
                'localize',
                'localizationRedirect',
                'localeViewPath',
            ])
                ->prefix(LaravelLocalization::setLocale())
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
        Route::pattern('id', '[0-9]+');
        Route::pattern('key', '[0-9A-z]+');
        Route::pattern('action', '(add|edit|create|clear|save|update|delete|remove|destroy)');
        Route::pattern('page_number', 'page-[0-9]+');
        Route::model('file', File::class);
        Route::model('user', User::class);
        Route::model('role', Role::class);
        Route::model('group', Group::class);
        Route::model('variable', Variable::class);
        Route::model('page', Page::class);
        Route::model('node', Node::class);
        Route::model('tag', Tag::class);
        Route::model('node_tag', NodeTag::class);
        Route::model('faq', Faq::class);
        Route::model('block', Block::class);
        Route::model('advantage', Advantage::class);
        Route::model('banner', Banner::class);
        Route::model('slider', Slider::class);
        Route::model('gallery', Gallery::class);
        Route::model('menu', Menu::class);
        Route::model('form', Forms::class);
        Route::model('forms_datum', FormsData::class);
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by(optional($request->user())->id ? : $request->ip());
        });
    }
}
