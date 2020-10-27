import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {
        business_name: '',
        date_type: 2,
        from: '',
        to: '',
        inactive: false,
    },
}

const store = new Vuex.Store({
    state: initialState,
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
        },
        resetSearchParams ({ searchParams }) {
            // 業務名はリセットしない
            let params = initialState.searchParams
            params.business_name = searchParams.business_name

            Object.assign(searchParams, params)
        }
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'businessStatesSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        }),
    ],
})
export default store
