<?php

namespace App\Providers;

use App\Libraries\IdentifyDevice;
use App\Libraries\Wrap;
use App\Rules\NotInArray;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        require_once(app_path('Helpers/Define.php'));
        $this->app->singleton('device', function ($app) {
            return new IdentifyDevice();
        });
        $this->app->singleton('wrap', function ($app) {
            return new Wrap();
        });
    }

    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('unique_in_array', 'App\Validators\Validations@uniqueInArray');
        Validator::extend('reCaptcha', 'App\Validators\Validations@reCaptcha');
    }
}
