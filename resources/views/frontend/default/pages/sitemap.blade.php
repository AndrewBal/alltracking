@extends('frontend.default.index')

@section('content')
    <article {!! render_attributes($_item->styleAttributes) !!}>
        <h1 id="page-title"
            class="uk-heading-divider">
            {!! $_item->title !!}
        </h1>
        <div class="uk-margin">
            {!! $_item->_items->get('output') !!}
        </div>
        <div id="page-body"
             class="uk-margin-top">
            {!! $_item->body !!}
        </div>
    </article>
@endsection

@push('edit_page')
    @if(isset($accessEdit['page']) && $accessEdit['page'])
        @if($locale == DEFAULT_LOCALE)
            @l('Редактировать', 'oleus.pages.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
        @else
            @l('Редактировать', 'oleus.pages.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
        @endif
    @endif
@endpush
