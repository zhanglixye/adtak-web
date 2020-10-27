import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'
const  initialState = {
    searchParams:{
        //request_file_name: '',
        request_file_id: '',
        business_name: '',
        from: '',
        to: '',
        date_type: 1,
        client_name: '',
        request_name: '',
        page: 1,
        sort_by: 'deadline',
        descending: false,
        rows_per_page: 20,
        status: 1,
    },
    showHeaders:[
        { text: Vue.i18n.translate('list.column.client_name'), value: 'client_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.subject'), value: 'request_name', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.created_at'), value: 'created_at', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.deadline'), value: 'deadline', align: 'center', width: '100' }
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.company_name'), value: 'company_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.business_name'), value: 'business_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.request_id'), value: 'request_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.status'), value: 'status', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.request_mail.subject'), value: 'request_mail_subject', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.request_mail.from_address'), value: 'request_mail_from', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.request_mail.to_address'), value: 'request_mail_to', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.request_mail.attachment_file'), value: 'mail_attachments_count', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.request_file.id'), value: 'request_file_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.request_file.name'), value: 'request_file_name', align: 'center', width: '400' }
    ],
}
let requestParams

const store = new Vuex.Store({
    state: initialState,
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
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
    actions: {
        setSearchParams ({dispatch, commit}, params = initialState.searchParams) {
            if (requestParams) {
                dispatch('setRequestParams', params)
            } else {
                commit('setSearchParams', {params: params})
            }
        },
        setRequestParams: (context, requests = {}) => {
            requestParams = initialState.searchParams
            Object.keys(requests).forEach(function(key) {
                requestParams[key] = requests[key];
            });
        },
    },
    getters: {
        getSearchParams: state => {
            return requestParams ? requestParams : state.searchParams
        },
    },
    plugins: [
        createPersistedState({
            key: 'requestSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        })
    ]

})
export default store
