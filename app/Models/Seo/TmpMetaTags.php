<?php

namespace App\Models\Seo;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;

class TmpMetaTags extends Model
{
    use BaseModel;

    protected $table = 'tmp_meta_tags';
    protected $fillable = [
        'model_type',
        'model_id',
        'type',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];
    protected $attributes = [
        'id'               => NULL,
        'model_type'       => NULL,
        'model_id'         => NULL,
        'type'             => NULL,
        'meta_title'       => NULL,
        'meta_keywords'    => NULL,
        'meta_description' => NULL,
    ];
    public $entity;
    public $timestamps = FALSE;
    public $translatable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Others
     */
    public function setMeta()
    {
        $_tmp_meta_tags = request()->input('tmp_meta_tags');
        $_meta_tags = $this->entity->_tmp_meta_tags;
        $_locale = $this->entity->frontLocale ? : DEFAULT_LOCALE;
        if ($_meta_tags instanceof TmpMetaTags) {
            foreach ($_tmp_meta_tags as $_field => $_value) {
                $_meta_tags->setTranslation($_field, $_locale, $_value);
            }
            $_meta_tags->save();
        }else{
            foreach ($_tmp_meta_tags as $_type => $_value) {
                if ($this->entity->_tmp_meta_tags->isNotEmpty()) {
                    $_save = $this->entity->_tmp_meta_tags->where('type', $_type)
                        ->first();
                    $_save->fill([
                        'meta_title'       => $_save->setTranslation('meta_title', $_locale, $_value['meta_title']),
                        'meta_keywords'    => $_save->setTranslation('meta_keywords', $_locale, $_value['meta_keywords']),
                        'meta_description' => $_save->setTranslation('meta_description', $_locale, $_value['meta_description']),
                    ]);
                    $_save->save();
                } else {
                    $_save = self::fill([
                        'type'             => $_type,
                        'meta_title'       => $_save->setTranslation('meta_title', $_locale, $_value['meta_title']),
                        'meta_keywords'    => $_save->setTranslation('meta_keywords', $_locale, $_value['meta_keywords']),
                        'meta_description' => $_save->setTranslation('meta_description', $_locale, $_value['meta_description']),
                    ]);
                    $this->entity->_tmp_meta_tags()->save($_save);
                }
            }
        }
    }

    /**
     * Relationships
     */
    public function model()
    {
        return $this->morphTo();
    }
}
