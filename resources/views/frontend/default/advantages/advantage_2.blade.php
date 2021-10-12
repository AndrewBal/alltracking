<div {!! render_attributes($_item->styleAttributes) !!}>

    @if(!$_item->hidden_title)
        <h3 class=" ">
            {!! $_item->title !!}
        </h3>

        @if($_item->sub_title)
            <div class="advantage-sub-title uk-text-muted">
                {!! $_item->sub_title !!}
            </div>
        @endif
    @endif



    <div class="advantage-items-list uk-flex">
        @foreach($_item->advantages as $_advantage)
            <div class="slider-item-{{ $_advantage->id }} advantage-item uk-text-center">


                <div class="advantage-item-img">
                    @if($_advantage->icon_fid)
                        @image($_advantage->_icon, 'thumb_small', ['attributes' => ['class' => 'uk-border-circle']])
                    @endif
                </div>


                    <div class="advantage-item-body">
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

            </div>
        @endforeach
    </div>
    @if($_item->body)
        <div class="advantage-content uk-margin-small-top uk-text-center">
            {!! $_item->body !!}
        </div>
    @endif
</div>
