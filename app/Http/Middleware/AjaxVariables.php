<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class AjaxVariables
{
    public function handle($request, Closure $next, $guard = NULL)
    {
        $_device = $request->header('device', 'pc');
        $_locale = $request->header('locale', DEFAULT_LOCALE);
        wrap()->set('locale', $_locale);
        wrap()->set('device.type', $_device);
        App::setLocale($_locale);

        return $next($request);
    }
}
