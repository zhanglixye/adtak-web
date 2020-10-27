import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {},
    showHeaders:[
        { text: Vue.i18n.translate('list.column.business_id'), value: 'business_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.business_name'), value: 'business_name', align: 'center', width: '' },
        { text: Vue.i18n.translate('list.column.company_name'), value: 'company_name', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.business_description'), value: 'business_description', align: 'center', width: '' },
    ],
    hiddenHeaders:[],
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
        },
        resetSearchParams ({ searchParams }) {
            Object.assign(searchParams, initialState.searchParams)
        },
        setShowHeaders ({ showHeaders }, { params }) {
            showHeaders.splice(params.length)
            Object.assign(showHeaders, params)
        },
        setHiddenHeaders ({ hiddenHeaders }, { params }) {
            hiddenHeaders.splice(params.length)
            Object.assign(hiddenHeaders, params)
        },
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'masterBusinessListSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        })
    ]
})
export default store
