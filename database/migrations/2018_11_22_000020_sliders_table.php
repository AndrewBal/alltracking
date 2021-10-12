<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class slidersTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'sliders_read',
                'display_name' => 'Просмотр списка слайдеров',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'sliders_view',
                'display_name' => 'Просмотр слайдера',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'sliders_create',
                'display_name' => 'Создание слайдера',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'sliders_update',
                'display_name' => 'Редактирование слайдера',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'sliders_delete',
                'display_name' => 'Удаление слайдера',
                'group'        => 'Слайдеры',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('options')
                ->nullable();

            $table->string('preset')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->boolean('status')
                ->default(1);
        });
        Schema::create('slider_items', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->integer('slider_id')
                ->unsigned()
                ->nullable();
            $table->integer('background_fid')
                ->unsigned()
                ->nullable();
            $table->text('body')
                ->nullable();
            $table->string('link', 511)
                ->nullable();
            $table->string('link_attributes', 511)
                ->nullable();
            $table->tinyInteger('sort')
                ->default(0);
            $table->boolean('status')
                ->default(1);
            $table->boolean('hidden_title')
                ->default(0);
            $table->foreign('background_fid')
                ->references('id')
                ->on('files_managed')
                ->onDelete('set null');
            $table->foreign('slider_id')
                ->references('id')
                ->on('sliders')
                ->onDelete('set null');
        });
        if (Schema::hasTable('sliders')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('sliders')) {
            Schema::table('slider_items', function (Blueprint $table) {
                $table->dropForeign('slider_items_slider_id_foreign');
                $table->drop();
            });
            Schema::dropIfExists('sliders');
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
