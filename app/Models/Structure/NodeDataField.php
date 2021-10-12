<?php

namespace App\Models\Structure;

use App\Libraries\BaseModel;
use App\Models\Seo\TmpMetaTags;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class NodeDataField extends Model
{
    use BaseModel;

    protected $table = 'node_data_fields';
    protected $fillable = [
        'field',
        'node_id',
        'data',
    ];
    protected $attributes = [
        'field'   => NULL,
        'node_id' => NULL,
        'data'    => NULL
    ];
    public $translatable = [
        'data'
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }
}
