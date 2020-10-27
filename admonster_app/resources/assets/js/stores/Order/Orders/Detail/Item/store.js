import Vuex from 'vuex';

const initialState = {
    order: {},
    startedAt: {},
    processingData: {
        items: [
            // {
            // id: number,
            // item: string,
            // display: string,
            // newDisplay: string,
            // labelId: number,
            // }
        ],
        valid: false,
    },
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setValid({ processingData }, valid) {
            processingData.valid = valid
        },
        setOrder({ order }, data) {
            Object.assign(order, data)
        },
        setProcessingData({ processingData }, data) {
            Object.assign(processingData, data)
        },
        setStartedAt({ startedAt }, data) {
            Object.assign(startedAt, data)
        },
    },
    actions: {
        async tryLoad({ commit, getters }, orderId) {
            try {
                const res = await axios.get(`/api/order/orders/${orderId}/item/edit`)
                if (res.data.status !== 200) throw res.data.message

                commit('setOrder', res.data.order)
                commit('setStartedAt', res.data.started_at)

                // 作業用のデータを作成
                const labelData = res.data.labelData
                const items = res.data.items.map(item => (
                    {
                        id: item.id,
                        item: item.item,
                        display: labelData[getters.langCode][item.label_id],
                        newDisplay: '',
                        labelId: item.label_id,

                    }
                ))
                commit('setProcessingData', { items: items })
            } catch (error) {
                console.log(error)
                if (error === 'no_admin_permission') throw Vue.i18n.translate('common.message.no_admin_permission')
                throw Vue.i18n.translate('common.message.internal_error')
            }
        },
        async trySave({ getters, state }) {
            try {
                const items = getters.items.flatMap(item => {
                    if (item.newDisplay === '') return []
                    if (item.display === item.newDisplay) return []
                    return {
                        id: item.id,
                        item: item.item,
                        display: item.display,
                        new_display: item.newDisplay,
                        label_id: item.labelId,
                    }
                })
                const params = {}
                params['items'] = JSON.stringify(items)
                params['started_at'] = state.startedAt['date']
                const res = await axios.post(`/api/order/orders/${state.order.id}/item/update`, params)
                if (res.data.status !== 200) throw res.data.message
            } catch (error) {
                console.log(error)
                if (error === 'updated_by_others') throw Vue.i18n.translate('common.message.updated_by_others')
                if (error === 'no_admin_permission') throw Vue.i18n.translate('common.message.no_admin_permission')
                throw Vue.i18n.translate('common.message.internal_error')
            }
        }
    },
    getters: {
        items: (state) => {
            return JSON.parse(JSON.stringify(state.processingData.items))
        },
        langCode: () => {
            return 'ja'
        },
    },
})
export default store
