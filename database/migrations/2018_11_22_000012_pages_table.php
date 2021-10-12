<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Structure\Page;
use App\Models\Seo\TmpMetaTags;
use App\Models\Seo\SearchIndex;
use App\Models\Components\FilesReference;
use App\Models\Seo\UrlAlias;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PagesTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $super_admin_permission;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'pages_view',
                'display_name' => 'Просмотр списка страницы',
                'guard_name'   => 'web',
                'group'        => 'Страница',
            ],
            [
                'name'         => 'pages_read',
                'display_name' => 'Просмотр списка страниц',
                'guard_name'   => 'web',
                'group'        => 'Страница',
            ],
            [
                'name'         => 'pages_create',
                'display_name' => 'Создание страницы',
                'guard_name'   => 'web',
                'group'        => 'Страница',
            ],
            [
                'name'         => 'pages_update',
                'display_name' => 'Редактирование стринцы',
                'guard_name'   => 'web',
                'group'        => 'Страница',
            ],
            [
                'name'         => 'pages_delete',
                'display_name' => 'Удаление страницы',
                'guard_name'   => 'web',
                'group'        => 'Страница',
            ]
        ]);
        $this->pages = collect([
            [
                'type'   => 'front',
                'title'  => 'Front page',
                'alias'  => NULL,
                'robots' => 'index, follow',
            ],
            [
                'type'    => 'sitemap',
                'title'   => 'Sitemap',
                'alias'   => 'sitemap',
                'no_used' => 1,
                'robots'  => 'noindex, nofollow',
            ],
            [
                'type'   => 'contacts',
                'title'  => 'Contacts',
                'alias'  => 'contacts',
                'robots' => 'index, follow',
            ],
            [
                'type'    => 'reviews',
                'title'   => 'Reviews',
                'alias'   => 'reviews',
                'no_used' => 1,
                'robots'  => 'index, follow',
                'options' => [
                    'per_page' => 24
                ]
            ],
            [
                'type'    => 'galleries',
                'title'   => 'Galleries',
                'alias'   => 'galleries',
                'no_used' => 1,
                'robots'  => 'index, follow',
                'options' => [
                    'per_page' => 24
                ]
            ],
            [
                'type'    => 'faq',
                'title'   => 'FAQ',
                'alias'   => 'faq',
                'no_used' => 1,
                'robots'  => 'index, follow',
                'options' => [
                    'per_page' => 24
                ]
            ],
            [
                'type'    => 'search',
                'title'   => 'Search',
                'alias'   => 'search',
                'robots'  => 'index, follow',
                'options' => [
                    'per_page' => 24
                ]
            ]
        ]);
    }

    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')
                ->nullable()
                ->default('normal_page');
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->text('breadcrumb_title')
                ->nullable();
            $table->longText('body')
                ->nullable();
            $table->text('meta_title')
                ->nullable();
            $table->text('meta_keywords')
                ->nullable();
            $table->text('meta_description')
                ->nullable();
            $table->string('options', 2047)
                ->nullable()
                ->default(NULL);
            $table->string('style_id')
                ->nullable();
            $table->string('style_class')
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->boolean('no_used')
                ->default(0);
            $table->timestamps();
        });
        if (Schema::hasTable('pages')) {
            $this->pages->each(function ($page) {
                $page = collect($page);
                $_locale = DEFAULT_LOCALE;
                $_save = $page->only([
                    'type',
                    'title',
                    'sort',
                    'options',
                    'no_used'
                ])->toArray();
                $_save = array_merge([
                    'sub_title'        => NULL,
                    'breadcrumb_title' => NULL,
                    'body'             => NULL,
                    'meta_title'       => NULL,
                    'meta_keywords'    => NULL,
                    'meta_description' => NULL,
                ], $_save);
                $_page = Page::create($_save);
                if ($page->get('alias')) {
                    $_alias = new UrlAlias([
                        'alias'       => $page->get('alias'),
                        'robots'      => $page->get('robots'),
                        'locale'      => $_locale,
                        'model_title' => $_page->getTranslation('title', $_locale),
                    ]);
                    $_page->_alias()->save($_alias);
                    $_search_index = new SearchIndex([
                        'title'  => $_page->getTranslation('title', $_locale),
                        'body'   => NULL,
                        'locale' => $_locale,
                        'status' => $_page->status,
                    ]);
                    $_page->_search_index()->save($_search_index);
                }
            });
            $this->permissions();
        }
    }

    public function down()
    {
        Schema::dropIfExists('pages');
        if (Schema::hasTable('url_alias')) {
            UrlAlias::where('model_type', Page::class)
                ->delete();
        }
        if (Schema::hasTable('tmp_meta_tags')) {
            TmpMetaTags::where('model_type', Page::class)
                ->delete();
        }
        if (Schema::hasTable('files_related')) {
            FilesReference::where('model_type', Page::class)
                ->delete();
        }
        if (Schema::hasTable('search_index')) {
            SearchIndex::where('model_type', Page::class)
                ->delete();
        }
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
