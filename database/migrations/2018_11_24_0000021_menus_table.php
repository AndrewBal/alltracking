<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenusTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $super_admin_permission;
    protected $user_permission;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'menus_read',
                'display_name' => 'Просмотр списка меню',
                'group'        => 'Меню',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'menus_view',
                'display_name' => 'Просмотр меню',
                'group'        => 'Меню',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'menus_create',
                'display_name' => 'Создание меню',
                'group'        => 'Меню',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'menus_update',
                'display_name' => 'Редактирование меню',
                'group'        => 'Меню',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'menus_delete',
                'display_name' => 'Удаление меню',
                'group'        => 'Меню',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->boolean('status')
                ->default(1);
            $table->string('style_id', 255)
                ->nullable();
            $table->string('style_class', 255)
                ->nullable();
        });
        Schema::create('menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_id')
                ->unsigned();
            $table->integer('parent_id')
                ->unsigned()
                ->nullable();
            $table->integer('alias_id')
                ->unsigned()
                ->nullable();
            $table->integer('icon_fid')
                ->unsigned()
                ->nullable();
            $table->string('link')
                ->nullable();
            $table->string('anchor')
                ->nullable();
            $table->string('title');
            $table->string('sub_title')
                ->nullable();
            $table->tinyInteger('sort')
                ->default(0);
            $table->text('data');
            $table->boolean('status')
                ->default(1);
            $table->foreign('menu_id')
                ->references('id')
                ->on('menus')
                ->onDelete('cascade');
            $table->foreign('alias_id')
                ->references('id')
                ->on('url_alias')
                ->onDelete('cascade');
            $table->foreign('parent_id')
                ->references('id')
                ->on('menu_items')
                ->onDelete('set null');
            $table->foreign('icon_fid')
                ->references('id')
                ->on('files_managed')
                ->onDelete('set null');
        });
        if (Schema::hasTable('menus')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('menus')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropForeign('menu_items_menu_id_foreign');
                $table->dropForeign('menu_items_alias_id_foreign');
                $table->dropForeign('menu_items_parent_id_foreign');
                $table->dropForeign('menu_items_icon_fid_foreign');
                $table->drop();
            });
            Schema::dropIfExists('menus');
            $this->permissions->each(function ($_permission) {
                Permission::findByName($_permission['name'])
                    ->delete();
            });
        }
    }

    public function permissions()
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
