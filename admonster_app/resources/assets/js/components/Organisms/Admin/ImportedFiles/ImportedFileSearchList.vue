<template>
    <div id="search-list">
        <header-custom-modal
            :selectedRowsPerPage="pagination.rowsPerPage"
            :showHeaders="shownHeaders"
            :hiddenHeaders="hiddenHeaders"
            :initialHeaders="initialHeaders"
        ></header-custom-modal>
        <!-- toolbar -->
        <v-layout row wrap align-center>
            <v-tooltip top>
                <v-btn flat icon color="primary" slot="activator" @click="openFileImportDialog()">
                    <v-icon>add_box</v-icon>
                </v-btn>
                <span>{{ $t('imported_files.import') }}</span>
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

        <div class="data-content">
            <!--table-->
            <v-data-table
                v-model="selected"
                :headers="shownHeaders"
                :items="imported_files"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                hide-actions
                must-sort
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="imported_file_sort_key">
                        <th
                            v-for="header in props.headers"
                            :key="header.text"
                            :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                            :style="{ 'min-width': header.width + 'px' }"
                            @click="changeSort(header.value)"
                        >
                            {{ header.text }}
                            <v-icon small>arrow_drop_up</v-icon>
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr :active="props.selected">
                        <template v-for="header in shownHeaders" >
                            <td
                                v-if="'business_name' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item[header.value] }}
                            </td>
                            <td
                                v-else-if="'importer_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <users-overview
                                    :users="importerUsers(props.item)"
                                    :candidates="candidates"
                                ></users-overview>
                            </td>
                            <td
                                v-else-if="'importer_id_no_image' === header.value" :key="header.text"
                                class="text-xs-left"
                                style="max-width: 0px;"
                            >
                                <users-name-display
                                    :userIds="props.item.importer_id ? [props.item.importer_id] : []"
                                    :usersInfo="candidates"
                                    :width="Number(header.width)"
                                ></users-name-display>
                            </td>
                            <td
                                v-else-if="'imported_file_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <a :href="requestUri(props.item.imported_file_id)">
                                    {{ props.item[header.value] }}
                                </a>
                                <!-- TODO プレビュー機能が存在 -->
                                <!-- <a :href="imported_fileDetailUri(props.item.imported_file_id)">
                                    <v-tooltip top>
                                        <span slot="activator">{{ props.item[header.value] }}</span>
                                        <span>{{ props.item[header.value] }}</span>
                                    </v-tooltip>
                                </a> -->
                            </td>
                            <td
                                v-else-if="'imported_file_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item[header.value] }}
                            </td>
                            <td
                                v-else-if="'imported_count' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <a :href="requestUri(props.item.imported_file_id)">
                                    {{ props.item[header.value] }}
                                </a>
                            </td>
                            <td
                                v-else-if="'excluded_count' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <a :href="requestUri(props.item.imported_file_id, 'excluded')">
                                    {{ props.item[header.value] }}
                                </a>
                            </td>
                            <td
                                v-else-if="'wip_count' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <a :href="requestUri(props.item.imported_file_id, 'wip')">
                                    {{ props.item[header.value] }}
                                </a>
                            </td>
                            <td
                                v-else-if="'completed_count' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <a :href="requestUri(props.item.imported_file_id, 'completed')">
                                    {{ props.item[header.value] }}
                                </a>
                            </td>
                            <td
                                v-else-if="'created_at' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{  props.item[header.value] | formatDateYmdHm(true) }}
                            </td>
                        </template>
                    </tr>
                </template>
                <template slot="no-data">
                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                </template>
            </v-data-table>
            <!--table-->
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

            <confirm-dialog ref="confirm"></confirm-dialog>
            <progress-circular v-if="loading"></progress-circular>
        </div>
    </div>
</template>

<script>
import Sortable from 'sortablejs';
import store from '../../../../stores/Admin/ImportedFiles/store'

