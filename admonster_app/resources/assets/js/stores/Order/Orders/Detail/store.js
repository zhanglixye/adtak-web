import Vuex from 'vuex';

const initialState = {
    order: {},
    initialData: {},// processingDataと構成は同じ
    processingData: {
        orderName: '',
        statedAt: '',
        customStatuses: [],
        temporaryForKey: {
            customStatus: 0,
            attribute: 0,
        },
        deleteCustomStatusIds: [],
        deleteCustomStatusAttributeIds: []
    },
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setOrder({ order }, obj) {
            Object.assign(order, obj)
        },
        setProcessingData({ processingData }, data) {
            Object.assign(processingData, data)
        },
        setInitialData({ initialData }, data) {
            Object.assign(initialData, data)
        },
        setCustomStatuses({ processingData }, newCustomStatuses) {
            processingData.customStatuses = newCustomStatuses
        },
        setForKeyCustomStatus({ processingData }, forKey) {
            processingData.temporaryForKey.customStatus = forKey
        },
        setForKeyAttribute({ processingData }, forKey) {
            processingData.temporaryForKey.attribute = forKey
        },
        addForKeyCustomStatus({ processingData }, number = 1) {
            processingData.temporaryForKey.customStatus += number
        },
        addForKeyAttribute({ processingData }, number = 1) {
            processingData.temporaryForKey.attribute += number
        },
        setDeleteCustomStatusIds({ processingData }, id) {
            processingData.deleteCustomStatusIds.push(id)
        },
        setDeleteCustomStatusAttributeIds({ processingData }, ids) {
            processingData.deleteCustomStatusAttributeIds = processingData.deleteCustomStatusAttributeIds.concat(ids)
        },
    },
    actions: {
        updateOrderName({ commit }, orderName) {
            commit('setProcessingData', { orderName: orderName })
        },
        async tryLoad({ commit }, orderId) {

            try {
                const params = {}
                params['order_id'] = orderId

                const url = '/api/order/orders/edit'
                const res = await axios.post(url, params)

                if (res.data.status !== 200) throw res.data.message
                const setData = {}

                // 参照渡し防止
                const customStatuses = JSON.parse(JSON.stringify(res.data['custom_statuses']))

                // custom_status_idの最大値が入る
                const customStatusIds = customStatuses.map(item => item['custom_status_id'])
                const maxCustomStatusForKey = customStatusIds.length === 0 ? 1 : customStatusIds.reduce((a, b) => Math.max(a, b))

                // custom_status_attribute_idの最大値が入る
                let maxAttributeForKey = 0
                for (const customStatus of customStatuses) {
                    customStatus['forKey'] = customStatus['custom_status_id']
                    customStatus['customStatusId'] = customStatus['custom_status_id']
                    delete customStatus['custom_status_id']
                    customStatus['customStatusName'] = res.data['label_data']['ja'][customStatus['label_id']]
                    customStatus['labelId'] = customStatus['label_id']
                    delete customStatus['label_id']
                    customStatus['isUseCustomStatus'] = customStatus['is_use_custom_status']
                    delete customStatus['is_use_custom_status']

                    const attributeIds = customStatus['attributes'].map(item => item['id'])
                    let maxAttributeId = 0
                    if (attributeIds.length > 0) maxAttributeId = attributeIds.reduce((a, b) => Math.max(a, b))
                    if (maxAttributeId > maxAttributeForKey) maxAttributeForKey = maxAttributeId
                    for (const attribute of customStatus['attributes']) {
                        attribute['name'] = res.data['label_data']['ja'][attribute['label_id']]
                        attribute['forKey'] = attribute['id']
                        attribute['labelId'] = attribute['label_id']
                        delete attribute['label_id']
                        attribute['isUseCustomStatusAttribute'] = attribute['is_use_custom_status_attribute']
                        delete attribute['is_use_custom_status_attribute']
                    }
                }
                const order = res.data['order']
                setData.orderName = order.name
                setData.statedAt = res.data['started_at']
                commit('setOrder', JSON.parse(JSON.stringify(order)))
                commit('setForKeyCustomStatus', maxCustomStatusForKey)
                commit('setForKeyAttribute', maxAttributeForKey)
                commit('setCustomStatuses', JSON.parse(JSON.stringify(customStatuses)))
                commit('setInitialData', JSON.parse(JSON.stringify(setData)))
                commit('setProcessingData', JSON.parse(JSON.stringify(setData)))
            } catch (error) {
                console.log(error)
                throw error
            }
        },
        async tryUpdate({ state }) {
            try {
                // パラメータの作成
                const params = {}
                params['order'] = {
                    id: state.order.id,
                    name: state.processingData.orderName,
                    custom_statuses: JSON.stringify(state.processingData.customStatuses),
                    delete_custom_status_ids: state.processingData.deleteCustomStatusIds,
                    delete_custom_status_attribute_ids: state.processingData.deleteCustomStatusAttributeIds
                }
                params['started_at'] = state.initialData.statedAt['date']
                console.log('state.initialData.statedAt[date]: ', state.initialData.statedAt['date']);

                const url = '/api/order/orders/edit/update'
                const res = await axios.post(url, params)
                if (res.data.status !== 200) throw res.data.message
            } catch (error) {
                console.log(error)
                if (['internal_error', 'updated_by_others', 'no_admin_permission'].includes(error)) {
                    throw Vue.i18n.translate(`common.message.${error}`)
                } else {
                    throw Vue.i18n.translate('common.message.internal_error')
                }
            }
        },
        addCustomStatus({ commit, state }, customStatus) {
            const newCustomStatuses = JSON.parse(JSON.stringify(state.processingData.customStatuses))
            newCustomStatuses.push(customStatus)
            commit('setCustomStatuses', newCustomStatuses)
        },
        updateCustomStatus({ commit, state }, { customStatus, deleteAttributeIds }) {
            const newCustomStatuses = state.processingData.customStatuses.map(item => {
                if (item.forKey === customStatus.forKey) return customStatus
                return item
            })
            commit('setDeleteCustomStatusAttributeIds', deleteAttributeIds)
            commit('setCustomStatuses', newCustomStatuses)
        },
    },
})
export default store
