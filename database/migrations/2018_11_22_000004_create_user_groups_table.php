<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User\User;
use Carbon\Carbon;

class CreateUserGroupsTable extends Migration
{
    protected $permissions;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'user_groups_view',
                'display_name' => 'Просмотр группы',
                'guard_name'   => 'web',
                'group'        => 'Группа пользователя',
            ],
            [
                'name'         => 'user_groups_read',
                'display_name' => 'Просмотр списка групп пользователей',
                'guard_name'   => 'web',
                'group'        => 'Группа пользователя',
            ],
            [
                'name'         => 'user_groups_create',
                'display_name' => 'Создание группы',
                'guard_name'   => 'web',
                'group'        => 'Группа пользователя',
            ],
            [
                'name'         => 'user_groups_update',
                'display_name' => 'Редактирование группы',
                'guard_name'   => 'web',
                'group'        => 'Группа пользователя',
            ],
            [
                'name'         => 'user_groups_delete',
                'display_name' => 'Удаление группы',
                'guard_name'   => 'web',
                'group'        => 'Группа пользователя',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->float('discount')
                ->nullable()
                ->default(0);
        });
        $this->permissions();
    }

    public function down()
    {
        Schema::dropIfExists('user_groups');
        $this->permissions->each(function ($_permission) {
            Permission::findByName($_permission['name'])
                ->delete();
        });
    }

    protected function permissions()
    {
        $this->permissions->each(function ($_permission) {
            Permission::create($_permission);
        });
        $_role = Role::findByName('admin');
        $this->permissions->each(function ($_permission) use ($_role) {
            $_role->givePermissionTo($_permission['name']);
        });
    }
}
