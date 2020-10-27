import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    processingData: {
        additionalInfos: [],
        openedTogglePanels: []
    },
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setAdditionalInfos({ processingData }, additionalInfos) {
            processingData.additionalInfos = additionalInfos
        },
        setOpenedTogglePanels ({ processingData }, params) {
            processingData.openedTogglePanels = params
        },
    },
    actions: {
        async getAdditionalInfos({ commit }, { orderId, orderDetailId }) {
            try {
                const res = await axios.get('/api/order/additional_infos/index', {
                    params: {
                        order_id: orderId,
                        order_detail_id: orderDetailId,
                    }
                })
                if (res.data.status !== 200) throw res.data.message
                commit('setAdditionalInfos', res.data.order_additional_infos)
            } catch (error) {
                console.log(error)
            }
        },
    },
    getters: {},
    plugins: [
        createPersistedState({
            key: 'orderDetailAdditionalInfo',
            paths: ['processingData'],
            storage: window.sessionStorage,
        })
    ]
})
export default store
