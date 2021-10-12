<div {!! render_attributes($_item->styleAttributes) !!}>
    @if(isset($accessEdit['banner']) && $accessEdit['banner'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.banners.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.banners.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    {!! $_item->link ? '<a href="'. _u($_item->link) .'" '. ($_item->link_attributes ?: NULL) .'>' : NULL !!}
    @if($_item->background_fid)
        @image($_item->_background, $_item->preset, ['attributes' => ['class' => '']])
    @endif
    {!! $_item->body !!}
    {!! $_item->link ? '</a>' : NULL !!}
</div>
