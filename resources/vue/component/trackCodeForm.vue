<template>
    <div class="block-track-form">
        <div @click="focus()" class="track-form-container">
            <div class="list-trackcode">
                <div class="trackcode" v-for="(item, index) in items" :key="item.id">
                    <span>{{ index + 1 }}.</span>
                    <input
                        v-if="item.id == 0"
                        ref="items"
                        v-model="item.datatype"
                        class="trackcode-input"
                        @input="showBtn($event.target.value, index)"
                        @keyup.enter="addItem($event.target.value, index); focusItem(index)"
                        :placeholder="placeholder"
                    >
                    <input
                        v-else
                        ref="items"
                        v-model="item.datatype"
                        class="trackcode-input"
                        @input="showBtn($event.target.value, index)"
                        @keyup.enter="addItem($event.target.value, index); focusItem(index)"
                        placeholder=""
                    >
                    <svg
                        @click="deleteItem(index)"
                        v-if="close"
                        width="12"
                        height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M12 6C12 7.5913 11.3679 9.11742 10.2426 10.2426C9.11742 11.3679 7.5913 12 6 12C4.4087 12 2.88258 11.3679 1.75736 10.2426C0.632141 9.11742 0 7.5913 0 6C0 4.4087 0.632141 2.88258 1.75736 1.75736C2.88258 0.632141 4.4087 0 6 0C7.5913 0 9.11742 0.632141 10.2426 1.75736C11.3679 2.88258 12 4.4087 12 6ZM4.0155 3.4845C3.94509 3.41408 3.84958 3.37453 3.75 3.37453C3.65042 3.37453 3.55491 3.41408 3.4845 3.4845C3.41408 3.55491 3.37453 3.65042 3.37453 3.75C3.37453 3.84958 3.41408 3.94509 3.4845 4.0155L5.46975 6L3.4845 7.9845C3.44963 8.01937 3.42198 8.06076 3.40311 8.10631C3.38424 8.15187 3.37453 8.20069 3.37453 8.25C3.37453 8.29931 3.38424 8.34813 3.40311 8.39369C3.42198 8.43924 3.44963 8.48063 3.4845 8.5155C3.55491 8.58591 3.65042 8.62547 3.75 8.62547C3.79931 8.62547 3.84813 8.61576 3.89369 8.59689C3.93924 8.57802 3.98063 8.55037 4.0155 8.5155L6 6.53025L7.9845 8.5155C8.01937 8.55037 8.06076 8.57802 8.10631 8.59689C8.15187 8.61576 8.20069 8.62547 8.25 8.62547C8.29931 8.62547 8.34813 8.61576 8.39369 8.59689C8.43924 8.57802 8.48063 8.55037 8.5155 8.5155C8.55037 8.48063 8.57802 8.43924 8.59689 8.39369C8.61576 8.34813 8.62547 8.29931 8.62547 8.25C8.62547 8.20069 8.61576 8.15187 8.59689 8.10631C8.57802 8.06076 8.55037 8.01937 8.5155 7.9845L6.53025 6L8.5155 4.0155C8.55037 3.98063 8.57802 3.93924 8.59689 3.89369C8.61576 3.84813 8.62547 3.79931 8.62547 3.75C8.62547 3.70069 8.61576 3.65187 8.59689 3.60631C8.57802 3.56076 8.55037 3.51937 8.5155 3.4845C8.48063 3.44963 8.43924 3.42198 8.39369 3.40311C8.34813 3.38424 8.29931 3.37453 8.25 3.37453C8.20069 3.37453 8.15187 3.38424 8.10631 3.40311C8.06076 3.42198 8.01937 3.44963 7.9845 3.4845L6 5.46975L4.0155 3.4845Z"/>
                    </svg>

                </div>
            </div>

            <div
                ref="action"
                class="track-form-action uk-animation-fast"
            >
                <button class="carrier-choice-btn" @click="$refs.modalName.openModal(); searchResults = null"> <span v-if="this.choiceDelivery">{{ this.choiceDelivery.name }}</span> <span v-else>Выбрать перевозчика</span> </button>
                <button
                    @click="clearAll()"
                    class="clear-all"
                    type="button"
                >
                    <img src="images/crash.svg" alt="crash">
                </button>
                <button

                    class="filter-btn"
                    type="button"
                >
                    <img src="images/filter.svg" alt="filter">
                </button>
            </div>
        </div>
        <button
            type="button"
            class="track-btn"
            @click="getStatusTracks()">
                Отследить
            <span v-if="itemsCount > 0">{{ itemsCount }}</span>
        </button>

        <modal    ref="modalName">
            <template v-slot:header>
                <h2 class="uk-modal-title">Выбрать перевозчика</h2>
                <div class="search-container">
                    <form class="uk-search">
                        <span class="uk-search-icon-flip" uk-search-icon></span>
                        <input @keyup="searchDelivery($event.target.value)" class="uk-search-input" type="search"
                               placeholder="Поиск">
                    </form>
                    <button class="search-btn" type="button">
                        Поиск
                    </button>
                </div>
            </template>

            <template v-slot:body>
                <tabs v-if="!searchResults"
                      class="header-tabs">
                    <tab name="Часто используемые" :selected="true">
                        <div class="auto-detect-box">
                            <button @click="clearDelivery" class="auto-datect-btn">
                                <div class="auto-datect-logo">
                                </div>
                                <span class="auto-datect-txt"> Автоматическое определение перевозчика
                                    <span>Система автоматически определяет трек-номер и запрашивает информацию из соответствующего центра</span>
                                </span>
                            </button>
                        </div>
                        <p class="uk-padding">
                            Подсказка: При нормальных условиях, система позволяет автоматически обнаружить и
                            идентифицировать перевозчика, страну отправления / назначения. Не нужно ничего указывать
                            вручную. Но из-за логистики по всему миру доступ к провайдерам отслеживания в настоящее
                            время чрезвычайно сложный. У нашего автоопределения нет 100% точности. Таким образом, если
                            при каких-либо обстоятельствах наша система не определила перевозчика, пожалуйста, укажите
                            его сами и мы будем отслеживать его в соответствии с вашими настройками.
                        </p>
                    </tab>
                    <tab name="Доставка почтой">
                        <div class="auto-detect-box">
                            <button @click="clearDelivery" class="auto-datect-btn">
                                <div class="auto-datect-logo">
                                </div>
                                <span class="auto-datect-txt"> Автоматическое определение перевозчика
                                <span>Система автоматически определяет трек-номер и запрашивает информацию из соответствующего центра</span>
                            </span>
                            </button>
                        </div>

                        <div class="alphabet-tabs">
                            <button @click="choiceLetter = a"
                                    v-for="(a) in alphabetList">
                                {{ a }}
                            </button>
                        </div>

                        <div class="delivery-items" v-for="(del) in viewPost">
                            <button class="delivery-item" @click="selectDelivery(del.id)">
                                <img :src="del.image" :alt="del.name">
                                <a>
                                    <span>{{ del.country }}</span>
                                    <span>{{ del.name }}</span>
                                </a>
                            </button>
                        </div>
                    </tab>
                    <tab name="Международные перевозчики">
                        <div class="auto-detect-box">
                            <button @click="clearDelivery" class="auto-datect-btn">
                                <div class="auto-datect-logo">
                                </div>
                                <span class="auto-datect-txt"> Автоматическое определение перевозчика
                                <span>Система автоматически определяет трек-номер и запрашивает информацию из соответствующего центра</span>
                            </span>
                            </button>
                        </div>
                        <div class="delivery-items">
                            <button v-for="(d) in deliveryWorld" @click="selectDelivery(d.id)" class="delivery-item">
                                <img :src="d.image" :alt="d.name">
                                <a>{{ d.name }}</a>
                            </button>
                        </div>

                    </tab>
                    <tab name="Китай">
                        <div class="auto-detect-box">
                            <button @click="clearDelivery" class="auto-datect-btn">
                                <div class="auto-datect-logo">
                                </div>
                                <span class="auto-datect-txt"> Автоматическое определение перевозчика
                                    <span>Система автоматически определяет трек-номер и запрашивает информацию из соответствующего центра</span>
                                </span>
                            </button>
                        </div>
                        <div class="delivery-items">
                            <button v-for="(d) in deliveryChine" @click="selectDelivery(d.id)" class="delivery-item">
                                <img :src="d.image" :alt="d.name">
                                <a>{{ d.name }}</a>
                            </button>
                        </div>
                    </tab>


                </tabs>
                <div class="search-results" v-if="searchResults">
                    <div class="delivery-items">
                        <button v-for="(r) in searchResults"  @click="selectDelivery(r.id)" class="delivery-item">
                            <img :src="r.image" :alt="r.name">
                            <a>{{ r.name }}</a>
                        </button>
                    </div>
                    <span v-if="!searchResults.length">Ничего не найдено</span>
                </div>

            </template>



        </modal>
        <message ref="message"></message>




    </div>
