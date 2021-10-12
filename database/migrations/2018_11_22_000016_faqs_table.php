<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Structure\Tag;
use App\Models\Seo\UrlAlias;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FaqsTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $super_admin_permission;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'faqs_read',
                'display_name' => 'Просмотр списков FAQ',
                'group'        => 'Вопрос/Ответ',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'faqs_view',
                'display_name' => 'Просмотр FAQ',
                'group'        => 'Вопрос/Ответ',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'faqs_create',
                'display_name' => 'Создание FAQ',
                'group'        => 'Вопрос/Ответ',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'faqs_update',
                'display_name' => 'Редактирование FAQ',
                'group'        => 'Вопрос/Ответ',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'faqs_delete',
                'display_name' => 'Удаление FAQ',
                'group'        => 'Вопрос/Ответ',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('body')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->boolean('visible_title')
                ->default(1);
        });
        Schema::create('faq_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('faq_id')
                ->unsigned();
            $table->text('question');
            $table->text('answer');
            $table->tinyInteger('sort')
                ->nullable()
                ->default(0);
            $table->boolean('status')
                ->default(1);
            $table->foreign('faq_id')
                ->references('id')
                ->on('faqs')
                ->onDelete('cascade');
        });
        if (Schema::hasTable('faqs')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('faqs')) {
            Schema::table('faq_items', function (Blueprint $table) {
                $table->dropForeign('faq_items_faq_id_foreign');
                $table->drop();
            });
            Schema::dropIfExists('faqs');
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
