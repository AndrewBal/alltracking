<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

class CreateRolesAndPermissionTables extends Migration
{
    protected $table;
    protected $roles;
    protected $permissions;
    protected $super_admin_permission;
    protected $user_permission;

    public function __construct()
    {
        $this->table = config('permission.table_names');
        $this->roles = collect([
            [
                'name'         => 'admin',
                'display_name' => json_encode([DEFAULT_LOCALE => 'Администратор']),
                'blocked'      => 1
            ],
            [
                'name'         => 'user',
                'display_name' => json_encode([DEFAULT_LOCALE => 'Пользователь']),
                'blocked'      => 1
            ]
        ]);
        $this->permissions = collect([
            [
                'name'         => 'access_dashboard',
                'display_name' => 'Доступ в "Панель администрирования"',
                'guard_name'   => 'web',
                'group'        => 'Доступ',
            ],
            [
                'name'         => 'access_personal',
                'display_name' => 'Доступ в "Личный кабинет" других пользователей',
                'guard_name'   => 'web',
                'group'        => 'Доступ',
            ],
            [
                'name'         => 'roles_read',
                'display_name' => 'Просмотр списка ролей',
                'guard_name'   => 'web',
                'group'        => 'Роль пользователя',
            ],
            [
                'name'         => 'roles_view',
                'display_name' => 'Просмотр роли',
                'guard_name'   => 'web',
                'group'        => 'Роль пользователя',
            ],
            [
                'name'         => 'roles_create',
                'display_name' => 'Создание роли',
                'guard_name'   => 'web',
                'group'        => 'Роль пользователя',
            ],
            [
                'name'         => 'roles_update',
                'display_name' => 'Редактирование роли',
                'guard_name'   => 'web',
                'group'        => 'Роль пользователя',
            ],
            [
                'name'         => 'roles_delete',
                'display_name' => 'Удаление роли',
                'guard_name'   => 'web',
                'group'        => 'Роль пользователя',
            ],
            [
                'name'         => 'roles_assignment',
                'display_name' => 'Назначения роли другому пользователю',
                'guard_name'   => 'web',
                'group'        => 'Роли пользовател',
            ],
            [
                'name'         => 'settings',
                'display_name' => 'Управление настройками',
                'guard_name'   => 'web',
                'group'        => 'Прочее',
            ],
            [
                'name'         => 'variables',
                'display_name' => 'Управление переменными',
                'guard_name'   => 'web',
                'group'        => 'Прочее',
            ],
        ]);
    }

    public function up()
    {
        $_table = $this->table;
        Schema::create($_table['permissions'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name', 1023);
            $table->string('group')
                ->nullable();
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create($_table['roles'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name');
            $table->string('guard_name');
            $table->boolean('blocked')
                ->default(0);
            $table->timestamps();
        });
        Schema::create($_table['model_has_permissions'], function (Blueprint $table) use ($_table) {
            $table->integer('permission_id')
                ->unsigned();
            $table->morphs('model');
            $table->foreign('permission_id')
                ->references('id')
                ->on($_table['permissions'])
                ->onDelete('cascade');
            $table->primary([
                'permission_id',
                'model_id',
                'model_type'
            ]);
        });
        Schema::create($_table['model_has_roles'], function (Blueprint $table) use ($_table) {
            $table->integer('role_id')
                ->unsigned();
            $table->morphs('model');
            $table->foreign('role_id')
                ->references('id')
                ->on($_table['roles'])
                ->onDelete('cascade');
            $table->primary([
                'role_id',
                'model_id',
                'model_type'
            ]);
        });
        Schema::create($_table['role_has_permissions'], function (Blueprint $table) use ($_table) {
            $table->integer('permission_id')
                ->unsigned();
            $table->integer('role_id')
                ->unsigned();
            $table->foreign('permission_id')
                ->references('id')
                ->on($_table['permissions'])
                ->onDelete('cascade');
            $table->foreign('role_id')
                ->references('id')
                ->on($_table['roles'])
                ->onDelete('cascade');
            $table->primary([
                'permission_id',
                'role_id'
            ]);
            app('cache')->forget('spatie.permission.cache');
        });
        if (Schema::hasTable($_table['permissions']) && Schema::hasTable($_table['roles']) && Schema::hasTable($_table['role_has_permissions']) && Schema::hasTable($_table['model_has_roles']) && Schema::hasTable($_table['model_has_permissions'])) {
            $this->permissions();
        }
    }

    public function down()
    {
        $_table = $this->table;
        Schema::dropIfExists($_table['role_has_permissions']);
        Schema::dropIfExists($_table['model_has_roles']);
        Schema::dropIfExists($_table['model_has_permissions']);
        Schema::dropIfExists($_table['roles']);
        Schema::dropIfExists($_table['permissions']);
    }

    protected function permissions()
    {
        $this->roles->each(function ($_role) {
            Role::create($_role);
        });
        $this->permissions->each(function ($_permission) {
            Permission::create($_permission);
        });
        $_role = Role::findByName('admin');
        $_role->syncPermissions($this->permissions->keyBy('name')->keys());
    }
}
