@extends('frontend.default.index')

@section('content')
    <article {!! render_attributes($_item->styleAttributes) !!}>
        <h1 id="page-title"
            class="uk-heading-divider">
            {!! $_item->title !!}
        </h1>
        <div id="page-items-list">
            @if($_item->_items->isNotEmpty())
                <div>
                    @foreach($_item->_items as $_node)
                        @include('frontend.default.nodes.teaser', ['_item' => $_node])
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    @lang('frontend.no_items')
                </div>
            @endif
        </div>
        <div id="page-items-list-pagination">
            @if(method_exists($_item->_items, 'links'))
                {!! $_item->_items->links('frontend.default.partials.pagination') !!}
            @endif
        </div>
        <div id="page-body"
             class="uk-margin-top">
            @if(is_null($wrap['seo']['page_number']))
                {!! $_item->body !!}
            @endif
        </div>
    </article>
@endsection

@push('edit_page')
    @if(isset($accessEdit['tag']) && $accessEdit['tag'])
        @if($locale == DEFAULT_LOCALE)
            @l('Редактировать', 'oleus.tags.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
        @else
            @l('Редактировать', 'oleus.tags.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
        @endif
    @endif
@endpush
