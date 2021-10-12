<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $_wrap = app('wrap')->_load($request);
            $_dashboard = $_wrap['page']['is_dashboard'];
            if (!$_dashboard) {
                wrap()->set('page.scripts', config('frontend.scripts'));
                wrap()->set('page.styles', config('frontend.styles'));
            }
            if ($_wrap['user']) {
                $_access_edit = [
                    'page'      => $_wrap['user']->hasAllPermissions('pages_update'),
                    'node'      => $_wrap['user']->hasAllPermissions('nodes_update'),
                    'tag'       => $_wrap['user']->hasAllPermissions('tags_update'),
                    'faq'       => $_wrap['user']->hasAllPermissions('faqs_update'),
                    'block'     => $_wrap['user']->hasAllPermissions('blocks_update'),
                    'banner'    => $_wrap['user']->hasAllPermissions('banners_update'),
                    'advantage' => $_wrap['user']->hasAllPermissions('advantages_update'),
                    'menu'      => $_wrap['user']->hasAllPermissions('menus_update'),
                    'slider'    => $_wrap['user']->hasAllPermissions('sliders_update'),
                    'gallery'   => $_wrap['user']->hasAllPermissions('galleries_update'),
                ];
            }
            View::share([
                'authUser'       => $_wrap['user'],
                'locale'         => $_wrap['locale'],
                'deviceType'     => $_wrap['device']['type'],
                'deviceTemplate' => $_wrap['device']['template'],
                'accessEdit'     => $_access_edit ?? [],
                'alert'          => Session::get('alert'),
            ]);

            return $next($request);
        });
    }
}
