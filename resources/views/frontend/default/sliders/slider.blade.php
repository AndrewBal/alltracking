<div {!! render_attributes($_item->styleAttributes) !!}>
    @if(isset($accessEdit['slider']) && $accessEdit['slider'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.sliders.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.sliders.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    <ul class="uk-slideshow-items uk-height-medium">
        @foreach($_item->slides as $_slide)
            <li class="slider-item-{{ $_slide->id }} uk-flex uk-flex-center uk-flex-middle uk-background-cover"
                style="background-image: url({{ render_image($_slide->_background, $_item->preset, ['only_way' => TRUE]) }});">
                {!! $_slide->link ? '<a href="'. _u($_slide->link) .'" '. ($_slide->link_attributes ?: NULL) .'>' : '<div>' !!}
                <div class="slide-body">
                    @if(!$_slide->hidden_title)
                        <h2 class="slide-title">
                            {!! $_slide->title !!}
                        </h2>
                        @if($_slide->sub_title)
                            <div class="slide-sub-title">
                                {!! $_slide->sub_title !!}
                            </div>
                        @endif
                    @endif
                    @if($_slide->body)
                        <div class="slide-content">
                            {!! $_slide->body !!}
                        </div>
                    @endif
                </div>
                {!! $_slide->link ? '</a>' : '</div>' !!}
            </li>
        @endforeach
    </ul>
    @if($_item->options->slidenav)
        <a class="uk-position-center-left uk-position-small uk-hidden-hover"
           href="#"
           uk-slidenav-previous
           uk-slideshow-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-hidden-hover"
           href="#"
           uk-slidenav-next
           uk-slideshow-item="next"></a>
    @endif
</div>
