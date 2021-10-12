<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BannersTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'banners_read',
                'display_name' => 'Просмотр списка баннеров',
                'group'        => 'Баннера',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'banners_view',
                'display_name' => 'Просмотр баннера',
                'group'        => 'Баннера',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'banners_create',
                'display_name' => 'Создание баннера',
                'group'        => 'Баннера',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'banners_update',
                'display_name' => 'Обновление баннера',
                'group'        => 'Баннера',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'banners_delete',
                'display_name' => 'Удаление баннера',
                'group'        => 'Баннера',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('body')
                ->nullable();
            $table->string('link', 511)
                ->nullable();
            $table->string('link_attributes', 511)
                ->nullable();
            $table->string('background_fid')
                ->nullable();
            $table->string('preset')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->text('style_class')
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->boolean('hidden_title')
                ->default(0);
        });
        if (Schema::hasTable('banners')) $this->permissions();
    }

    public function down()
    {
        Schema::dropIfExists('banners');
        $this->permissions->each(function ($_permission) {
            Permission::findByName($_permission['name'])
                ->delete();
        });
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
