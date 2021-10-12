<?php

namespace App\Models\User;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    const DEFAULT_GUARD = 'web';
    protected $entity;
    protected $bundle;
    protected $table;
    public $status = 0;
    protected $perPage = 100;
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group',
    ];
    protected $attributes = [
        'id'           => NULL,
        'name'         => NULL,
        'display_name' => NULL,
        'description'  => NULL,
        'guard_name'   => NULL,
        'group'        => 0,
    ];
    protected $hidden = [
        'guard_name'
    ];

    public function __construct($entity = NULL, $bundle = NULL)
    {
        $this->table = config('permission.table_names.permissions');
        $this->entity = $entity;
        $this->bundle = $bundle;
    }

    public function access()
    {
        if ($this->entity) {
            $_permission_name = "view_{$this->bundle}_{$this->entity->id}";
            $_permission = NULL;
            if (self::whereName($_permission_name)
                ->first()) {
                $_permission = self::findByName($_permission_name);
            }
            if ($_access = request()->input('access_roles')) {
                if (!$_permission) {
                    $_permission = \Spatie\Permission\Models\Permission::create([
                        'name'         => $_permission_name,
                        'display_name' => trans("common::others.permission_view_{$this->bundle}", ['object' => $this->entity->title]),
                        'guard_name'   => 'web',
                    ]);
                }
                if ($_permission) {
                    $_role = Role::findByName('super_admin');
                    if (!$_role->hasPermissionTo($_permission_name)) $_role->givePermissionTo($_permission_name);
                    foreach ($_access as $_role_name => $value) {
                        $_role = Role::findByName($_role_name);
                        if (!$_role->hasPermissionTo($_permission_name)) $_role->givePermissionTo($_permission_name);
                    }
                }
                $this->status = 1;
            } elseif ($_permission) {
                $_permission->delete();
            }
            $this->entity->access = $this->status;
            $this->entity->save();
        }
    }

    public function clear()
    {
        if ($this->entity) {
            $_permission_name = "view_{$this->bundle}_{$this->entity->id}";
            if (self::whereName($_permission_name)
                ->first()) {
                self::findByName($_permission_name)
                    ->delete();
            }
        }
    }
}
