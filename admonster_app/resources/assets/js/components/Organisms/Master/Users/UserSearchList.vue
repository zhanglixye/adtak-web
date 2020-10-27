<template>
    <div id="search-list">
        <header-custom-modal
                :selectedRowsPerPage="pagination.rowsPerPage"
                :showHeaders="shownHeaders"
                :hiddenHeaders="hiddenHeaders"
                :initialHeaders="initialHeaders"
        >
        </header-custom-modal>
        <!-- toolbar -->
        <v-layout row wrap align-center>
            <v-tooltip top>
                <v-btn flat icon color="primary" slot="activator" @click="openUserDiglog()">
                    <v-icon>person_add</v-icon>
                </v-btn>
                <span>{{ $t('list.add_user') }}</span>
            </v-tooltip>
            <v-tooltip top v-show="updatePasswordFlg">
                <v-btn flat icon color="primary" slot="activator" @click="openUpdatePasswordDiaLog()">
                    <v-icon>vpn_key</v-icon>
                </v-btn>
                <span>{{ $t('list.update_password') }}</span>
            </v-tooltip>
            <v-spacer></v-spacer>
            <v-tooltip top>
                <v-btn flat icon color="primary" slot="activator" @click="openHeaderCustomModal()">
                    <v-icon>settings</v-icon>
                </v-btn>
                <span>{{ $t('list.config') }}</span>
            </v-tooltip>
        </v-layout>
        <!-- toolbar -->
        <v-data-table
                v-model="selected"
                :headers="shownHeaders"
                :items="users"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                hide-actions
                must-sort
                class="elevation-1"
        >
            <template slot="headers" slot-scope="props">
                <tr id="user_sort_key">
                    <th id="ignore-elements">
                        <v-checkbox
                                primary
                                hide-details
                                @click.native="toggleAll(props)"
                                :input-value="props.all"
                                :indeterminate="props.indeterminate"
                                disabled
                        ></v-checkbox>
                    </th>
                    <th
                            v-for="header in props.headers"
                            :key="header.text"
                            :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                            :style="{ minWidth: header.width + 'px' }"
                            @click="changeSort(header.value)"
                    >
                        {{ header.textForSetting ? header.textForSetting : header.text }}
                        <v-icon small>arrow_drop_up</v-icon>
                    </th>
                </tr>
            </template>
            <template slot="items" slot-scope="props">
                <tr
                        :active="props.selected"
                        style="cursor:pointer;"
                >
                    <td
                            width="40px"
                            class="text-xs-center"
                            @click.stop=""
                    >
                        <v-checkbox
                                :input-value="props.selected"
                                primary
                                hide-details
                                color="primary"
                                @click.native="selectedItem(props.item)"
                        ></v-checkbox>
                    </td>
                    <template v-for="header in shownHeaders">
                        <td
                            v-if="'id' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            {{ props.item.id }}
                        </td>
                        <td
                            v-if="'full_name' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            {{ props.item.name }}
                        </td>
                        <td
                            v-if="'user_image' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            <template>
                                <v-avatar size="32px" :tile="false">
                                    <img :src="props.item.user_image_path">
                                </v-avatar>
                            </template>

                        </td>
                        <td
                            v-if="'email' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            {{ props.item.email }}
                        </td>
                        <td
                            v-if="'nickname' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            {{ props.item.nickname }}
                        </td>
                        <td
                            v-if="'sex' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            {{ sexString(props.item.sex)}}
                        </td>
                        <td
                            v-if="'birthday' === header.value" :key="header.text"
                            class="text-xs-center overflow"
                        >
                            {{ props.item.birthday }}
                        </td>
                        <td
                                v-if="'postal_code' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.postal_code }}
                        </td>
                        <td
                                v-if="'address' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.address }}
                        </td>
                        <td
                                v-if="'tel' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.tel }}
                        </td>
                        <td
                                v-if="'remarks' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.remarks }}
                        </td>
                        <td
                                v-if="'created_at' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.created_at }}
                        </td>
                        <td
                                v-if="'updated_at' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.updated_at }}
                        </td>
                        <td
                                v-if="'timezone' === header.value" :key="header.text"
                                class="text-xs-center overflow"
                        >
                            {{ props.item.timezone }}
                        </td>
                    </template>

                </tr>

            </template>
            <template slot="no-data">
                <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
            </template>

        </v-data-table>
        <!--pagination-->
        <v-container fluid grid-list-md pa-2>
            <v-layout row wrap align-center>
                <v-spacer></v-spacer>
                <span>{{ $t('common.pagination.display_count_per_one_page') }}</span>
                <v-select
                        v-model="rowsPerPage"
                        :style="{'max-width': '50px', 'margin-left': '30px', 'margin-right': '50px'}"
                        :items="rowsCandidatesPerPage"
                        :menu-props="{ maxHeight: '300' }"
                ></v-select>
                <div>
                    {{ $t('common.pagination.all') + paginate.data_count_total + $t('common.pagination.items') + paginate.page_from + $t('common.pagination.from') + '～' + paginate.page_to + $t('common.pagination.to') }}
                </div>
                <v-pagination
                        v-model="page"
                        :length="paginate.page_count"
                        circle
                        :total-visible="5"
                        @input="changePage(page)"
                ></v-pagination>
            </v-layout>
        </v-container>
        <!--pagination-->
        <alert-dialog ref="alert"></alert-dialog>
        <user-dialog
            ref="UserDialog"
            @add-user="addUser"
        ></user-dialog>
        <update-password-dialog
            ref="UpdatePasswordDialog"
            :updateUserData ="updateUserData"
            @update-password="updatePassword"
        ></update-password-dialog>
    </div>
