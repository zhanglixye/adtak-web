<template>
    <div id="request-work-list">

        <!-- toolbar -->
        <v-layout row wrap align-center>
            <div v-show="showButton">
                <v-tooltip top>
                    <v-btn @click="goToMultipleAllocations()" flat icon color="primary" slot="activator">
                        <v-icon>group_add</v-icon>
                    </v-btn>
                    <span>{{ $t('request_works.multi_allocate') }}</span>
                </v-tooltip>
            </div>
            <v-spacer></v-spacer>
            <v-tooltip top>
                <v-btn flat icon color="primary" slot="activator">
                    <v-icon>settings</v-icon>
                </v-btn>
                <span>{{ $t('list.config') }}</span>
            </v-tooltip>
        </v-layout>
        <!-- toolbar -->

        <div class="data-content">
            <!-- table -->
            <v-data-table
                v-model="selected"
                :headers="headers"
                :items="request_works"
                :pagination.sync="pagination"
                item-key="request_work_id"
                :total-items="paginate.data_count_total"
                hide-actions
                must-sort
                select-all
                class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <tr
                        :active="props.selected"
                        @mouseenter="requestDetailLinkIconShow(props.item.request_work_id)"
                        @mouseleave="requestDetailLinkIconHide(props.item.request_work_id)"
                    >
                        <td class="text-xs-center">
                            <v-checkbox
                                :input-value="props.selected"
                                primary
                                hide-details
                                color="primary"
                                @click="props.selected = !props.selected"
                            ></v-checkbox>
                        </td>
                        <td class="text-xs-center">{{ requestWorkCode(props.item) }}</td>
                        <td class="text-xs-left overflow request-work-title" style="min-width: 400px;">
                            <span class="request-work-title-content">
                                <a :href="requestDetailUrl(props.item.id)" :class="'mr-1 request-detail-link request-detail-link' + props.item.request_work_id">
                                    <v-tooltip top>
                                        <span slot="activator"><v-icon small>mail_outline</v-icon></span>
                                        <span>依頼詳細へ</span>
                                    </v-tooltip>
                                </a>
                                <a :href="taskUrl(props.item.step_url, props.item.request_work_id, props.item.task_id)" class="request-work-title-text" target="_blank">
                                    <v-tooltip top>
                                        <span slot="activator">{{ props.item.request_work_name }}</span>
                                        <span>{{ props.item.request_work_name }}</span>
                                    </v-tooltip>
                                </a>
                            </span>
                        </td>
                        <td class="text-xs-center">{{ props.item.step_name }}</td>
                        <td class="text-xs-center">
                            <template v-if="isDeleted(props.item.request_status)">
                                <v-chip small label text-color="white" disabled color="grey">除外</v-chip>
                            </template>
                            <template v-else-if="isInactive(props.item.request_work_is_active)">
                                <v-chip small outline label disabled color="grey">無効</v-chip>
                            </template>
                            <template v-else v-for="(process, i) in processes">
                                <a :href="showProcess(process, props.item).link" :key="i" style="display: contents; text-decoration: none;">
                                    <v-tooltip top>
                                        <v-icon slot="activator" :color="showProcess(process, props.item).color">forward</v-icon>
                                        <span>{{ $t(process.title) + showProcess(process, props.item).suffix }}</span>
                                    </v-tooltip>
                                </a>
                            </template>
                        </td>
                        <td class="text-xs-center">
                            <users-overview :users="users(props.item)" :candidates="candidates"></users-overview>
                        </td>
                        <td class="text-xs-center">{{ props.item.request_work_created_at | formatDateYmdHm(true) }}</td>
                        <td class="text-xs-center">{{ props.item.deadline | formatDateYmdHm }}</td>
                    </tr>
                </template>
                <template slot="no-data">
                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                </template>
            </v-data-table>
            <!-- table -->

            <!-- pagination -->
            <v-container fluid grid-list-md pa-2>
                <v-layout row wrap align-center>
                    <v-spacer></v-spacer>
                    <div style="margin-right:14px;">
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
            <!-- pagination -->
            <progress-circular v-if="loading"> </progress-circular>
            <!-- <message-dialog v-if="dialog" :messages="dialogMessages"></message-dialog> -->
            <v-dialog v-model="dialog" persistent max-width="600px">
                <v-card>
                    <div v-for="(item, index) in dialogMessages" :key="index">
                        <v-card-text v-if="item">
                            <v-icon small color="red">error</v-icon>{{ item }}
                        </v-card-text>
                    </div>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" flat @click="dialog = false">閉じる</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

        </div>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/RequestWorks/store.js'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
