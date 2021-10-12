<?php

namespace App\Listeners;

use App\Events\EntitySave;
use App\Models\Components\DisplayRules;
use App\Models\Components\FilesReference;
use App\Models\Seo\SearchIndex;
use App\Models\Seo\TmpMetaTags;
use App\Models\Seo\UrlAlias;
use App\Models\Structure\NodeTag;
use App\Models\Structure\Tag;

class EntitySaveProcessing
{
    public function handle(EntitySave $event)
    {
        update_last_modified_timestamp();
        $_request = request();
        if (method_exists($event->entity, '_alias') && $_request->has('url')) {
            $_url_alias = new UrlAlias();
            $_url_alias->entity = $event->entity;
            $_url_alias->founder = [];
            $_url_alias->setAlias();
            if ($_request->has('tmp_meta_tags')) {
                $_tmp_meta = new TmpMetaTags();
                $_tmp_meta->entity = $event->entity;
                $_tmp_meta->setMeta();
            }
            if (method_exists($event->entity, '_search_index')) {
                $_search = new SearchIndex();
                $_search->entity = $event->entity;
                $_search->setIndex();
            }
        }
        if (method_exists($event->entity, '_files_related') && ($_request->has('medias') || $_request->has('files'))) {
            $_medias = new FilesReference();
            $_medias->entity = $event->entity;
            $_medias->setReference();
        }
        if (method_exists($event->entity, '_tags') && $_request->has('tags')) {
            $_tags = new Tag();
            $_tags->entity = $event->entity;
            $_tags->setTag();
        }
        if (method_exists($event->entity, '_node_tags') && $_request->has('node_tags')) {
            $_tags = new NodeTag();
            $_tags->entity = $event->entity;
            $_tags->setTag();
        }
    }
}
