<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class galleriesTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'galleries_read',
                'display_name' => 'Просмотр списка галлерей',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'galleries_view',
                'display_name' => 'Просмотр геллереи',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'galleries_create',
                'display_name' => 'Создание геллереи',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'galleries_update',
                'display_name' => 'Редактирование геллереи',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'galleries_delete',
                'display_name' => 'Удаление геллереи',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->string('preset_preview')
                ->nullable();
            $table->string('preset_full')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->boolean('hidden_title')
                ->default(0);
            $table->boolean('status')
                ->default(1);
        });
        if (Schema::hasTable('galleries')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('galleries')) {
            Schema::dropIfExists('galleries');
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
