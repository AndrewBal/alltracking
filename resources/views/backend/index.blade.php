@php
    $_menu = config('dashboard.menu');
    $_open_menu = isset($_COOKIE['open_dashboard_menu']) && $_wrap['device']['type'] == 'pc' ? TRUE : FALSE;
@endphp
    <!DOCTYPE html>
<html>
    <head>
        <title>
            {{ strip_tags($_wrap['seo']['title']) }}
        </title>
        <meta name="robots"
              content="noindex, nofollow" />
        <meta charset="utf-8">
        <meta http-equiv="Content-Type"
              content="text/html; charset=utf-8" />
        <meta name="viewport"
              content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="csrf-token"
              content="{{ $_wrap['token'] }}">
        @if(isset($_wrap['page']['favicon']) && $_wrap['page']['favicon'])
            <link href="/favicon.ico"
                  rel="shortcut icon"
                  type="image/x-icon" />
        @endif
        <script type="text/javascript">
            window.Laravel = {!! isset($_wrap['page']['js_settings']) ? (is_array($_wrap['page']['js_settings']) ? json_encode($_wrap['page']['js_settings']) : $_wrap['page']['js_settings']) : json_encode([]) !!};
            window.reCaptchaKey = '{{ $_wrap['services']['reCaptcha'] ?? NULL }}';
        </script>
        <link rel="preconnect"
              href="//fonts.googleapis.com">
        <link rel="dns-prefetch"
              href="//fonts.googleapis.com">
        <link rel="preconnect"
              href="//fonts.gstatic.com/"
              crossorigin>
        <link rel="dns-prefetch"
              href="//fonts.gstatic.com/">
        @if(isset($_wrap['page']['styles']['header']) && ($_link_styles_in_head = $_wrap['page']['styles']['header']))
            {!! $_link_styles_in_head !!}
        @endif
        @if(isset($_wrap['page']['scripts']['header']) && ($_link_scripts_in_head = $_wrap['page']['scripts']['header']))
            {!! $_link_scripts_in_head !!}
        @endif
    </head>
    <body {!! $_wrap['page']['attributes'] ?? NULL !!}>
        <div class="uk-position-relative">
            <div class="uk-top-bar">
                <div class="uk-navbar-container"
                     uk-navbar>
                    <div class="uk-navbar-left">
                        <div class="uk-navbar-item uk-padding-remove">
                            <svg class="ham hamRotate ham1 uk-menu-hamburger{{ $_open_menu ? ' active' : NULL }}"
                                 viewBox="0 0 100 100"
                                 width="60">
                                <path
                                    class="line top"
                                    d="m 30,33 h 40 c 0,0 9.044436,-0.654587 9.044436,-8.508902 0,-7.854315 -8.024349,-11.958003 -14.89975,-10.85914 -6.875401,1.098863 -13.637059,4.171617 -13.637059,16.368042 v 40" />
                                <path
                                    class="line middle"
                                    d="m 30,50 h 40" />
                                <path
                                    class="line bottom"
                                    d="m 30,67 h 40 c 12.796276,0 15.357889,-11.717785 15.357889,-26.851538 0,-15.133752 -4.786586,-27.274118 -16.667516,-27.274118 -11.88093,0 -18.499247,6.994427 -18.435284,17.125656 l 0.252538,40" />
                            </svg>
                        </div>
                        <div class="uk-navbar-item uk-logo">
                            <img src="{{ formalize_path('dashboard/images/oleus.jpg') }}"
                                 alt="OLEUS">
                        </div>
                        <div class="uk-navbar-item"></div>
                    </div>
                    <div class="uk-navbar-right">
                        <div>
                            <button class="uk-button uk-button-default uk-padding-small-left uk-padding-small-right"
                                    type="button">
                                @if($_wrap['user']->_avatar)
                                    {!! render_image($_wrap['user']->_avatar, 'avatar', ['attributes' => ['width' => 25, 'height' => 25, 'class' => 'uk-margin-xsmall-right uk-border-circle']]) !!}
                                @endif
                                {{ $_wrap['user']->full_name }}
                            </button>
                            <div uk-dropdown="mode: click; pos: bottom-right;">
                                <ul class="uk-nav uk-dropdown-nav">
                                    <li>
                                        <a href="">Доп. ссылки</a>
                                    </li>
                                    <li class="uk-nav-divider"></li>
                                    <li>
                                        <form action="{{ _r('logout') }}"
                                              method="POST"
                                              class="uk-padding-xsmall-top uk-padding-xsmall-bottom">
                                            {{ method_field('POST') }}
                                            {{ csrf_field() }}
                                            <button type="submit"
                                                    class="uk-button uk-button-link uk-text-danger uk-text-remove-underline">
                                                <span uk-icon="logout"></span> Выйти
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @if($_wrap['user']->hasRole('admin'))
                            <div>
                                <button class="uk-button uk-button-danger uk-margin-small-left"
                                        type="button">
                                    <span uk-icon="cached"></span>
                                </button>
                                <div uk-dropdown="mode: click; pos: bottom-right;">
                                    <ul class="uk-nav uk-dropdown-nav">
                                        <li>
                                            @l('Clear CACHE', 'oleus.artisan', ['p' => ['command' => 'clear', 'target' => 'cache'], 'attributes' => ['class' => 'uk-text-danger']])
                                        </li>
                                        <li>
                                            @l('Clear VIEW', 'oleus.artisan', ['p' => ['command' => 'clear', 'target' => 'view', 'attributes' => ['class' => 'uk-text-danger']]])
                                        </li>
                                        <li>
                                            @l('Clear ROUTE', 'oleus.artisan', ['p' => ['command' => 'clear', 'target' => 'route', 'attributes' => ['class' => 'uk-text-danger']]])
                                        </li>
                                        <li>
                                            @l('Clear CONFIG', 'oleus.artisan', ['p' => ['command' => 'clear', 'target' => 'config', 'attributes' => ['class' => 'uk-text-danger']]])
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        <a href="{{ _u('/') }}"
                           target="_blank"
                           class="uk-button uk-button-success uk-margin-small-left uk-but">
                            <span uk-icon="open_in_new"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="uk-left-side-bar {{ $_wrap['device']['type'] }}{{ $_open_menu && $_wrap['device']['type'] != 'pc' ? ' uk-open' : NULL }}">
                <div class="bottom-panel">
                    <div class="uk-padding-small-top">
                        @if($_menu)
                            <ul class="uk-nav uk-nav-parent-icon"
                                uk-nav>
                                @foreach($_menu as $_item)
                                    @if(isset($_item['children']) && count($_item['children']))
                                        @php
                                            $_access_item = FALSE;
                                            if(isset($_item['permission']) && $_item['permission']){
                                                foreach ($_item['permission'] as $_permission_item) {
                                                    if($_wrap['user']->can($_permission_item)) {
                                                        $_access_item = TRUE;
                                                        break;
                                                    }
                                                }
                                            }else{
                                                $_access_item = TRUE;
                                            }
                                        @endphp
                                        @if($_access_item)
                                            @php
                                                $_children = collect($_item['children']);
                                                $_children_routes = $_children->pluck('route');
                                                $_item_active = _ar($_children_routes->all());
                                            @endphp
                                            <li class="uk-parent{{ $_item_active && $_open_menu && $_wrap['device']['type'] == 'pc' ? "{$_item_active} uk-open" : ($_item_active ? $_item_active : NULL) }}">
                                                <a href="javascript:void(0);"
                                                   rel="nofollow">
                                                    {{ $_item['link'] }}
                                                    @isset($_item['icon'])
                                                        <span uk-icon="icon: {{ $_item['icon'] }}"></span>
                                                    @endisset
                                                </a>
                                                <ul class="uk-nav-sub">
                                                    @foreach($_children as $_item_children)
                                                        @if((isset($_item_children['permission']) && $_item_children['permission'] && $_wrap['user']->can($_item_children['permission'])) || (!isset($_item_children['permission']) || is_null($_item_children['permission'])))
                                                            <li class="{{ _ar($_item_children['route'], ($_item_children['params'] ?? NULL)) }}">
                                                                <a href="{{ _r($_item_children['route'], ($_item_children['params'] ?? [])) }}">
                                                                    {{ $_item_children['link'] }}
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                    @elseif($_item['route'])
                                        @if($_wrap['user']->can($_item['permission']))
                                            <li class="{{ _ar($_item['route'], NULL, TRUE) }}">
                                                <a href="{{ _r($_item['route'], ($_item['params'] ?? [])) }}">
                                                    {{ $_item['link'] }}
                                                    @isset($_item['icon'])
                                                        <span uk-icon="icon: {{ $_item['icon'] }};"></span>
                                                    @endisset
                                                </a>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <div class="uk-right-side-bar">
                <div>
                    <div class="uk-grid uk-grid-divider uk-grid-small uk-flex uk-flex-middle uk-margin-small-bottom"
                         style="border-bottom: 1px solid #e5e5e5; padding: 5px 0;">
                        <div class="uk-width-auto">
                            <h2 class="uk-article-title uk-margin-remove uk-text-thin">
                                @if($_wrap['page']['callback_route'])
                                    @l($_wrap['page']['title'], $_wrap['page']['callback_route'], ['attributes' => ['class' => 'uk-link-text']])
                                @else
                                    {!! $_wrap['page']['title'] !!}
                                @endif
                            </h2>
                        </div>
                        <div class="uk-width-expand">
                            @if($_wrap['breadcrumbs'])
                                <ul class="uk-breadcrumb uk-margin-small-top uk-margin-small-bottom">
                                    @foreach($_wrap['breadcrumbs'] as $_i)
                                        <li class="{{ $loop->last ? 'uk-active' : NULL }}">
                                            @if(!$loop->last && $_i['url'])
                                                <a href="{{ $_i['url'] }}">{!! $_i['name'] !!}</a>
                                            @else
                                                <span>{!! $_i['name'] !!}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
        @if(isset($_wrap['page']['styles']['footer']) && ($_link_styles_in_footer = $_wrap['page']['styles']['footer']))
            {!! $_link_styles_in_footer !!}
        @endif
        @if(isset($_wrap['page']['scripts']['footer']) && ($_link_scripts_in_footer = $_wrap['page']['scripts']['footer']))
            {!! $_link_scripts_in_footer !!}
        @endif
        @stack('styles')
        @stack('scripts')
        @include('backend.partials.notice')
    </body>
</html>