</template>


<script>
import Modal from './Modal'
import Tabs from './Tabs'
import Tab from './Tab'
import Message from './Message'

export default {

    data() {
        return {
            items: [
                {
                    id: 0,
                    datatype: "",
                },
            ],
            close: false,
            placeholder: "Введите до 40 номеров, по одному в каждой строке.",
            itemsCount: 0,
            choiceLetter: null,
            choiceDelivery: null,
            deliveryTypes: null,
            alphabetList: [],
            deliveryChine: [],
            deliveryWorld: [],
            searchResults: null
        };
    },
    components: {
        Modal,
        Tabs,
        Tab,
        Message
    },
    props: [
        'packages',
        'delivery',
        'deliveries',
        'alphabet',
    ],
    created() {
        this.choiceLetter = 'А'
        this.deliveryTypes = JSON.parse(this.deliveries)
        this.alphabetList = JSON.parse(this.alphabet)
        this.deliveryChine = this.deliveryTypes.types.type_3
        if (typeof this.deliveryChine == 'object') this.deliveryChine = Object.values(this.deliveryChine)
        this.deliveryWorld = this.deliveryTypes.types.type_2
        if (typeof this.deliveryWorld == 'object') this.deliveryWorld = Object.values(this.deliveryWorld)

    },
    computed: {
        viewPost: function () {
            let t = this.deliveryTypes.types.type_1
            if (typeof t == 'object') t = Object.values(t)
            let l = this.choiceLetter
            return t.filter(function (el) {
                return el.first_letter == l
            })
        }
    },
    methods: {
        selectDelivery(index) {
            let a = this.deliveryTypes.all
            if (typeof a == 'object') a = Object.values(a)
            this.choiceDelivery = a.filter(function (el) {
                return el.id == index
            })[0]
        },
        clearDelivery() {
            this.choiceDelivery = null
            this.$refs.modalName.show = false
        },
        addItem(value, index) {
            if (value.length) {
                this.items.push({
                    id: index + 1,
                    datatype: ""
                });
            }


        },
        focusItem(index) {
            setTimeout(() => {
                this.$refs.items[this.$refs.items.length - 1].focus()
            }, 50);
        },
        showBtn(value, index) {
            if (value.length && value.length < 2) {
                this.close = true
                this.itemsCount = this.itemsCount + 1
                if (index === 0) {
                    this.$refs.action.classList.add('uk-animation-slide-bottom')
                }
                this.$refs.action.onanimationend = () => {
                    this.$refs.action.classList.remove('uk-animation-slide-bottom')
                };
                this.$refs.action.style.visibility = "visible"

            }

        },
        deleteItem(index) {
            if (index == 0 && this.items.length == 1) {
                this.choiceDelivery = null
                this.items[index].datatype = ''
                this.$refs.items[index].placeholder = this.placeholder
                this.close = false
                this.$refs.action.classList.add('uk-animation-slide-bottom', 'uk-animation-reverse')
                this.$refs.action.onanimationend = () => {
                    this.$refs.action.classList.remove('uk-animation-slide-bottom', 'uk-animation-reverse')
                    this.$refs.action.style.visibility = "hidden"
                };

                this.itemsCount = this.itemsCount - 1

            } else {
                this.items.splice(index, 1);
                this.itemsCount = this.itemsCount - 1
            }

        },
        clearAll() {
            this.choiceDelivery = null
            this.items[0].datatype = ''
            this.$refs.items[0].placeholder = this.placeholder
            this.close = false
            this.items.splice(1, this.items.length);
            this.$refs.action.classList.add('uk-animation-slide-bottom', 'uk-animation-reverse')
            this.$refs.action.onanimationend = () => {
                this.$refs.action.classList.remove('uk-animation-slide-bottom', 'uk-animation-reverse')
                this.$refs.action.style.visibility = "hidden"
            };
            this.itemsCount = 0
        },
        getStatusTracks() {
            const regExpCode = /[^a-zA-Z0-9]+/;
            let inputs = document.querySelectorAll('.trackcode-input')
            inputs.forEach((input) => {
                if (regExpCode.test(input.value)) {
                    this.$refs.message.isShow = true;
                    this.$refs.message.text = 'В номере обнаружены некорректные символы. Он должен состоять только из букв и цифр.'
                    setTimeout(() => {
                        this.$refs.message.isShow = false;
                    }, 3000);
                }
            })
        },
        focus() {
            this.$refs.items[0].focus()
        },
        searchDelivery(value) {
            let v = value.trim();
            this.searchResults = null
            if (v.length) {
                let a = this.deliveryTypes.all
                v = String(v).toLowerCase()
                if (typeof a == 'object') a = Object.values(a)
                this.searchResults = a.filter(function (el) {
                    let n = String(el.search).toLowerCase()
                    return n.includes(v)
                })
            }
        }
    },

}
</script>

