import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'
const  initialState = {
    show:{
        searchList: false,
        latest_imported_file_list: true,
    },
    searchParams:{
        imported_file_name: '',
        imported_file_id: '',
        from: '',
        to: '',
        status: _const.IMPORTED_FILE_STATUS.DOING,
        business_name: '',
        importer: '',
        page: 1,
        sort_by: 'created_at',
        descending: false,
        rows_per_page: 20,
    },
    uploadFile: {},
    showHeaders:[
        { text: Vue.i18n.translate('list.column.business_name'), value: 'business_name', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.importer') + Vue.i18n.translate('list.column.has_image'), value: 'importer_id', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.request_file.name'), value: 'imported_file_name', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.occurred'), value: 'imported_count', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.excluded'), value: 'excluded_count', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.wip'), value: 'wip_count', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.completed'), value: 'completed_count', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.request_file.created_at'), value: 'created_at', align: 'center', width: '100' },
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.importer') + Vue.i18n.translate('list.column.no_image'), value: 'importer_id_no_image', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.request_file.id'), value: 'imported_file_id', align: 'center', width: '200' }
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
        openSearchList ({ show }) {
            show.searchList = true
        },
        closeSearchList ({ show }) {
            show.searchList = false
        },
        openLatestImportedFileList ({ show }) {
            show.latest_imported_file_list = true
        },
        closeLatestImportedFileList ({ show }) {
            show.latest_imported_file_list = false
        },
        setUploadFile (state, val) {
            Object.assign(state.uploadFile, val)
        },
        resetUploadFile (state) {
            state.uploadFile = {}
        },
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'AD-MONSTER-importedFiles',
            paths: ['show', 'searchParams', 'uploadFile'],
            storage: window.sessionStorage,
        })
    ]
})
export default store
