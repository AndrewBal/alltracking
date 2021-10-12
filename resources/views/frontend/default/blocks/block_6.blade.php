<div {!! render_attributes($_item->styleAttributes) !!}>

    @if(!$_item->hidden_title)
        <h3 >
            {!! $_item->title !!}
        </h3>
        @if($_item->sub_title)
            <div class="block-sub-title uk-text-muted">
                {!! $_item->sub_title !!}
            </div>
        @endif
    @endif
    @if($_item->body)
        <div class="block-content">
            @foreach($_item->relatedMedias as $image)
                {!! render_image($image) !!}
            @endforeach

                {!! $_item->body !!}

        </div>
    @endif



</div>
