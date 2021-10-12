<?php

namespace App\Models\Form;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class FormsData extends Model
{
    use BaseModel;

    protected $table = 'forms_data';
    protected $fillable = [
        'user_id',
        'form_id',
        'data',
        'status',
        'notified',
        'referer_path',
        'comment',
    ];
    protected $attributes = [
        'id'           => NULL,
        'user_id'      => NULL,
        'form_id'      => NULL,
        'data'         => NULL,
        'status'       => 1,
        'notified'     => 0,
        'referer_path' => NULL,
        'comment'      => NULL,
    ];
    public $translatable;
    protected $perPage = 100;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Attribute
     */
    public function getDataAttribute()
    {
        return is_json($this->attributes['data']) ? json_decode($this->attributes['data']) : NULL;
    }

    public function setDataAttribute($value = NULL)
    {
        $this->attributes['data'] = json_encode($value);
    }


    public function getViewDataAttribute()
    {
        $_response = NULL;
        switch ($this->form) {

        }

        return $_response;
    }

    /**
     * Relationships
     */
    public function _form()
    {
        return $this->belongsTo(Forms::class, 'form_id');
    }
}
