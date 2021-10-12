<div {!! render_attributes($_item->styleAttributes) !!}>

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


        @foreach($_item->relatedMedias as $image)
            {!! render_image($image) !!}
        @endforeach



    @if($_item->body)
        <div class="block-content">
            {!! $_item->body !!}
        </div>
    @endif
</div>
