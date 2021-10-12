@extends('frontend.default.index')

@section('content')
    <article {!! render_attributes($_item->styleAttributes) !!}>
        <h1 id="page-title"
            class="uk-heading-divider">
            {!! $_item->title !!}
        </h1>
        <div>
            <form action=""
                  class="uk-form uk-margin-bottom"
                  method="get">
                <div class="uk-grid uk-grid-small">
                    <div class="uk-width-expand">
                        <input type="search"
                               name="search-string"
                               class="uk-input"
                               placeholder="@lang('forms.fields.search.field')"
                               value="{{ request()->get('search-string') }}">
                    </div>
                    <div class="uk-width-auto">
                        <button type="submit"
                                class="uk-button uk-button-primary">
                            @lang('forms.buttons.search.submit')
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div id="page-items-list">
            @if($_item->_items->isNotEmpty())
                <div>
                    @foreach($_item->_items as $_search)
                        {!! $_search->output !!}
                    @endforeach
                </div>
            @elseif(request()->get('search-string'))
                <div class="uk-alert uk-alert-warning">
                    @lang('frontend.not_found_items')
                </div>
            @else
                <div class="uk-alert">
                    @lang('frontend.use_search_field')
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
    @if(isset($accessEdit['page']) && $accessEdit['page'])
        @if($locale == DEFAULT_LOCALE)
            @l('Редактировать', 'oleus.pages.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
        @else
            @l('Редактировать', 'oleus.pages.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
        @endif
    @endif
@endpush
