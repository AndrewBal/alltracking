@extends('frontend.default.index')

@section('content')
    <article {!! render_attributes($_item->styleAttributes) !!}>
        <section class="main uk-container   uk-container-custom">
            <div class="main_logo">
                <img src="images/logo.svg" alt="logo" width="438" height="71">
            </div>

            <track-form packages='' delivery='' deliveries='{!! $_others['deliveries'] !!}' alphabet='{!! $_others['alphabet'] !!}'></track-form>

            <div class="front-block-1">

                <div id="page-body"
                     class="uk-margin-top">
                    <h1 id="page-title"
                        class=" ">
                        {!! $_item->title !!}
                    </h1>
                    {!! $_item->body !!}
                </div>
                @block(2)




        </section>
        <section class="front-bg-grey">
            <div class="uk-container   uk-container-custom">

                @block(3)

            </div>

            <div class="uk-container  uk-container-custom">
                <hr>
                   <div class="front-bg-grey__bottom">
                       @block(4)
                       @block(5)
                   </div>
            </div>
        </section>


        <section class="problems">
            <div class="uk-container  uk-container-custom">
                @block(6)
            </div>
        </section>


        <section class="advantage-1">
            <div class="uk-container  uk-container-custom">
                @advantage(1)
            </div>
        </section>




         <section class="advantage-2">
            <div class="uk-container  uk-container-custom">
                @advantage(2)
            </div>
        </section>






        <div class="front-sidebtns uk-position-fixed uk-position-center-right">
            <a href="" class="totop sidebtn" uk-totop uk-scroll></a>
            <button class="sidebtn socials" type="button"></button>
            <div uk-dropdown="pos: left-center; offset: 0">
                <a class="vk sidebtn-social" target="_blank"
                   href="{!! $wrap['loads']['contacts']['socials']['vk'] !!}">
                    <svg width="25" height="15" viewBox="0 0 25 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24.7772 12.7574C24.71 12.6449 24.2943 11.7417 22.2942 9.88532C20.2003 7.94142 20.4816 8.25707 23.0036 4.89588C24.5397 2.84886 25.1538 1.59877 24.9616 1.06435C24.7787 0.554938 23.649 0.689323 23.649 0.689323L19.8925 0.711199C19.8925 0.711199 19.6143 0.673697 19.4065 0.797143C19.2049 0.919027 19.0752 1.2003 19.0752 1.2003C19.0752 1.2003 18.4798 2.78479 17.686 4.13177C16.0125 6.97416 15.3421 7.12418 15.0687 6.9476C14.4327 6.53663 14.5921 5.29435 14.5921 4.41304C14.5921 1.65814 15.0093 0.509622 13.7779 0.212725C13.3685 0.11428 13.0685 0.0486503 12.0231 0.037712C10.6824 0.0236484 9.54637 0.0423998 8.90413 0.356486C8.47598 0.565876 8.14627 1.0331 8.34784 1.05966C8.5963 1.09248 9.15884 1.21124 9.4573 1.61752C9.84327 2.14099 9.8292 3.31921 9.8292 3.31921C9.8292 3.31921 10.0511 6.56163 9.31198 6.96479C8.80413 7.24137 8.10876 6.67727 6.61646 4.09739C5.85234 2.77698 5.27418 1.31593 5.27418 1.31593C5.27418 1.31593 5.16323 1.04404 4.96478 0.898714C4.72414 0.722138 4.38661 0.665884 4.38661 0.665884L0.814472 0.68776C0.814472 0.68776 0.278494 0.703387 0.0816049 0.936217C-0.0934081 1.14404 0.0675413 1.5722 0.0675413 1.5722C0.0675413 1.5722 2.86462 8.11487 6.03048 11.4136C8.93539 14.4372 12.2325 14.2388 12.2325 14.2388H13.7264C13.7264 14.2388 14.178 14.1888 14.4077 13.9403C14.6202 13.7122 14.6124 13.284 14.6124 13.284C14.6124 13.284 14.5827 11.2792 15.514 10.9838C16.4313 10.6932 17.6095 12.9215 18.858 13.7794C19.8018 14.4278 20.5191 14.2856 20.5191 14.2856L23.8584 14.2388C23.8584 14.2388 25.6054 14.1309 24.7772 12.7574Z" fill="#797878"/>
                    </svg>

                </a>
                <a class="linkedin sidebtn-social" target="_blank"
                   href="{!! $wrap['loads']['contacts']['socials']['linkedin'] !!}">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.02525 5.625H0V18H4.02525V5.625Z" fill="#797878"/>
                        <path d="M14.9895 5.77013C14.9468 5.75663 14.9062 5.742 14.8612 5.72962C14.8073 5.71725 14.7532 5.70713 14.6981 5.69813C14.4844 5.65538 14.2504 5.625 13.9759 5.625C11.6291 5.625 10.1407 7.33163 9.65025 7.99088V5.625H5.625V18H9.65025V11.25C9.65025 11.25 12.6923 7.01325 13.9759 10.125C13.9759 12.9026 13.9759 18 13.9759 18H18V9.64912C18 7.77937 16.7186 6.22125 14.9895 5.77013Z" fill="#797878"/>
                        <path d="M1.96875 3.9375C3.05606 3.9375 3.9375 3.05606 3.9375 1.96875C3.9375 0.881439 3.05606 0 1.96875 0C0.881439 0 0 0.881439 0 1.96875C0 3.05606 0.881439 3.9375 1.96875 3.9375Z" fill="#797878"/>
                    </svg>

                </a>
                <a class="facebook sidebtn-social" target="_blank"
                   href="{!! $wrap['loads']['contacts']['socials']['facebook'] !!}">
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.875 0H2.125C0.953062 0 0 0.953062 0 2.125V14.875C0 16.0469 0.953062 17 2.125 17H8.5V11.1562H6.375V8.5H8.5V6.375C8.5 4.61444 9.92694 3.1875 11.6875 3.1875H13.8125V5.84375H12.75C12.1635 5.84375 11.6875 5.7885 11.6875 6.375V8.5H14.3438L13.2812 11.1562H11.6875V17H14.875C16.0469 17 17 16.0469 17 14.875V2.125C17 0.953062 16.0469 0 14.875 0Z" fill="#797878"/>
                    </svg>


                </a>
                <a class="twitter sidebtn-social" target="_blank"
                   href="{!! $wrap['loads']['contacts']['socials']['twitter'] !!}">
                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 2.01994C20.2191 2.3625 19.3869 2.58956 18.5194 2.69981C19.4119 2.16694 20.0931 1.32956 20.4133 0.32025C19.5812 0.816375 18.6624 1.16681 17.6833 1.36238C16.8932 0.521063 15.7671 0 14.5386 0C12.1551 0 10.2362 1.93463 10.2362 4.30631C10.2362 4.64756 10.2651 4.97569 10.3359 5.28806C6.75675 5.1135 3.58969 3.39806 1.46212 0.784875C1.09069 1.42931 0.872813 2.16694 0.872813 2.961C0.872813 4.452 1.64062 5.77369 2.78513 6.53887C2.09344 6.52575 1.41488 6.32494 0.84 6.00863C0.84 6.02175 0.84 6.03881 0.84 6.05587C0.84 8.148 2.33231 9.88575 4.28925 10.2861C3.93881 10.3819 3.55687 10.4278 3.1605 10.4278C2.88488 10.4278 2.60662 10.4121 2.34544 10.3543C2.90325 12.0592 4.48613 13.3127 6.36825 13.3534C4.9035 14.4992 3.04369 15.1896 1.03031 15.1896C0.67725 15.1896 0.338625 15.1738 0 15.1305C1.90706 16.3603 4.16719 17.0625 6.6045 17.0625C14.5267 17.0625 18.858 10.5 18.858 4.81163C18.858 4.62131 18.8514 4.43756 18.8423 4.25513C19.6967 3.64875 20.4146 2.89144 21 2.01994Z" fill="#797878"/>
                    </svg>

                </a>
            </div>
            <button class="sidebtn likebtn" type="button"></button>
        </div>


    </article>
@endsection

{{--@push('edit_page')--}}
{{--    @if(isset($accessEdit['page']) && $accessEdit['page'])--}}
{{--        @if($locale == DEFAULT_LOCALE)--}}
{{--            @l('Редактировать', 'oleus.pages.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])--}}
{{--        @else--}}
{{--            @l('Редактировать', 'oleus.pages.translate', ['p' => [$_item, $locale], 'attributes' => ['target' => '_blank']])--}}
{{--        @endif--}}
{{--    @endif--}}
{{--@endpush--}}
