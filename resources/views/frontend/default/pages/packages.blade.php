@extends('frontend.default.index')

@section('content')







    <article {!! render_attributes($_item->styleAttributes) !!}>
        <button class="uk-position-center-right search-btn-fix uk-position-fixed">
            <span uk-search-icon></span>
        </button>
        <div class="uk-flex  page-status" >

            <tracking packages='{!! json_encode($_item->packages) !!}'></tracking>
            <div class="track-status-sidebar">
                <track-form packages='' delivery='' deliveries='{!! $_others['deliveries'] !!}' alphabet='{!! $_others['alphabet'] !!}'></track-form>

                <div class="side-faq">
                    <div class="side-faq__title">
                        Нужна помощь?
                    </div>
                    <ul>
                        <li>Как я могу отследить посылку или
                            ePacket от EMS China?</li>
                        <li>Моя посылка из Китая в пути, что это
                            значит?</li>
                        <li>Что такое зарегистрированная/
                            отслеживаемая и
                            незарегистрированная посылка?</li>
                        <li>Как отследить посылку, отправленную
                            из Китая и как долго?</li>
                    </ul>


                    <button class="track-btn">Посетите наш FAQ</button>
                </div>
            </div>


        </div>


    </article>

@endsection

