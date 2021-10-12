<?php

namespace App\Models\Structure;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class NodeRelationField extends Model
{
    use BaseModel;

    protected $table = 'node_relation_fields';
    protected $fillable = [
        'id',
        'node_id',
        'field',
        'sort',
    ];
    protected $attributes = [
        'id'      => NULL,
        'node_id' => NULL,
        'field'   => NULL,
        'sort'    => 0
    ];
    public $translatable;
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    public function _node()
    {
        return $this->belongsTo(Node::class, 'node_id');
    }
}
