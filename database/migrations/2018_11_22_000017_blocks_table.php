<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BlocksTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'blocks_read',
                'display_name' => 'Просмотр списка блоков',
                'group'        => 'Блоки',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'blocks_view',
                'display_name' => 'Просмотр блока',
                'group'        => 'Блоки',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'blocks_create',
                'display_name' => 'Создание блока',
                'group'        => 'Блоки',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'blocks_update',
                'display_name' => 'Редактирование блока',
                'group'        => 'Блоки',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'blocks_delete',
                'display_name' => 'Удаление блока',
                'group'        => 'Блоки',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->longText('body');
            $table->integer('background_fid')
                ->unsigned()
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
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
        if (Schema::hasTable('blocks')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('blocks')) {
            Schema::table('blocks', function (Blueprint $table) {
                $table->dropForeign('blocks_background_fid_foreign');
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
