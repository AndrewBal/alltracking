@if($paginator->hasPages())
    <div class="pagination-box">
        @php
            global $wrap;
            $_current_page = $paginator->currentPage();
            $_total = $paginator->total();
            $_current_url = $wrap['seo']['url_alias'];
            $_current_url_query = $wrap['seo']['url_query'];
            $_next_page_link = $wrap['seo']['link_next'] ?? NULL;
            $_prev_page_link = $wrap['seo']['link_prev'] ?? NULL;
            $_per_page = $paginator->perPage();
            $_count_showing = $_per_page * $_current_page;
            $_load_more_number = ($_total - $_count_showing) > $_per_page ? $_per_page : $_total - $_count_showing;
        @endphp
        <ul class="uk-pagination uk-flex-center">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="uk-disabled">
                        <span>
                            {{ $element }}
                        </span>
                    </li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @php
                            $url = $page > 1 ? trim($_current_url, '/') . "/page-{$page}" : $_current_url;
                            $url = _u($url) . $_current_url_query;
                        @endphp
                        @if ($page == $_current_page)
                            <li class="uk-active">
                                <a href="#"
                                   disabled>
                                    {{ $page }}
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
        @if($_next_page_link)
            <div class="pagination-load-more uk-text-center">
                <a href="{{ $_next_page_link }}"
                   data-load_more="1"
                   class="use-ajax uk-button uk-button-small uk-button-primary">
                    @lang('frontend.pagination_load_more', ['number' => $_load_more_number])<span>/{{ $_total }}</span>
                </a>
            </div>
        @endif
    </div>
@endif
