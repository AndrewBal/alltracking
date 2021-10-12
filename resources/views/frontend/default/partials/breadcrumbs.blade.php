@php
    $_breadcrumb = isset($breadcrumb) && $breadcrumb ? $breadcrumb : (isset($wrap['page']['breadcrumb']) && $wrap['page']['breadcrumb'] ? $wrap['page']['breadcrumb'] : NULL)
@endphp
@if($_breadcrumb)
    <div id="page-breadcrumbs"
         class="uk-container uk-margin-small-bottom uk-margin-small-top">
        <ul class="uk-breadcrumb">
            @foreach($_breadcrumb as $_item)
                <li class='inline{{ $loop->last ? ' uk-active' : NULL }}'>
                    @if($loop->last)
                        <span>
                            {!! $_item['name'] !!}
                        </span>
                    @else
                        <a href='{{ $_item['url'] }}'>
                            {!! $_item['name'] !!}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
