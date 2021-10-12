<?php

namespace App\Models\Structure;

use App\Libraries\BaseModel;
use App\Models\Seo\SearchIndex;
use App\Models\Seo\UrlAlias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class NodeTag extends Model
{
    use BaseModel;

    protected $table = 'node_tags';
    protected $fillable = [
        'title',
        'default_title',
        'status',
        'sort',
    ];
    protected $attributes = [
        'id'            => NULL,
        'title'         => NULL,
        'default_title' => NULL,
        'status'        => 1,
        'sort'          => 0,
    ];
    public $entity;
    public $translatable = [
        'title',
    ];
    protected $perPage = 1000;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Relationships
     */
    public function _nodes()
    {
        return $this->morphedByMany(Node::class, 'model', 'node_taggables')
            ->visibleOnList('nodes')
            ->with([
                '_alias',
                '_page',
                '_user',
                '_preview'
            ]);
    }

    /**
     * Others
     */
    public function setTag()
    {
        $_response = NULL;
        $_tags = request()->input('node_tags');
        if ($this->entity) {
            $this->entity->_node_tags()->detach();
            if ($_tags) {
                $_attach = [];
                foreach ($_tags as $_tag) {
                    if (ctype_digit($_tag)) {
                        $_attach[] = $_tag;
                    } else {
                        $_item = new self;
                        $_item->fill([
                            'title'         => $_tag,
                            'default_title' => $_tag
                        ]);
                        $_item->save();
                        $_attach[] = $_item->id;
                    }
                }
                $this->entity->_node_tags()->attach($_attach);
            }
        }
    }
}
