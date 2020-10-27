import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const state = {
    conditions: {
        selected: [],
        parallel: null,
        evenness: _const.ALLOCATION_EVENNESS.EVEN,
        ratios: {},
        ratioSum: 0,
        afterOperators: []
    }
}

const store = new Vuex.Store({
    state: state,
    mutations: {
        setAllocationConditions (state, payload) {
            state.conditions.selected = payload.selected
            state.conditions.parallel = payload.parallel
            state.conditions.evenness = payload.evenness
            state.conditions.ratios = payload.ratios
            state.conditions.ratioSum = payload.ratioSum
            state.conditions.afterOperators = payload.afterOperators
        },
        resetAllocationConditions (state) {
            state.conditions.selected = []
            state.conditions.parallel = null
            state.conditions.evenness = _const.ALLOCATION_EVENNESS.EVEN
            state.conditions.ratios = {}
            state.conditions.ratioSum = 0
            state.conditions.afterOperators = []
        }
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'educationMultipleAllocations',
            paths: ['conditions'],
            storage: window.sessionStorage,
        }),
    ],
})
export default store