// import messageDialog from '../../../Atoms/messageDialog'

const processes = [
    { id: 1, title: 'request_works.process.allocation', link: '/allocations/' },
    { id: 2, title: 'request_works.process.work', link: '/allocations/' },
    { id: 3, title: 'request_works.process.approval', link: '/approvals/' },
    { id: 4, title: 'request_works.process.delivery', link: '/approvals/' },
]

export default {
    data: () => ({
        dialog: false,
        dialogMessages: [],
        // headers: headers,

        // TODO: ブラウザバックボタンと同様の戻るボタンを設置
        previousUri: '',

        processes: processes,

        selected: [],

        request_works: [],
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
        //loading
        loading: false,
    }),
    components: {
        ProgressCircular,
        UsersOverview,
        // messageDialog
    },
    created () {
        let self = this;
        eventHub.$on('search', function({ searchParams }) {
            // ページ、ソート条件をリセット
            self.page = searchParams.page
            self.pagination.sortBy = searchParams.sort_by
            self.pagination.descending = searchParams.descending
            // 依頼作業情報を検索
            self.searchRequestWorkListAsync(searchParams)
        })
    },
    computed: {
        searchParams () {
            return store.state.searchParams
        },
        // 一括操作ボタンの表示制御
        showButton () {
            return this.selected.length
        },
        headers () {
            return [
                { text: eventHub.$t('request_works.request_work_id'), value: 'request_work_id', align: 'center', width: '100px' },
                { text: eventHub.$t('request_works.request_work_name'), value: 'request_work_name', align: 'center', width: '' },
                { text: eventHub.$t('request_works.current_task'), value: 'step_name', align: 'center', width: '100px' },
                { text: eventHub.$t('request_works.process.title'), value: 'process', align: 'center', width: '100px' }, // TODO: ソート方法検討
                { text: eventHub.$t('request_works.operator'), value: 'user_ids', align: 'center', width: '100px' },     // TODO: ソート方法検討
                { text: eventHub.$t('request_works.created_at'), value: 'request_work_created_at', align: 'center', width: '100px' },
                { text: eventHub.$t('request_works.deadline'), value: 'deadline', align: 'center', width: '100px' },
            ]
        },
        completed () {
            return eventHub.$t('request_works.process.completed')
        },
        inactiveRequestWork () {
            return function(activeFlg) {
                return activeFlg === _const.REQUEST_WORK_ACTIVE_FLG.INACTIVE
            }
        },
        requestDetailUrl () {
            return function (requestId) {
                return '/management/requests/' + requestId
            }
        },
        // TODO : WF概算修正業務を早期運用開始するための暫定対応。必要なくなり次第削除 2019/03/11
        taskUrl () {
            return function (path, requestWorkId, taskId) {
                return '/biz/' + path + '/' + requestWorkId + '/' + taskId + '/create'
            }
        },
        requestWorkDetailUrI () {
            return function (requestWorkId) {
                return '/management/request_works/' + requestWorkId
            }
        },
        isDeleted () {
            return function (status) {
                return status === _const.REQUEST_STATUS.EXCEPT
            }
        },
        isInactive () {
            return function (is_active) {
                return is_active === _const.FLG.INACTIVE
            }
        },
        requestWorkCode() {
            return function(item) {
                const prefix = _const.MASTER_ID_PREFIX
                return prefix.REQUEST_ID + item.id + prefix.SEPARATOR + prefix.REQUEST_WORKS_ID + item.request_work_id
            }
        },
    },
    methods: {
        searchRequestWorkListAsync (searchParams) {
            this.loading = true
            axios.post('/api/request_works', searchParams)
                .then((res) => {
                    console.log(res)
                    // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: searchParams })
                    // 検索結果を画面に反映
                    this.candidates = res.data.candidates
                    this.request_works = res.data.request_works.data
                    this.paginate.data_count_total = res.data.request_works.total
                    this.paginate.page_count = res.data.request_works.last_page
                    this.paginate.page_from = res.data.request_works.from ? res.data.request_works.from : 0
                    this.paginate.page_to = res.data.request_works.to ? res.data.request_works.to : 0
                })
                .catch((err) => {
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                })
        },
        showProcess: function (process, item) {
            let array = null;
            // DONE
            if ( process.id < item.process || (item.process === 4 && item.status === 2) ) {
                let link = process.link === '' ? '/management/request_works/#' : process.link + item.request_work_id + '/edit'
                array = { link: link, color: 'grey lighten-1', suffix: '：'+this.completed };
            // ON
            } else if ( process.id === item.process ) {
                let link = process.link === '' ? '/management/request_works/#' : process.link + item.request_work_id + '/edit'
                array = { link: link, color: 'teal darken-2', suffix: '' };
            // WAIT
            } else {
                let link = process.link === '' ? '/management/request_works/#' : process.link + item.request_work_id + '/edit'
                array = { link: link, color: 'teal lighten-4', suffix: '' };
            }
            return array;
        },
        users (item) {
            return {
                allocated_user_ids: item.user_ids ? item.user_ids.split(',') : [],
                completed_user_ids: item.completed_user_ids ? item.completed_user_ids.split(',') : [],
            }
        },
        goToMultipleAllocations: function () {
            const selectedRequests = this.selected
            let possibleProcessIds = [1, 2]
            let selectedStepIds = []
            let isPossible = true
            let requestWorkIds = []
            let messages = []
            selectedRequests.forEach(function(item){
                requestWorkIds.push(item.request_work_id)
                // 案件のステータスチェック(承認以降はNG)
                if (possibleProcessIds.indexOf(item.process) == -1 || item.request_work_is_active == _const.REQUEST_WORK_ACTIVE_FLG.INACTIVE) {
                    messages[0] = '割振が行えないステータスの案件が含まれています'
                }
                // step_idの一意性チェック
                if (selectedStepIds.length < 1) {
                    selectedStepIds.push(item.step_id)
                } else {
                    if (selectedStepIds.indexOf(item.step_id) == -1) {
                        messages[1] = '複数作業にわたっての一括割振はできません'
                    }
                }
            });

            if (messages.length > 0) {
                this.dialogMessages = messages
                this.dialog = true
                isPossible = false
            }

            if (isPossible) {
                // 一括割振画面に遷移
                window.location.href = '/multiple_allocations/create?request_work_ids=' + encodeURIComponent(requestWorkIds)
            }
        },
        changePage (page) {
            let params = this.searchParams
            params.page = page
            this.searchRequestWorkListAsync(params)
        },
        requestDetailLinkIconShow: function(requestWorkId) {
            let self = this
            self.$nextTick(function () {
                $('.request-detail-link' + requestWorkId).show()
            });
        },
        requestDetailLinkIconHide: function(requestWorkId) {
            let self = this
            self.$nextTick(function () {
                $('.request-detail-link' + requestWorkId).hide()
            });
        }
    },
    watch: {
        pagination: {
            // ヘッダークリックによるソード処理（サーバ）
            handler () {
                // ソート条件を更新
                let params = this.searchParams
                params.sort_by = this.pagination.sortBy
                params.descending = this.pagination.descending
                // 依頼作業一覧を取得
                this.searchRequestWorkListAsync(params)
            },
        }
    },
}
</script>
<style>
#data-content {
    position: relative;
}
</style>
