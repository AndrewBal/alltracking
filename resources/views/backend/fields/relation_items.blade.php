<div class="uk-form-row">
    <div class="uk-form-controls">
        <div id="{{ $items_id }}"
             class="uk-list">
            @include('backend.fields.relation_items_table', compact('items'))
        </div>
        <div class="uk-clearfix uk-text-right">
            @l('Сохранить сортировку', 'oleus.nodes.relation', ['p' => [$entity, $field, 'sort'], 'attributes' => ['class' => 'uk-button uk-button-small uk-margin-small-right uk-button-primary uk-button-relation-save-sorting' . ($items->isNotEmpty() ? NULL : ' uk-hidden'), 'data-field' => $field, 'data-id' => $entity->id]])
            @l('Добавить', 'oleus.nodes.relation', ['p' => [$entity, $field, 'add'], 'attributes' => ['class' => 'uk-button uk-button-small uk-button-success use-ajax']])
        </div>
    </div>
</div>
