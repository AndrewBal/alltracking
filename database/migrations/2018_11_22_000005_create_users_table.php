<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User\User;
use Carbon\Carbon;

class CreateUsersTable extends Migration
{
    protected $permissions;

    public function __construct()
    {
        $this->permissions = collect([
            [
                'name'         => 'users_view',
                'display_name' => 'Просмотр профиля пользователя',
                'guard_name'   => 'web',
                'group'        => 'Пользователь',
            ],
            [
                'name'         => 'users_read',
                'display_name' => 'Просмотр списка пользователей',
                'guard_name'   => 'web',
                'group'        => 'Пользователь',
            ],
            [
                'name'         => 'users_create',
                'display_name' => 'Создание пользователя',
                'guard_name'   => 'web',
                'group'        => 'Пользователь',
            ],
            [
                'name'         => 'users_update',
                'display_name' => 'Редактирование пользователя',
                'guard_name'   => 'web',
                'group'        => 'Пользователь',
            ],
            [
                'name'         => 'users_delete',
                'display_name' => 'Удаление пользователя',
                'guard_name'   => 'web',
                'group'        => 'Пользователь',
            ],
            [
                'name'         => 'users_export_data',
                'display_name' => 'Экспорт данных пользователей',
                'guard_name'   => 'web',
                'group'        => 'Пользователь',
            ],
        ]);
    }

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')
                ->nullable();
            $table->string('patronymic')
                ->nullable();
            $table->string('name')
                ->nullable();
            $table->integer('avatar_fid')
                ->unsigned()
                ->nullable();
            $table->integer('group_id')
                ->unsigned()
                ->nullable();
            $table->string('email')
                ->unique();
            $table->string('phone')
                ->nullable();
            $table->string('password');
            $table->char('locale', 2)
                ->nullable();
            $table->tinyInteger('sex')
                ->default(0);
            $table->float('amount')
                ->default(0);
            $table->boolean('blocked')
                ->default(0);
            $table->string('comment', 1024)
                ->nullable();
            $table->rememberToken();
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('birthday')
                ->nullable();
            $table->timestamps();
            $table->foreign('avatar_fid')
                ->references('id')
                ->on('files_managed')
                ->onDelete('set null');
            $table->foreign('group_id')
                ->references('id')
                ->on('user_groups')
                ->onDelete('set null');
        });
        $this->permissions();
        $this->records();
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_avatar_fid_foreign');
                $table->dropForeign('users_group_id_foreign');
                $table->drop();
            });
            $this->permissions->each(function ($_permission) {
                Permission::findByName($_permission['name'])
                    ->delete();
            });
        }
    }

    protected function permissions()
    {
        $this->permissions->each(function ($_permission) {
            Permission::create($_permission);
        });
        $_role = Role::findByName('admin');
        $this->permissions->each(function ($_permission) use ($_role) {
            $_role->givePermissionTo($_permission['name']);
        });
    }

    protected function records()
    {
        $_user = User::create([
            'name'              => 'admin',
            'email'             => 'admin@gmail.com',
            'blocked'           => 0,
            'email_verified_at' => Carbon::now(),
            'password'          => '$2y$10$qtERG0OeakTGowUTPzi4pOblSUuFTXlVxHHfU0b1IIWqw4HER1Tou'
        ]);
        $_user->syncRoles('admin');
    }
}
