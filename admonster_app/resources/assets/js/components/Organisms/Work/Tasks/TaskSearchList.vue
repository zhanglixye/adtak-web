<template>
    <!-- 一覧 -->
    <div id="search-list">
        <header-custom-modal
            :selectedRowsPerPage="pagination.rowsPerPage"
            :showHeaders="shownHeaders"
            :hiddenHeaders="hiddenHeaders"
            :initialHeaders="initialHeaders"
        ></header-custom-modal>
        <!-- toolbar -->
        <v-layout row wrap align-center>
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
                :items="tasks"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                hide-actions
                must-sort
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="task_sort_key">
                        <!-- <th id="ignore-elements">
                            <v-checkbox
                                hide-details
                                @click.native="toggleAll(props)"
                                :input-value="props.all"
                                :indeterminate="props.indeterminate"
                            >
                            </v-checkbox>
                        </th> -->
                        <th
                            v-for="header in props.headers"
                            :key="header.text"
                            :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                            :style="{ minWidth: header.width + 'px' }"
                            @click="changeSort(header.value)"
                        >
                            {{ header.text }}
                            <v-icon small>arrow_drop_up</v-icon>
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr
                        :active="props.selected"
                        @click="goToWorkPage('/biz/' + props.item.step_url + '/' + props.item.request_work_id + '/' + props.item.id + '/create',$event)"
                        style="cursor:pointer;"
                    >
                        <template v-for="header in shownHeaders">
                            <td
                                v-if="'business_name' === header.value || 'step_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item[header.value] }}</span>
                                    <span>{{ props.item[header.value] }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'request_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <a class="text-underline">
                                    <v-tooltip top>
                                        <div
                                            slot="activator"
                                            @click.stop.exact="showReferenceView(requestDetailUri(props.item.request_id))"
                                            @click.stop.shift="showReferenceView(requestDetailUri(props.item.request_id), 'window')"
                                            @click.stop.ctrl="showReferenceView(requestDetailUri(props.item.request_id), 'tab')"
                                        >{{ requestCode + props.item[header.value] }}</div>
                                        <span>{{ $t('common.request_content.information.request_detail_confirm') }}<v-icon dark small>open_in_new</v-icon></span>
                                    </v-tooltip>
                                </a>
                            </td>
                            <td v-else-if="'step_id' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.step_description }}</span>
                                    <span>{{ props.item.step_description }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'created_at' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.created_at | formatDateYmdHm(true) }}
                            </td>
                            <td
                                v-else-if="'deadline' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.deadline | formatDateYmdHm }}
                            </td>
                            <td
                                v-else-if="'status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ displayStatus(props.item) }}
                            </td>
                            <td
                                v-else-if="'is_verified' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <template v-if="props.item.delivery_id !== null && isDoneTask(props.item.status) && isActiveTask(props.item.is_active)">
                                    <v-tooltip top>
                                        <div
                                            slot="activator"
                                            @click.stop="showVerificationPage(props.item.request_work_id, props.item.id)"
                                        >
                                            <work-status-chip :status="props.item.is_verified"></work-status-chip>
                                        </div>
                                        {{ $t('tasks.show_validation_screen') }}<v-icon dark small>reply</v-icon>
                                    </v-tooltip>
                                </template>
                                <template v-else>-</template>
                            </td>
                            <td
                                v-else-if="'is_display_educational' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <template>
                                    {{ props.item.is_education && props.item.is_display_educational ? $t('tasks.education') : $t('tasks.production') }}
                                </template>
                            </td>
                            <td
                                v-else-if="'client_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayName(props.item.client_name) }}</span>
                                    <span>{{ displayName(props.item.client_name) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'request_work_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.request_work_name }}</span>
                                    <span>{{ props.item.request_work_name }}</span>
                                </v-tooltip>
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
            <progress-circular v-if="loading"></progress-circular>
            <reference-view-dialog ref="referenceViewDialog" content-class="fill-height"></reference-view-dialog>
        </div>
    </div>
    <!-- / 一覧 -->
</template>

<script>
import Sortable from 'sortablejs'
import store from '../../../../stores/Work/Tasks/store.js'

import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import WorkStatusChip from '../../../Atoms/Chips/WorkStatusChip'
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    data: () => ({
        selected: [],
        tasks: [],
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
        loading: false,
        //表頭カスタムモーダルでのデフォルト値
        initialHeaders: {},
    }),
    components :{
        ProgressCircular,
        HeaderCustomModal,
        WorkStatusChip,
        ReferenceViewDialog,
    },
    created () {
        eventHub.$on('commitHeaderCustomData', function(data) {

            // 表頭カスタムデータのコミット処理
            // TODO 一度のコミット処理で行いたい
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })

            // localStorageへ表頭カスタムデータを格納
            // TODO keyの命名規約
            localStorage.setItem('taskHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialTask'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('taskHeaders', JSON.stringify(localHeadersdata));
            } catch (e) {
                console.log(e)
                localStorage.removeItem('taskHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        })

        let self = this;
        eventHub.$on('search', function({ searchParams }) {
            // ページ、ソート条件をリセット
            self.page = searchParams.page
            self.pagination.sortBy = searchParams.sort_by
            self.pagination.descending = searchParams.descending
            self.pagination.rowsPerPage = searchParams.rows_per_page
            // 作業情報を検索
            self.searchTaskListAsync(searchParams)
        })
        eventHub.$on('changeRowsPerPage', function(data) {
            self.rowsPerPage = data.rowsPerPage
        })

        //初期化用にstoreの初期値を保存しておく
        localStorage.setItem('initialTask', JSON.stringify(store.state));

        // localStorageより表頭カスタムデータを取得
        // TODO keyの命名規約
        if (localStorage.getItem('taskHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('taskHeaders'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                console.log(e)
                localStorage.removeItem('taskHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        }
    },
    computed: {
        searchParams () {
            return store.state.searchParams
        },
        shownHeaders() {
            return store.state.showHeaders
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
                this.searchTaskListAsync(this.searchParams)
            },
            get () {
                return this.pagination.rowsPerPage
            }
        },
        requestCode () {
            return _const.MASTER_ID_PREFIX.REQUEST_ID
        },
        requestDetailUri () {
            return function(id) {
                return '/management/requests/' + id
            }
        },
    },
    methods: {
        goToWorkPage (url,e) {
            let evt = window.event || e
            if (evt.target.classList.contains('btn__content')) return;
            window.location.href = url;
        },
        searchTaskListAsync (params) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/tasks', searchParams)
                .then((res) => {
                // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: params })
                    // 検索結果を画面に反映
                    this.tasks = res.data.tasks.data
                    this.paginate.data_count_total = res.data.tasks.total
                    this.paginate.page_count = res.data.tasks.last_page
                    this.paginate.page_from = res.data.tasks.from ? res.data.tasks.from : 0
                    this.paginate.page_to = res.data.tasks.to ? res.data.tasks.to : 0
                })
                .catch((err) => {
                    console.log(err)
                }).finally(() => {
                    this.loading = false
                })
        },
        changePage (page) {
            let params = this.searchParams
            params.page = page
            this.searchTaskListAsync(params)
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
            this.searchTaskListAsync(params)
        },
        displayName (name) {
            if (!name) return ''

            let displayName = name
            // 「b' <xxx@xxx>」の部分を削除する（TODO: バッチ不具合修正前の仮対応）
            displayName = displayName.replace(/b' <[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*>'$/, '')
            // 「 <xxx@xxx>」の部分を削除する
            displayName = displayName.replace(/ <[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*>$/, '')

            // 「"xxx@xxx"」であれば、
            // 先頭「"」と末尾「@xxx"」を削除する
            if (/^"[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*"$/.test(displayName)) {
                displayName = displayName.slice(1)
                displayName = displayName.replace(/@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*"$/, '')
            }

            return displayName
        },
        displayStatus(item) {
            // 無効なタスクの場合は一律完了として表示する
            if (item.is_active === _const.FLG.INACTIVE) {
                return Vue.i18n.translate('tasks.statuses.const2')
            }

            let name = ''
            switch (item.status) {
            case _const.TASK_STATUS.ON:
                name = Vue.i18n.translate('tasks.statuses.const1')
                break;
            case _const.TASK_STATUS.DONE:
                name = Vue.i18n.translate('tasks.statuses.const2')
                break;
            default:
                name = Vue.i18n.translate('tasks.statuses.const0')
                break;
            }
            return name
        },
        showVerificationPage(request_work_id, task_id) {
            window.location.href = `/verification/${request_work_id}/${task_id}/edit`  // TODO:検証画面に遷移？ポップアップ？
        },
        openHeaderCustomModal() {
            this.initialHeaders = JSON.parse(localStorage.getItem('initialTask'));
            eventHub.$emit('open-header-custom-modal')
        },
        isDoneTask(status) {
            return status === _const.TASK_STATUS.DONE
        },
        isActiveTask(isActive) {
            return isActive === _const.FLG.ACTIVE
        },
        showReferenceView (uri, type) {
            this.$refs.referenceViewDialog.show([uri], type)
        },
    },
    watch: {
        pagination: {
            // ヘッダークリックによるソード処理（サーバ）
            handler () {
                // ソート条件を更新
                let params = this.searchParams
                params.sort_by = this.pagination.sortBy
                params.descending = this.pagination.descending
                // タスク一覧を取得
                this.searchTaskListAsync(params)
            },
        }
    },
    mounted () {
        this.$nextTick(() => {
            const element = document.getElementById('task_sort_key')
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
}
</script>
<style>
/* v-chipに親要素のcursor指定を継承させる */
#search-list .v-chip__content {
    cursor: inherit;
}
</style>
