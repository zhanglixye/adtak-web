import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const state = {
    openDetails: true,
    progressClosedStepIds: [],
    openedAddInfoIds: [],
    openRelatedMails: [],
    unfoldFlag: false
}

const store = new Vuex.Store({
    state: state,
    mutations: {
        setStateObj (state, params) {
            Object.assign(state, params)
        }
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'requestDetails',
            paths: ['openDetails', 'progressClosedStepIds', 'openedAddInfoIds', 'openRelatedMails', 'unfoldFlag'],
            storage: window.sessionStorage,
        })
    ]

})
export default store
