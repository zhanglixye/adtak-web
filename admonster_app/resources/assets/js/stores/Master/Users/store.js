import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const  initialState = {
    searchParams:{
        id: null,
        name: '',
        email: '',
        page: 1,
        sort_by: 'id',
        descending: true,
        rows_per_page: 20,
        status: 1,
    },
    showHeaders:[
        { text: Vue.i18n.translate('list.column.id'), value: 'id', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.full_name'), value: 'full_name', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.user_image'), value: 'user_image', align: 'center', width: '300' },
        { text: Vue.i18n.translate('list.column.email'), value: 'email', align: 'center', width: '200' }
    ],
    hiddenHeaders:[
        { text: Vue.i18n.translate('list.column.nickname'), value: 'nickname', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.sex'), value: 'sex', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.birthday'), value: 'birthday', align: 'center', width: '50' },
        { text: Vue.i18n.translate('list.column.postal_code'), value: 'postal_code', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.address'), value: 'address', align: 'center', width: '400' },
        { text: Vue.i18n.translate('list.column.tel'), value: 'tel', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.remarks'), value: 'remarks', align: 'center', width: '200' },
        { text: Vue.i18n.translate('list.column.created_at'), value: 'created_at', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.updated_at'), value: 'updated_at', align: 'center', width: '150' },
        { text: Vue.i18n.translate('list.column.updated_user_id'), value: 'updated_user_id', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.created_user_id'), value: 'created_user_id', align: 'center', width: '100' },
        { text: Vue.i18n.translate('list.column.timezone'), value: 'timezone', align: 'center', width: '100' },
    ],
}
let userParams
const store = new Vuex.Store({
    state: initialState,
    getters: {
        getSearchParams: state => {
            return userParams ? userParams : state.searchParams
        },
    },
    mutations: {
        setSearchParams ({ searchParams }, { params }) {
            Object.assign(searchParams, params)
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
    actions: {
        setSearchParams ({dispatch, commit}, params = initialState.searchParams) {
            if (userParams) {
                dispatch('setUserParams', params)
            } else {
                commit('setSearchParams', {params: params})
            }
        }
    },
    plugins: [
        createPersistedState({
            key: 'userSearchParams',
            paths: ['searchParams'],
            storage: window.sessionStorage,
        })
    ]

})

export default store