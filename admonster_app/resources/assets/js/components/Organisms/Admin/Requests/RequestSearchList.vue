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
                :items="requests"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                hide-actions
                must-sort
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="request_sort_key">
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
                    <tr
                        :active="props.selected"
                        @click="goToRequestPage(props.item.request_id)"
                        @mouseleave="previewIconHide(props.item.request_id)"
                        @mouseenter="previewIconShow(props.item.request_id)"
                        style="cursor:pointer;"
                    >
                        <template v-for="header in shownHeaders" >
                            <td
                                v-if="'client_name' === header.value || 'request_mail_from' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayName(props.item[header.value]) }}</span>
                                    <span>{{ displayName(props.item[header.value]) }}</span>
                                </v-tooltip>

                            </td>
                            <td
                                v-else-if="'request_name' === header.value || 'request_mail_subject' === header.value" :key="header.text"
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
                                        :class="['mr-1', 'request-file-preview', 'request-file-preview' + props.item.request_id]"
                                    >
                                        <v-tooltip top>
                                            <span
                                                v-if="props.item.request_file_id !== null"
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
                                {{  props.item[header.value] | formatDateYmdHm(true) }}
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
                                        {{ props.item[header.value] | formatDateYmdHm }}
                                    </span>
                                    {{ $t('list.tooltip.change_delivery_deadline') }}<v-icon dark small>open_in_new</v-icon>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'company_name' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item[header.value] }}</span>
                                    <span>{{ props.item[header.value] }}</span>
                                </v-tooltip>


                            </td>
                            <td
                                v-else-if="'business_name' === header.value" :key="header.text"
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
                            <td
                                v-else-if="'request_file_id' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                {{  props.item[header.value] }}
                            </td>
                            <td
                                v-else-if="'status' === header.value" :key="header.text"
                                class="text-xs-center"
                            >
                                <v-tooltip top>
                                    <v-chip
                                        slot="activator"
                                        :color="displayStatusColor(props.item.status)"
                                        class="cursor-pointer"
                                        @click.stop="goToWorkListPage(props.item)"
                                        small outline label disabled
                                    >
                                        {{ displayStatusName(props.item.status) }}
                                    </v-chip>
                                    {{ $t('menu.list.works') }}<v-icon dark small>reply</v-icon>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'request_mail_to' === header.value" :key="header.text"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayNames(props.item[header.value]) }}</span>
                                    <span>{{ displayNames(props.item[header.value]) }}</span>
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
            <request-mail-attachments-modal ref="attachmentListModal" :attachments.sync="attachments"></request-mail-attachments-modal>
            <change-delivery-deadline-dialog
                ref="changeDeliveryDeadlineDialog"
                :apiPath="requestsApiPath"
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
import store from '../../../../stores/Admin/Requests/store'
import HeaderCustomModal from '../../../Organisms/Common/HeaderCustomModal'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import RequestMailAttachmentsModal from '../../../Molecules/Requests/RequestMailAttachmentsModal'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import ChangeDeliveryDeadlineDialog from '../../../Atoms/Dialogs/ChangeDeliveryDeadlineDialog'
import FilePreviewDialog from '../../../Atoms/Dialogs/FilePreviewDialog'
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    data: () => ({
        selected: [],
        requests: [],
        attachments: [],

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

        //表頭カスタムモーダルでのデフォルト値
        initialHeaders: {},

        //loading
        loading: false,
        filePreviewLoading: false,
    }),
    components:{
        ProgressCircular,
        HeaderCustomModal,
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
            localStorage.setItem('requestHeaders', JSON.stringify(data));
        })
        eventHub.$on('resetHeaderCustomData', function() {
            // localへ保存した初期値を使用して初期化する。
            try {

                let localHeadersdata = JSON.parse(localStorage.getItem('initialRequest'));

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
        localStorage.setItem('initialRequest', JSON.stringify(store.state));

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
    computed: {
        requestsApiPath () {
            return '/api/requests'
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
        requestDetailUri() {
            return function(id) {
                let uri = '/management/requests/' + id
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
        requestCode() {
            return _const.MASTER_ID_PREFIX.REQUEST_ID
        },
    },
    mounted () {
        this.$nextTick(() => {
            const element = document.getElementById('request_sort_key')
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
        canChangeDeliveryDeadline (item) {
            return item['status'] === _const.REQUEST_STATUS.DOING
        },
        previewIconShow (requestId) {
            this.$nextTick(function () {
                $('.request-file-preview' + requestId).show()
            })
        },
        previewIconHide (requestId) {
            this.$nextTick(function () {
                $('.request-file-preview' + requestId).hide()
            })
        },
        showAttachmentList (requestMailId) {
            this.attachments = []
            this.$refs.attachmentListModal.show(requestMailId)
        },
        openChangeDeliveryDeadlineDialog (item) {
            if (!this.canChangeDeliveryDeadline(item)) return
            this.$refs.changeDeliveryDeadlineDialog.show(item.deadline, item.request_id)
        },
        goToRequestPage (requestId) {
            window.location.href = this.requestDetailUri(requestId)
        },
        goToWorkListPage (request) {
            const params = {business_name: this.requestCode + request.request_id}
            window.location.href = '/management/works?' + Object.entries(params).map((value) => value[0] + '=' + value[1]).join('&')
        },
        searchRequestListAsync (params = this.searchParams) {
            this.loading = true

            let searchParams = Vue.util.extend({}, params)

            axios.post('/api/requests', searchParams)
                .then((res) =>{
                // 検索条件をstoreに保存
                    store.dispatch('setSearchParams', params)
                    // 検索結果を画面に反映
                    this.requests = res.data.requests.data
                    this.paginate.data_count_total = res.data.requests.total
                    this.paginate.page_count = res.data.requests.last_page
                    this.paginate.page_from = res.data.requests.from ? res.data.requests.from : 0
                    this.paginate.page_to = res.data.requests.to ? res.data.requests.to : 0
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
        openHeaderCustomModal() {
            //TODO 初期変数の格納タイミング
            this.initialHeaders = JSON.parse(localStorage.getItem('initialRequest'));
            eventHub.$emit('open-header-custom-modal')
        },
        displayStatusName(status) {
            let name = ''
            switch (status) {
            case _const.REQUEST_STATUS.DOING:
                name = Vue.i18n.translate('requests.list.request_status.doing')
                break;
            case _const.REQUEST_STATUS.FINISH:
                name = Vue.i18n.translate('requests.list.request_status.finish')
                break;
            case _const.REQUEST_STATUS.EXCEPT:
                name = Vue.i18n.translate('requests.list.request_status.except')
                break;
            }
            return name
        },
        displayStatusColor(status) {
            let color = ''
            switch (status) {
            case _const.REQUEST_STATUS.DOING:
                color = 'blue'
                break;
            case _const.REQUEST_STATUS.FINISH:
                color = 'secondary'
                break;
            case _const.REQUEST_STATUS.EXCEPT:
                color = 'gray'
                break;
            }
            return color
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
