<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Structure\Tag;
use App\Models\Seo\TmpMetaTags;
use App\Models\Seo\SearchIndex;
use App\Models\Components\FilesReference;
use App\Models\Seo\UrlAlias;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TagsTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $super_admin_permission;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'tags_read',
                'display_name' => 'Просмотр страниц тегов',
                'group'        => 'Страница тега',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'tags_view',
                'display_name' => 'Просмотр страницы тега',
                'group'        => 'Страница тега',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'tags_create',
                'display_name' => 'Создание страницы тега',
                'group'        => 'Страница тега',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'tags_update',
                'display_name' => 'Редактирование страницы тега',
                'group'        => 'Страница тега',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'tags_delete',
                'display_name' => 'Удаление страницы тега',
                'group'        => 'Страница тега',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->text('breadcrumb_title')
                ->nullable();
            $table->longText('body')
                ->nullable();
            $table->integer('parent_id')
                ->unsigned()
                ->nullable();
            $table->string('meta_title')
                ->nullable();
            $table->text('meta_keywords')
                ->nullable();
            $table->text('meta_description')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->tinyInteger('sort')
                ->default(0);
            $table->timestamps();
            $table->foreign('parent_id')
                ->references('id')
                ->on('tags')
                ->onDelete('set null');
        });
        Schema::create('taggables', function (Blueprint $table) {
            $table->integer('tag_id')
                ->unsigned()
                ->nullable();
            $table->morphs('model');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onDelete('cascade');
        });
        if (Schema::hasTable('tags')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('tags')) {
            Schema::table('taggables', function (Blueprint $table) {
                $table->dropForeign('taggables_tag_id_foreign');
                $table->drop();
            });
            Schema::table('tags', function (Blueprint $table) {
                $table->dropForeign('tags_parent_id_foreign');
                $table->drop();
            });
            if (Schema::hasTable('tmp_meta_tags')) {
                TmpMetaTags::where('model_type', Tag::class)
                    ->delete();
            }
            if (Schema::hasTable('files_related')) {
                FilesReference::where('model_type', Tag::class)
                    ->delete();
            }
            if (Schema::hasTable('search_index')) {
                SearchIndex::where('model_type', Tag::class)
                    ->delete();
            }
            if (Schema::hasTable('url_alias')) {
                UrlAlias::where('model_type', Tag::class)
                    ->delete();
            }
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
