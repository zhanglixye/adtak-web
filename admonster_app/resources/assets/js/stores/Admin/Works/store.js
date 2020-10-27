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
        subject: '',
        step_name: '',
        status: [
            { text: Vue.i18n.translate('works.list.task_status.none'), value: _const.TASK_STATUS.NONE },
            { text: Vue.i18n.translate('works.list.task_status.on'), value: _const.TASK_STATUS.ON },
        ],
        approval_status: [],
        task_contact: false,

        page: 1,
        sort_by: 'task_created_at',
        descending: false,
        rows_per_page: 20,
    },
    showHeaders:[
        { text: Vue.i18n.translate('list.column.worker') + Vue.i18n.translate('list.column.has_image'), value: 'user_id', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.worker') },
        { text: Vue.i18n.translate('list.column.client_name'), value: 'client_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.subject'), value: 'request_work_name', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.status'), value: 'status', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.task_created_at'), value: 'task_created_at', align: 'center', width: '100' },
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.worker') + Vue.i18n.translate('list.column.no_image'), value: 'user_id_no_image', align: 'center', width: '100', textForSetting: Vue.i18n.translate('list.column.worker') },
        { text: Vue.i18n.translate('list.column.company_name'), value: 'company_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.business_name'), value: 'business_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.request_id'), value: 'request_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.request_work_id'), value: 'request_work_id', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.created_at'), value: 'created_at', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.deadline'), value: 'deadline', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.step_name'), value: 'step_name', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.comment'), value: 'task_message', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.task_time_taken'), value: 'task_time_taken', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.approval_result'), value: 'approval_result', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.approval_status'), value: 'approval_status', align: 'center', width: '100' },
        // TODO 別途、各種集計をとるための設計が必要
        // { text: Vue.i18n.translate('list.column.business_cumulative_time'), value: 'business_cumulative_time', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.work_cumulative_time'), value: 'work_cumulative_time', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.remain_task_count'), value: 'remain_task_count', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.remain_task_expect_time'), value: 'remain_task_expect_time', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.task_result_count'), value: 'task_result_count', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.task_result_average'), value: 'task_result_average', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.login_status'), value: 'login_status', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.last_login'), value: 'last_login', align: 'center', width: '100' },
        // { text: Vue.i18n.translate('list.column.percentage'), value: 'percentage', align: 'center', width: '100' },
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
        resetSearchParams ({ searchParams }) {
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
            key: 'workSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        })
    ]

})
export default store
