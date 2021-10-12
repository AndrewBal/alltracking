<div {!! render_attributes($_item->styleAttributes) !!}>
    @if(isset($accessEdit['faq']) && $accessEdit['faq'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.faqs.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.faqs.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    @if(!$_item->hidden_title)
        <h2 class="faq-title uk-text-uppercase uk-heading-bullet uk-margin-remove-top">
            {!! $_item->title !!}
        </h2>
    @endif
    <dl class="faq-items-list uk-description-list uk-description-list-divider">
        @foreach($_item->faqs as $_faq)
            <dt>{!! $_faq->question !!}</dt>
            <dd>{!! $_faq->answer !!}</dd>
        @endforeach
    </dl>
    @if($_item->body)
        <div class="faq-content">
            {!! $_item->body !!}
        </div>
    @endif
</div>
