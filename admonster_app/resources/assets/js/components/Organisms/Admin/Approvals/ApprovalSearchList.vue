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
                :items="items"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                hide-actions
                must-sort
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="approval_sort_key">
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
                        @click="goToApprovalPage(props.item.request_work_id)"
                        @mouseleave="previewIconHide(props.item.request_work_id)"
                        @mouseenter="previewIconShow(props.item.request_work_id)"
                        style="cursor:pointer;"
                    >
                        <template v-for="header in shownHeaders">
                            <td
                                v-if="'client_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayClientName(props.item.client_name) }}</span>
                                    <span>{{ displayClientName(props.item.client_name) }}</span>
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
                            <td
                                v-else-if="'approver_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <div v-if="user_image_path(props.item.approver_id) == undefined">
                                    <v-avatar size="32px" class="ma-1">
                                        <img src="/images/question.svg">
                                    </v-avatar>
                                </div>
                                <v-tooltip top v-else>
                                    <v-avatar slot="activator" size="32px" class="ma-1">
                                        <img :src="user_image_path(props.item.approver_id)">
                                    </v-avatar>
                                    <span>{{ user_name(props.item.approver_id) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'approver_id_no_image' === header.value" :key="header.text"
                                class="text-xs-left"
                                style="max-width: 0px;"
                            >
                                <users-name-display
                                    :userIds="props.item.approver_id ? [props.item.approver_id] : []"
                                    :usersInfo="candidates"
                                    :width="Number(header.width)"
                                ></users-name-display>
                            </td>
                            <td
                                v-else-if="'request_file_name' === header.value" :key="header.text"
                                class="text-xs-left overflow search-title"
                            >
                                <span class="search-title-content">
                                    <div
                                        style="display: none;"
                                        :class="['mr-1', 'request-file-preview', 'request-file-preview' + props.item.request_work_id]"
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
                                v-else-if="'approval_status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-chip small outline label disabled :color="displayStatusColor(props.item.approval_status)" class="cursor-pointer">
                                    {{ displayStatusName(props.item.approval_status) }}
                                </v-chip>
                            </td>
                            <td
                                v-else-if="'created_at' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.created_at | formatDateYmdHm(true) }}
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
                                        >{{ requestCode + props.item.request_id }}</div>
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
                                v-else-if="'worker_ids' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-tooltip top>
                                    <div
                                        slot="activator"
                                        class="d-inline-block cursor-pointer"
                                        @click.stop="open(props.item)"
                                    >
                                        <users-overview
                                            :users="workerUsers(props.item)"
                                            :candidates="candidates"
                                            :max-views="2"
                                            hide-tooltip
                                        ></users-overview>
                                    </div>
                                    {{ $t('approvals.list.worker_detail_confirm') }}<v-icon dark small>open_in_new</v-icon>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'worker_ids_no_image' === header.value" :key="header.text"
                                class="text-xs-left"
                                style="max-width: 0px;"
                            >
                                <v-tooltip top>
                                    <div
                                        slot="activator"
                                        class="d-inline-block cursor-pointer"
                                        @click.stop="open(props.item)"
                                    >
                                        <users-name-display
                                            :userIds="props.item.worker_ids ? props.item.worker_ids.split(',') : []"
                                            :usersInfo="candidates"
                                            :width="Number(header.width)"
                                        ></users-name-display>
                                    </div>
                                    {{ $t('allocations.list.worker_detail_confirm') }}<v-icon dark small>open_in_new</v-icon>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'reject_reason' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item.reject_reason }}
                            </td>
                            <td
                                v-else-if="'abort_reason' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item.abort_reason }}
                            </td>
                            <td
                                v-else-if="'is_auto' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayApprovalConditions(props.item.is_auto, props.item.approval_conditions) }}</span>
                                    <span>{{ displayApprovalConditions(props.item.is_auto, props.item.approval_conditions) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'approval_time' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                <span v-if="props.item.approval_time">
                                    {{ props.item.approval_time | formatDateYmdHm(true)  }}
                                </span>
                            </td>
                            <td
                                v-else-if="'subject' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item.subject }}</span>
                                    <span>{{ props.item.subject }}</span>
                                </v-tooltip>
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
            <progress-circular v-if="loading || filePreviewLoading"></progress-circular>
            <work-list-modal ref="wokerListModal"></work-list-modal>
            <request-mail-attachments-modal ref="attachmentListModal" :attachments.sync="attachments"></request-mail-attachments-modal>
            <change-delivery-deadline-dialog
                ref="changeDeliveryDeadlineDialog"
                :apiPath="approvalsApiPath"
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
import store from '../../../../stores/Admin/Approvals/store'
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import UsersNameDisplay from '../../../Molecules/Users/UsersNameDisplay'
import WorkListModal from '../../../Atoms/Modal/WorkListModal'
import RequestMailAttachmentsModal from '../../../Molecules/Requests/RequestMailAttachmentsModal'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import FilePreviewDialog from '../../../Atoms/Dialogs/FilePreviewDialog'
import ChangeDeliveryDeadlineDialog from '../../../Atoms/Dialogs/ChangeDeliveryDeadlineDialog'
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    data: () => ({
        selected: [],
        items: [],
        attachments: [],

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
        loading: false,
        filePreviewLoading: false,
    }),
    components:{
        ProgressCircular,
        HeaderCustomModal,
        WorkListModal,
        UsersOverview,
        UsersNameDisplay,
        NotifyModal,
        RequestMailAttachmentsModal,
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
            localStorage.setItem('approvalHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialApproval'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('approvalHeaders', JSON.stringify(localHeadersdata));
            } catch (e) {
                console.log(e)
                localStorage.removeItem('approvalHeaders');
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
        localStorage.setItem('initialApproval', JSON.stringify(store.state));

        // localStorageより表頭カスタムデータを取得
        // TODO keyの命名規約
        if (localStorage.getItem('approvalHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('approvalHeaders'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                console.log(e)
                localStorage.removeItem('approvalHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        }
    },
    computed: {
        approvalsApiPath () {
            return '/api/approvals'
        },
        searchParams () {
            return store.state.searchParams
        },
        shownHeaders() {
            return store.state.showHeaders
        },
        hiddenHeaders() {
            return store.state.hiddenHeaders
        },
        approvalDetailUri() {
            return function(id) {
                let uri = '/approvals/'+ id +'/edit'
                return uri
            }
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
    },
    methods: {
        canChangeDeliveryDeadline (item) {
            return item['request_status'] === _const.REQUEST_STATUS.DOING && item['delivery_status'] !== _const.DELIVERY_STATUS.DONE
        },
        openChangeDeliveryDeadlineDialog (item) {
            if (!this.canChangeDeliveryDeadline(item)) return
            this.$refs.changeDeliveryDeadlineDialog.show(item.deadline, item.request_work_id)
        },
        previewIconShow (requestWorkId) {
            this.$nextTick(function () {
                $('.request-file-preview' + requestWorkId).show()
            })
        },
        previewIconHide (requestWorkId) {
            this.$nextTick(function () {
                $('.request-file-preview' + requestWorkId).hide()
            })
        },
        showAttachmentList (requestMailId) {
            this.attachments = []
            this.$refs.attachmentListModal.show(requestMailId)
        },
        goToApprovalPage (requestWorkId) {
            window.location.href = this.approvalDetailUri(requestWorkId)
        },
        open (item) {
            this.$refs.wokerListModal.show(item.request_work_id)
        },
        searchRequestListAsync (params = this.searchParams) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/approvals/index', searchParams)
                .then((res) =>{
                // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: params })
                    // 検索結果を画面に反映
                    this.items = res.data.list.data
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
        displayStatusName(approval_status) {
            let name = ''
            switch (approval_status) {
            case _const.APPROVAL_STATUS.ON:
                name = Vue.i18n.translate('approvals.list.approval_status.on')
                break;
            case _const.APPROVAL_STATUS.DONE:
                name = Vue.i18n.translate('approvals.list.approval_status.done')
                break;
            default:
                name = Vue.i18n.translate('approvals.list.approval_status.none')
                break;
            }
            return name
        },
        displayStatusColor(approval_status) {
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
        openHeaderCustomModal() {
            //TODO 初期変数の格納タイミング
            this.initialHeaders = JSON.parse(localStorage.getItem('initialApproval'));
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
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
        workerUsers (item) {
            const completedUserIds = (item) => {
                const doneTask = _const.TASK_STATUS.DONE
                const task_status = item.task_status ? item.task_status.split(',') : [item.worker_ids.split(',').length]
                return item.worker_ids.split(',').filter( (worker_id, index) => {
                    return task_status[index] == doneTask
                })
            }
            const a = {
                allocated_user_ids: item.worker_ids ? item.worker_ids.split(',') : [],
                completed_user_ids: item.worker_ids ? completedUserIds(item) : [],
            }
            return a
        },
        displayApprovalConditions(isAuto, approvalConditions) {
            let conditions = ''

            if (!isAuto){
                return conditions = Vue.i18n.translate('approvals.list.is_auto.false')
            }

            conditions = Vue.i18n.translate('approvals.list.is_auto.true')+'('

            switch (approvalConditions) {
            case _const.APPROVAL_CONDITION.FULL_MATCH:
                conditions = conditions + Vue.i18n.translate('approvals.list.conditions.full_match')+')'
                break;
            }

            return conditions
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
            const element = document.getElementById('approval_sort_key')
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
