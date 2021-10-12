<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Structure\Tag;
use App\Models\Seo\SearchIndex;
use App\Models\Components\FilesReference;
use App\Models\Seo\UrlAlias;


class NodesTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $super_admin_permission;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'nodes_read',
                'display_name' => 'Просмотр списка материалов',
                'group'        => 'Материал',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'nodes_view',
                'display_name' => 'Просмотр материала',
                'group'        => 'Материал',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'nodes_create',
                'display_name' => 'Создание материала',
                'group'        => 'Материал',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'nodes_update',
                'display_name' => 'Редактирование материала',
                'group'        => 'Материал',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'nodes_delete',
                'display_name' => 'Удаление материала',
                'group'        => 'Материал',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')
                ->nullable()
                ->unsigned();
            $table->integer('page_id')
                ->nullable()
                ->unsigned();
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->text('breadcrumb_title')
                ->nullable();
            $table->text('teaser')
                ->nullable();
            $table->longText('body')
                ->nullable();
            $table->integer('preview_fid')
                ->unsigned()
                ->nullable();
            $table->text('meta_title')
                ->nullable();
            $table->text('meta_keywords')
                ->nullable();
            $table->text('meta_description')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->tinyInteger('sort')
                ->default(0);
            $table->boolean('status')
                ->default(1);
            $table->boolean('visible_on_list')
                ->default(1);
            $table->boolean('visible_on_block')
                ->default(1);
            $table->timestamp('published_at')
                ->useCurrent();
            $table->timestamps();
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
            $table->foreign('preview_fid')
                ->references('id')
                ->on('files_managed')
                ->onDelete('set null');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
        if (Schema::hasTable('nodes')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('nodes')) {
            Schema::table('nodes', function (Blueprint $table) {
                $table->dropForeign('nodes_page_id_foreign');
                $table->dropForeign('nodes_preview_fid_foreign');
                $table->dropForeign('nodes_user_id_foreign');
                $table->drop();
            });
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
            if (Schema::hasTable('taggables')) {
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
