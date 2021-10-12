<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class FilesReference extends Model
{
    use BaseModel;

    protected $table = 'files_related';
    protected $fillable = [
        'model_type',
        'model_id',
        'type',
        'file_id',
    ];
    protected $attributes = [
        'model_type' => NULL,
        'model_id'   => NULL,
        'type'       => 'medias',
        'file_id'    => NULL,
    ];
    public $entity;
    public $timestamps = FALSE;
    public $translatable;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Others
     */
    public function setReference()
    {
        if ($this->entity) {
            $_medias = request()->input('medias');
            $_files = request()->input('files');
            $this->entity->_files_related()->detach();
            $_attach = NULL;
            if ($_medias) foreach ($_medias as $_file) $_attach[$_file['id']] = ['type' => 'medias'];
            if ($_files) foreach ($_files as $_file) $_attach[$_file['id']] = ['type' => 'files'];
            if ($_attach) $this->entity->_files_related()->attach($_attach);
        }

        return NULL;
    }
}
