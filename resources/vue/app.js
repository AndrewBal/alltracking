window._ = require('lodash');
// window.wNumb = require('wnumb');

import Vue from 'vue'
// import store from './store'

window.app = new Vue({
    components: {
        trackForm: () => import('./component/trackCodeForm'),
        tracking: () => import('./component/Tracking')
    }
}).$mount('#app')
