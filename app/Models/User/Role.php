<?php

namespace App\Models\User;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\HasTranslations;

class Role extends SpatieRole
{
    use HasTranslations;

    public static $defaultGuardName = 'web';
    protected $perPage = 100;
    protected $table = 'roles';
    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
        'blocked',
    ];
    protected $attributes = [
        'id'           => NULL,
        'name'         => NULL,
        'display_name' => NULL,
        'guard_name'   => NULL,
        'blocked'      => 0,
    ];
    protected $hidden = [
        'guard_name'
    ];
    public $translatable = [
        'display_name'
    ];

    public function __construct()
    {
        $this->entityEventSave = FALSE;
        $this->entityEventDelete = FALSE;
    }

    public function getCountUsersAttribute()
    {
        return DB::table('model_has_roles')
            ->where('role_id', $this->id)
            ->count();
    }
}
