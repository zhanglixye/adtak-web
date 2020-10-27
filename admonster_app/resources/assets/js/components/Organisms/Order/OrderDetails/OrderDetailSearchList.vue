<template>
    <div id="search-list">
        <header-custom-modal
            :selectedRowsPerPage="pagination.rowsPerPage"
            :showHeaders="shownHeaders"
            :hiddenHeaders="hiddenHeaders"
            :initialHeaders="initialHeaders"
            :orderId="orderId"
            :isOrderAdmin="isAdmin"
            :prepend-function-to-cancel="clearSettingDisplayFormats"
            :isIconShow="true"
        ></header-custom-modal>
        <!-- toolbar -->
        <v-layout row wrap align-center>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-btn flat icon color="primary" slot="activator" @click="noDataShow()">
                    <v-icon>add_box</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.list.create_order_detail') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-form :action="'/imported_files/order_setting'" method="post" @submit.prevent ref="addOrderDetailForm">
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="file_name" :value="tmpFileInfo.file_name">
                    <input type="hidden" name="tmp_file_dir" :value="tmpFileInfo.file_dir">
                    <input type="hidden" name="readonly" :value="true">
                    <input type="hidden" name="order_id" :value="orderId">
                    <input type="hidden" name="tmp_file_path" :value="tmpFileInfo.file_path">
                </v-form>
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    @click="openFileImportDialog()"
                >
                    <v-icon>mdi-file-plus</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.list.create_order_detail_from_a_file') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderDetailIds.length !== 0"
                    @click="changeStatus(1)"
                >
                    <v-icon>mdi-delete-restore</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.list.to_active') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderDetailIds.length !== 0"
                    @click="changeStatus(0)"
                >
                    <v-icon>delete</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.list.to_inactive') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderDetailIds.length !== 0"
                    @click="createOrderFile()"
                    >
                    <v-icon>mdi-cloud-download</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.list.download_order_details') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderDetailIds.length !== 0"
                    @click="openBusinessSelectionDialog()"
                >
                    <v-icon>mdi-playlist-plus</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.list.bulk_create_request') }}</span>
            </v-tooltip>
            <div v-show="selectedOrderDetailIds.length > 0" class="px-3">
                <span v-if="selectedOrderDetailIds.length === allOrderDetailId.length">
                    <span>{{ $t('list.selecting_all_search_result', {'count': paginate.data_count_total}) }}</span>
                    <span class="pr-2">／</span>
                    <a @click="cancelSelectAll()">
                        <span>{{ $t('list.deselection') }}</span>
                    </a>
                </span>
                <span v-else>
                    <span>{{ $t('list.selecting_count', {'count': selectedOrderDetailIds.length}) }}</span>
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
        <div class="data-content">
            <!--table-->
            <v-data-table
                :value="selectedOrderDetails"
                :headers="shownHeaders"
                :items="orderDetails"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                item-key="order_detail_id"
                hide-actions
                must-sort
                select-all
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr id="order_sort_key">
                        <th id="ignore-elements">
                            <v-checkbox
                                primary
                                hide-details
                                @click.native="toggleAll()"
                                :input-value="props.all"
                                :indeterminate="props.indeterminate"
                            ></v-checkbox>
                        </th>
                        <th
                            v-for="header in props.headers"
                            :key="header.value"
                            :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                            :style="{ 'min-width': header.width + 'px' }"
                            @click="changeSort(header.value)"
                        >
                            <v-icon class="table-herder-icon" small>
                                <template v-if="header.isEdit">mdi-file-excel</template>
                                <template v-else>mdi-wrench</template>
                            </v-icon>
                            {{ header.text }}
                            <v-icon small>arrow_drop_up</v-icon>
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr
                        :active="props.selected"
                        :style="{'background-color' : getBackgroundColorByStatus(props.item.status), 'cursor' : 'pointer'}"
                        @click="show(props.item.order_detail_id)"
                    >
                        <td width="40px" class="text-xs-center" @click.stop>
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
                                v-if="'created_at' === header.value"
                                :key="header.value"
                                class="text-xs-center"
                            >{{ props.item[header.value] | formatDateYmdHm(true) }}</td>
                            <td
                                v-else-if="'order_detail_name' === header.value"
                                :key="header.value"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ (props.item.order_detail_name) }}</span>
                                    <span>{{ (props.item.order_detail_name) }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'status' === header.value"
                                :key="header.value"
                                class="text-xs-center"
                            >
                                <v-chip
                                    small
                                    outline
                                    label
                                    disabled
                                    :color="displayStatusColor(props.item[header.value])"
                                    class="cursor-pointer"
                                >{{ displayStatusName(props.item[header.value]) }}</v-chip>
                            </td>
                            <td
                                v-else-if="customStatusValues.includes(header.value)"
                                :key="header.value"
                                class="text-xs-center"
                            >
                                <v-chip
                                    v-if="props.item[header.value] !== undefined"
                                    small
                                    outline
                                    label
                                    disabled
                                    color="primary"
                                    class="cursor-pointer"
                                >{{ props.item[header.value] }}</v-chip>
                            </td>
                            <td
                                v-else
                                :key="header.value"
                                :class="isItemCenter(header.value) ? 'text-xs-center' : 'text-xs-left overflow'"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ displayFormat(header.value, props.item[header.value]) }}</span>
                                    <span>
                                        {{ displayFormat(header.value, props.item[header.value]) }}
                                    </span>
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
                        :style="{
                            'max-width': '50px',
                            'margin-left': '30px',
                            'margin-right': '50px',
                        }"
                        :items="rowsCandidatesPerPage"
                        :menu-props="{ maxHeight: '300' }"
                    ></v-select>
                    <div>{{ $t('common.pagination.all') + paginate.data_count_total + $t('common.pagination.items') + paginate.page_from + $t('common.pagination.from') + '～' + paginate.page_to + $t('common.pagination.to') }}</div>
                    <v-pagination
                        v-model="page"
                        :length="paginate.page_count"
                        circle
                        :total-visible="5"
                    ></v-pagination>
                </v-layout>
            </v-container>
            <!--pagination-->
            <progress-circular v-if="loading"></progress-circular>
        </div>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <file-import-dialog
            :file-import-dialog.sync="fileImportDialog"
            :import-method-selection-dialog.sync="isToImportSettingPage"
            v-if="fileImportDialog"
        ></file-import-dialog>
        <setting-display-format-dialog
            ref="settingDisplayFormatDialog"
            :callback="settingDisplayFormatCallback"
        ></setting-display-format-dialog>
        <business-selection-dialog ref="businessSelectionDialog"></business-selection-dialog>
        <div style="display: none;">
            <editor-quil
                class="mb-3"
                :heightAdd="0"
                ref="editor"
            ></editor-quil>
        </div>
        <v-dialog v-model="invalidClickDialog" persistent></v-dialog>
    </div>
