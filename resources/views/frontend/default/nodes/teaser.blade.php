<article class="article-teaser uk-margin{{ isset($accessEdit['node']) && $accessEdit['node'] ? ' uk-position-relative edit-div' : null }}">
    @if(isset($accessEdit['node']) && $accessEdit['node'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.nodes.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.nodes.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    <div class="teaser-body uk-card uk-card-body uk-padding-small uk-card-default uk-border-rounded{{ isset($_class) ? " {$_class} " : NULL }}">
        <h3 class="teaser-title uk-text-uppercase uk-heading-bullet">
            <a href="{{ $_item->generate_url }}"
               title="{{ $_item->title }}">
                {!! Str::limit(strip_tags($_item->title), 80) !!}
            </a>
        </h3>
        <div class="teaser-content">
            {!! $_item->teaser !!}
        </div>
        @if($_item->_tags->isNotEmpty())
            <div class="teaser-tags">
                @foreach($_item->_tags as $_tag)
                    @l($_tag->title, $_tag->generate_url, ['attributes' => ['target' => '_blank', 'class' => 'uk-margin-xsmall-right']])
                @endforeach
            </div>
        @endif
    </div>
</article>
