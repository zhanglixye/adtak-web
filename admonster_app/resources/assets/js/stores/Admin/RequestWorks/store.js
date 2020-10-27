import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {
        business_name: '',
        step_name: '',
        request_work_name: '',
        operator_name: '',
        date_type: 2,
        from: '',
        to: '',
        request_file_name: '',
        request_mail_subject: '',
        request_mail_to: '',
        status: ['11', '21', '31'],
        self: false,
        completed: false,
        inactive: false,
        excluded: false,
        page: 1,
        sort_by: 'deadline',
        descending: false,
        rows_per_page: 20
    },
}

const store = new Vuex.Store({
    state: initialState,
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
        },
        resetSearchParams ({ searchParams }) {
            Object.assign(searchParams, initialState.searchParams)
        }
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'requestWorksSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        }),
    ],
})
export default store