<style>


.track-form-container {
    width: 100%;
    height: 176px;
    background: #F7F7F7;
    border: 1px solid #146C43;
    padding: 10px 0px;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 1px solid #146C43;
}

.list-trackcode {
    max-height: 100%;
    overflow: auto;
}

.list-trackcode::-webkit-scrollbar {
    width: 5px;

}

.list-trackcode::-webkit-scrollbar-track {
    background: #EEEEEE;
}


.list-trackcode::-webkit-scrollbar-thumb {
    background: #C4C4C4;
    border-radius: 3px;
    max-height: 50px;
}

.trackcode {
    display: flex;
    align-items: center;
    position: relative;
    padding-left: 20px;
    position: relative;
    padding-right: 26px;
}

.trackcode svg {
    cursor: pointer;
    fill: #bfbfbf;
    width: 13px;
    transition: fill .2s ease-in-out;
}

.trackcode svg:hover {
    fill: #0A3622;
    transition: fill .2s ease-in-out;
}

.trackcode span {
    color: #B4B4B4;
}

.trackcode-input {
    box-sizing: border-box;
    border: none;
    background: transparent;
    width: 100%;
    font-family: Roboto;
    font-size: 14px;
    text-transform: uppercase;
    line-height: 1;
    color: #000000;
    outline: none;
    padding: 6px 15px;
    text-transform: uppercase;
}

