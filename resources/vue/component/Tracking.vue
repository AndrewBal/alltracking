<template>

    <div class="track-status-container ">
        <div class="uk-container uk-container-custom-large">
            <div class="track-filter-container">
                <div class="track-filter-items">
                        <span class="count-filter">
                            Все(17)
                        </span>
                    <div class="track-filter-item close-fltr">

                        <span>(1)</span>
                    </div>
                    <div class="track-filter-item plane-fltr">

                        <span>(1)</span>
                    </div>
                    <div class="track-filter-item flag-fltr">

                        <span>(1)</span>
                    </div>
                    <div class="track-filter-item smile-fltr">

                        <span>(1)</span>
                    </div>
                    <div class="track-filter-item check-fltr">

                        <span>(1)</span>
                    </div>
                    <div class="track-filter-item warning-fltr">

                        <span>(1)</span>
                    </div>
                    <div class="track-filter-item alarm-fltr">
                        <span>(1)</span>
                    </div>
                </div>
                <div class="track-filter-actions">
                    <button class="track-filter-btn copy-btn">
                        <img src="/images/copy.svg" alt="">
                    </button>
                    <button class="track-filter-btn file-btn">
                        <img src="/images/file.svg" alt="">
                    </button>
                    <button class="track-filter-btn update-btn">
                        <img src="/images/update.svg" alt="">
                    </button>
                    <button class="track-filter-btn turn-btn">
                        <img src="/images/turn.svg" alt="">
                    </button>
                </div>
            </div>
            <div class="track-status-accordion">
                <ul uk-accordion>
                    <li>
                        <div class="accordion-title" href="#">
                            <div class="track-title-container done">
                                <div class="main-info">
                                    <span class="track-code">AEPU0003733721RU2</span>
                                    <span class="track-code-status">Доставлено (9 Дни)</span>
                                    <button class="track-filter-btn update-btn">
                                        <img src="/images/update.svg" alt="">
                                    </button>
                                </div>
                                <div class="track-route">
                                    <div class="track-route__from">
                                        <span class="country">Российская Федерация</span>
                                        <a    class="delivery"> DPD (RU)</a>
                                        <ul  uk-dropdown="mode: click">
                                            <li>
                                                <a href="">
                                                    Официальный сайт
                                                </a> </li>
                                            <li>
                                                <a href="">
                                                    Выбрать перевозчика
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <img src="images/arrow.svg" alt="">
                                    <div class="track-route__to unknown">
                                        <span class="country">Неизвестный</span>
                                        <a   class="delivery">Fedex</a>
                                        <ul  uk-dropdown="mode: click">
                                            <li>
                                                <a href="">
                                                    Официальный сайт
                                                </a> </li>
                                            <li>
                                                <a href="">
                                                    Выбрать перевозчика
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="add-info">
                                    <span>2021-03-23 00:00Delivered</span>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        </div>
                    </li>
                    <li>
                        <div class="accordion-title" href="#">
                            <div class="track-title-container">
                                <div class="main-info">
                                    <span class="track-code">AEPU0003733721RU2</span>
                                    <span class="track-code-status">Доставлено (9 Дни)</span>
                                    <button class="track-filter-btn update-btn">
                                        <img src="/images/update.svg" alt="">
                                    </button>
                                </div>

                                <div class="track-route">
                                    <div class="track-route__from warning">

                                        <span class="country">Неизвестный</span>
                                        <a  class="delivery"> DPD (RU)</a>
                                        <ul  uk-dropdown="mode: click">
                                            <li>
                                                <a href="">
                                                    Официальный сайт
                                                </a> </li>
                                            <li>
                                                <a href="">
                                                    Выбрать перевозчика
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <img src="images/arrow.svg" alt="">
                                    <div class="track-route__to ">
                                        <span class="country">Неизвестный</span>
                                    </div>
                                </div>
                                <div class="add-info">
                                    <span class="warning-txt" >Ваш номер отслеживания в данный момент не найден. Он пока не доступен в системе перевозчика. Пожалуйста, попробуйте позже.</span>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor reprehenderit.</p>
                        </div>
                    </li>
                    <li>
                        <div class="accordion-title" href="#">
                            <div class="track-title-container done">
                                <div class="main-info">
                                    <span class="track-code">LP194598804SG</span>
                                    <span class="track-code-status">Доставлено (3 Дни)</span>
                                    <button class="track-filter-btn update-btn">
                                        <img src="/images/update.svg" alt="">
                                    </button>
                                </div>
                                <div class="track-route">
                                    <div class="track-route__from">

                                        <span class="country">Сингапур</span>
                                        <a    class="delivery delivery-link">Singapore Post</a>
                                        <ul  uk-dropdown="mode: click">
                                            <li>
                                                <a href="">
                                                    Официальный сайт
                                                </a> </li>
                                            <li>
                                                <a href="">
                                                    Выбрать перевозчика
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <img src="images/arrow.svg" alt="">
                                    <div class="track-route__to ">
                                        <span class="country">Таиланд</span>
                                        <a  class="delivery delivery-link">Thailand Post</a>
                                        <ul  uk-dropdown="mode: click">
                                            <li>
                                                <a href="">
                                                    Официальный сайт
                                                </a> </li>
                                            <li>
                                                <a href="">
                                                    Выбрать перевозчика
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="add-info">
                                    <span  >2021-03-17 12:02RAWAI, Final delivery, Successful</span>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <div class="track-info-destination">
                                <div class="track-info-title">
                                    Пункт назначения - <span>Прогнозируемый период доставки: 09.04.2021- 19.04.2021(не гарантируемый)</span>
                                    <button type="button" class="reference"></button>
                                    <div class="help-info" uk-dropdown="mode: click">
                                        <div class="help-info__header">
                                            <span>Справочная информация</span>
                                        </div>
                                        <div class="help-info__content">
                                            <p>
                                                Срок доставки рассчитывается на основании анализа статистик дотавленных международных почтовых отправлений за последний месяц. В расчет не берутся задержки с отправкой посылки, зависания на томожне, утерянные посылки и т.д. Указанный период, это не 100% информация, а всего лишь прогноз, ориентирующий Вас когда ждать помылку. Если срок доставки Вашей посылки больше, чем указано в прогнозе, не переживайте, она приедет, но чуть позже)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <a href="" class="feedback-link">Как все прошло? Оцените службу доставки!</a>
                                <ul class="track-info-items">
                                    <li class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">RAWAI, Final delivery, Successful</span>
                                    </li>
                                    <li class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">RAWAI, Final delivery, Successful</span>
                                    </li>
                                    <li class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">RAWAI, Final delivery, Successful</span>
                                    </li>
                                    <li class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">RAWAI, Final delivery, Successful</span>
                                    </li>
                                    <li class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">RAWAI, Final delivery, Successful</span>
                                    </li>
                                    <li class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">RAWAI, Final delivery, Successful</span>
                                    </li>
                                </ul>

                            </div>
                            <div class="track-info-dispatch">
                                <div class="track-info-title">
                                    Пункт отправления - <span>Отслеживание потребления: 181 ms</span>
                                    <span class="reference"></span>
                                </div>
                                <div class="track-info-items">
                                    <div class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">Product Delivered (Country code: TH)</span>
                                    </div>
                                    <div class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">Product Delivered (Country code: TH)</span>
                                    </div>
                                    <div class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">Released from Custom</span>
                                    </div>
                                    <div class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">Released from Custom</span>
                                    </div>
                                    <div class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">Despatched to overseas (Country code: TH)</span>
                                    </div>
                                    <div class="track-info-item">
                                        <span class="track-info-content__date">2021-03-17 10:53</span>
                                        <span class="track-info-content__place">Despatched to overseas (Country code: TH)</span>
                                    </div>
                                </div>

                            </div>

                            <div class="uk-accordion-content-bottom">
                                <div class="links">
                                    <a href="" class="copy-link">Копировать детали</a>
                                    <a href="" class="tracking-link">Ссылка для отслеживания</a>
                                </div>
                                <div class="right-row">
                                    <div class="social-links">
                                        <a href="" class="viber">
                                            <img src="images/viber.svg" alt="">
                                        </a>
                                        <a href="" class="vk">
                                            <img src="images/vk.svg" alt="">
                                        </a>
                                        <a href="" class="phone">
                                            <img src="images/phone.svg" alt="">
                                        </a>
                                    </div>
                                    <div class="lang-block">
                                        <span>Перевести:</span>
                                        <a class="   " uk-toggle="target: #modal-close-default">Русский
                                        </a>
                                        <div id="modal-close-default" uk-modal>
                                            <div class="uk-modal-dialog uk-modal-body">
                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                <h2 class="uk-modal-title">Default</h2>
                                                @if(USE_MULTI_LANGUAGE)
                                                <div class="block-language-link">
                                                    <ul>
                                                        @foreach($wrap['page']['translate_links'] as $_locale_code => $_properties)
                                                        <li class="{{ $locale == $_properties['code'] ? 'active' : NULL }}">
                                                            @if($locale == $_properties['code'])
                                                            {!! $_properties['active'] !!}
                                                            @else
                                                            {!! $_properties['link'] !!}
                                                            @endif
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <label class="container-checkbox">
                                            <input type="checkbox" class="chexkbox-alltrack">
                                            <span class="checkmark"></span>
                                        </label>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li  >
                        <div class="accordion-title " href="#">

                            <div class="track-title-container tracking">
                                <div class="main-info">
                                    <span class="track-code">LP194598804SG</span>
                                    <span class="track-code-status">Доставлено (3 Дни)</span>
                                    <button class="track-filter-btn update-btn">
                                        <img src="/images/update.svg" alt="">
                                    </button>
                                </div>


                                <div @click="toggleAccordion()" class="add-info">
                                    <div class="loadingio-spinner-dual-ball-pc7xkp8eqx"><div class="ldio-q63b8iya3bo">
                                        <div></div><div></div><div></div>
                                    </div></div>
                                    <span  >Отслеживание, немного подождите</span>
                                </div>
                            </div>
                        </div>

                    </li>
                    <li ref="accordionItem">
                        <div class="accordion-title" href="#">
                            <div class="track-title-container not-found">
                                <div class="main-info">
                                    <span class="track-code">LP194598804SG</span>
                                    <span class="track-code-status">Не найдено</span>
                                    <button class="track-filter-btn update-btn">
                                        <img src="/images/update.svg" alt="">
                                    </button>
                                </div>
                                <button type="button" class="accordion-btn">Выбрать перевозчика</button>
                                <div @click="toggleAccordion()" class="add-info">
                                    <span  class="warning-txt">Для этого числа есть несколько возможных перевозчиков.</span>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <span class="title warning-txt">Трек может относиться к одному из следующих перевозчиков:</span>
                            <div class="delivery-items">
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                                <div class="delivery-item">
                                    <img src="images/5post.png" alt="5post">
                                    <a>5Post</a>
                                </div>
                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </div>


    </div>

</template>


<script>




export default {
    data() {
        return {
            tracksData: null,
        }
    },
    props: [
        'packages'
    ],
    created() {
        this.tracksData = JSON.parse(this.packages)
        console.log(this.tracksData)
    },
    methods: {
        toggleAccordion() {
            this.$refs.accordionItem.classList.toggle("open");
        }
    }
}
</script>
