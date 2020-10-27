import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    searchParams: {
        request_work_ids: [],
        business_name: '',
        date_type: 1,
        from: '',
        to: '',
        client_name: '',
        worker: '',
        deliverer: '',
        subject: '',
        step_name: '',
        delivery_status: [{ text: Vue.i18n.translate('deliveries.list.status.none'), value: _const.DELIVERY_STATUS.NONE }],

        page: 1,
        sort_by: 'created_at',
        descending: false,
        rows_per_page: 20,
    },
    showHeaders:[
        { text: Vue.i18n.translate('list.column.client_name'), value: 'client_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.subject'), value: 'request_work_name', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.deliverer') + Vue.i18n.translate('list.column.has_image'), value: 'deliverer_id', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.deliverer') },
        { text: Vue.i18n.translate('list.column.status'), value: 'delivery_status', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.created_at'), value: 'created_at', align: 'center', width: '100' },
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.company_name'), value: 'company_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.business_name'), value: 'business_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.request_id'), value: 'request_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.request_work_id'), value: 'request_work_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.deadline'), value: 'deadline', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.assign_date'), value: 'assign_date', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.step_name'), value: 'step_name', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.worker') + Vue.i18n.translate('list.column.has_image'), value: 'worker_ids', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.worker') },
        { text: Vue.i18n.translate('list.column.worker') + Vue.i18n.translate('list.column.no_image'), value: 'worker_ids_no_image', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.worker') },
        { text: Vue.i18n.translate('list.column.delivery_data_creator') + Vue.i18n.translate('list.column.has_image'), value: 'delivery_data_creator', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.delivery_data_creator') },
        { text: Vue.i18n.translate('list.column.delivery_data_creator') + Vue.i18n.translate('list.column.no_image'), value: 'delivery_data_creator_no_image', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.delivery_data_creator') },
        { text: Vue.i18n.translate('list.column.deliverer') + Vue.i18n.translate('list.column.no_image'), value: 'deliverer_id_no_image', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.deliverer') },
        { text: Vue.i18n.translate('list.column.delivery_time'), value: 'delivery_time', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.request_mail.subject'), value: 'subject', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.request_mail.from_address'), value: 'request_mail_from', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.request_mail.to_address'), value: 'request_mail_to', align: 'center', width: '200' },
        // ※表示したいとなっても対応できるようにコメントアウトにしておく
        // { text: Vue.i18n.translate('list.column.request_mail.attachment_file'), value: 'mail_attachments_count', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.request_file.name'), value: 'request_file_name', align: 'center', width: '400' }
    ],
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
        },
        resetSearchParams ({searchParams}) {
            Object.assign(searchParams, initialState.searchParams)
        },
        setShowHeaders ({ showHeaders },{ params }) {
            showHeaders.splice(params.length)
            Object.assign(showHeaders, params)
        },
        setHiddenHeaders ({ hiddenHeaders }, { params }) {
            hiddenHeaders.splice(params.length)
            Object.assign(hiddenHeaders, params)
        },
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'deliverySearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,

        })
    ]

})
export default store