import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import UsersNameDisplay from '../../../Molecules/Users/UsersNameDisplay'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components:{
        ProgressCircular,
        ConfirmDialog,
        UsersOverview,
        UsersNameDisplay,
        HeaderCustomModal
    },
    props: {
        eventHub: eventHub,
        fileImportDialog: { type: Boolean, require: true }
    },
    data: () => ({
        selected: [],
        imported_files: [],
        candidates: [],

        page: store.state.searchParams.page,
        pagination: {
            sortBy: store.state.searchParams.sort_by,
            descending: store.state.searchParams.descending,
            rowsPerPage: store.state.searchParams.rows_per_page,
        },
        paginate: {
            data_count_total: 0,
            page_count: 0,
            page_from: 0,
            page_to: 0,
        },

        //表頭カスタムモーダルでのデフォルト値
        initialHeaders: {},

        //loading
        loading: false
    }),
    computed: {
        searchParams () {
            return store.state.searchParams
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
                this.searchImportedFileListAsync(this.searchParams)
            },
            get () {
                return this.pagination.rowsPerPage
            }
        }
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
                this.searchImportedFileListAsync(params)
            },
        }
    },
    created () {
        eventHub.$on('commitHeaderCustomData', function(data) {

            // 表頭カスタムデータのコミット処理
            // TODO 一度のコミット処理で行いたい
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })

            // localStorageへ表頭カスタムデータを格納
            // TODO keyの命名規約
            localStorage.setItem('importedFileHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialImportedFile'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('importedFileHeaders', JSON.stringify(localHeadersdata));
            } catch (e) {
                console.log(e)
                localStorage.removeItem('importedFileHeaders');
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
            self.searchImportedFileListAsync(searchParams)
        })
        eventHub.$on('changeRowsPerPage', function(data) {
            self.rowsPerPage = data.rowsPerPage
        })

        //初期化用にstoreの初期値を保存しておく
        localStorage.setItem('initialImportedFile', JSON.stringify(store.state));

        // localStorageより表頭カスタムデータを取得
        // TODO keyの命名規約
        if (localStorage.getItem('importedFileHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('importedFileHeaders'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                console.log(e)
                localStorage.removeItem('importedFileHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        }
    },
    mounted () {
        this.$nextTick(() => {
            const element = document.getElementById('imported_file_sort_key')
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
    methods: {
        searchImportedFileListAsync (params) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/imported_files', searchParams)
                .then((res) =>{
                // 検索結果を画面に反映
                    this.imported_files = res.data.list.data
                    this.candidates = res.data.candidates
                    this.paginate.data_count_total = res.data.list.total
                    this.paginate.page_count = res.data.list.last_page
                    this.paginate.page_from = res.data.list.from ? res.data.list.from : 0
                    this.paginate.page_to = res.data.list.to ? res.data.list.to : 0
                }).catch((error) => {
                    console.log(error)
                }).finally(() => {
                    this.loading = false
                })
        },
        changePage (page) {
            let params = this.searchParams
            params.page = page
            this.searchImportedFileListAsync(params)
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
            // ファイル取込一覧を取得
            this.searchImportedFileListAsync(params)
        },
        openFileImportDialog () {
            store.commit('resetUploadFile')
            this.$emit('update:fileImportDialog', true)
        },
        openHeaderCustomModal () {
            //TODO 初期変数の格納タイミング
            this.initialHeaders = JSON.parse(localStorage.getItem('initialImportedFile'));
            eventHub.$emit('open-header-custom-modal')
        },
        async deleteFile (file_id) {
            if (await this.$refs.confirm.show('ファイルを削除しますか？ 発生した依頼データも削除されます。')) {
                this.loading = true
                axios.post('/api/imported_files/delete', {request_file_id: file_id})
                    .then((res) =>{
                        if (res.data.result == 'not_allowed') {
                            eventHub.$emit('open-notify-modal', { message: '進行中のタスクが存在しているため削除できません。' });
                        }
                        if (res.data.result == 'success') {
                            eventHub.$emit('open-notify-modal', { message: '削除しました。' });
                            this.searchImportedFileListAsync(this.searchParams)

                            eventHub.$emit('updateLatestImportedFileList', {})
                        }
                    }).catch((error) => {
                        console.log(error)
                        eventHub.$emit('open-notify-modal', { message: 'データの削除に失敗しました。' });
                    }).finally(() => {
                        this.loading = false
                    })
            }
        },
        importerUsers (item) {
            return {
                allocated_user_ids: item. importer_id ? [item. importer_id] : [],
                completed_user_ids: item.user_id ? [] : [],
            }
        },
        completed_count (item) {
            let all_active_count = item.wip_count + item.completed_count
            if (item.completed_count === all_active_count) {
                return item.completed_count
            } else {
                return item.completed_count + '/' + all_active_count
            }
        },
        requestUri (request_file_id, status=null) {
            let uri = '/management/requests?request_file_id=' + encodeURIComponent(request_file_id)
            if (status){
                uri += '&status=' + encodeURIComponent(this.requestStatus(status))
            }
            return uri
        },
        requestStatus (status) {
            switch (status) {
            case 'all':
                return 0
            case 'wip':
                return 1
            case 'completed':
                return 2
            case 'excluded':
                return 3
            default:
                return 0
            }
        },
    }
}
</script>
