import Vue from 'vue'
import Vuex from 'vuex'
import vuexI18n from 'vuex-i18n'
import createPersistedState from 'vuex-persistedstate'

import Locales from '../vue-i18n-locales.generated'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

// state管理対象を指定
const modules = {}

// 保存対象のstateを指定
const paths = [
]

const store = new Vuex.Store({
    modules: modules,
    strict: debug,
    plugins: [
        createPersistedState({
            key: 'AD-MONSTER-' + process.env.NODE_ENV,
            paths: paths,
            storage: window.sessionStorage,
        }),
    ],
})

Vue.use(vuexI18n.plugin, store)

// resources/lang で用意したlocaleファイルを追加
Vue.i18n.add('ja', Locales.ja)
Vue.i18n.add('en', Locales.en)

// デフォルトlocaleを設定
Vue.i18n.set('ja')

export default store
