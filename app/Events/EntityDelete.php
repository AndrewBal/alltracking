<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class EntityDelete
{
    use SerializesModels;

    public $entity;

    public function __construct($entity)
    {
        $_languages = config('laravellocalization.supportedLocales');
        $this->entity = $entity;
        $_orm_name = strtolower(class_basename($entity));
        $_roles = Role::pluck('name')->prepend('anonymous');
        $_devices = [
            'pc',
            'tablet',
            'mobile',
        ];
        $_clear_cache = NULL;
        foreach ($_devices as $_device) {
            foreach ($_roles as $_role) {
                foreach ($_languages as $_code => $_data) {
                    $_clear_cache[] = cache_key($_orm_name, $entity, $_code, $_role, $_device);
                }
            }
        }
        if ($_clear_cache) {
            foreach ($_clear_cache as $_key) {
                Cache::forget($_key);
            }
        }
    }
}
