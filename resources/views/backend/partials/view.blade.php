@extends('backend.index')

@section('content')
    <article class="uk-article">
        <div class="uk-card uk-card-default uk-card-small uk-border-rounded">
            <div class="uk-card-header uk-text-right">
                @l('', "oleus.{$_view->route_tag}", ['attributes' => ['class' => 'uk-button uk-button-default uk-button-icon uk-button-small', 'uk-icon' => 'icon: reply']])
            </div>
            <div class="uk-card-body">
                @foreach($_view->contents as $content)
                    @if(array_key_exists(1, $content))
                        <div class='uk-grid uk-grid-small'>
                            <div class='uk-width-1-4 uk-text-bold uk-text-right'>{!! $content[0] !!}</div>
                            <div class='uk-width-3-4'>{!! $content[1] !!}</div>
                        </div>
                    @else
                        {!! $content[0] !!}
                    @endif
                @endforeach
            </div>
        </div>
    </article>
@endsection
