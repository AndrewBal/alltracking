<?php

namespace App\Models\User;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    use BaseModel;

    protected $table = 'user_groups';
    protected $perPage = 100;
    protected $fillable = [
        'name',
        'discount',
    ];
    protected $attributes = [
        'id'       => NULL,
        'name'     => NULL,
        'discount' => 0,
    ];
    public $translatable = [
        'name'
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->entityEventSave = FALSE;
        $this->entityEventDelete = FALSE;
    }

    public function getCountUsersAttribute()
    {
        return DB::table('users')
            ->where('group_id', $this->id)
            ->count();
    }
}
