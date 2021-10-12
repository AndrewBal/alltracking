<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FormsTable extends Migration
{
    protected $table;
    protected $permissions;
    protected $pages;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'forms_read',
                'display_name' => 'Просмотр списка форм',
                'group'        => 'Формы',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_view',
                'display_name' => 'Просмотр формы',
                'group'        => 'Формы',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_create',
                'display_name' => 'Создание формы',
                'group'        => 'Формы',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_update',
                'display_name' => 'Обновление формы',
                'group'        => 'Формы',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_delete',
                'display_name' => 'Удаление формы',
                'group'        => 'Формы',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_data_list_read',
                'display_name' => 'Просмотр списка данных форм',
                'group'        => 'Данные форм',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_data_update',
                'display_name' => 'Обновление данных формы',
                'group'        => 'Данные форм',
                'guard_name'   => 'web',
            ],
            [
                'name'         => 'forms_data_delete',
                'display_name' => 'Удаление данных формы',
                'group'        => 'Данные форм',
                'guard_name'   => 'web',
            ]
        ]);
    }

    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('sub_title')
                ->nullable();
            $table->longText('body')
                ->nullable();
            $table->string('style_id')
                ->nullable();
            $table->string('style_class',)
                ->nullable();
            $table->text('prefix')
                ->nullable();
            $table->text('suffix')
                ->nullable();
            $table->string('attributes', 511)
                ->nullable();
            $table->text('settings')
                ->nullable();
            $table->boolean('completion_type')
                ->default(1);
            $table->text('completion_modal_text')
                ->nullable();
            $table->integer('completion_page_id')
                ->unsigned()
                ->nullable();
            $table->string('button_send', 255)
                ->nullable();
            $table->string('button_open_form', 255)
                ->nullable();
            $table->string('email_to_receive', 511)
                ->nullable();
            $table->string('email_subject', 511)
                ->nullable();
            $table->boolean('send_to_user')
                ->default(0);
            $table->integer('user_email_field_id')
                ->unsigned()
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->boolean('hidden_title')
                ->default(0);
            $table->foreign('completion_page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('set null');
        });
        Schema::create('form_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')
                ->unsigned()
                ->nullable();
            $table->text('title');
            $table->text('help')
                ->nullable();
            $table->string('type');
            $table->longText('data')
                ->nullable();
            $table->string('value', 511)
                ->nullable();
            $table->text('options')
                ->nullable();
            $table->boolean('multiple')
                ->default(0);
            $table->text('markup')
                ->nullable();
            $table->tinyInteger('sort')
                ->default(0);
            $table->boolean('status')
                ->default(1);
            $table->boolean('required')
                ->default(0);
            $table->string('other_rules', 511)
                ->nullable();
            $table->boolean('hidden_label')
                ->default(0);
            $table->boolean('placeholder_label')
                ->default(0);
            $table->foreign('form_id')
                ->references('id')
                ->on('forms')
                ->onDelete('cascade');
        });
        Schema::create('forms_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')
                ->unsigned()
                ->nullable();
            $table->integer('form_id')
                ->unsigned()
                ->nullable();
            $table->longText('data')
                ->nullable();
            $table->boolean('status')
                ->default(1);
            $table->boolean('notified')
                ->default(0);
            $table->string('referer_path', 511)
                ->nullable();
            $table->text('comment')
                ->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('form_id')
                ->references('id')
                ->on('forms')
                ->onDelete('cascade');
        });
        if (Schema::hasTable('forms')) $this->permissions();
    }

    public function down()
    {
        if (Schema::hasTable('forms')) {
            Schema::dropIfExists('forms_data');
            Schema::table('forms_data', function (Blueprint $table) {
                $table->dropForeign('forms_data_user_id_foreign');
                $table->dropForeign('forms_data_form_id_foreign');
                $table->drop();
            });
            Schema::table('form_fields', function (Blueprint $table) {
                $table->dropForeign('form_fields_form_id_foreign');
                $table->drop();
            });
            Schema::dropIfExists('forms');
            Schema::dropIfExists('form_fields');
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
