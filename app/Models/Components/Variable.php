<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Variable extends Model
{
    use BaseModel;

    protected $table = 'variables';
    protected $fillable = [
        'key',
        'name',
        'value',
        'use_php',
        'comment'
    ];
    public $timestamps = FALSE;
    public $translatable = [
        'value',
    ];
    protected $attributes = [
        'id'      => NULL,
        'key'     => NULL,
        'name'    => NULL,
        'value'   => NULL,
        'use_php' => 0,
        'comment' => NULL
    ];

    public function __construct($attributes = [])
    {
        $this->entityEventSave = FALSE;
        $this->entityEventDelete = FALSE;
        parent::__construct($attributes);
    }

    /**
     * Others
     */
    public function _load($key)
    {
        $_item = self::where('key', $key)
            ->first();

        return $_item->value ? ($_item->use_php ? eval($_item->value) : $_item->value) : NULL;
    }

    public function getShortcut($options = [])
    {
        return $this->value ? ($this->use_php ? eval($this->value) : $this->value) : NULL;
    }
}
