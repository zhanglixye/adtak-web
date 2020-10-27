<template>
    <div id="allocation-search-list">
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
            <v-layout row wrap align-center style="padding-bottom: 8px;">
                <div v-show="showButton">
                    <v-tooltip top>
                        <v-btn type="submit" flat icon color="primary" slot="activator">
                            <v-icon>group_add</v-icon>
                        </v-btn>
                        <span>{{ $t('education_allocations.multi_allocate') }}</span>
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
                <div :style="{'color': 'grey', 'padding-right': '6px'}">{{ $t('education_allocations.list.list_display_contents') }}</div>
                <v-btn color="primary" dark @click="changeNomalMode()">{{ $t('education_allocations.list.switch_to_normal_mode') }}</v-btn>
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
                    <tr id="allocation_sort_key">
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
                            {{ header.text }}
                            <v-icon small>arrow_drop_up</v-icon>
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr
                        :active="props.selected"
                        @click="goToAllocationPage(props.item.request_work_id)"
                        @mouseleave="previewIconHide(props.item.request_work_id)"
                        @mouseenter="previewIconShow(props.item.request_work_id)"
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
                                v-if="'client_name' === header.value" :key="header.text"
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
                                    {{ $t('education_allocations.list.worker_detail_confirm') }}<v-icon dark small>open_in_new</v-icon>
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
                                v-else-if="'allocator_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <users-overview
                                    :users="allocatorUsers(props.item)"
                                    :candidates="candidates"
                                    ></users-overview>
                            </td>
                            <td
                                v-else-if="'allocator_id_no_image' === header.value" :key="header.text"
                                class="text-xs-left"
                                style="max-width: 0px;"
                            >
                                <users-name-display
                                    :userIds="props.item.allocator_id ? [props.item.allocator_id] : []"
                                    :usersInfo="candidates"
                                    :width="Number(header.width)"
                                ></users-name-display>
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
                                {{  props.item.deadline | formatDateYmdHm }}
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
                                v-else-if="'parallel' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item.parallel }}
                            </td>
                            <td
                                v-else-if="'is_auto' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayAllocationMethod(props.item.is_auto, props.item.allocation_method) }}</span>
                                    <span>{{ displayAllocationMethod(props.item.is_auto, props.item.allocation_method) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'allocated_at' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                <span v-if="props.item.allocated_at">
                                    {{ props.item.allocated_at | formatDateYmdHm(true) }}
                                </span>
                            </td>
                            <td
                                v-else-if="'task_status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-chip small outline label disabled :color="displayStatusColor(props.item.task_status)" class="cursor-pointer">
                                    {{ displayStatusName(props.item.task_status) }}
                                </v-chip>
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
            <alert-dialog ref="alert"></alert-dialog>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <work-list-modal ref="wokerListModal"></work-list-modal>
            <request-mail-attachments-modal ref="attachmentListModal" :attachments.sync="attachments"></request-mail-attachments-modal>
            <notify-modal></notify-modal>
            <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
            <reference-view-dialog ref="referenceViewDialog" content-class="fill-height"></reference-view-dialog>
        </div>
    </div>
</template>

<script>
import Sortable from 'sortablejs';
import store from '../../../../stores/Admin/education/Allocations/store'
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import WorkListModal from '../../../Atoms/Modal/WorkListModal'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import UsersNameDisplay from '../../../Molecules/Users/UsersNameDisplay'
import RequestMailAttachmentsModal from '../../../Molecules/Requests/RequestMailAttachmentsModal'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import FilePreviewDialog from '../../../Atoms/Dialogs/FilePreviewDialog'
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    components:{
        ProgressCircular,
        HeaderCustomModal,
        UsersOverview,
        WorkListModal,
        NotifyModal,
        UsersNameDisplay,
        RequestMailAttachmentsModal,
        AlertDialog,
        ConfirmDialog,
        FilePreviewDialog,
        ReferenceViewDialog,
    },
    data: () => ({
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        selectedRequestWorkIds: [],
        allRequestWorkIds: [],
        selected: [],
        items: [],
        candidates: [],
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
        eachAllocationDetailUri() {
            return function(id) {
                let uri = 'allocations/'+ id +'/edit'
                return uri
            }
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
            return '/education/allocations/create'
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
    },
    created () {
        eventHub.$on('commitHeaderCustomData', function(data) {

            // 表頭カスタムデータのコミット処理
            // TODO 一度のコミット処理で行いたい
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })

            // localStorageへ表頭カスタムデータを格納
            // TODO keyの命名規約
            localStorage.setItem('educationAllocationHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialAllocation'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('educationAllocationHeaders', JSON.stringify(localHeadersdata));
            } catch (e) {
                console.log(e)
                localStorage.removeItem('educationAllocationHeaders');
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
        localStorage.setItem('initialAllocation', JSON.stringify(store.state));

        // localStorageより表頭カスタムデータを取得
        // TODO keyの命名規約
        if (localStorage.getItem('educationAllocationHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('educationAllocationHeaders'));

                let localShowHeaders = localHeadersdata.showHeaders;
                let localHiddenHeaders = localHeadersdata.hiddenHeaders;

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                console.log(e)
                localStorage.removeItem('educationAllocationHeaders');
                eventHub.$emit('resetHeaderCustomData');
            }
        }
    },
    mounted () {
        this.initMessageDialog()

        this.$nextTick(() => {
            const element = document.getElementById('allocation_sort_key')
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
    methods: {
        formSubmit () {
            this.loading = true
            const formData = new FormData()
            formData.append('request_work_ids',this.selectedRequestWorkIds)
            axios.post('/api/education/allocations/canMultipleAllocations', formData)
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
                        this.$refs.alert.show(this.$t('allocations.list.notice.allocating_to_multiple_steps'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                }).finally(() => {
                    this.loading = false
                })
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
        goToAllocationPage (requestWorkId) {
            window.location.href = this.eachAllocationDetailUri(requestWorkId)
        },
        open (item) {
            this.$refs.wokerListModal.show(item.request_work_id, {
                'is_education': true
            })
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
        allocatorUsers (item) {
            return {
                allocated_user_ids: item.allocator_id ? [item.allocator_id] : [],
                completed_user_ids: item.user_id ? [] : [],
            }
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
        displayStatusName(status) {
            let name = ''
            if (status === null){
                // 未割振
                name = Vue.i18n.translate('education_allocations.list.status.none')
            } else {
                // 割振済
                name = Vue.i18n.translate('education_allocations.list.status.done')
            }
            return name
        },
        displayStatusColor(status) {
            let color = ''

            if (status === null){
                // 未割振
                color = 'red'
            } else {
                // 割振済
                color = 'secondary'
            }
            return color
        },
        displayAllocationMethod(isAuto, allocationMethod) {
            let method = ''

            if (!isAuto){
                return method = Vue.i18n.translate('education_allocations.list.is_auto.false')
            }

            method = Vue.i18n.translate('education_allocations.list.is_auto.true')+'('

            switch (allocationMethod) {
            case _const.ALLOCATION_METHOD.EQUALITY:
                method = method + Vue.i18n.translate('education_allocations.list.method.equality')+')'
                break;
            case _const.ALLOCATION_METHOD.BUSINESS_VOLUME:
                method = method + Vue.i18n.translate('education_allocations.list.method.business_volume')+')'
                break;
            case _const.ALLOCATION_METHOD.LEARNING_LEVEL:
                method = method + Vue.i18n.translate('education_allocations.list.method.learning_level')+')'
                break;
            }
            return method
        },
        searchRequestListAsync (params) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/education/allocations/index', searchParams)
                .then((res) =>{
                // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: params })
                    // 検索結果を画面に反映
                    this.items = res.data.list.data
                    this.selectedRequestWorkIds = []
                    this.selected = []
                    this.candidates = res.data.candidates

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
        openHeaderCustomModal() {
            //TODO 初期変数の格納タイミング
            this.initialHeaders = JSON.parse(localStorage.getItem('initialAllocation'));
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
        async initMessageDialog () {
            // 教育割振りモード ⇒ 通常モードへの切替え時
            const message = '<div>'+ this.$t('education_allocations.list.requests_already_delivered') +'</div><div>' + this.$t('education_allocations.list.assignment_screen_as_educational') + '</div>'
            if (await this.$refs.alert.show(message)) {
                // OKボタンクリック
            }
        },
        async changeNomalMode () {
            // 教育割振りモード ⇒ 通常モードへの切替え時
            const message = '<h4>' + this.$t('education_allocations.list.confirm_switch_to_normal_mode') + '</h4>'
            if (await this.$refs.confirm.show(message)) {
                /**
                 * OKの場合
                 */
                // 割振一覧画面へ遷移
                window.location.href = '/allocations'
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
    }
}
</script>

<style>

#allocation-search-list div.v-input--selection-controls__ripple.accent--text,
#allocation-search-list i.v-icon.material-icons.theme--light.accent--text {
    color: rgba(0,0,0,.54) !important;
    caret-color: rgba(0,0,0,.54) !important;
}
</style>

<style scoped>
#allocation-search-list >>> .cursor-pointer > span {
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
