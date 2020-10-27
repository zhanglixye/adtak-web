import Vuex from 'vuex'

const initialState = {
    businessesInCharge: [],
    requests: [],
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setBusinessesInCharge(state, val) {
            state.businessesInCharge = val
        },
        setRequests(state, val) {
            state.requests = val
        },
    },
    actions: {
        async searchBusinessesInfo({ commit }) {
            try {
                const res = await axios.post('/api/order/order_details/search_businesses')
                if (res['data']['status'] != 200) {
                    console.log(res)
                }
                commit('setBusinessesInCharge', res['data']['businesses_in_charge'])
            } catch (error) {
                console.log(error)
            }
        },
        async searchRequestsInfo({ commit }, params) {
            try {
                const res = await axios.post('/api/order/order_details/search_requests', {
                    'order_detail_id': params['order_detail_id']
                })
                if (res['data']['status'] != 200) {
                    console.log(res)
                }
                commit('setRequests', res['data']['requests'])
            } catch (error) {
                console.log(error)
            }
        }
    },
    getters: {},
})
export default store
