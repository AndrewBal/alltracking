<?php

namespace App\Models\User;

use App\Models\Components\File;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory,
        Notifiable,
        HasRoles;

    protected $perPage = 100;
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'patronymic',
        'surname',
        'phone',
        'avatar_fid',
        'locale',
        'sex',
        'amount',
        'blocked',
        'comment',
        'birthday',
        'group_id',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $attributes = [
        'id'                => NULL,
        'patronymic'        => NULL,
        'surname'           => NULL,
        'name'              => NULL,
        'email'             => NULL,
        'phone'             => NULL,
        'password'          => NULL,
        'avatar_fid'        => NULL,
        'locale'          => NULL,
        'sex'               => 0,
        'amount'            => 0,
        'blocked'           => 0,
        'comment'           => NULL,
        'birthday'          => NULL,
        'email_verified_at' => NULL,
        'group_id'          => NULL,
    ];

    public function getViewRoleAttribute()
    {
        $_view_roles = NULL;
        $_user_role = $this->getRoleNames();
        if ($_roles = Role::all()) {
            $_view_roles = $_roles->filter(function ($role) use ($_user_role) {
                return $role->name == $_user_role->get(0);
            })->map(function ($role) {
                return _l($role->display_name, 'oleus.roles.edit', ['p' => [$role]]);
            })->toArray();
        }

        return $_view_roles ? implode(',', $_view_roles) : NULL;
    }

    public function getViewSexAttribute()
    {
        $_label = [
            0 => 'Не указано',
            1 => 'Мужчина',
            2 => 'Женщина',
        ];

        return $_label[$this->sex];
    }

    public function getViewGroupAttribute()
    {
        $_label = [
            0 => 'Не указано',
            1 => 'Мужчина',
            2 => 'Женщина',
        ];

        return $_label[$this->sex];
    }

    public function getActiveAttribute()
    {
        return (boolean)$this->email_verified_at;
    }

    public function getFullNameAttribute()
    {
        $_name = NULL;
        if ($this->surname) $_name[] = $this->surname;
        if ($this->name) $_name[] = $this->name;
        if ($this->patronymic) $_name[] = $this->patronymic;

        return $_name ? implode(' ', $_name) : $this->email;
    }

    public function _avatar()
    {
        return $this->hasOne(File::class, 'id', 'avatar_fid')
            ->remember(REMEMBER_LIFETIME * 24 * 7);
    }

    public function _group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id')
            ->remember(REMEMBER_LIFETIME * 24 * 7);
    }

}
