<div {!! render_attributes($_item->styleAttributes) !!}>
    @if(isset($accessEdit['block']) && $accessEdit['block'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.blocks.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.blocks.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    @if(!$_item->hidden_title)
        <h2 class="block-title uk-text-uppercase uk-heading-bullet uk-margin-remove-top">
            {!! $_item->title !!}
        </h2>
        @if($_item->sub_title)
            <div class="block-sub-title uk-text-muted">
                {!! $_item->sub_title !!}
            </div>
        @endif
    @endif
    @if($_item->body)
        <div class="block-content">
            {!! $_item->body !!}
        </div>
    @endif
</div>
