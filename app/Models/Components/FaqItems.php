<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class FaqItems extends Model
{
    use BaseModel;

    protected $table = 'faq_items';
    protected $fillable = [
        'faq_id',
        'question',
        'answer',
        'sort',
        'status',
    ];
    public $translatable = [
        'question',
        'answer',
    ];
    protected $attributes = [
        'id'               => NULL,
        'question'         => NULL,
        'answer'           => NULL,
        'sort'             => 0,
        'status'           => 0,
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }
}
