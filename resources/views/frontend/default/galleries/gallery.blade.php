<div {!! render_attributes($_item->styleAttributes) !!}>
    @if(isset($accessEdit['gallery']) && $accessEdit['gallery'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.galleries.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.galleries.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    @if(!$_item->hidden_title)
        <h2 class="gallery-title uk-text-uppercase uk-margin-remove-top uk-text-center">
            {!! $_item->title !!}
        </h2>
    @endif
    @if($_item->photos)
        <div uk-lightbox
             class="gallery-content uk-position-relative uk-image uk-grid uk-grid-small uk-child-width-1-6">
            @foreach($_item->photos as $_photo)
                <a href="{{ "/storage/{$_photo->file_name}" }}">
                    {!! render_image($_photo, 'thumb_small', ['attributes' => ['class' => 'uk-border-rounded uk-box-shadow-hover-small uk-display-block']]) !!}
                </a>
            @endforeach
        </div>
    @endif
</div>
