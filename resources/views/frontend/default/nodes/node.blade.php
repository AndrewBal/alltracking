@extends('frontend.default.index')

@section('content')
    <div class="uk-container uk-container-custom title-text">
     {!! ($_item->title) !!}
    </div>
    @include('frontend.default.partials.breadcrumbs')
    <article {!! render_attributes($_item->styleAttributes) !!} class="nodes">
        <section class="main uk-container uk-container-custom node">
            <div class="company_header">
                <div class="company_img">
                    <img src="images/image 11.png" alt="company_img">
                </div>
                <div class="company_header_info">
                   {!! ($_item->teaser) !!}
                </div>
                <div class="company_header_info_mob">
                   {!! ($_item->teaser) !!}
                </div>
            </div>

            <div class="company_text">
                 {!! ($_item->body) !!}
            </div>
        </section>
    </article>
@endsection

@push('edit_page')
    @if(isset($accessEdit['node']) && $accessEdit['node'])
        @if($locale == DEFAULT_LOCALE)
            @l('Редактировать', 'oleus.nodes.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
        @else
            @l('Редактировать', 'oleus.nodes.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
        @endif
    @endif
@endpush
