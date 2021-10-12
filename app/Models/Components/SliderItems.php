<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class SliderItems extends Model
{
    use BaseModel;

    protected $table = 'slider_items';
    protected $fillable = [
        'title',
        'sub_title',
        'slider_id',
        'background_fid',
        'body',
        'link',
        'link_attributes',
        'sort',
        'status',
        'hidden_title',
    ];
    protected $attributes = [
        'id'              => NULL,
        'title'           => NULL,
        'sub_title'       => NULL,
        'slider_id'       => NULL,
        'background_fid'  => NULL,
        'body'            => NULL,
        'link'            => NULL,
        'link_attributes' => NULL,
        'sort'            => 0,
        'status'          => 1,
        'hidden_title'    => 0,
    ];
    public $translatable = [
        'title',
        'sub_title',
        'body',
        'link'
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _slider()
    {
        return $this->belongsTo(Slider::class, 'slider_id');
    }
}