.trackcode-input::placeholder {
    text-transform: uppercase;
}

.trackcode-input:focus {
    border: none;
    outline: none;
}

.trackcode:nth-child(even) {
    background: #EEEEEE;
}

.track-form-action {
    visibility: hidden;
    padding: 0 26px;
    position: absolute;
    bottom: 10px;
    right: 0;
}

.filter-btn,
.clear-all {
    position: relative;
    cursor: pointer;
    width: 31px;
    height: 31px;
    background: #FFFFFF;
    border: 1px solid #FFFFFF;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    border-radius: 5px;
}

.filter-btn:hover,
.clear-all:hover {
    background: #146C43;
    border: 1px solid #146C43;
}

.filter-btn:hover img,
.clear-all:hover img {
    filter: invert(1) brightness(2);
}

.track-btn {
    cursor: pointer;
    user-select: none;
    margin-top: 15px;
    padding: 10px 0;
    text-align: center;
    width: 100%;
    color: #fff;
    font-weight: 500;
    font-size: 16px;
    position: relative;
    background: #FD9843;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    border: none;
    font-family: 'Roboto';
    text-align: center;
}

.track-btn span {
    width: 127px;
    text-align: right;
    padding-top: 2px;
    padding-right: 15px;
    border-top-right-radius: 5px;
    color: #000;
    font-size: 12px;
    font-weight: 500;
    height: 32px;
    position: absolute;
    top: 0;
    right: 0;
    background-repeat: no-repeat;
    background-image: url("data:image/svg+xml,%3Csvg width='151' height='32' viewBox='0 0 151 32' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Crect width='163.233' height='46.0237' transform='matrix(0.962333 0.271873 -0.779253 0.62671 36.7238 -41.7893)' fill='%23FECBA1'/%3E%3C/svg%3E%0A");
}

@media (max-width: 768px) {
    .track-form-action {
        padding: 0 10px;
    }
}

.overflow-hidden {
    overflow: hidden;
}

.mx-auto {
    margin-left: auto;
    margin-right: auto;
}

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.justify-content-between {
    justify-content: space-between;
}
</style>
