<?php

namespace App\Listeners;

use App\Events\EntityDelete;

class EntityDeleteProcessing
{
    public function handle(EntityDelete $event)
    {
        if (isset($event->entity->_aliases) && $event->entity->_aliases->isNotEmpty()) $event->entity->_aliases()->delete();
        if (isset($event->entity->_search_index) && $event->entity->_search_index->isNotEmpty()) $event->entity->_search_index()->delete();
        if (isset($event->entity->_files_related) && $event->entity->_files_related->isNotEmpty()) $event->entity->_files_related()->delete();
        if (isset($event->entity->_tmp_meta_tags)) $event->entity->_tmp_meta_tags->delete();
    }
}
