@php
    if(!isset($wrap) || (isset($wrap) && !is_array($wrap))) abort(500);
@endphp
@php
    $_header_logotype = $wrap['page']['logotype']['top'];
    $_footer_logotype = $wrap['page']['logotype']['footer'] ?: $_header_logotype;
@endphp
    <!DOCTYPE html>
<html lang="{{ $wrap['locale'] }}">
    <head>
        <base href="{{ $wrap['seo']['base_url'] }}">
        <meta charset="utf-8">
        <meta http-equiv="Content-Type"
              content="text/html; charset=utf-8" />
        <meta name="viewport"
              content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <base href="{{ $wrap['seo']['base_url'] }}">
        <title>{{ strip_tags("{$wrap['seo']['title']} {$wrap['seo']['title_suffix']}{$wrap['seo']['page_number_suffix']}") }}</title>
        <meta name="description"
              content="{{ $wrap['seo']['description'] . ($wrap['seo']['description'] ? $wrap['seo']['page_number_suffix'] : NULL) }}">
        <meta name="keywords"
              content="{{ $wrap['seo']['keywords'] }}">
        <meta name="robots"
              content="{{ USE_BLOCK_SCANNING ? 'noindex, nofollow' : $wrap['seo']['robots'] }}" />
        @if(isset($wrap['seo']['last_modified']) && $wrap['seo']['last_modified'])
            <meta http-equiv="Last-Modified"
                  content="{{ $wrap['seo']['last_modified'] }}">
        @endif
        @if(isset($wrap['seo']['url']) && $wrap['seo']['url'])
            <meta name="url"
                  content="{{ $wrap['seo']['base_url'] . $wrap['seo']['url'] }}">
        @endif
        @if(isset($wrap['seo']['canonical']) && $wrap['seo']['canonical'])
            <link rel="canonical"
                  href="{{ $wrap['seo']['base_url'] . $wrap['seo']['canonical'] }}" />
        @endif
        @if(isset($wrap['seo']['color']) && $wrap['seo']['color'])
            <meta name="theme-color"
                  content="{{ $wrap['seo']['color'] }}">
        @endif
        @if(isset($wrap['seo']['copyright']) && $wrap['seo']['copyright'])
            <meta name="copyright"
                  content="{{ $wrap['seo']['copyright'] }}">
        @endif
        <meta name="csrf-token"
              content="{{ $wrap['token'] }}">
        @if(isset($wrap['page']['favicon']) && $wrap['page']['favicon'])
            <link href="/favicon.ico"
                  rel="shortcut icon"
                  type="image/x-icon" />
        @endif
        @if(isset($wrap['seo']['link_prev']) && $wrap['seo']['link_prev'])
            <link rel="prev"
                  href="{{ $wrap['seo']['base_url'] . $wrap['seo']['link_prev'] }}" />
        @endif
        @if(isset($wrap['seo']['link_next']) && $wrap['seo']['link_next'])
            <link rel="next"
                  href="{{ $wrap['seo']['base_url'] . $wrap['seo']['link_next'] }}" />
        @endif
        @if(USE_MULTI_LANGUAGE && isset($wrap['seo']['href_lang']) && $wrap['seo']['href_lang'])
            {!! $wrap['seo']['href_lang'] !!}
        @endif
        {!! $wrap['seo']['open_graph'] !!}
        <link rel="dns-prefetch"
              href="//fonts.gstatic.com" />
        <link rel="dns-prefetch"
              href="//fonts.googleapis.com" />
        <link rel="preconnect"
              href="//fonts.gstatic.com"
              crossorigin="" />
        <link rel="preconnect"
              href="//fonts.googleapis.com" />
        <link rel="dns-prefetch"
              href="//maxcdn.bootstrapcdn.com" />
        <link rel="preconnect"
              href="//maxcdn.bootstrapcdn.com" />
        <link rel="dns-prefetch"
              href="//languages" />
        <link rel="preconnect"
              href="//languages" />
        <script type="text/javascript">
            window.Laravel = {!! isset($wrap['page']['js_settings']) ? (is_array($wrap['page']['js_settings']) ? json_encode($wrap['page']['js_settings']) : $wrap['page']['js_settings']) : json_encode([]) !!};
            var FbData = {path: '{!! $wrap['seo']['url'] != '/' ? trim($wrap['seo']['url'], '/') : '/' !!}', locale: '{!! $wrap['locale'] !!}', title: '{!! $wrap['page']['title'] !!}', device: '{!! $wrap['device']['type'] !!}'}
        </script>
        @if(isset($wrap['page']['styles']['header']) && ($_link_styles_in_head = $wrap['page']['styles']['header']))
            {!! $_link_styles_in_head !!}
        @endif
        @if(isset($wrap['page']['scripts']['header']) && ($_link_scripts_in_head = $wrap['page']['scripts']['header']))
            {!! $_link_scripts_in_head !!}
        @endif
        @if($wrap['services']['googleTAG'])
            <script async
                    src="https://www.googletagmanager.com/gtag/js?id={{ $wrap['services']['googleTAG'] }}"></script>
            <script type="text/javascript">
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }

                gtag('js', new Date());
                gtag('config', '{{ $wrap['services']['googleTAG'] }}', {'send_page_view': false});
            </script>
        @endif
        @if($wrap['services']['googleGTM'])
            <script>
                (function (w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({
                        'gtm.start':
                            new Date().getTime(), event: 'gtm.js'
                    });
                    var f = d.getElementsByTagName(s)[0],
                        j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', '{{ $wrap['services']['googleGTM'] }}');
            </script>
        @endif
        @if($wrap['services']['facebookPixel'])
            <script>
                !function (f, b, e, v, n, t, s) {
                    if (f.fbq) return;
                    n = f.fbq = function () {
                        n.callMethod ?
                            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n;
                    n.push = n;
                    n.loaded = !0;
                    n.version = '2.0';
                    n.queue = [];
                    t = b.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s)
                }(window, document, 'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{ $wrap['services']['facebookPixel'] }}');
                fbq('track', 'PageView');
            </script>
        @endif
    </head>
    <body {!! $wrap['page']['attributes'] ?? NULL !!}>
        <div id="app"
             class="">
            <header class="">
                    <nav class="uk-navbar-container  uk-container uk-container-custom-large uk-navbar-transparent  " uk-navbar="dropbar: true; dropbar-mode: push">

                            <div class="uk-navbar-left uk-navbar-item uk-logo">
                                <div class="logo-container">
                                    @if($wrap['page']['is_front'])
                                        {!! render_image($_header_logotype, NULL, ['attributes' => ['uk-img' => TRUE, 'alt' => $wrap['page']['site_name'],
                                        'width' => '99', 'height' => '170']]) !!}

                                    @else
                                        <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}"
                                           class="">
                                            {!! render_image($_header_logotype, NULL, ['attributes' => ['alt' => $wrap['page']['site_name'],
                                            'width' => '99', 'height' => '170']]) !!}
                                        </a>
                                    @endif
                                    <span>Универсальный трекер посылок для отслеживания</span>
                                </div>
                            </div>
                            @if($wrap['device']['type'] === 'mobile')

                                <div class="uk-navbar-right uk-navbar-item">
                                    <a class="uk-link-reset lang  " uk-toggle="target: .wrapper-modal">Русский
                                    </a>





                                        {{--                                        <button class="uk-modal-close-default" type="button" uk-close></button>--}}
                                        {{--                                       --}}
                                        {{--                                        @if(USE_MULTI_LANGUAGE)--}}
                                        {{--                                            <div class="block-language-link">--}}
                                        {{--                                                <ul>--}}
                                        {{--                                                    @foreach($wrap['page']['translate_links'] as $_locale_code => $_properties)--}}
                                        {{--                                                        <li class="{{ $locale == $_properties['code'] ? 'active' : NULL }}">--}}
                                        {{--                                                            @if($locale == $_properties['code'])--}}
                                        {{--                                                                {!! $_properties['active'] !!}--}}
                                        {{--                                                            @else--}}
                                        {{--                                                                {!! $_properties['link'] !!}--}}
                                        {{--                                                            @endif--}}
                                        {{--                                                        </li>--}}
                                        {{--                                                    @endforeach--}}
                                        {{--                                                </ul>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        @endif--}}


                                        <div class="wrapper-modal" uk-modal>
                                            <div class="modal">
                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                <div class="modal__title_wrapper">
                                                    <div class="modal__title">Выберите Ваш язык</div>
                                                    <p>Пожалуйста, помогите нам сделать сервис лучше. О всех найденных ошибках в переводе сообщайте на наш E-mail</p>
                                                </div>
                                                <div class="wrapperes">
                                                    <div class="lang__wrapper">
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>

                                                    </div>
                                                    <div class="thanks">
                                                        <div class="thanks__title">
                                                            Спасибо за перевод нашим клиентам:
                                                        </div>
                                                        <ul>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>


                                    <div class="uk-inline">
                                        <button class="user-btn" type="button">
                                            <img src="images/user.svg" alt="">
                                        </button>
                                        <div uk-dropdown="mode: click">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</div>
                                    </div>
                                    @menu(2)
                                </div>
                            @else
                                @menu(1)
                                <div class="uk-navbar-right uk-navbar-item">
                                    <a class="uk-link-reset lang  " uk-toggle="target: .wrapper-modal">Русский
                                    </a>





                                        {{--                                        <button class="uk-modal-close-default" type="button" uk-close></button>--}}
                                        {{--                                       --}}
                                        {{--                                        @if(USE_MULTI_LANGUAGE)--}}
                                        {{--                                            <div class="block-language-link">--}}
                                        {{--                                                <ul>--}}
                                        {{--                                                    @foreach($wrap['page']['translate_links'] as $_locale_code => $_properties)--}}
                                        {{--                                                        <li class="{{ $locale == $_properties['code'] ? 'active' : NULL }}">--}}
                                        {{--                                                            @if($locale == $_properties['code'])--}}
                                        {{--                                                                {!! $_properties['active'] !!}--}}
                                        {{--                                                            @else--}}
                                        {{--                                                                {!! $_properties['link'] !!}--}}
                                        {{--                                                            @endif--}}
                                        {{--                                                        </li>--}}
                                        {{--                                                    @endforeach--}}
                                        {{--                                                </ul>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        @endif--}}


                                        <div class="wrapper-modal" uk-modal>
                                            <div class="modal">
                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                <div class="modal__title_wrapper">
                                                    <div class="modal__title">Выберите Ваш язык</div>
                                                    <p>Пожалуйста, помогите нам сделать сервис лучше. О всех найденных ошибках в переводе сообщайте на наш E-mail</p>
                                                </div>
                                                <div class="wrapperes">
                                                    <div class="lang__wrapper">
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>

                                                    </div>
                                                    <div class="thanks">
                                                        <div class="thanks__title">
                                                            Спасибо за перевод нашим клиентам:
                                                        </div>
                                                        <ul>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>


                                    <div class="uk-inline">
                                        <button class="user-btn" type="button">
                                            <img src="images/user.svg" alt="">
                                        </button>
                                        <div uk-dropdown="mode: click">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</div>
                                    </div>
                                </div>
                            @endif






                    </nav>
            </header>
            <div id="main-wrapper">
                @if($errors->any())
                    <div class="uk-alert uk-alert-danger uk-margin-small-bottom">
                        <ul class="uk-list">
                            @foreach ($errors->all() as $_error)
                                <li class="uk-margin-remove">{!! $_error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
{{--                 @include('frontend.default.partials.breadcrumbs')--}}
                @yield('content')
            </div>
            <footer class="uk-margin-small-top">

                    <nav class="uk-navbar-container  uk-container uk-container-custom-large uk-navbar-transparent " uk-navbar="">

                        <div class="uk-navbar-left uk-navbar-item uk-logo">
                            <div class="logo-container">
                                @if($wrap['page']['is_front'])
                                    {!! render_image($_footer_logotype, NULL, ['attributes' => ['alt' => $wrap['page']['site_name'],
                                    'width' => '99', 'height' => '170']]) !!}


                                @else
                                    <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}"
                                        >
                                        {!! render_image($_footer_logotype, NULL, ['attributes' => ['alt' => $wrap['page']['site_name'],
                                        'width' => '99', 'height' => '170']]) !!}
                                    </a>
                                @endif

                            </div>
                            <span>{!! $wrap['page']['site_copyright'] !!}</span>
                        </div>
                        @if($wrap['device']['type'] === 'mobile')

                            <div class="uk-navbar-right uk-navbar-item">
                                <a class="uk-link-reset lang  " uk-toggle="target: .wrapper-modal">Русский
                                </a>





                                    {{--                                        <button class="uk-modal-close-default" type="button" uk-close></button>--}}
                                    {{--                                       --}}
                                    {{--                                        @if(USE_MULTI_LANGUAGE)--}}
                                    {{--                                            <div class="block-language-link">--}}
                                    {{--                                                <ul>--}}
                                    {{--                                                    @foreach($wrap['page']['translate_links'] as $_locale_code => $_properties)--}}
                                    {{--                                                        <li class="{{ $locale == $_properties['code'] ? 'active' : NULL }}">--}}
                                    {{--                                                            @if($locale == $_properties['code'])--}}
                                    {{--                                                                {!! $_properties['active'] !!}--}}
                                    {{--                                                            @else--}}
                                    {{--                                                                {!! $_properties['link'] !!}--}}
                                    {{--                                                            @endif--}}
                                    {{--                                                        </li>--}}
                                    {{--                                                    @endforeach--}}
                                    {{--                                                </ul>--}}
                                    {{--                                            </div>--}}
                                    {{--                                        @endif--}}


                                    <div class="wrapper-modal" uk-modal>
                                        <div class="modal">
                                            <button class="uk-modal-close-default" type="button" uk-close></button>
                                            <div class="modal__title_wrapper">
                                                <div class="modal__title">Выберите Ваш язык</div>
                                                <p>Пожалуйста, помогите нам сделать сервис лучше. О всех найденных ошибках в переводе сообщайте на наш E-mail</p>
                                            </div>
                                            <div class="wrapperes">
                                                <div class="lang__wrapper">
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/1.jpg" alt="flag">
                                                        <a href="#" class="lang__name">English</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/2.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Español</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/3.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Русский</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/4.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Deutsch</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/5.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Italiano</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/6.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Українська</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/7.jpg" alt="flag">
                                                        <a href="#" class="lang__name">język polski</a>
                                                    </div>
                                                    <div class="lang__item">
                                                        <img src="images/flags/8.jpg" alt="flag">
                                                        <a href="#" class="lang__name">Azərbaycan dili</a>
                                                    </div>

                                                </div>
                                                <div class="thanks">
                                                    <div class="thanks__title">
                                                        Спасибо за перевод нашим клиентам:
                                                    </div>
                                                    <ul>
                                                        <li>
                                                            <span>Андрей Иванов</span>
                                                            Деятельность: <a href="#">Web-designer</a>
                                                        </li>
                                                        <li>
                                                            <span>Андрей Иванов</span>
                                                            Деятельность: <a href="#">Web-designer</a>
                                                        </li>
                                                        <li>
                                                            <span>Андрей Иванов</span>
                                                            Деятельность: <a href="#">Web-designer</a>
                                                        </li>
                                                        <li>
                                                            <span>Андрей Иванов</span>
                                                            Деятельность: <a href="#">Web-designer</a>
                                                        </li>
                                                        <li>
                                                            <span>Андрей Иванов</span>
                                                            Деятельность: <a href="#">Web-designer</a>
                                                        </li>
                                                        <li>
                                                            <span>Андрей Иванов</span>
                                                            Деятельность: <a href="#">Web-designer</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>


                                        </div>
                                    </div>



                                @menu(4)
                            </div>
                        @else
                                @menu(3)
                            <div class="uk-navbar-right uk-navbar-item">
                                <a class="uk-link-reset lang  " uk-toggle="target: .wrapper-modal">Русский
                                </a>






{{--                                        <button class="uk-modal-close-default" type="button" uk-close></button>--}}
{{--                                       --}}
{{--                                        @if(USE_MULTI_LANGUAGE)--}}
{{--                                            <div class="block-language-link">--}}
{{--                                                <ul>--}}
{{--                                                    @foreach($wrap['page']['translate_links'] as $_locale_code => $_properties)--}}
{{--                                                        <li class="{{ $locale == $_properties['code'] ? 'active' : NULL }}">--}}
{{--                                                            @if($locale == $_properties['code'])--}}
{{--                                                                {!! $_properties['active'] !!}--}}
{{--                                                            @else--}}
{{--                                                                {!! $_properties['link'] !!}--}}
{{--                                                            @endif--}}
{{--                                                        </li>--}}
{{--                                                    @endforeach--}}
{{--                                                </ul>--}}
{{--                                            </div>--}}
{{--                                        @endif--}}


                                        <div class="wrapper-modal" uk-modal>
                                            <div class="modal">
                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                <div class="modal__title_wrapper">
                                                    <div class="modal__title">Выберите Ваш язык</div>
                                                    <p>Пожалуйста, помогите нам сделать сервис лучше. О всех найденных ошибках в переводе сообщайте на наш E-mail</p>
                                                </div>
                                                <div class="wrapperes">
                                                    <div class="lang__wrapper">
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/1.jpg" alt="flag">
                                                            <a href="#" class="lang__name">English</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/2.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Español</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/3.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Русский</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/4.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Deutsch</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/5.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Italiano</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/6.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Українська</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/7.jpg" alt="flag">
                                                            <a href="#" class="lang__name">język polski</a>
                                                        </div>
                                                        <div class="lang__item">
                                                            <img src="images/flags/8.jpg" alt="flag">
                                                            <a href="#" class="lang__name">Azərbaycan dili</a>
                                                        </div>

                                                    </div>
                                                    <div class="thanks">
                                                        <div class="thanks__title">
                                                            Спасибо за перевод нашим клиентам:
                                                        </div>
                                                        <ul>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                            <li>
                                                                <span>Андрей Иванов</span>
                                                                Деятельность: <a href="#">Web-designer</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>


                                        </div>
                                    </div>

                            </div>
                        @endif
                    </nav>
            </footer>
        </div>
        @if(isset($wrap['page']['styles']['footer']) && ($_link_styles_in_footer = $wrap['page']['styles']['footer']))
            {!! $_link_styles_in_footer !!}
        @endif
        @if(isset($wrap['page']['scripts']['footer']) && ($_link_scripts_in_footer = $wrap['page']['scripts']['footer']))
            {!! $_link_scripts_in_footer !!}
        @endif
        @stack('styles')
        @stack('scripts')
        @if($wrap['microdata'])
            {!! $wrap['microdata'] !!}
        @endif
        @if($wrap['services']['googleGTM'])
            <noscript>
                <iframe src="https://www.googletagmanager.com/ns.html?id={{ $wrap['services']['googleGTM'] }}"
                        height="0"
                        width="0"
                        style="display:none;visibility:hidden"></iframe>
            </noscript>
        @endif
        @if($wrap['services']['facebookPixel'])
            <noscript>
                <img height="1"
                     width="1"
                     style="display:none"
                     src="https://www.facebook.com/tr?id={{ $wrap['services']['facebookPixel'] }}&ev=PageView&noscript=1"
                />
            </noscript>
        @endif
{{--        @if($authUser)--}}
{{--            <div id="control-edit-box"--}}
{{--                 class="uk-position-top-right uk-margin-top uk-margin-right">--}}
{{--                <div class="uk-card uk-card-body uk-card-default uk-padding-small uk-border-rounded uk-box-shadow-small uk-text-small">--}}
{{--                    @stack('edit_page')--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}
    </body>
</html>
