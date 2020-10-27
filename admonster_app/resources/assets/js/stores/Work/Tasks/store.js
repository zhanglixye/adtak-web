import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {
        business_name: '',
        step_name: '',
        date_type: 1,
        from: '',
        to: '',
        status: ['0', '1'],
        unverified: 0,
        page: 1,
        sort_by: 'deadline',
        descending: false,
        rows_per_page: 20
    },
    showHeaders:[
        { text: Vue.i18n.translate('tasks.business_name'), value: 'business_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('tasks.step_name'), value: 'step_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('tasks.step_description'), value: 'step_id', align: 'center', width: '400' },
        { text: Vue.i18n.translate('tasks.created_at'), value: 'created_at', align: 'center', width: '100' },
        { text: Vue.i18n.translate('tasks.deadline'), value: 'deadline', align: 'center', width: '100' },
        { text: Vue.i18n.translate('tasks.status'), value: 'status', align: 'center', width: '100' },
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.request_id'), value: 'request_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.client_name'), value: 'client_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.subject'), value: 'request_work_name', align: 'center', width: '400' },
        { text: Vue.i18n.translate('tasks.verification'), value: 'is_verified', align: 'center', width: '50' },
        { text: Vue.i18n.translate('tasks.work_type'), value: 'is_display_educational', align: 'center', width: '50' },
    ],
}

const store = new Vuex.Store({
    state: initialState,
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
        },
        resetSearchParams ({ searchParams }) {
            Object.assign(searchParams, initialState.searchParams)
        },
        setShowHeaders ({ showHeaders },{ params }) {
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
            key: 'taskSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        }),
    ],
})
export default store
