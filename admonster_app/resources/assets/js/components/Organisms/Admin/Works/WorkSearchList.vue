<template>
    <div id="search-list">
        <header-custom-modal
            :selectedRowsPerPage="pagination.rowsPerPage"
            :showHeaders="shownHeaders"
            :hiddenHeaders="hiddenHeaders"
            :initialHeaders="initialHeaders"
        ></header-custom-modal>
        <v-form
            :action="actionPath"
            method="post"
            @submit.prevent="formSubmit"
            ref="form"
        >
            <input type="hidden" name="_token" :value="csrf">
            <!-- toolbar -->
            <v-layout row wrap align-center>
                <div v-show="showButton">
                    <v-tooltip top>
                        <v-btn type="submit" flat icon color="primary" slot="activator">
                            <v-icon>group_add</v-icon>
                        </v-btn>
                        <span>{{ $t('allocations.multi_allocate') }}</span>
                    </v-tooltip>
                </div>
                <div v-show="selectedRequestWorkIds.length > 0" class="px-3">
                    <span v-if="selectedRequestWorkIds.length === allRequestWorkIds.length">
                            <span>{{ $t('list.selecting_all_search_result', {'count': paginate.data_count_total}) }}</span>
                            <span class="pr-2">／</span>
                            <a @click="cancelSelectAll()">
                                <span>{{ $t('list.deselection') }}</span>
                            </a>
                        </span>
                        <span v-else>
                            <span>{{ $t('list.selecting_count', {'count': selectedRequestWorkIds.length}) }}</span>
                            <span class="pr-2">／</span>
                            <a @click="selectAll()">
                                <span>{{ $t('list.select_all_search_result', {'count': paginate.data_count_total}) }}</span>
                            </a>
                        </span>
                </div>
                <v-spacer></v-spacer>
                <v-tooltip top>
                    <v-btn flat icon color="primary" slot="activator" @click="openHeaderCustomModal()">
                        <v-icon>settings</v-icon>
                    </v-btn>
                    <span>{{ $t('list.config') }}</span>
                </v-tooltip>
            </v-layout>
            <!-- toolbar -->
            <input type="hidden" name="request_work_ids" :value="selectedRequestWorkIds">
        </v-form>

        <div class="data-content">
            <!--table-->
            <v-data-table
                v-model="selected"
                :headers="shownHeaders"
                :items="items"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                item-key="request_work_id"
                hide-actions
                must-sort
                select-all
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="work_sort_key">

                        <th id="ignore-elements">
                            <v-checkbox
                            primary
                            hide-details
                            @click.native="toggleAll(props)"
                            :input-value="props.all"
                            :indeterminate="props.indeterminate"
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
                        @click="goToWorkPage(props.item.request_work_id)"
                        @mouseleave="previewIconHide(props.item.request_work_id, props.item.user_id)"
                        @mouseenter="previewIconShow(props.item.request_work_id, props.item.user_id)"
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

                        <template v-for="(header,i) in shownHeaders">
                            <td
                                v-if="'user_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <div class="d-inline-block" @click.stop>
                                    <v-menu right offset-y nudge-top="15">
                                        <template slot="activator">
                                            <v-tooltip top>
                                                <v-avatar slot="activator" size="32px" class="ma-1">
                                                    <img :src="props.item.user_image_path">
                                                </v-avatar>
                                                <span>{{ props.item.user_name }}</span>
                                            </v-tooltip>
                                        </template>
                                        <worker-link-list :user-id="props.item.user_id" :request-work-id="props.item.request_work_id"></worker-link-list>
                                    </v-menu>
                                </div>
                            </td>
                            <td
                                v-else-if="'user_id_no_image' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <div class="d-inline-block" @click.stop>
                                    <v-menu right offset-y nudge-top="15">
                                        <template slot="activator">
                                            <span>{{ props.item.user_name }}</span>
                                        </template>
                                        <worker-link-list :user-id="props.item.user_id" :request-work-id="props.item.request_work_id"></worker-link-list>
                                    </v-menu>
                                </div>
                            </td>
                            <td
                                v-else-if="'client_name' === header.value" :key="i"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayClientName(props.item.client_name) }}</span>
                                    <span>{{ displayClientName(props.item.client_name) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-badge left overlap color="red">
                                    <v-icon slot="badge" v-if="isTaskContact(props.item)" dark>priority_high</v-icon>
                                    <v-chip small outline label disabled :color="displayStatusColor(props.item)" class="cursor-pointer">
                                        {{ displayStatusName(props.item) }}
                                    </v-chip>
                                </v-badge>
                            </td>
                            <td
                                v-else-if="'request_work_name' === header.value || 'subject' === header.value"
                                :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item[header.value] }}</span>
                                    <span>{{ props.item[header.value] }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'request_file_name' === header.value" :key="header.text"
                                class="text-xs-left overflow search-title"
                            >
                                <span class="search-title-content">
                                    <div
                                        style="display: none;"
                                        :class="['mr-1', 'request-file-preview', 'request-file-preview' + props.item.request_work_id  + props.item.user_id]"
                                    >
                                        <v-tooltip top>
                                            <span
                                                v-if="props.item.request_file_name !== null"
                                                slot="activator"
                                            >
                                                <v-icon
                                                    small
                                                    @click.stop.exact="filePreview(props.item)"
                                                    @click.stop.shift="filePreview(props.item, 'window')"
                                                    @click.stop.ctrl="filePreview(props.item, 'tab')"
                                                >
                                                    mdi-file-eye
                                                </v-icon>
                                            </span>
                                            <span>{{ $t('list.tooltip.preview') }}</span>
                                        </v-tooltip>
                                    </div>
                                    <div class="search-title-text" >
                                        <v-tooltip top>
                                            <span slot="activator">{{ props.item[header.value] }}</span>
                                            <span>{{ props.item[header.value] }}</span>
                                        </v-tooltip>
                                    </div>
                                </span>
                            </td>
                            <td
                                v-else-if="'created_at' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.created_at | formatDateYmdHm(true) }}
                            </td>
                            <td
                                v-else-if="'task_created_at' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.task_created_at | formatDateYmdHm(true) }}
                            </td>
                            <td
                                v-else-if="'company_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.company_name }}</span>
                                    <span>{{ props.item.company_name }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'business_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.business_name }}</span>
                                    <span>{{ props.item.business_name }}</span>
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
                            <td
                                v-else-if="'request_work_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ requestWorkCode(props.item) }}
                            </td>
                            <td
                                v-else-if="'deadline' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-tooltip top :disabled="!canChangeDeliveryDeadline(props.item)">
                                    <span
                                        slot="activator"
                                        class="cursor-pointer"
                                        @click.stop="openChangeDeliveryDeadlineDialog(props.item)"
                                    >
                                        {{ props.item.deadline | formatDateYmdHm }}
                                    </span>
                                    {{ $t('list.tooltip.change_delivery_deadline') }}<v-icon dark small>open_in_new</v-icon>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'step_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.step_name }}</span>
                                    <span>{{ props.item.step_name }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'task_message' === header.value" :key="header.text"
                                class="text-xs-left  overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.task_message }}</span>
                                    <span>{{ props.item.task_message }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'task_time_taken' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ displayTaskTimeTaken(props.item.started_at, props.item.finished_at) }}
                            </td>
                            <td
                                v-else-if="'judgment_result' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item.judgment_result }}
                            </td>
                            <td
                                v-else-if="'approval_result' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <!-- TODO: テンプレート作成 -->
                                <template v-if="props.item.approval_result === 0">
                                    <v-chip small disabled text-color="white" color="primary" class="cursor-pointer">
                                        {{ $t('works.list.approval_result.ok') }}
                                    </v-chip>
                                </template>
                                <template v-else-if="props.item.approval_result === 1">
                                    <v-chip small disabled text-color="white" color="red" class="cursor-pointer">
                                        {{ $t('works.list.approval_result.ng') }}
                                    </v-chip>
                                </template>
                                <template v-else>
                                    <v-chip small disabled outline color="grey" class="cursor-pointer">
                                        {{ $t('works.list.approval_result.none') }}
                                    </v-chip>
                                </template>
                            </td>
                            <td
                                v-else-if="'approval_status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-chip small outline label disabled :color="displayApprovalStatusColor(props.item.approval_status)" class="cursor-pointer">
                                    {{ displayApprovalStatusName(props.item.approval_status) }}
                                </v-chip>
                            </td>
                            <td
                                v-if="'request_mail_from' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayName(props.item.request_mail_from) }}</span>
                                    <span>{{ displayName(props.item.request_mail_from) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'request_mail_to' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayNames(props.item.request_mail_to) }}</span>
                                    <span>{{ displayNames(props.item.request_mail_to) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'mail_attachments_count' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <div
                                    v-if="props.item.mail_attachments_count > 0"
                                    class="d-inline-block"
                                    @click.stop="showAttachmentList(props.item.request_mail_id)"
                                >
                                    <v-tooltip top>
                                        <v-badge
                                            slot="activator"
                                            color="green"
                                            right
                                            overlap
                                        >
                                            <template slot="badge">
                                                <span>{{ props.item.mail_attachments_count }}</span>
                                            </template>
                                            <v-icon size="32px">attachment</v-icon>
                                        </v-badge>
                                        {{ $t('list.tooltip.attachment_details_confirm') }}<v-icon dark small>open_in_new</v-icon>
                                    </v-tooltip>
                                </div>
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
            <alert-dialog ref="alert"></alert-dialog>
            <progress-circular v-if="loading || filePreviewLoading"></progress-circular>
            <request-mail-attachments-modal ref="attachmentListModal" :attachments.sync="attachments"></request-mail-attachments-modal>
            <change-delivery-deadline-dialog
                ref="changeDeliveryDeadlineDialog"
                :apiPath="requestWorksApiPath"
                @reload="searchRequestListAsync"
            ></change-delivery-deadline-dialog>
            <notify-modal></notify-modal>
            <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
            <reference-view-dialog ref="referenceViewDialog" content-class="fill-height"></reference-view-dialog>
        </div>
    </div>
</template>

<script>
import Sortable from 'sortablejs';
import store from '../../../../stores/Admin/Works/store'
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import RequestMailAttachmentsModal from '../../../Molecules/Requests/RequestMailAttachmentsModal'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import FilePreviewDialog from '../../../Atoms/Dialogs/FilePreviewDialog'
import WorkerLinkList from '../../../Molecules/Admin/Works/WorkerLinkList'
import ChangeDeliveryDeadlineDialog from '../../../Atoms/Dialogs/ChangeDeliveryDeadlineDialog'
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    data: () => ({
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        selectedRequestWorkIds: [],
        allRequestWorkIds: [],
        selected: [],
        items: [],

        page: store.state.searchParams.page,
        attachments: [],
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
        loading: false,
        filePreviewLoading: false,
    }),
    components:{
        AlertDialog,
        ProgressCircular,
        HeaderCustomModal,
        NotifyModal,
        RequestMailAttachmentsModal,
        WorkerLinkList,
        ChangeDeliveryDeadlineDialog,
        FilePreviewDialog,
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
            localStorage.setItem('workHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialWork'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('workHeaders', JSON.stringify(localHeadersdata));
            } catch (e) {
                localStorage.removeItem('workHeaders');
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
        localStorage.setItem('initialWork', JSON.stringify(store.state));

        // localStorageより表頭カスタムデータを取得
        // TODO keyの命名規約
        if (localStorage.getItem('workHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('workHeaders'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                localStorage.removeItem('workHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        }
    },
    computed: {
        requestWorksApiPath () {
            return '/api/works'
        },
        searchParams () {
            return store.state.searchParams
        },
        shownHeaders() {
            return store.state.showHeaders;
        },
        hiddenHeaders() {
            return store.state.hiddenHeaders
        },
        // 一括操作ボタンの表示制御
        showButton () {
            return this.selected.length
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
        actionPath () {
            return '/multiple_allocations/create'
        },
        requestCode () {
            return _const.MASTER_ID_PREFIX.REQUEST_ID
        },
        requestWorkCode () {
            return function(item) {
                const prefix = _const.MASTER_ID_PREFIX
                return this.requestCode + item.request_id + prefix.SEPARATOR + prefix.REQUEST_WORKS_ID + item.request_work_id
            }
        },
        requestDetailUri () {
            return function(id) {
                return '/management/requests/' + id
            }
        },
        isTaskContact () {
            return function(item) {
                return item.task_is_defective == _const.FLG.ACTIVE
            }
        },
    },
    methods: {
        canChangeDeliveryDeadline (item) {
            return item['request_status'] === _const.REQUEST_STATUS.DOING && item['delivery_status'] !== _const.DELIVERY_STATUS.DONE
        },
        openChangeDeliveryDeadlineDialog (item) {
            if (!this.canChangeDeliveryDeadline(item)) return
            this.$refs.changeDeliveryDeadlineDialog.show(item.deadline, item.request_work_id)
        },
        formSubmit () {
            this.loading = true
            const formData = new FormData()
            formData.append('request_work_ids',this.selectedRequestWorkIds)
            axios.post('/api/allocations/canMultipleAllocations', formData)
                .then((res) =>{

                    if (res.data.status !== 200) {
                        if (res.data.status === 412) {
                            throw Object.assign(new Error(), {name: res.data.errors, statusCode: res.data.status})
                        } else {
                            throw Object.assign(new Error(res.data.message), {name: res.data.error, statusCode: res.data.status})
                        }
                    } else {
                        this.$refs.form.$el.submit()
                    }
                }).catch((error) => {
                    console.log(error)
                    if (error.statusCode === 412) {
                        const messages = []
                        if (error.name.is_approval_status_error) messages.push(this.$t('allocations.list.notice.unallocated_status'))
                        if (error.name.is_step_id_error) messages.push(this.$t('allocations.list.notice.allocating_to_multiple_steps'))
                        this.$refs.alert.show(messages.join('<br />'), ()=>{})
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                }).finally(() => {
                    this.loading = false
                })
        },
        previewIconShow (requestWorkId, userId) {
            this.$nextTick(function () {
                $('.request-file-preview' + requestWorkId + userId).show()
            })
        },
        previewIconHide (requestWorkId, userId) {
            this.$nextTick(function () {
                $('.request-file-preview' + requestWorkId + userId).hide()
            })
        },
        showAttachmentList (requestMailId) {
            this.attachments = []
            this.$refs.attachmentListModal.show(requestMailId)
        },
        goToWorkPage (requestWorkId) {
            window.location.href = this.requestWorkDetailUri(requestWorkId)
        },
        taskUrl (info) {
            let uri = '/' + ['biz', info.step_url, info.request_work_id, info.task_id, 'create'].join('/')
            return uri
        },
        isTaskStatusDone (item) {
            // 無効なタスクの場合は一律完了とみなして表示する
            if (item.task_is_active === _const.FLG.INACTIVE) {
                return true
            }
            return item.status === _const.TASK_STATUS.DONE
        },
        requestWorkDetailUri (request_work_id) {
            return '/management/request_works/' + request_work_id
        },
        searchRequestListAsync (params = this.searchParams) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/works', searchParams)
                .then((res) =>{
                // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: params })
                    // 検索結果を画面に反映
                    this.items = res.data.list.data
                    this.selectedRequestWorkIds = []
                    this.selected = []
                    this.paginate.data_count_total = res.data.list.total
                    this.paginate.page_count = res.data.list.last_page
                    this.allRequestWorkIds = res.data.all_request_work_ids
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
            this.searchRequestListAsync(params)
        },
        displayClientName (client_name) {
            if (!client_name) return ''

            let displayClientName = client_name
            // 「b' <xxx@xxx>」の部分を削除する（TODO: バッチ不具合修正前の仮対応）
            displayClientName = displayClientName.replace(/b' <[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*>'$/, '')
            // 「 <xxx@xxx>」の部分を削除する
            displayClientName = displayClientName.replace(/ <[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*>$/, '')

            // 「"xxx@xxx"」であれば、
            // 先頭「"」と末尾「@xxx"」を削除する
            if (/^"[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*"$/.test(displayClientName)) {
                displayClientName = displayClientName.slice(1)
                displayClientName = displayClientName.replace(/@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*"$/, '')
            }

            return displayClientName
        },
        displayTaskTimeTaken (started_at, finished_at) {

            if (finished_at === null){
                return ''
            }

            let ms = moment(finished_at) - moment(started_at)

            var h = String(Math.floor(ms / 3600000) + 100).substring(1);
            var m = String(Math.floor((ms - h * 3600000)/60000)+ 100).substring(1);
            var s = String(Math.round((ms - h * 3600000 - m * 60000)/1000)+ 100).substring(1);

            // 1分未満は切り上げ
            if (s !== '00'){
                m = String(Number(m) + 101).substring(1);
            }

            return h+':'+m;

        },
        displayStatusName(item) {
            // 無効なタスクの場合は一律完了として表示する
            if (item.task_is_active === _const.FLG.INACTIVE) {
                return Vue.i18n.translate('works.list.task_status.done')
            }

            let name = ''
            switch (item.status) {
            case _const.TASK_STATUS.ON:
                name = Vue.i18n.translate('works.list.task_status.on')
                break;
            case _const.TASK_STATUS.DONE:
                name = Vue.i18n.translate('works.list.task_status.done')
                break;
            default:
                name = Vue.i18n.translate('works.list.task_status.none')
                break;
            }
            return name
        },
        displayStatusColor(item) {
            // 無効なタスクの場合は一律完了として表示する
            if (item.task_is_active === _const.FLG.INACTIVE) {
                return 'secondary'
            }

            let color = ''
            switch (item.status) {
            case _const.TASK_STATUS.ON:
                color = 'blue'
                break;
            case _const.TASK_STATUS.DONE:
                color = 'secondary'
                break;
            default:
                color = 'red'
                break;
            }
            return color
        },
        displayApprovalStatusName(approval_status) {
            let name = ''
            switch (approval_status) {
            case _const.APPROVAL_STATUS.ON:
                name = Vue.i18n.translate('works.list.approval_status.on')
                break;
            case _const.APPROVAL_STATUS.DONE:
                name = Vue.i18n.translate('works.list.approval_status.done')
                break;
            default:
                name = Vue.i18n.translate('works.list.approval_status.none')
                break;
            }
            return name
        },
        displayApprovalStatusColor(approval_status) {
            let color = ''
            switch (approval_status) {
            case _const.APPROVAL_STATUS.ON:
                color = 'blue'
                break;
            case _const.APPROVAL_STATUS.DONE:
                color = 'secondary'
                break;
            default:
                color = 'red'
                break;
            }
            return color
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
        openHeaderCustomModal() {
            this.initialHeaders = JSON.parse(localStorage.getItem('initialWork'));
            eventHub.$emit('open-header-custom-modal')
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
        displayNames (names) {
            if (!names) return ''

            let nameArray = []
            let displayNameArray = []

            nameArray = names.split(',');

            for (let i = 0; i < nameArray.length; i++) {
                displayNameArray.push(this.displayName(nameArray[i]))
            }

            return displayNameArray.join(',');
        },
        toggleAll () {
            if (this.selected.length == this.items.length) {
                this.selected = []
                this.selectedRequestWorkIds = []
            }
            else {
                this.selected = this.items.slice()
                this.selectedRequestWorkIds = this.items.map(item => item.request_work_id).slice()
            }
        },
        selectedItem (item) {
            const requestWorkId = item.request_work_id
            if (!this.selectedRequestWorkIds.includes(requestWorkId)) {
                this.selectedRequestWorkIds.push(requestWorkId)
                this.selected.push(item)
            }
            else {
                this.selectedRequestWorkIds = this.selectedRequestWorkIds.filter(id => id !== requestWorkId)
                this.selected = this.selected.filter(item => item.request_work_id !== requestWorkId)
            }
        },
        selectAll () {
            this.selected = this.items.slice()
            this.selectedRequestWorkIds = this.allRequestWorkIds
        },
        cancelSelectAll () {
            this.selected = []
            this.selectedRequestWorkIds = []
        },
        filePreview: async function (item, type = '') {
            const file = {
                name: item.request_file_name,
                file_path: item.request_file_path,
            }
            this.$refs.filePreviewDialog.show([file], [], type)
        },
        showReferenceView (uri, type) {
            this.$refs.referenceViewDialog.show([uri], type)
        },
    },
    mounted () {
        this.$nextTick(() => {
            const element = document.getElementById('work_sort_key')
            const _self = this
            Sortable.create(element, {
                filter: '#ignore-elements',
                onMove(e) {
                    return e.related.id !== 'ignore-elements';
                },
                onEnd({ newIndex, oldIndex }) {
                    let headers = _self.shownHeaders
                    const headerSelected = headers.splice(oldIndex-1, 1)[0]
                    headers.splice(newIndex-1, 0, headerSelected)
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
#search-list >>> .cursor-pointer > span {
    cursor: pointer;
}
.search-title-content {
    display: flex;
}
.search-title-text {
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
