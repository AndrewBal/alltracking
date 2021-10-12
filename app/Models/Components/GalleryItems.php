<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class GalleryItems extends Model
{
    use BaseModel;

    protected $table = 'gallery_items';
    protected $fillable = [
        'title',
        'gallery_id',
        'background_fid',
        'body',
        'sort',
        'status',
    ];
    protected $attributes = [
        'id'             => NULL,
        'title'          => NULL,
        'gallery_id'     => NULL,
        'background_fid' => NULL,
        'body'           => NULL,
        'sort'           => 0,
        'status'         => 1,
    ];
    public $translatable = [
        'title',
        'body',
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _gallery()
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }
}
