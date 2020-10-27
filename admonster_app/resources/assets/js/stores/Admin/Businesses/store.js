import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {
        company_name: '',
        date_type: 1,
        from: '',
        to: '',
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
            key: 'businessSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        }),
    ],
})
export default store
