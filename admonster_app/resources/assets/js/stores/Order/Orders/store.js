import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    processingData: {
        orders: [],
        allOrderId: [],
        allAdminOrderId: [],
        selectedOrderIds: [],
        candidates: [],
        loading: false,
        isAdmin: true,
        startedAt: null
    },
    searchParams: {
        // 検索欄
        order_id: null,
        order_name: '',
        from: '',
        to: '',
        status: 1,
        imported_file_name: '',
        importer: '',

        // 一覧表示関連
        page: 1,
        sort_by: 'imported_at',
        descending: false,
        rows_per_page: 20,
    },
    // ページ情報
    paginate: {
        data_count_total: 0,
        page_count: 0,
        page_from: 0,
        page_to: 0,
    },
    showHeaders:[
        { text: Vue.i18n.translate('list.column.import_at'), value: 'imported_at', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.order_name'), value: 'order_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.request_file.name'), value: 'imported_file_name', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.order_id'), value: 'order_id', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.importer') + Vue.i18n.translate('list.column.has_image'), value: 'importer_id', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.importer') },
        { text: Vue.i18n.translate('list.column.status'), value: 'status', align: 'center', width: '100' },
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.importer') + Vue.i18n.translate('list.column.no_image'), value: 'importer_id_no_image', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.importer') },
    ],
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setShowHeaders({ showHeaders }, { params }) {
            showHeaders.splice(params.length)
            Object.assign(showHeaders, params)
        },
        setHiddenHeaders({ hiddenHeaders }, { params }) {
            hiddenHeaders.splice(params.length)
            Object.assign(hiddenHeaders, params)
        },
        resetSearchParams({ searchParams }) {
            Object.assign(searchParams, initialState.searchParams)
        },
        setLoading({ processingData }, val) {
            processingData.loading = val
        },
        setSearchParams ({ searchParams }, val) {
            Object.assign(searchParams, val)
        },
        setOrders({ processingData }, orders) {
            processingData.orders = orders
        },
        setCandidates({ processingData }, candidates) {
            processingData.candidates = candidates
        },
        setPaginate({ paginate }, val) {
            Object.assign(paginate, val)
        },
        setSelectedOrderIds({ processingData }, ids) {
            processingData.selectedOrderIds = ids
        },
        setAllOrderIds({ processingData }, ids) {
            processingData.allOrderId = ids
        },
        setAllAdminOrderIds({ processingData }, ids) {
            processingData.allAdminOrderId = ids
        },
        setIsAdmin({ processingData }, newIsAdmin) {
            processingData.isAdmin = newIsAdmin
        },
        setStartedAt({ processingData }, startedAt) {
            processingData.startedAt = startedAt
        },
    },
    actions: {
        async searchOrderList({ commit, getters, state }) {
            commit('setLoading', true)
            const searchParams = Vue.util.extend({}, state.searchParams)

            try {
                const res = await axios.post('/api/order/orders/index', searchParams)
                // 検索条件をstoreに保存
                commit('setSearchParams', JSON.parse(JSON.stringify(state.searchParams)))

                // 検索結果を画面に反映
                commit('setOrders', res.data.list.data)
                commit('setCandidates', res.data.candidates)
                commit('setAllOrderIds', res.data.all_order_ids)
                commit('setAllAdminOrderIds', res.data.all_admin_order_ids)
                commit('setShowHeaders', { params: getters.headers.showHeaders })
                commit('setHiddenHeaders', { params: getters.headers.hiddenHeaders })
                commit('setSelectedOrderIds', [])
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
        async updateOrders({ commit, state }, { status }) {// 案件のステータスを変更
            commit('setLoading', true)
            const searchParams = Vue.util.extend({}, { order_ids: state.processingData.selectedOrderIds, is_active: status, started_at: state.processingData.startedAt })

            try {
                const res = await axios.post('/api/order/orders/bulk_update', searchParams)
                // 検索条件をstoreに保存
                if (res.data.status !== 200) throw 'Failed to update the order'
            } catch (error) {
                console.log(error)
                commit('setLoading', false)
                throw error
            }
        },
        async createEachOrderFIle({ commit, state }) {
            commit('setLoading', true)
            const form = Vue.util.extend({}, { order_ids: state.processingData.selectedOrderIds })

            try {
                const res = await axios.post('/api/order/orders/create_each_order_file', form)

                if (res.data.status !== 200) throw res.data.message
                // ファイルのダウンロード
                const a = document.createElement('a')
                a.download = res.data.file_name // ダウンロードを失敗した時に出るファイル名
                a.target   = '_blank' // 別タブに切り替えないようにする
                let uri = '/api/utilities/downloadFromLocal?file_path='
                uri += encodeURIComponent(res.data.file_path) + '&file_name=' + encodeURIComponent(res.data.file_name)
                a.href = uri
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)
            } catch (error) {
                console.log(error)
                commit('setLoading', false)
                throw error
            }
            commit('setLoading', false)
        },
    },
    getters: {
        initialHeaders () {
            return {
                showHeaders: initialState.showHeaders,
                hiddenHeaders: initialState.hiddenHeaders,
            }
        },
        headers: state => {
            return state
        },
        searchParams: state => {
            return state.searchParams
        },
    },
    plugins: [
        createPersistedState({
            key: 'orderSearchParams',
            paths: ['searchParams', 'showHeaders', 'hiddenHeaders'],
            storage: window.sessionStorage,
        }),
        createPersistedState({
            key: 'orderHeaders',
            paths: ['showHeaders', 'hiddenHeaders'],
            storage: window.localStorage,
        }),
    ]
})
export default store
