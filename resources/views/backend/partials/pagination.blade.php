{{--@if ($paginator->hasPages())--}}
{{--    <ul class="uk-pagination uk-flex-center uk-flex-middle uk-margin-top">--}}
{{--        @if ($paginator->onFirstPage())--}}
{{--            <li class="uk-disabled">--}}
{{--                <span uk-pagination-previous></span>--}}
{{--            </li>--}}
{{--        @else--}}
{{--            <li>--}}
{{--                <a href="{{ $paginator->previousPageUrl() }}">--}}
{{--                    <span uk-pagination-previous></span>--}}
{{--                </a>--}}
{{--            </li>--}}
{{--        @endif--}}
{{--        @foreach ($elements as $element)--}}
{{--            @if (is_string($element))--}}
{{--                <li class="uk-disabled">--}}
{{--                    <span>{{ $element }}</span>--}}
{{--                </li>--}}
{{--            @endif--}}
{{--            @if (is_array($element))--}}
{{--                @foreach ($element as $page => $url)--}}
{{--                    @if ($page == $paginator->currentPage())--}}
{{--                        <li class="uk-active">--}}
{{--                            <span>{{ $page }}</span>--}}
{{--                        </li>--}}
{{--                    @else--}}
{{--                        <li>--}}
{{--                            <a href="{{ $url }}">--}}
{{--                                {{ $page }}--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    @endif--}}
{{--                @endforeach--}}
{{--            @endif--}}
{{--        @endforeach--}}
{{--        @if ($paginator->hasMorePages())--}}
{{--            <li>--}}
{{--                <a href="{{ $paginator->nextPageUrl() }}">--}}
{{--                    <span uk-pagination-next></span>--}}
{{--                </a>--}}
{{--            </li>--}}
{{--        @else--}}
{{--            <li class="uk-disabled">--}}
{{--                <span uk-pagination-next></span>--}}
{{--            </li>--}}
{{--        @endif--}}
{{--    </ul>--}}
{{--@endif--}}
