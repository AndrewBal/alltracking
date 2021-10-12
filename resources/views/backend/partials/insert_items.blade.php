<div class="uk-form-row">
    <div class="uk-form-controls">
        <div id="list-insert-items"
             class="uk-list">
            @include('backend.partials.insert_items_table', compact('items'))
        </div>
        <div class="uk-clearfix uk-text-right">
            @if(Route::has("oleus.{$route}.sort"))
                <a href="{{ _r("oleus.{$route}.sort", [$entity]) }}"
                   class="uk-button uk-button-small uk-margin-small-right uk-button-primary uk-button-save-sorting {{ $items->isNotEmpty() ? NULL : 'uk-hidden' }}">
                    Сохранить сортировку
                </a>
            @endif
            @if(isset($button))
                {!! $button !!}
            @else
                @l('Добавить', "oleus.{$route}.item", ['p' => [$entity, 'add'], 'attributes' => ['class' => 'uk-button uk-button-small uk-button-success use-ajax']])
            @endif
        </div>
    </div>
</div>
