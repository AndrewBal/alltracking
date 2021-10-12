<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdvantagesTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'advantages_read',
                'display_name' => 'Просмотр списка преимуществ',
                'group'        => 'Преимущества',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'advantages_view',
                'display_name' => 'Просмотр преимущества',
                'group'        => 'Преимущества',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'advantages_create',
                'display_name' => 'Создание преимущества',
                'group'        => 'Преимущества',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'advantages_update',
                'display_name' => 'Обновление преимущества',
                'group'        => 'Преимущества',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'advantages_delete',
                'display_name' => 'Удаление преимущества',
                'group'        => 'Преимущества',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('advantages', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->integer('background_fid')
                ->unsigned()
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->longText('body')
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->boolean('hidden_title')
                ->default(0);
            $table->foreign('background_fid')
                ->references('id')
                ->on('files_managed')
                ->onDelete('set null');
        });
        Schema::create('advantage_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('advantage_id')
                ->unsigned()
                ->nullable();
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->integer('icon_fid')
                ->unsigned()
                ->nullable();
            $table->longText('body')
                ->nullable();
            $table->tinyInteger('sort')
                ->default(0);
            $table->boolean('status')
                ->default(1);
            $table->boolean('hidden_title')
                ->default(0);
            $table->foreign('advantage_id')
                ->references('id')
                ->on('advantages')
                ->onDelete('cascade');
            $table->foreign('icon_fid')
                ->references('id')
                ->on('files_managed')
                ->onDelete('set null');
        });
        if (Schema::hasTable('advantages')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('advantages')) {
            Schema::table('advantage_items', function (Blueprint $table) {
                $table->dropForeign('advantage_items_advantage_id_foreign');
                $table->dropForeign('advantage_items_icon_fid_foreign');
                $table->drop();
            });
            Schema::table('advantages', function (Blueprint $table) {
                $table->dropForeign('advantages_background_fid_foreign');
                $table->drop();
            });
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
