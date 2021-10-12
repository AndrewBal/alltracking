@extends('frontend.default.index')

@section('content')
    <article {!! render_attributes($_item->styleAttributes) !!}>
        <h1 id="node-title"
            class="uk-heading-divider">
            {!! $_item->title !!}
        </h1>
        <p class="uk-text-meta">{!! $_item->published_at->format('d/m/Y') !!}</p>
        @if($_item->body)
            <div id="node-body"
                 class="uk-margin-top">
                {!! $_item->body !!}
            </div>
        @endif
        <div class="uk-text-center">
            {!! \App\Models\Form\Forms::getButtonOpenForm(1, ['class' => 'uk-button uk-button-large uk-button-default uk-border-rounded', 'name' => trans('form.buttons.consultation.open')]) !!}
            @if(isset($_item->additionalFields['link_to_site']) && $_item->additionalFields['link_to_site'])
                <a href="{{ $_item->additionalFields['link_to_site']['data'] }}"
                   class="uk-button uk-button-large uk-button-primary uk-border-rounded uk-margin-small-left">Go to Site</a>
            @endif
        </div>
        @if($_item->relatedMedias->isNotEmpty())
            <div class="uk-child-width-1-5 uk-margin-top uk-grid-small"
                 uk-grid
                 uk-lightbox="animation: fade">
                @foreach($_item->relatedMedias as $_media)
                    <div>
                        <a class="uk-display-block uk-border-rounded uk-overflow-hidden"
                           href="{{ $_media->base_url }}"
                           data-caption="{{ $_media->title }}">
                            {!! render_image($_media, 'thumb_small', ['attributes' => ['alt' => $_media->alt ?: $_item->title . ' - ' . $loop->index, 'class' => 'uk-width-1-1']]) !!}
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
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