</template>

<script>
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import UserDialog from './UserDialog'
import UpdatePasswordDialog from './UpdatePasswordDialog'
import store from '../../../../stores/Master/Users/store'
import AlertDialog from './../../../Atoms/Dialogs/AlertDialog';
import Sortable from 'sortablejs'
export default {
    name: 'UserSearchList',
    components:{
        HeaderCustomModal,
        UserDialog,
        UpdatePasswordDialog,
        AlertDialog
    },
    data:()=>({
        selected: [],
        users: [],
        //表頭カスタムモーダルでのデフォルト値
        initialHeaders: {},
        selectedUserIds: [],
        updateUserData: {},

        page: store.getters.getSearchParams.page,
        pagination: {
            sortBy: store.getters.getSearchParams.sort_by,
            descending: store.getters.getSearchParams.descending,
            rowsPerPage: store.getters.getSearchParams.rows_per_page,
        },
        paginate: {
            data_count_total: 0,
            page_count: 0,
            page_from: 0,
            page_to: 0,
        },
    }),
    computed: {
        updatePasswordFlg () {
            if (this.selectedUserIds.length === 1) {
                return true
            } else {
                return  false
            }
        },
        searchParams () {
            return store.getters.getSearchParams
        },
        shownHeaders() {
            return store.state.showHeaders;
        },
        hiddenHeaders() {
            return store.state.hiddenHeaders
        },
        rowsCandidatesPerPage () {
            return [20, 50, 100]
        },
        rowsPerPage: {
            set (rowsPerPage) {
                this.searchParams.rows_per_page = rowsPerPage
                this.pagination.rowsPerPage = rowsPerPage
                // ページを初期化
                this.page = 1
                this.searchParams.page = this.page
                this.searchRequestListAsync(this.searchParams)
            },
            get () {
                return this.pagination.rowsPerPage
            }
        },
    },
    created () {
        eventHub.$on('commitHeaderCustomData', function(data) {

            // 表頭カスタムデータのコミット処理
            // TODO 一度のコミット処理で行いたい
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })

            // localStorageへ表頭カスタムデータを格納
            // TODO keyの命名規約
            localStorage.setItem('userHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialUser'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('requestHeaders', JSON.stringify(localHeadersdata));
            } catch (e) {
                console.log(e)
                localStorage.removeItem('requestHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        })

        let self = this
        eventHub.$on('search', function ({ searchParams }) {
            // ページ、ソート条件をリセット
            self.page = searchParams.page
            self.pagination.sortBy = searchParams.sort_by
            self.pagination.descending = searchParams.descending
            self.pagination.rowsPerPage = searchParams.rows_per_page
            // 案件情報を検索
            self.searchRequestListAsync(searchParams)
        })
        eventHub.$on('changeRowsPerPage', function(data) {
            self.rowsPerPage = data.rowsPerPage
        })

        //初期化用にstoreの初期値を保存しておく
        localStorage.setItem('initialUser', JSON.stringify(store.state));

        // localStorageより表頭カスタムデータを取得
        // TODO keyの命名規約
        if (localStorage.getItem('requestHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('requestHeaders'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                console.log(e)
                localStorage.removeItem('requestHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        }

    },
    methods: {
        sexString(sex){
            if (sex === _const.SEX.MALE) {
                return Vue.i18n.translate('list.sex_category.male')
            } else if (sex === _const.SEX.FEMALE){
                return Vue.i18n.translate('list.sex_category.female')
            } else {
                return ''
            }
        },
        selectedItem (item) {
            const userId = item.id
            if (!this.selectedUserIds.includes(userId)) {
                this.selectedUserIds.push(userId)
                this.selected.push(item)
            }
            else {
                this.selectedUserIds = this.selectedUserIds.filter(id => id !== userId)
                this.selected = this.selected.filter(item => item.id !== userId)
            }
        },
        updatePassword(userId,newPassword){
            this.loading = true
            axios.post('/api/master/users/updatePassword', {
                userId : userId,
                newPassword : newPassword
            })
                .then((res) =>{
                    if (res.data.status === 200){
                        this.$refs.UpdatePasswordDialog.close()
                        this.selected = []
                        this.selectedUserIds = []
                    }
                }).catch((error) => {
                    console.log(error)
                }).finally(() => {
                    this.loading = false
                })

        },
        addUser: async function (user) {
            this.loading = true
            // 画像データをblobURL -> base64
            const files = user.image
            const convert = this.convertToBase64
            await Promise.all(files.map(async upload_file => upload_file = await convert(upload_file)))
            for (let i = 0; i <  user.image.length; i++){
                delete user.image[i].type
            }
            axios.post('/api/master/users/addUser', {
                user : user
            })
                .then((res) =>{
                    if (res.data.status === 200){
                        this.$refs.UserDialog.close()
                        let params = this.searchParams
                        params.sort_by = this.pagination.sortBy
                        params.descending = this.pagination.descending
                        // タスク一覧を取得
                        this.searchRequestListAsync(params)
                    } else if (res.data.message === 'this email address was also used') {
                        this.$refs.alert.show(this.$t('master.users.err_message.email_used'))
                    }

                }).catch((error) => {
                    console.log(error)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }).finally(() => {
                    this.loading = false
                })
        },
        changeSort (column) {
            if (this.pagination.sortBy === column) {
                this.pagination.descending = !this.pagination.descending
            } else {
                this.pagination.sortBy = column
                this.pagination.descending = false
            }
            // ソート条件を更新
            let params = this.searchParams
            params.sort_by = this.pagination.sortBy
            params.descending = this.pagination.descending
            // タスク一覧を取得
            this.searchRequestListAsync(params)
        },
        changePage (page) {
            let params = this.searchParams
            params.page = page
            this.searchRequestListAsync(params)
        },
        openUserDiglog:async function () {
            this.$refs.UserDialog.show()
        },
        openUpdatePasswordDiaLog:async function () {
            this.updateUserData = this.selected[0]
            this.$refs.UpdatePasswordDialog.show()
        },
        openHeaderCustomModal() {
            //TODO 初期変数の格納タイミング
            this.initialHeaders = JSON.parse(localStorage.getItem('initialUser'));
            eventHub.$emit('open-header-custom-modal')
        },
        searchRequestListAsync (params = this.searchParams) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/master/users', searchParams)
                .then((res) =>{
                    // 検索条件をstoreに保存
                    store.dispatch('setSearchParams', params)
                    // 検索結果を画面に反映
                    this.users = res.data.users.data
                    this.paginate.data_count_total = res.data.users.total
                    this.paginate.page_count = res.data.users.last_page
                    this.paginate.page_from = res.data.users.from ? res.data.users.from : 0
                    this.paginate.page_to = res.data.users.to ? res.data.users.to : 0
                }).catch((error) => {
                    console.log(error)
                }).finally(() => {
                    this.loading = false
                })
        },
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {

                // base64データが入っている場合は処理しない
                if ('data' == file.file_data.substring(0, 4)) resolve(file)

                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob'
                xhr.onload = () => {
                    var reader = new window.FileReader()
                    reader.readAsDataURL(xhr.response)
                    reader.onloadend = () => {
                        // メモリから削除
                        URL.revokeObjectURL(file.file_data)
                        file.file_data = reader.result
                        resolve(file)
                    }
                    reader.onerror = (e) => reject(e)
                }
                xhr.onerror = (e) => reject(e)
                xhr.open('GET', file.file_data)
                xhr.send()
            })
        },

    },
    mounted () {
        this.$nextTick(() => {
            const element = document.getElementById('user_sort_key')
            const _self = this
            Sortable.create(element, {
                onEnd({ newIndex, oldIndex }) {
                    let headers = _self.shownHeaders
                    const headerSelected = headers.splice(oldIndex, 1)[0]
                    headers.splice(newIndex, 0, headerSelected)
                    eventHub.$emit('commitHeaderCustomData', {
                        'showHeaders': headers,
                        'hiddenHeaders': store.state.hiddenHeaders
                    });
                }
            })
        })
    },
    watch: {
        pagination: {
            // ヘッダークリックによるソート処理（サーバ）
            handler () {
                // ソート条件を更新
                let params = this.searchParams

                params.sort_by = this.pagination.sortBy
                params.descending = this.pagination.descending
                // タスク一覧を取得
                this.searchRequestListAsync(params)
            },
        }
    }
}
</script>

<style scoped>

</style>