</template>

<script>
import Sortable from 'sortablejs'
import HeaderCustomModal from '../../Common/HeaderCustomModal'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import FileImportDialog from '../../Admin/ImportedFiles/FileImportDialog'
import importedFilesStore from '../../../../stores/Admin/ImportedFiles/store'
import store from '../../../../stores/Order/OrderDetails/store'
import SettingDisplayFormatDialog from './SettingDisplayFormatDialog'
import BusinessSelectionDialog from './BusinessSelectionDialog'
import EditorQuil from './EditorQuil'

export default {
    components: {
        HeaderCustomModal,
        ConfirmDialog,
        ProgressCircular,
        AlertDialog,
        FileImportDialog,
        SettingDisplayFormatDialog,
        BusinessSelectionDialog,
        EditorQuil,
    },
    props: {
        orderId: { type: Number, required: true }
    },
    data () {
        return {
            invalidClickDialog: false,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            fileImportDialog: false,
            settingDisplayFormatCallback: 'settingDisplayFormat',
            isToImportSettingPage: false,
            tmpFileInfo: {
                file_dir: '',
                file_name: '',
                file_path: '',
            },
            settingDisplayFormats: [],
            selectedOrderDetailColumns: [],
            selectedOrderDetailNames: [],
        }
    },
    watch: {
        searchParams: {
            handler: function (val) {
                store.commit('setSearchParams', val)
            },
            deep: true
        },
        isToImportSettingPage(val) {
            if (val) {
                this.formSubmit()
            }
        },
    },
    computed: {
        initialHeaders () {
            return store.getters.initialHeaders
        },
        isAdmin () {
            return store.state.processingData.isAdmin
        },
        displayLangCode () {
            return store.state.displayLangCode
        },
        pagination: {
            set(val) {
                store.commit('setSearchParams', { sort_by: val.sortBy, descending: val.descending, rows_per_page: val.rowsPerPage})
            },
            get() {
                return {
                    sortBy: store.state.searchParams.sort_by,
                    descending: store.state.searchParams.descending,
                    rowsPerPage: store.state.searchParams.rows_per_page,
                }
            }
        },
        selectedOrderDetailIds: {
            set (ids) {
                store.commit('setSelectedOrderDetailIds', ids)
            },
            get () {
                return JSON.parse(JSON.stringify(store.state.processingData.selectedOrderDetailIds))
            }
        },
        selectedOrderDetails() {
            return this.orderDetails.filter(orderDetail => this.selectedOrderDetailIds.includes(orderDetail.order_detail_id))
        },
        allOrderDetailId () {
            return store.state.processingData.allOrderDetailId
        },
        loading () {
            return store.state.processingData.loading
        },
        labelData: {
            set(labelData) {
                store.commit('setLabelData', labelData)
            },
            get() {
                return store.state.processingData.labelData
            },
        },
        orderFileImportColumnConfigs () {
            return store.state.processingData.orderFileImportColumnConfigs
        },
        orderDetails () {
            return store.state.processingData.orderDetails
        },
        shownHeaders () {
            return store.state.showHeaders
        },
        hiddenHeaders () {
            return store.state.hiddenHeaders
        },
        customStatusValues () {
            return store.state.processingData.customStatuses.slice().map(customStatus => `${store.state.customStatusColumnPrefix}${customStatus.id}`)
        },
        rowsCandidatesPerPage () {
            return [20, 50, 100]
        },
        paginate () {
            return store.state.paginate
        },
        rowsPerPage: {
            set(rowsPerPage) {
                store.commit('setSearchParams', { rows_per_page : rowsPerPage})
                this.page = 1
            },
            get() {
                return store.state.searchParams.rows_per_page
            },
        },
        page: {
            set(page) {
                // 再検索
                store.commit('setSearchParams', { page : page})
                store.dispatch('searchOrderDetailList')
            },
            get() {
                return store.state.searchParams.page
            },
        }
    },
    created () {
        const initialSearchParams = Object.assign({}, store.getters.searchParams(this.orderId), { order_id: this.orderId })
        store.commit('setSearchParams', initialSearchParams)
        store.dispatch('searchOrderDetailList')
        const self = this
        eventHub.$on('commitHeaderCustomData', function(data) {
            self.settingDisplayFormat()
            self.clearSettingDisplayFormats()
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })
        })

        eventHub.$on('changeRowsPerPage', function(data) {
            self.rowsPerPage = data.rowsPerPage
        })
        eventHub.$on('openEdit', function(element) {
            self.openSettingDisplayFormat(element)
        })
        eventHub.$on('settingDisplayFormat', function(selectedDisplayFormats, orderFileImportColumnConfigId) {
            const settingDisplayFormatFunction = self.settingDisplayFormats.find(item => item.orderFileImportColumnConfigId === orderFileImportColumnConfigId)
            if (settingDisplayFormatFunction === undefined) {
                self.settingDisplayFormats.push({
                    'selectedDisplayFormats': selectedDisplayFormats,
                    'orderFileImportColumnConfigId': orderFileImportColumnConfigId
                })
            } else {
                settingDisplayFormatFunction.selectedDisplayFormats = selectedDisplayFormats
            }
        })
    },
    mounted () {
        this.$nextTick(() => {
            const element = document.getElementById('order_sort_key')
            const _self = this
            Sortable.create(element, {
                filter: '#ignore-elements',
                onMove(e) {
                    return e.related.id !== 'ignore-elements'
                },
                onEnd({ newIndex, oldIndex }) {
                    let headers = _self.shownHeaders
                    const headerSelected = headers.splice(oldIndex - 1, 1)[0]
                    headers.splice(newIndex - 1, 0, headerSelected)
                    eventHub.$emit('commitHeaderCustomData', {
                        showHeaders: headers,
                        hiddenHeaders: store.state.hiddenHeaders,
                    })
                },
            })
        })
    },
    methods: {
        formSubmit () {
            this.tmpFileInfo = importedFilesStore.state.uploadFile.tmpFileInfo
            this.$nextTick(() => {
                this.$refs.addOrderDetailForm.$el.submit()
            })
        },
        openFileImportDialog () {
            importedFilesStore.commit('resetUploadFile')
            this.fileImportDialog = true
        },
        getBackgroundColorByStatus (status) {
            return status === _const.FLG.ACTIVE ? '' : 'rgba(0,0,0,.12)'
        },
        changeSort (column) {
            const params = Object.assign({}, this.pagination)
            if (this.pagination.sortBy === column) {
                params.descending = !this.pagination.descending
            } else {
                params.sortBy = column
                params.descending = false
            }
            this.pagination = params
            store.dispatch('searchOrderDetailList')
        },
        isItemCenter (value) {
            const orderFileImportColumnConfig = this.orderFileImportColumnConfigs.find(
                item => item.column === value
            )
            return orderFileImportColumnConfig === undefined ? true : [_const.ITEM_TYPE.NUM.ID, _const.ITEM_TYPE.DATE.ID].includes(orderFileImportColumnConfig.item_type)
        },
        displayFormat (headerValue, itemValue) {
            const orderFileImportColumnConfig = this.orderFileImportColumnConfigs.find(
                item => item.column === headerValue
            )
            if (orderFileImportColumnConfig.display_formats.includes(_const.DISPLAY_FORMAT.NUM_TREE_DIGITS_COMMA_DELIMITED)) {
                return this.convertIntoCommaDelimited(itemValue)
            }
            return itemValue
        },
        openSettingDisplayFormat (element) {
            const orderFileImportColumnConfig = this.orderFileImportColumnConfigs.find(
                item => item.column === element.value
            )
            const displayFormats = []

            // 項目のタイプ名を取得
            const itemTypeKeys = Object.keys(_const.ITEM_TYPE)
            const itemTypeKey = itemTypeKeys.find(itemTypeKey => _const.ITEM_TYPE[itemTypeKey]['ID'] === orderFileImportColumnConfig.item_type)

            // 取得したタイプに関する表示形式を取得
            for (const [displayFormatKey, displayFormatValue] of Object.entries(_const.DISPLAY_FORMAT)) {
                const end = displayFormatKey.indexOf('_')
                if (![itemTypeKey].includes(displayFormatKey.substr(0, end))) continue
                displayFormats.push(
                    {
                        'id': displayFormatValue,
                        'text': this.$t(`order.order_details.dialog.setting_display_format.item_type_${_const.ITEM_TYPE[itemTypeKey]['ID']}.${_const.PREFIX}${displayFormatValue}`)
                    }
                )
            }
            const itemType = this.$t(`order.order_details.dialog.setting_display_format.item_type_${_const.ITEM_TYPE[itemTypeKey]['ID']}.type_name`)
            const settingDisplayFormatFunction = this.settingDisplayFormats.find(item => item.orderFileImportColumnConfigId === orderFileImportColumnConfig.id)
            const selectedDisplayFormats = settingDisplayFormatFunction === undefined ? orderFileImportColumnConfig.display_formats : settingDisplayFormatFunction.selectedDisplayFormats
            this.$refs.settingDisplayFormatDialog.show(element.text, displayFormats, selectedDisplayFormats, orderFileImportColumnConfig.id, itemType)
        },
        async settingDisplayFormat () {
            try {
                await store.dispatch('settingDisplayFormat', { settingDisplayFormats: this.settingDisplayFormats })
            } catch (error) {
                console.log('error: ', error)
                if (error === 'no_admin_permission') {
                    this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                } else if (error === 'updated_by_others') {
                    this.$refs.alert.show(this.$t('common.message.updated_by_others'))
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }
            }
        },
        clearSettingDisplayFormats () {
            this.settingDisplayFormats = []
        },
        convertIntoCommaDelimited (value) {
            if (value === '') return value
            return Number(value).toLocaleString()
        },
        subject (labelId) {
            return this.labelData[this.displayLangCode][labelId]
        },
        selectAll () {
            this.selectedOrderDetailIds = JSON.parse(JSON.stringify(store.state.processingData.allOrderDetailId))
        },
        cancelSelectAll () {
            this.selectedOrderDetailIds = []
        },
        noDataShow () {
            window.location.href = '/order/order_details/create?order_id=' + this.orderId
        },
        show (id) {
            window.location.href = '/order/order_details/' + id + '/edit'
        },
        async changeStatus (status) {
            let message = ''
            if (status === _const.FLG.ACTIVE) message = this.$t('order.order_details.dialog.status.to_active.text')
            if (status === _const.FLG.INACTIVE) message = this.$t('order.order_details.dialog.status.to_inactive.text')
            if (await this.$refs.confirm.show(message)) {
                try {
                    await store.dispatch('updateOrderDetails', { status: status })
                    await store.dispatch('searchOrderDetailList')
                } catch (error) {
                    console.log('error: ', error)
                    if (error === 'no_admin_permission') {
                        this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                    } else if (error === 'updated_by_others') {
                        this.$refs.alert.show(this.$t('common.message.updated_by_others'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                }
            }
        },
        async createOrderFile() {
            store.commit('setLoading', true)
            const selectedOrderDetailIds = JSON.parse(JSON.stringify(this.selectedOrderDetailIds))
            selectedOrderDetailIds.sort((a, b) => a - b)// 昇順にソート
            const form = Vue.util.extend({}, { order_detail_ids: selectedOrderDetailIds, order_id: this.orderId })

            try {
                const res = await axios.post('/api/order/order_details/create_order_file', form)
                if (res.data.status !== 200) throw res.data.message

                // ファイルのダウンロード
                const a = document.createElement('a')
                a.download = res.data.file_name // ダウンロードを失敗した時に出るファイル名
                a.target   = '_blank' // 別タブに切り替えないようにする
                let uri = '/api/utilities/downloadFromLocal?file_path='
                uri += encodeURIComponent(res.data.file_path) + '&file_name=' + encodeURIComponent(res.data.file_name)
                a.href = uri
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)
            } catch (error) {
                console.log(error)
                if (error === 'no_permission') {
                    this.$refs.alert.show(this.$t('common.message.no_permission'))
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }
            }
            store.commit('setLoading', false)
        },
        selectedItem (item) {
            const orderDetailId = item.order_detail_id
            const selectedOrderDetailIds = JSON.parse(JSON.stringify(this.selectedOrderDetailIds))
            if (!this.selectedOrderDetailIds.includes(orderDetailId)) {
                // add
                selectedOrderDetailIds.push(orderDetailId)
                this.selectedOrderDetailIds = selectedOrderDetailIds
            } else {
                // remove
                this.selectedOrderDetailIds = this.selectedOrderDetailIds.filter(
                    id => id !== orderDetailId
                )
            }
        },
        toggleAll () {
            if (this.selectedOrderDetailIds.length >= this.orderDetails.length) {
                this.selectedOrderDetailIds = []
            } else {
                this.selectedOrderDetailIds = this.orderDetails.map(orderDetail => orderDetail.order_detail_id)
            }
        },
        openHeaderCustomModal () {
            eventHub.$emit('open-header-custom-modal')
        },
        displayStatusName (status) {
            if (status === _const.FLG.ACTIVE) {
                // 有効
                return this.$t('order.order_details.list.status.active')
            } else {
                // 無効
                return this.$t('order.order_details.list.status.inactive')
            }
        },
        displayStatusColor (status) {
            if (status === _const.FLG.ACTIVE) {
                // 有効
                return 'secondary'
            } else {
                // 無効
                return 'red'
            }
        },
        formatColumnsForDisplay: function(columns, filteringColumns = []) {
            const columnTexts = []
            for (const column of Object.values(columns)) {
                // NOTE: 絞り込み条件あり && 項目名が合致しない場合はスキップ
                /*
                 * NOTE: 作業画面への文字列埋め込みに対応する為、
                 *       AG登録業務では表頭文字列を変更しないよう依頼しているのでこの方法が使えるが
                 *       今後どう対応していくかは別途検討が必要
                 */
                if (filteringColumns.length > 0 && filteringColumns.indexOf(this.subject(column.label_id)) === -1) {
                    continue
                }
                columnTexts.push(`${this.subject(column.label_id)} : ${column.value}`)
            }
            return columnTexts.join('\n')
        },
        async getCreateRequestData() {
            store.commit('setLoading', true)
            const form = Vue.util.extend({}, {
                order_detail_ids: JSON.stringify(this.selectedOrderDetailIds),
                label_ids: JSON.stringify(Object.keys(this.labelData[this.displayLangCode])),
            })
            try {
                const res = await axios.post('/api/order/order_details/get_create_request_data', form)
                if (res.data.status !== 200) throw res.data.message
                this.selectedOrderDetailColumns = JSON.parse(JSON.stringify(res.data.order_detail_columns))
                this.selectedOrderDetailNames = JSON.parse(JSON.stringify(res.data.order_detail_names))
                this.labelData = JSON.parse(JSON.stringify(res.data.label_data))
            } catch (error) {
                console.log(error)
                this.$refs.alert.show(this.$t('common.message.internal_error'))
                this.selectedOrderDetailColumns = []
                this.selectedOrderDetailNames = []
            }
            store.commit('setLoading', false)
        },
        openBusinessSelectionDialog: async function() {
            let businessId = await this.$refs.businessSelectionDialog.show()
            if (businessId) {
                await this.getCreateRequestData()
                if (await this.$refs.confirm.show(this.$t('order.order_details.dialog.request_bulk_creation.confirm'))) {
                    this.invalidClickDialog = true
                    store.commit('setLoading', true)
                    const orderDetails = []
                    this.selectedOrderDetailIds.forEach(orderDetailId => {
                        let body = ''
                        this.$refs.editor.$data.content = ''
                        // NOTE: 最終的には業務単位にデフォルトフォーマット設定をDB管理できるようにする
                        if ([12, 14].indexOf(businessId) !== -1) {
                            let filteringColumns = ['サイト名','リリース日','Q','トリガー','納品期限','予想メニュー数','媒体資料URL','媒体資料ファイル名','チェックリストURL','AG媒体資料紐づけ']
                            let columnsText = this.formatColumnsForDisplay(this.selectedOrderDetailColumns[orderDetailId], filteringColumns)
                            body = 'ご担当者様\n\nお疲れ様です。\n下記、AGの登録と媒体資料の紐づけをお願いします。\n\n' + columnsText
                        } else if ([13, 15].indexOf(businessId) !== -1) {
                            let filteringColumns = ['サイト名','リリース日','Q','トリガー','納品期限','予想メニュー数','媒体資料URL','媒体資料ファイル名','チェックリストURL','Xone媒体資料紐づけ']
                            let columnsText = this.formatColumnsForDisplay(this.selectedOrderDetailColumns[orderDetailId], filteringColumns)
                            body = 'ご担当者様\n\nお疲れ様です。\n下記、Xone紐づけをお願いします。\n\n' + columnsText
                        } else {
                            body = this.formatColumnsForDisplay(this.selectedOrderDetailColumns[orderDetailId])
                        }
                        this.$refs.editor.insertText(body)
                        orderDetails.push({
                            id: orderDetailId,
                            subject: this.selectedOrderDetailNames[orderDetailId],
                            body: this.$refs.editor.$data.content
                        })
                    })
                    try {
                        const formData = new FormData()
                        formData.append('order_id', this.orderId)
                        formData.append('order_details', JSON.stringify(orderDetails))
                        formData.append('business_id', businessId)
                        const res = await axios.post('/api/order/order_details/bulk_create_requests', formData)
                        this.invalidClickDialog = false
                        if (res.data.status !== 200) throw res.data.message
                        this.$refs.alert.show(
                            this.$t('order.order_details.dialog.request_bulk_creation.success'),
                            () => store.dispatch('searchOrderDetailList')
                        )
                    } catch (error) {
                        this.invalidClickDialog = false
                        console.log({error})
                        if (error === 'no_admin_permission') {
                            this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                        } else {
                            this.$refs.alert.show(
                                this.$t('order.order_details.dialog.request_bulk_creation.failure')
                            )
                        }
                    }
                    store.commit('setLoading', false)
                }
            }
        },
    },
}
</script>

<style scoped>
.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

#search-list >>> div.v-input--selection-controls__ripple.accent--text,
#search-list >>> i.v-icon.material-icons.theme--light.accent--text {
    color: rgba(0,0,0,.54) !important;
    caret-color: rgba(0,0,0,.54) !important;
}
.v-datatable thead th.column.sortable .v-icon.table-herder-icon {
    opacity: 1;
    transform: rotate(0);
}
</style>
