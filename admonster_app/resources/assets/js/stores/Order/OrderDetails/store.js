import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'
const initialState = {
    processingData: {
        orderDetails: [],
        labelData: {},
        orderFileImportColumnConfigs: [],
        allOrderDetailId: [],
        selectedOrderDetailIds: [],
        loading: false,
        orderName: '',
        customStatuses: [],
        isAdmin: false,
        startedAt: null
    },
    customStatusColumnPrefix: 'custom_statuses_',
    displayLangCode: 'ja',// 各案件固有情報を多言語対応するか分からないので、日本語に固定
    searchParams: {
        // 検索欄---------------
        // 各案件で共通の検索条件
        order_id: null,
        order_detail_name: '',
        from: '',
        to: '',
        status: 1,

        // 各案件固有の検索条件
        display_column: '',
        display_id: null,
        display_item_type: null,
        display_from: '',
        display_to: '',
        display_text: '',
        // ---------------

        // 一覧表示関連
        page: 1,
        sort_by: 'created_at',
        descending: false,
        rows_per_page: 20,
        selected_custom_status_id: null,
        selected_attribute_id: null,
    },
    eachOrderSearchParams: {},
    paginate: {// ページ情報
        data_count_total: 0,
        page_count: 0,
        page_from: 0,
        page_to: 0,
    },
    showHeaders:[
    ],
    hiddenHeaders:[
    ],
    eachOrderHeader: {},
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setShowHeaders({ showHeaders, eachOrderHeader, searchParams }, { params }) {
            showHeaders.splice(params.length)
            Object.assign(showHeaders, params)
            if (searchParams.order_id in eachOrderHeader) {
                Object.assign(eachOrderHeader[searchParams.order_id], { showHeaders: params })
            } else {
                const obj = {}
                obj[searchParams.order_id] = { showHeaders: params }
                Object.assign(eachOrderHeader, obj)
            }
        },
        setHiddenHeaders({ hiddenHeaders, eachOrderHeader, searchParams }, { params }) {
            hiddenHeaders.splice(params.length)
            Object.assign(hiddenHeaders, params)
            if (searchParams.order_id in eachOrderHeader) {
                Object.assign(eachOrderHeader[searchParams.order_id], { hiddenHeaders: params })
            } else {
                const obj = {}
                obj[searchParams.order_id] = { hiddenHeaders: params }
                Object.assign(eachOrderHeader, obj)
            }
        },
        resetSearchParams({ searchParams }) {
            Object.assign(searchParams, initialState.searchParams, { order_id: searchParams.order_id })
        },
        setSearchParams({ searchParams }, val) {
            Object.assign(searchParams, val)
        },
        setEachOrderSearchParams({ eachOrderSearchParams }, searchParams) {
            if (searchParams.order_id in eachOrderSearchParams) {
                Object.assign(eachOrderSearchParams[searchParams.order_id], searchParams)
            } else {
                const obj = {}
                obj[searchParams.order_id] = searchParams
                Object.assign(eachOrderSearchParams, obj)
            }
        },
        setLoading({ processingData }, val) {
            processingData.loading = val
        },
        setOrderDetails({ processingData }, orderDetails) {
            processingData.orderDetails = orderDetails
        },
        setPaginate({ paginate }, val) {
            Object.assign(paginate, val)
        },
        setLabelData({ processingData }, labelData) {
            processingData.labelData = labelData
        },
        setOrderFileImportColumnConfigs({ processingData }, val) {
            processingData.orderFileImportColumnConfigs = val
        },
        setSelectedOrderDetailIds({ processingData }, ids) {
            processingData.selectedOrderDetailIds = ids
        },
        setAllOrderDetailIds({ processingData }, ids) {
            processingData.allOrderDetailId = ids
        },
        setOrderName({ processingData }, name) {
            Object.assign(processingData, { orderName: name })
        },
        setSelectedCustomStatusId({ searchParams }, id) {
            searchParams.selected_custom_status_id = id
        },
        setSelectedAttributeId({ searchParams }, id) {
            searchParams.selected_attribute_id = id
        },
        setCustomStatuses({ processingData }, newCustomStatuses) {
            processingData.customStatuses = newCustomStatuses
        },
        setIsAdmin({ processingData }, newIsAdmin) {
            processingData.isAdmin = newIsAdmin
        },
        setStartedAt({ processingData }, startedAt) {
            processingData.startedAt = startedAt
        },
    },
    actions: {
        async searchOrderDetailList({ commit, getters, state }) {
            commit('setLoading', true)
            const searchParams = Vue.util.extend({}, state.searchParams)

            try {
                const res = await axios.post('/api/order/order_details/index', searchParams)

                // 検索条件をstoreに保存
                commit('setEachOrderSearchParams', JSON.parse(JSON.stringify(state.searchParams)))

                // 参照渡し防止
                const customStatuses = JSON.parse(JSON.stringify(res.data.custom_statuses))
                const orderDetails = JSON.parse(JSON.stringify(res.data.list.data))

                for (const customStatus of customStatuses) {
                    customStatus['text'] = res.data['label_data']['ja'][customStatus['label_id']]
                    delete customStatus['label_id']

                    for (const attribute of customStatus['attributes']) {
                        attribute['text'] = res.data['label_data']['ja'][attribute['label_id']]
                        delete attribute['label_id']
                    }

                    // カスタムステータス属性候補の先頭に空文字を追加
                    customStatus['attributes'].unshift({ id: null, text: '' })

                    for (const orderDetail of orderDetails) {
                        orderDetail[`${state.customStatusColumnPrefix}${customStatus.id}`] = res.data.label_data[state.displayLangCode][orderDetail[`${state.customStatusColumnPrefix}${customStatus.id}`]]
                    }
                }

                const selectedCustomStatus = customStatuses.find(customStatus => customStatus.id === state.searchParams.selected_custom_status_id)

                if (selectedCustomStatus === undefined) {// 選択されたカスタムステータスが削除されている
                    commit('setSelectedCustomStatusId', null)
                    commit('setSelectedAttributeId', null)
                } else {
                    if (!selectedCustomStatus['attributes'].map(attribute => attribute.id).includes(state.searchParams.selected_attribute_id)) {// 選択された属性が削除されている
                        commit('setSelectedAttributeId', null)
                    }
                }

                // 検索結果を画面に反映
                commit('setLabelData', res.data.label_data)
                commit('setCustomStatuses', customStatuses)
                commit('setOrderDetails', orderDetails)
                commit('setOrderFileImportColumnConfigs', res.data.item_column_configs)
                commit('setAllOrderDetailIds', res.data.all_order_detail_ids)
                commit('setShowHeaders', { params: getters.headers.showHeaders })
                commit('setHiddenHeaders', { params: getters.headers.hiddenHeaders })
                commit('setSelectedOrderDetailIds', [])
                commit('setOrderName', res.data.order_name)
                commit('setIsAdmin', res.data.is_admin)
                commit('setStartedAt', res.data.started_at)

                const paginate = {}
                paginate.data_count_total = res.data.list.total
                paginate.page_count = res.data.list.last_page
                paginate.page_from = res.data.list.from ? res.data.list.from : 0
                paginate.page_to = res.data.list.to ? res.data.list.to : 0
                commit('setPaginate', paginate)
                commit('setLoading', false)
            } catch (error) {
                commit('setLoading', false)
                throw error
            }
        },
        async updateOrderDetails({ commit, state }, { status }) {
            commit('setLoading', true)
            const searchParams = Vue.util.extend({}, {
                order_id: state.searchParams.order_id,
                order_detail_ids: state.processingData.selectedOrderDetailIds,
                is_active: status,
                started_at: state.processingData.startedAt
            })
            try {
                const res = await axios.post('/api/order/order_details/bulk_update', searchParams)
                // 検索条件をstoreに保存
                if (res.data.status !== 200) throw res.data.message
            } catch (error) {
                console.log(error)
                commit('setLoading', false)
                throw error
            }
        },
        async settingDisplayFormat({ commit, state }, { settingDisplayFormats }) {
            commit('setLoading', true)
            const params = Vue.util.extend({}, {
                order_id: state.searchParams.order_id,
                setting_display_formats: settingDisplayFormats,
                started_at: state.processingData.startedAt
            })
            try {
                const res = await axios.post('/api/order/order_details/setting_display_format', params)
                if (res.data.status !== 200) throw res.data.message
            } catch (error) {
                console.log(error)
                commit('setLoading', false)
                throw error
            }
            commit('setLoading', false)
        },
    },
    getters: {
        initialHeaders: (state, getters) => {
            const displayItemNumber = 4
            return {
                showHeaders: initialState.showHeaders.concat(getters.displayHeaders.slice(0, displayItemNumber)),
                hiddenHeaders: getters.displayHeaders.slice(displayItemNumber),
            }
        },
        displayHeaders: state => {
            const displayNameItems = state.processingData.orderFileImportColumnConfigs.map(item => ({
                align: 'center',
                value: item.column,
                width: '150',
                isEdit: true,
                text: state.processingData.labelData[state.displayLangCode][item.label_id],
                labelId: item.label_id,
            }))
            const systemHeaders = [
                { text: Vue.i18n.translate('list.column.system_subject'), value: 'order_detail_name', align: 'center', width: '150' },
                { text: Vue.i18n.translate('list.column.status'), value: 'status', align: 'center', width: '150' },
                { text: Vue.i18n.translate('list.column.created_at'), value: 'created_at', align: 'center', width: '200' },
            ];
            const customStatusHeaders = state.processingData.customStatuses.map(customStatus => ({
                align: 'center',
                value: `${state.customStatusColumnPrefix}${customStatus.id}`,
                width: '150',
                text: customStatus.text
            }))
            return displayNameItems.concat(systemHeaders).concat(customStatusHeaders)
        },
        headers: (state, getters) => {
            // まずはstorageからデータを取得
            if (state.searchParams.order_id in state.eachOrderHeader) {
                const orderHeader = JSON.parse(JSON.stringify(state.eachOrderHeader[state.searchParams.order_id]))
                for (const key of Object.keys(orderHeader)) {
                    for (const header of orderHeader[key]) {
                        if ('labelId' in header && ![null, undefined].includes(header.labelId)) {
                            header.text = state.processingData.labelData[state.displayLangCode][header.labelId]// 表示名の更新
                        }
                        if ('order_detail_name' === header.value) {
                            header.text = Vue.i18n.translate('list.column.system_subject')
                        }
                        const columns = state.processingData.orderFileImportColumnConfigs.map(item => item.column)
                        if (columns.includes(header.value) && !('isEdit' in header)) {
                            header.isEdit = true
                        }
                    }
                }
                const customStatusHeaders = state.processingData.customStatuses.map(customStatus => ({
                    align: 'center',
                    value: `${state.customStatusColumnPrefix}${customStatus.id}`,
                    width: '150',
                    text: customStatus.text
                }))

                let newHeaderValues = ['order_detail_name', 'status', 'created_at']
                newHeaderValues = newHeaderValues.concat(state.processingData.orderFileImportColumnConfigs.map(item => (item.column)))
                const customStatusHeaderValues = customStatusHeaders.map(customStatusHeader => customStatusHeader.value)
                newHeaderValues = newHeaderValues.concat(customStatusHeaderValues)

                const hiddenHeaders = []
                for (const hiddenHeader of orderHeader.hiddenHeaders) {
                    if (newHeaderValues.includes(hiddenHeader.value)) {// storageに登録されているヘッダーが削除されていない
                        if (customStatusHeaderValues.includes(hiddenHeader.value)) {
                            hiddenHeader['text'] = customStatusHeaders.find(customStatusHeader => customStatusHeader.value === hiddenHeader.value)['text']// カスタムステータス名更新
                            hiddenHeaders.push(hiddenHeader)
                        } else {
                            hiddenHeaders.push(hiddenHeader)
                        }
                    }
                }

                const showHeaders = []
                for (const showHeader of orderHeader.showHeaders) {
                    if (newHeaderValues.includes(showHeader.value)) {// storageに登録されているヘッダーが削除されていない
                        if (customStatusHeaderValues.includes(showHeader.value)) {
                            showHeader['text'] = customStatusHeaders.find(customStatusHeader => customStatusHeader.value === showHeader.value)['text']// カスタムステータス名更新
                            showHeaders.push(showHeader)
                        } else {
                            showHeaders.push(showHeader)
                        }
                    }
                }

                const headerValues = showHeaders.concat(hiddenHeaders).map(header => header.value)
                const addCustomStatusHeaders = customStatusHeaders.filter(customStatusHeader =>
                    !headerValues.includes(customStatusHeader.value)
                )

                orderHeader.showHeaders = showHeaders
                orderHeader.hiddenHeaders = hiddenHeaders.concat(addCustomStatusHeaders)
                return orderHeader
            }
            return getters.initialHeaders
        },
        searchParams: (state) => (orderId) => {
            // まずはstorageからデータを取得
            if (orderId in state.eachOrderSearchParams) {
                return state.eachOrderSearchParams[orderId]
            }
            return initialState.searchParams
        },
    },
    plugins: [
        createPersistedState({
            key: 'orderDetailSearchParams',
            paths: ['eachOrderSearchParams'],
            storage: window.sessionStorage,
        }),
        createPersistedState({
            key: 'initialOrderDetails',
            paths: ['eachOrderHeader'],
            storage: window.localStorage,
        })
    ]
})
export default store
