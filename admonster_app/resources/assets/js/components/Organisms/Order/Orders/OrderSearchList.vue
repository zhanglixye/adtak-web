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
            <v-tooltip top style="max-width: 40px;">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    @click="openFileImportDialog()"
                >
                    <v-icon>add_box</v-icon>
                </v-btn>
                <span>{{ $t('imported_files.import') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderIds.length !== 0"
                    @click="changeStatus(1)"
                >
                    <v-icon>mdi-delete-restore</v-icon>
                </v-btn>
                <span>{{ $t('order.orders.list.to_active') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;" v-if="isAdmin">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderIds.length !== 0"
                    @click="changeStatus(0)"
                >
                    <v-icon>delete</v-icon>
                </v-btn>
                <span>{{ $t('order.orders.list.to_inactive') }}</span>
            </v-tooltip>
            <v-tooltip top style="max-width: 40px;">
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    v-if="selectedOrderIds.length !== 0"
                    @click="createEachOrderFIle()"
                >
                    <v-icon>mdi-cloud-download</v-icon>
                </v-btn>
                <span>{{ $t('order.orders.list.dl_order_file') }}</span>
            </v-tooltip>
            <div v-show="selectedOrderIds.length > 0" class="px-3">
                <span v-if="selectedOrderIds.length === allOrderId.length">
                    <span>{{ $t('list.selecting_all_search_result', {'count': paginate.data_count_total}) }}</span>
                    <span class="pr-2">／</span>
                    <a @click="cancelSelectAll()">
                        <span>{{ $t('list.deselection') }}</span>
                    </a>
                </span>
                <span v-else>
                    <span>{{ $t('list.selecting_count', {'count': selectedOrderIds.length}) }}</span>
                    <span class="pr-2">／</span>
                    <a @click="selectAll()">
                        <span>{{ $t('list.select_all_search_result', {'count': paginate.data_count_total}) }}</span>
                    </a>
                </span>
            </div>
            <v-spacer></v-spacer>
            <v-tooltip top>
                <v-btn
                    flat
                    icon
                    color="primary"
                    slot="activator"
                    @click="openHeaderCustomModal()"
                >
                    <v-icon>settings</v-icon>
                </v-btn>
                <span>{{ $t('list.config') }}</span>
            </v-tooltip>
        </v-layout>
        <!-- toolbar -->
        <div class="data-content">
            <!-- table -->
            <v-data-table
                v-model="selectedOrders"
                :headers="shownHeaders"
                :items="orders"
                :pagination.sync="pagination"
                :total-items="paginate.data_count_total"
                item-key="order_id"
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
                            :key="header.text"
                            :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                            :style="{ 'min-width': header.width + 'px' }"
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
                        :style="{ 'background-color' : getBackgroundColorByStatus(props.item.status), 'cursor' : 'pointer' }"
                        @click="goToOrderPage(props.item.order_id)"
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
                                v-if="'imported_at' === header.value"
                                :key="header.value"
                                class="text-xs-center"
                            >
                                {{ props.item[header.value] | formatDateYmdHm(true) }}
                            </td>
                            <td
                                v-if="'order_name' === header.value"
                                :key="header.value"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <a class="text-cut" v-if="isOrderAdmin(props.item.order_id)" @click.stop slot="activator" @click="openOrderSetting(props.item.order_id)">
                                        {{ props.item[header.value] }}
                                    </a>
                                    <span v-else slot="activator">{{ props.item[header.value] }}</span>
                                    <span>{{ props.item[header.value] }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-if="'imported_file_name' === header.value"
                                :key="header.value"
                                class="text-xs-left overflow"
                            >
                                <v-tooltip top>
                                    <span slot="activator">{{ props.item[header.value] }}</span>
                                    <span>{{ props.item[header.value] }}</span>
                                </v-tooltip>
                            </td>
                            <td
                                v-else-if="'order_id' === header.value"
                                :key="header.value"
                                class="text-xs-center"
                            >
                                {{ props.item[header.value] }}
                            </td>
                            <td
                                v-else-if="'importer_id' === header.value"
                                :key="header.value"
                                class="text-xs-center"
                            >
                                <users-overview
                                    :users="importerUsers(props.item)"
                                    :candidates="candidates"
                                ></users-overview>
                            </td>
                            <td
                                v-else-if="'importer_id_no_image' === header.value"
                                :key="header.value"
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
                                >
                                    {{ displayStatusName(props.item[header.value]) }}
                                </v-chip>
                            </td>
                        </template>
                    </tr>
                </template>
                <template slot="no-data">
                    <div class="text-xs-center">
                        {{ $t('common.pagination.no_data') }}
                    </div>
                </template>
            </v-data-table>
            <!-- table -->
            <!-- pagination -->
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
                    <div>
                        {{ $t('common.pagination.all') + paginate.data_count_total + $t('common.pagination.items') + paginate.page_from + $t('common.pagination.from') + '～' + paginate.page_to + $t('common.pagination.to') }}
                    </div>
                    <v-pagination
                        v-model="page"
                        :length="paginate.page_count"
                        circle
                        :total-visible="5"
                    ></v-pagination>
                </v-layout>
            </v-container>
            <!-- pagination -->
        </div>
        <progress-circular v-if="loading"></progress-circular>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <file-import-dialog
            :is-file-import.sync="isFileImport"
            :file-import-dialog.sync="fileImportDialog"
            :import-method-selection-dialog.sync="importMethodSelectionDialog"
            v-if="fileImportDialog"
        ></file-import-dialog>
        <import-method-selection-dialog
            :file-import-dialog.sync="importMethodSelectionDialog"
            :import-method-selection-dialog.sync="fileImportDialog"
            :isFileImport="isFileImport"
            v-if="importMethodSelectionDialog"
            isOrderList
        ></import-method-selection-dialog>
    </div>
</template>

<script>
import Sortable from 'sortablejs'
import HeaderCustomModal from '../../Common/HeaderCustomModal'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import UsersNameDisplay from '../../../Molecules/Users/UsersNameDisplay'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import store from '../../../../stores/Order/Orders/store'
import importedFilesStore from '../../../../stores/Admin/ImportedFiles/store'
import FileImportDialog from '../../Admin/ImportedFiles/FileImportDialog'
import ImportMethodSelectionDialog from '../../Admin/ImportedFiles/ImportMethodSelectionDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'

export default {
    components: {
        HeaderCustomModal,
        UsersOverview,
        UsersNameDisplay,
        ConfirmDialog,
        FileImportDialog,
        ImportMethodSelectionDialog,
        ProgressCircular,
        AlertDialog
    },
    data: () => ({
        fileImportDialog: false,
        importMethodSelectionDialog: false,
        isFileImport: false,
    }),
    computed: {
        initialHeaders () {
            return store.getters.initialHeaders
        },
        pagination: {
            set(val) {
                store.commit('setSearchParams', { sort_by: val.sortBy, descending: val.descending, rows_per_page: val.rowsPerPage })
            },
            get() {
                return {
                    sortBy: store.state.searchParams.sort_by,
                    descending: store.state.searchParams.descending,
                    rowsPerPage: store.state.searchParams.rows_per_page,
                }
            }
        },
        selectedOrderIds: {
            set (ids) {
                store.commit('setSelectedOrderIds', ids)
            },
            get () {
                return JSON.parse(JSON.stringify(store.state.processingData.selectedOrderIds))
            }
        },
        selectedOrders () {
            return this.orders.filter(order => this.selectedOrderIds.includes(order.order_id))
        },
        allOrderId () {
            return store.state.processingData.allOrderId
        },
        allAdminOrderId () {
            return store.state.processingData.allAdminOrderId
        },
        loading () {
            return store.state.processingData.loading
        },
        orders () {
            return store.state.processingData.orders
        },
        candidates () {
            return store.state.processingData.candidates
        },
        shownHeaders () {
            return store.state.showHeaders
        },
        hiddenHeaders () {
            return store.state.hiddenHeaders
        },
        rowsCandidatesPerPage () {
            return [20, 50, 100]
        },
        paginate () {
            return store.state.paginate
        },
        rowsPerPage: {
            set(rowsPerPage) {
                store.commit('setSearchParams', { rows_per_page : rowsPerPage })
                this.page = 1
            },
            get() {
                return store.state.searchParams.rows_per_page
            },
        },
        page: {
            set(page) {
                // 再検索
                store.commit('setSearchParams', { page : page })
                store.dispatch('searchOrderList')
            },
            get() {
                return store.state.searchParams.page
            },
        },
        isAdmin () {
            return store.state.processingData.isAdmin
        },
    },
    watch: {
        searchParams: {
            handler: function (val) {
                store.commit('setSearchParams', val)
            },
            deep: true
        },
    },
    created () {
        const initialSearchParams = Object.assign({}, store.getters.searchParams, { page: store.state.searchParams.page })
        store.commit('setSearchParams', initialSearchParams)
        store.dispatch('searchOrderList')

        eventHub.$on('commitHeaderCustomData', function (data) {
            store.commit('setShowHeaders', { params: data.showHeaders })
            store.commit('setHiddenHeaders', { params: data.hiddenHeaders })
        })

        const self = this
        eventHub.$on('changeRowsPerPage', function(data) {
            self.rowsPerPage = data.rowsPerPage
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
                    // チェックボックスを追加しているので-1
                    const headerSelected = headers.splice(oldIndex - 1, 1)[0]
                    // チェックボックスを追加しているので-1
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
            store.dispatch('searchOrderList')
        },
        createOrderDetailListUri (id) {
            return '/order/order_details?order_id=' + id
        },
        selectAll () {
            this.selectedOrderIds = JSON.parse(JSON.stringify(store.state.processingData.allOrderId))
        },
        cancelSelectAll () {
            this.selectedOrderIds = []
        },
        openFileImportDialog () {
            importedFilesStore.commit('resetUploadFile')
            this.fileImportDialog = true
        },
        goToOrderPage (orderId) {
            window.location.href = this.createOrderDetailListUri(orderId)
        },
        openOrderSetting (orderId) {
            window.location.href = '/order/orders/' + orderId + '/edit'
        },
        async changeStatus (status) {
            const messages = []
            if (status === _const.FLG.ACTIVE) messages.push(Vue.i18n.translate('order.orders.dialog.status.to_active.text'))
            if (status === _const.FLG.INACTIVE) messages.push(Vue.i18n.translate('order.orders.dialog.status.to_inactive.text'))
            messages.push(this.$t('order.orders.dialog.status.reference_permission_only_granted_order_status_unchangable'))
            const message = messages.join('<br>')
            if (await this.$refs.confirm.show(message)) {
                try {
                    await store.dispatch('updateOrders', { status: status })
                    await store.dispatch('searchOrderList')
                } catch (error) {
                    if (error === 'updated_by_others') {
                        this.$refs.alert.show(this.$t('common.message.updated_by_others'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                }
            }
        },
        async createEachOrderFIle () {
            try {
                await store.dispatch('createEachOrderFIle')
            } catch (error) {
                if (error === 'no_permission') {
                    this.$refs.alert.show(this.$t('order.orders.list.changed_permission'))
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }
            }
        },
        selectedItem (item) {
            const orderId = item.order_id
            const selectedOrderIds = JSON.parse(JSON.stringify(this.selectedOrderIds))
            if (!this.selectedOrderIds.includes(orderId)) {
                // add
                selectedOrderIds.push(orderId)
                // 画面で変更を検知させるために代入
                this.selectedOrderIds = selectedOrderIds
            } else {
                // remove
                this.selectedOrderIds = this.selectedOrderIds.filter(
                    id => id !== orderId
                )
            }
        },
        toggleAll () {
            if (this.selectedOrderIds.length >= this.orders.length) {
                this.selectedOrderIds = []
            } else {
                this.selectedOrderIds = this.orders.map(order => order.order_id)
            }
        },
        importerUsers (item) {
            return {
                allocated_user_ids: item.importer_id ? [item.importer_id] : [],
                completed_user_ids: [],
            }
        },
        openHeaderCustomModal () {
            eventHub.$emit('open-header-custom-modal')
        },
        displayStatusName (status) {
            if (status === _const.FLG.ACTIVE) {
                // 有効
                return Vue.i18n.translate('order.orders.list.status.active')
            } else {
                // 無効
                return Vue.i18n.translate('order.orders.list.status.inactive')
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
        isOrderAdmin (orderId) {
            return this.allAdminOrderId.includes(orderId)
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
</style>
