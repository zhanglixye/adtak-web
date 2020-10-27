import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {},
    showHeaders:[
        { text: Vue.i18n.translate('list.column.step_id'), value: 'step_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.step_name'), value: 'step_name', align: 'center', width: '' },
        { text: Vue.i18n.translate('list.column.updated_at'), value: 'updated_at', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.updated_user'), value: 'updated_user', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.before_work_template'), value: 'before_work_template', align: 'center', width: '50' },
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
            key: 'masterStepListSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        })
    ]
})
export default store
