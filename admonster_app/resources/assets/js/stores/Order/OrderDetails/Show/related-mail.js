import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    openRelatedMails: [],
    unfoldFlag: false
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setStateObj (state, params) {
            Object.assign(state, params)
        }
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'orderDetailRelatedMail',
            paths: ['openRelatedMails', 'unfoldFlag'],
            storage: window.sessionStorage,
        })
    ]

})
export default store
