require('./bootstrap');

window.Vue = require('vue');

// vuetify
import Vuetify from 'vuetify';
import colors from 'vuetify/es5/util/colors';
import 'vuetify/dist/vuetify.min.css';
// vuetify extensions
import VTooltip from 'v-tooltip';

// material design icon
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import '@mdi/font/css/materialdesignicons.css';

// global components
import AppFooter from './components/Organisms/Layouts/AppFooter.vue';
import AppHeader from './components/Organisms/Layouts/AppHeader.vue';
import AppMenu from './components/Organisms/Layouts/AppMenu.vue';

Vue.use(Vuetify, {
    theme: {
        primary: colors.teal.lighten2,
        secondary: colors.teal.lighten3,
    }
});

Vue.use(VTooltip);

// filters.jsをglobalに読み込み
var Filters = require('./filters');
Vue.use(Filters);

//空のVueインスタンスをeventHubとして定義
global.eventHub = new Vue();

// 定数定義をglobalに読み込み
global._const = require('./const.json');
// moment.jsをglobalに読み込み
global.moment = require('moment-timezone');

// 全画面で利用するコンポーネントをグローバル登録
Vue.component('app-footer', AppFooter);
Vue.component('app-header', AppHeader);
Vue.component('app-menu', AppMenu);

export default Vue;
