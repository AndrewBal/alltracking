<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class AdvantageItems extends Model
{
    use BaseModel;

    protected $table = 'advantage_items';
    protected $fillable = [
        'advantage_id',
        'title',
        'sub_title',
        'icon_fid',
        'body',
        'sort',
        'status',
        'hidden_title',
    ];
    public $timestamps = FALSE;
    public $translatable = [
        'title',
        'sub_title',
        'body',
    ];
    protected $attributes = [
        'id'           => NULL,
        'advantage_id' => NULL,
        'title'        => NULL,
        'sub_title'    => NULL,
        'icon_fid'     => NULL,
        'body'         => NULL,
        'sort'         => 0,
        'status'       => 1,
        'hidden_title' => 0,
    ];


}
