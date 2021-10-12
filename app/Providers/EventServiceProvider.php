<?php

namespace App\Providers;

use App\Events\EntityDelete;
use App\Events\EntitySave;
use App\Listeners\EntityDeleteProcessing;
use App\Listeners\EntitySaveProcessing;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class   => [
            SendEmailVerificationNotification::class,
        ],
        EntitySave::class   => [
            EntitySaveProcessing::class
        ],
        EntityDelete::class => [
            EntityDeleteProcessing::class
        ]
    ];

    public function boot()
    {
        //
    }
}
