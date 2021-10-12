@extends('frontend.default.index')

@section('content')
    <article {!! render_attributes($_item->styleAttributes) !!}>
        <h1 id="page-title"
            class="uk-heading-divider">
            {!! $_item->title !!}
        </h1>
        <div id="page-body"
             class="uk-margin-top">
            {!! $_item->body !!}
        </div>
    </article>
@endsection

