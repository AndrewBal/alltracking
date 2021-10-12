<div {!! render_attributes($_item->styleAttributes) !!}>
    @if(isset($accessEdit['advantage']) && $accessEdit['advantage'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.advantages.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.advantages.translate', ['p' => [$_item, $_locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    @if(!$_item->hidden_title)
        <h2 class="advantage-title uk-text-uppercase uk-text-center uk-margin-remove-top">
            {!! $_item->title !!}
        </h2>
        @if($_item->sub_title)
            <div class="advantage-sub-title uk-text-muted">
                {!! $_item->sub_title !!}
            </div>
        @endif
    @endif
    <div class="advantage-items-list uk-grid uk-grid-small uk-flex uk-flex-middle uk-child-width-1-4">
        @foreach($_item->advantages as $_advantage)
            <div class="slider-item-{{ $_advantage->id }} uk-text-center">
                @if($_advantage->icon_fid)
                    @image($_advantage->_icon, 'thumb_small', ['attributes' => ['class' => 'uk-border-circle']])
                @endif
                @if(!$_advantage->hidden_title)
                    <h3 class="advantage-item-title">
                        {!! $_advantage->title !!}
                    </h3>
                    @if($_advantage->sub_title)
                        <div class="advantage-sub-item-title">
                            {!! $_advantage->sub_title !!}
                        </div>
                    @endif
                @endif
                @if($_advantage->body)
                    <div class="advantage-item-content">
                        {!! $_advantage->body !!}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    @if($_item->body)
        <div class="advantage-content uk-margin-small-top uk-text-center">
            {!! $_item->body !!}
        </div>
    @endif
</div>
