import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const state = {
    openDetails: false,
    progressClosedStepIds: [],
    openedAddInfoIds: [],
    openRelatedMails: [],
    unfoldFlag: false,
    showWholeStepsBlock: false,
    appendicesSearchParams:{
        page: 1,
        descending: false,
        rows_per_page: 5,
    },
}

const store = new Vuex.Store({
    state: state,
    mutations: {
        setStateObj (state, params) {
            Object.assign(state, params)
        },
        setAppendicesSearchParams ({ appendicesSearchParams }, { params }) {
            Object.assign(appendicesSearchParams, params)
        },
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'clientRequestDetails',
            paths: [
                'openDetails',
                'progressClosedStepIds',
                'openedAddInfoIds',
                'openRelatedMails',
                'unfoldFlag',
                'appendicesSearchParams'
            ],
            storage: window.sessionStorage,
        })
    ]
})
export default store
