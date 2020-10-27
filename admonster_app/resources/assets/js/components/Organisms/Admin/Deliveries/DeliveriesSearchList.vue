<template>
    <div id="search-list">
        <header-custom-modal
            :selectedRowsPerPage="pagination.rowsPerPage"
            :showHeaders="shownHeaders"
            :hiddenHeaders="hiddenHeaders"
            :initialHeaders="initialHeaders"
        ></header-custom-modal>
        <alert-dialog ref="alert" width="450"></alert-dialog>
        <!-- toolbar -->
        <v-layout row wrap align-center>
            <div v-show="selected.length > 0">
                <v-tooltip top>
                    <v-btn flat icon color="primary" slot="activator" @click="download">
                        <v-icon>mdi-cloud-download</v-icon>
                    </v-btn>
                    <span>{{ $t('deliveries.list.download_delivery_files') }}</span>
                </v-tooltip>
            </div>
            <div v-show="selectedRequestWorkIds.length > 0" class="px-3">
                <span v-if="selectedRequestWorkIds.length === allRequestWorkIds.length">
                    <span>{{ $t('list.selecting_all_search_result', {'count': allRequestWorkIds.length}) }}</span>
                    <span class="pr-2">／</span>
                    <a @click="cancelSelectAll()">
                        <span>{{ $t('list.deselection') }}</span>
                    </a>
                </span>
                <span v-else>
                    <span>{{ $t('list.selecting_count', {'count': selectedRequestWorkIds.length}) }}</span>
                    <span class="pr-2">／</span>
                    <a @click="selectAll()">
                        <span>{{ $t('list.select_all_search_result', {'count': allRequestWorkIds.length}) }}</span>
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
                item-key="request_work_id"
                select-all
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="delivery_sort_key">
                        <th id="ignore-elements">
                            <v-checkbox
                                hide-details
                                @click.native="toggleAll()"
                                :input-value="isCheckedAllDoneItems"
                                :indeterminate="isIndeterminate"
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
                        @click="goToDeliveryPage(props.item)"
                        @mouseleave="previewIconHide(props.item.request_work_id)"
                        @mouseenter="previewIconShow(props.item.request_work_id)"
                        :style="{cursor: canActiveLink(props.item) ? 'pointer' : ''}"
                    >
                        <td width="40px"
                            class="text-xs-center">
                            <v-checkbox
                                :input-value="props.selected"
                                hide-details
                                color="primary"
                                @click.stop="selectedItem(props.item)"
                                :disabled="isDisabledCheckbox(props)"
                            ></v-checkbox>
                        </td>
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
                                v-else-if="'deliverer_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <div v-if="user_image_path(props.item.deliverer_id) == undefined">
                                    <v-avatar size="32px" class="ma-1">
                                        <img src="/images/question.svg">
                                    </v-avatar>
                                </div>
                                <v-tooltip top v-else>
                                    <v-avatar slot="activator" size="32px" class="ma-1">
                                        <img :src="user_image_path(props.item.deliverer_id)">
                                    </v-avatar>
                                    <span>{{ user_name(props.item.deliverer_id) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'deliverer_id_no_image' === header.value" :key="header.text"
                                class="text-xs-left"
                                style="max-width: 0px;"
                            >
                                <users-name-display
                                    :userIds="props.item.deliverer_id ? [props.item.deliverer_id] : []"
                                    :usersInfo="candidates"
                                    :width="Number(header.width)"
                                ></users-name-display>
                            </td>
                            <td
                                v-else-if="'delivery_status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-chip small outline label disabled :color="displayStatusColor(props.item)" :class="[canActiveLink(props.item) ? 'cursor-pointer' : '']">
                                    {{ displayStatusName(props.item) }}
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
                                v-else-if="'assign_date' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-tooltip top :disabled="!isChangeDeliveryAssignDate(props.item)">
                                    <span
                                        v-if="props.item.is_assign_date"
                                        slot="activator"
                                        class="cursor-pointer"
                                        @click.stop="showChangeDeliveryAssignDateDialog(props.item)"
                                    >
                                        <span v-if="props.item.assign_delivery_at">{{ props.item.assign_delivery_at | formatDateYmdHm }}</span>
                                    </span>
                                    {{ $t('list.tooltip.change_delivery_date') }}<v-icon dark small>open_in_new</v-icon>
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
                                    {{ $t('deliveries.list.worker_detail_confirm') }}<v-icon dark small>open_in_new</v-icon>
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
                                v-else-if="'delivery_data_creator' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <div v-if="user_image_path(props.item.delivery_data_creator) == undefined">
                                    <v-avatar size="32px" class="ma-1">
                                        <img src="/images/question.svg">
                                    </v-avatar>
                                </div>
                                <v-tooltip top v-else>
                                    <v-avatar slot="activator" size="32px" class="ma-1">
                                        <img :src="user_image_path(props.item.delivery_data_creator)">
                                    </v-avatar>
                                    <span>{{ user_name(props.item.delivery_data_creator) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'delivery_data_creator_no_image' === header.value" :key="header.text"
                                class="text-xs-left"
                                style="max-width: 0px;"
                            >
                                <users-name-display
                                    :userIds="props.item.delivery_data_creator ? [props.item.delivery_data_creator] : []"
                                    :usersInfo="candidates"
                                    :width="Number(header.width)"
                                ></users-name-display>
                            </td>
                            <td
                                v-else-if="'delivery_time' === header.value" :key="header.text"
                                class="text-xs-left"
                            >
                                <span v-if="props.item.delivery_time">
                                    {{ props.item.delivery_time | formatDateYmdHm(true)  }}
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
                :apiPath="deliveriesApiPath"
                @reload="searchRequestListAsync"
            ></change-delivery-deadline-dialog>
            <change-delivery-assign-date-dialog
                ref="changeDeliveryAssignDateDialog"
                @reload="searchRequestListAsync"
            ></change-delivery-assign-date-dialog>
            <notify-modal></notify-modal>
            <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
            <reference-view-dialog ref="referenceViewDialog" content-class="fill-height"></reference-view-dialog>
        </div>
    </div>
</template>

<script>
import Sortable from 'sortablejs'
import store from '../../../../stores/Admin/Deliveries/store'
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import UsersNameDisplay from '../../../Molecules/Users/UsersNameDisplay'
import WorkListModal from '../../../Atoms/Modal/WorkListModal'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import RequestMailAttachmentsModal from '../../../Molecules/Requests/RequestMailAttachmentsModal'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import FilePreviewDialog from '../../../Atoms/Dialogs/FilePreviewDialog'
import ChangeDeliveryDeadlineDialog from '../../../Atoms/Dialogs/ChangeDeliveryDeadlineDialog'
import ChangeDeliveryAssignDateDialog from '../../../Atoms/Dialogs/ChangeDeliveryAssignDateDialog'
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    components: {
        ProgressCircular,
        HeaderCustomModal,
        WorkListModal,
        UsersOverview,
        UsersNameDisplay,
        AlertDialog,
        NotifyModal,
        RequestMailAttachmentsModal,
        ChangeDeliveryDeadlineDialog,
        ChangeDeliveryAssignDateDialog,
        FilePreviewDialog,
        ReferenceViewDialog,
    },
    data: () => ({
        selected: [],
        items: [],
        attachments: [],
        selectedRequestWorkIds: [],
        allRequestWorkIds: [],

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

        // ダウンロードに関しての数値
        downloadLimit: 0,
        eachDeliveryInfo : {},

        filePreviewLoading: false,
    }),
    computed: {
        deliveriesApiPath () {
            return '/api/deliveries'
        },
        searchParams () {
            return store.state.searchParams
        },
        shownHeaders () {
            return store.state.showHeaders
        },
        hiddenHeaders () {
            return store.state.hiddenHeaders
        },
        deliveryDetailUri () {
            return function(id) {
                let uri = '/deliveries/'+ id
                return uri
            }
        },
        isIndeterminate () {
            const allDoneItems = this.items.filter(data => data['delivery_status'] === _const.DELIVERY_STATUS.DONE)
            return (this.selected.length < allDoneItems.length && this.selected.length > 0)
        },
        isCheckedAllDoneItems () {
            if (this.items.length === 0) return false// 画面表示時に全選択にチェックが入るのを防ぐ処理
            const allDoneItems = this.items.filter(data => data['delivery_status'] === _const.DELIVERY_STATUS.DONE)
            if (allDoneItems.length === 0) return false// 納品済みが無い場合の初期表示で全選択のチェックを防ぐ処理
            return this.selected.length === allDoneItems.length
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
    watch: {
        pagination: {
            // ヘッダークリックによるソート処理（サーバ）
            handler () {

                // ソート条件を更新
                let params = this.searchParams
                params.sort_by = this.pagination.sortBy
                params.descending = this.pagination.descending

                // 納品一覧を取得
                this.searchRequestListAsync(params)
            },
        },
        selectedRequestWorkIds : async function (newSelected) {
            if (newSelected.length === 0) return// 未選択時は即終了

            // 納品対象ファイルサイズを取得
            this.loading = true
            try {
                const formData = new FormData()
                // データの無いrequest work id のみ
                const existingRequestWorkId = Object.keys(this.eachDeliveryInfo).map(str => parseInt(str, 10))
                const requestWorkIds = newSelected.filter(id => !existingRequestWorkId.includes(id))

                // データの取得
                if (requestWorkIds.length > 0) {
                    formData.append('request_work_ids',JSON.stringify(requestWorkIds))
                    const res = await axios.post('/api/deliveries/deliveryInfo', formData)
                    Object.assign(this.eachDeliveryInfo, res.data.each_delivery_info)
                    // 選択した件名のDLサイズを検査
                    this.downloadLimit = res.data.limit_size
                }

                if (this.checkDownloadSizeExceed(this.downloadLimit)) {
                    // プレースホルダに入れる文字列の生成
                    const byteSize = this.downloadLimit.toLocaleString()
                    const splitSize = byteSize.split(',')
                    const sizeUnits = ['Byte', 'KB', 'MB', 'GB']
                    const sizeUnit = sizeUnits[splitSize.length - 1]

                    this.$refs.alert.show(Vue.i18n.translate('deliveries.list.message.limit_DL_size', {limit_size: `${splitSize[0]}${sizeUnit}`}))
                }

                this.loading = false

            } catch (e) {
                this.loading = false
                console.log(e)
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
            }
        }
    },
    created () {
        eventHub.$on('commitHeaderCustomData', function(data) {

            // 表頭カスタムデータのコミット処理
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })

            // localStorageへ表頭カスタムデータを格納
            localStorage.setItem('deliveryHeaders', JSON.stringify(data))
        })
        eventHub.$on('resetHeaderCustomData', function() {

            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialDelivery'))

                let localShowHeaders = localHeadersdata.showHeaders
                let localHiddenHeaders = localHeadersdata.hiddenHeaders

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })

                localStorage.setItem('deliveryHeaders', JSON.stringify(localHeadersdata))
            } catch (e) {
                console.log(e)
                localStorage.removeItem('deliveryHeaders')
                eventHub.$emit('resetHeaderCustomData')
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
        localStorage.setItem('initialDelivery', JSON.stringify(store.state))

        // localStorageより表頭カスタムデータを取得
        if (localStorage.getItem('deliveryHeaders')) {
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('deliveryHeaders'))

                let localShowHeaders = localHeadersdata.showHeaders
                let localHiddenHeaders = localHeadersdata.hiddenHeaders

                store.commit('setShowHeaders', { params: localShowHeaders })
                store.commit('setHiddenHeaders', { params: localHiddenHeaders })
            } catch (e) {
                console.log(e)
                localStorage.removeItem('deliveryHeaders')
                eventHub.$emit('resetHeaderCustomData')
            }
        }
    },
    mounted () {
        this.$nextTick(() => {
            // テーブルの表頭をDrag&Dropで並び替えする処理の宣言
            const element = document.getElementById('delivery_sort_key')
            const _self = this
            Sortable.create(element, {
                filter: '#ignore-elements',
                onMove(e) {
                    return e.related.id !== 'ignore-elements';
                },
                onEnd({ newIndex, oldIndex }) {
                    let headers = _self.shownHeaders
                    const headerSelected = headers.splice(oldIndex - 1, 1)[0]// チェックボックスを追加しているので-1
                    headers.splice(newIndex - 1, 0, headerSelected)// チェックボックスを追加しているので-1
                    eventHub.$emit('commitHeaderCustomData', {
                        'showHeaders': headers,
                        'hiddenHeaders': store.state.hiddenHeaders
                    })
                }
            })
        })
    },
    methods: {
        canChangeDeliveryDeadline (item) {
            return item['request_status'] === _const.REQUEST_STATUS.DOING && item['delivery_status'] !== _const.DELIVERY_STATUS.DONE
        },
        openChangeDeliveryDeadlineDialog (item) {
            if (!this.canChangeDeliveryDeadline(item)) return
            this.$refs.changeDeliveryDeadlineDialog.show(item.deadline, item.request_work_id)
        },
        isChangeDeliveryAssignDate (item) {
            return item.delivery_status === _const.DELIVERY_STATUS.SCHEDULED
        },
        showChangeDeliveryAssignDateDialog (item) {
            if (!this.isChangeDeliveryAssignDate(item)) return
            this.$refs.changeDeliveryAssignDateDialog.show(item, item.assign_delivery_at)
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
        goToDeliveryPage (item) {
            if (this.canActiveLink(item) ) {
                window.location.href = this.deliveryDetailUri(item.request_work_id)
            }
        },
        open (item) {
            this.$refs.wokerListModal.show(item.request_work_id)
        },
        searchRequestListAsync (params = this.searchParams) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/deliveries/index', searchParams)
                .then((res) =>{

                    // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: params })

                    // 検索結果を画面に反映
                    this.items = res.data.list.data
                    this.candidates = res.data.candidates
                    this.allRequestWorkIds = res.data.all_request_work_ids
                    this.selectedRequestWorkIds = []
                    this.selected = []
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

            // 「b' <xxx@xxx>」の部分を削除する
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
        displayStatusName (item) {
            switch (item.delivery_status) {
            case _const.DELIVERY_STATUS.NONE:
                return Vue.i18n.translate('deliveries.list.status.none')
            case _const.DELIVERY_STATUS.DONE:
                return Vue.i18n.translate('deliveries.list.status.done')
            case _const.DELIVERY_STATUS.SCHEDULED:
                return this.$t('deliveries.list.status.before')
            }
            return ''
        },
        displayStatusColor (item) {
            switch (item.delivery_status) {
            case _const.DELIVERY_STATUS.NONE:
                return 'red'
            case _const.DELIVERY_STATUS.DONE:
                return 'secondary'
            case _const.DELIVERY_STATUS.SCHEDULED:
                return 'blue'
            }
            return ''
        },
        openHeaderCustomModal () {
            this.initialHeaders = JSON.parse(localStorage.getItem('initialDelivery'))
            eventHub.$emit('open-header-custom-modal')
        },
        displayName (name) {
            if (!name) return ''

            let displayName = name

            // 「b' <xxx@xxx>」の部分を削除する
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

            nameArray = names.split(',')

            for (let i = 0; i < nameArray.length; i++) {
                displayNameArray.push(this.displayName(nameArray[i]))
            }

            return displayNameArray.join(',')
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

            // 納品一覧を取得
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
            const users = {
                allocated_user_ids: item.worker_ids ? item.worker_ids.split(',') : [],
                completed_user_ids: item.worker_ids ? completedUserIds(item) : [],
            }
            return users
        },
        toggleAll () {
            const allDoneItems = this.items.filter(data => data['delivery_status'] === _const.DELIVERY_STATUS.DONE)
            this.selected = this.selected.length === allDoneItems.length ? [] : allDoneItems
            this.selectedRequestWorkIds  = this.selectedRequestWorkIds .length === allDoneItems.length ? [] : allDoneItems.map(data => data['request_work_id'])
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
        canActiveLink (item) {
            const inactiveList = [1, 4, 5] // business id list
            return item.result_type == _const.RESULT_TYPE.SUCCESS && !inactiveList.includes(item.business_id)
        },
        async download () {
            // DLサイズチェック
            if (this.checkDownloadSizeExceed(this.downloadLimit)) {
                // プレースホルダに入れる文字列の生成
                const byteSize = this.downloadLimit.toLocaleString()
                const splitSize = byteSize.split(',')
                const sizeUnits = ['Byte', 'KB', 'MB', 'GB']
                const sizeUnit = sizeUnits[splitSize.length - 1]

                this.$refs.alert.show(Vue.i18n.translate('deliveries.list.message.limit_DL_size', {limit_size: `${splitSize[0]}${sizeUnit}`}))
                return
            }

            // 複数選択した際のエラーダイアログ
            if (this.selected.length > 1) {
                const sizes = this.selectedRequestWorkIds.map(data => this.eachDeliveryInfo[data]['size'])// 選択した依頼のサイズが入っている配列を作成
                const zeroSizes = sizes.filter(size => size === 0)

                // 納品データが無い作業も納品データが登録されていない作業も両方sizeは0になるため
                if (zeroSizes.length > 0) {
                    this.$refs.alert.show(Vue.i18n.translate('deliveries.list.message.cannot_download_multiple_delivery_data', {number: zeroSizes.length}))
                    return
                }
            }

            if (this.selected.length === 1) {
                // 納品無しの作業のため、ダウンロードできません
                const isDelivered = this.eachDeliveryInfo[this.selectedRequestWorkIds[0]]['is_delivered']// 選択した依頼のサイズが入っている配列を作成
                if (!isDelivered) {
                    this.$refs.alert.show(Vue.i18n.translate('deliveries.list.message.cannot_be_downloaded_due_to_work_without_delivery'))
                    return
                }
            }
            // ダウンロードサイズが0の場合は、処理しない
            // 各サイズの配列を作成
            const size = this.eachDeliveryInfo[this.selectedRequestWorkIds[0]]['size']// 選択した依頼のサイズが入っている配列を作成
            if (size === 0) {
                this.$refs.alert.show(Vue.i18n.translate('deliveries.list.message.can_not_DL_not_delivery_data'))
                return
            }

            this.loading = true
            try {
                const formData = new FormData()
                formData.append('request_work_ids',JSON.stringify(this.selectedRequestWorkIds))
                const res = await axios.post('/api/deliveries/createZipFile', formData)
                this.loading = false

                // ファイルのダウンロード
                const a = document.createElement('a')
                a.download = res.data.file_name // ダウンロードを失敗した時に出るファイル名
                a.target   = '_blank' // 別タブに切り替えないようにする
                let uri = 'api/utilities/downloadFromLocal?file_path='
                uri += encodeURIComponent(res.data.file_path) + '&file_name=' + encodeURIComponent(res.data.file_name)
                a.href = uri
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)
            } catch (e) {
                this.loading = false
                console.log(e)
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
            }
        },
        checkDownloadSizeExceed  (limit_size) {
            // 各サイズの配列を作成
            const sizes = this.selected.map(data => this.eachDeliveryInfo[data['request_work_id']]['size'])// 選択した依頼のサイズが入っている配列を作成
            const sumSize = sizes.reduce((accumulator, currentValue) => accumulator + currentValue, 0)// 配列内のサイズを合計 初期数は0
            return sumSize >= limit_size
        },
        isDisabledCheckbox (props) {
            return props.item.delivery_status === _const.DELIVERY_STATUS.NONE
        },
        selectAll () {
            const allDoneItems = this.items.filter(data => data['delivery_status'] === _const.DELIVERY_STATUS.DONE)
            this.selected = allDoneItems
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

<style scoped>
.cursor-pointer,
#search-list >>> .cursor-pointer > span {
    cursor: pointer;
}

.cursor-default >>> * {
    cursor: default !important;
}
.search-title-content {
    display: flex;
}
.search-title-text {
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 全選択チェックボックス */
#ignore-elements >>> div.v-input--selection-controls__ripple.accent--text,
#ignore-elements >>>  i.v-icon.material-icons.theme--light.accent--text {
    color: rgba(0,0,0,.54) !important;
    caret-color: rgba(0,0,0,.54) !important;
}
</style>
