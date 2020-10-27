<template>
    <!-- 作業一覧モーダル -->
    <div id="work-list-modal-block">
        <v-layout row justify-center>
            <v-dialog id="searchDialog" fullscreen  transition="dialog-bottom-transition" v-model="modal">
                <v-card>
                    <v-card-title>
                            <v-toolbar
                                :clipped-left="$vuetify.breakpoint.lgAndUp"
                                color="primary"
                                dark
                                app
                                fixed
                                flat
                                dense
                                id="modal_header"
                                class="teal"
                            >
                                <v-toolbar-title>
                                    <v-btn color="white" dark flat @click="close"><v-icon>close</v-icon></v-btn>
                                    <span class="hidden-sm-and-down">{{ $t('works.list.title') }}</span>
                                </v-toolbar-title>
                                <v-spacer></v-spacer>
                            </v-toolbar>
                    </v-card-title>
                    <v-card-text class="pt-4">
                        <div class="data-content">
                            <!-- table -->
                            <v-data-table
                                :headers="headers"
                                :items="items"
                                :pagination.sync="pagination"
                                :total-items="paginate.data_count_total"
                                hide-actions
                                must-sort
                                class="elevation-1"
                            >
                                <template slot="headers" slot-scope="props">
                                    <tr>
                                        <th
                                            v-for="(header,i) in props.headers"
                                            :key="i"
                                            :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                                            :style="{ width: header.width }"
                                            @click="changeSort(header.value)"
                                        >
                                            {{ header.text }}
                                            <v-icon small>arrow_drop_up</v-icon>
                                        </th>
                                    </tr>
                                </template>
                                <template slot="items" slot-scope="props">
                                    <tr @click="goToRequestPage(props.item.request_id)" style="cursor:pointer;">
                                        <template v-for="(header,i) in headers">
                                            <td
                                                v-if="'client_name' === header.value" :key="i"
                                                class="text-xs-left"
                                            >
                                                {{ displayClientName(props.item.client_name) }}
                                            </td>
                                            <td
                                                v-else-if="'request_work_name' === header.value" :key="i"
                                                class="text-xs-left overflow"
                                                style="min-width: 400px;"
                                            >
                                                <v-tooltip top>
                                                    <span slot="activator">{{ props.item.request_work_name }}</span>
                                                    <span>{{ props.item.request_work_name }}</span>
                                                </v-tooltip>
                                            </td>
                                            <td
                                                v-else-if="'user_id' === header.value" :key="i"
                                                class="text-xs-center"
                                            >
                                                <users-overview
                                                    :users="workerUsers(props.item)"
                                                    :candidates="workerCandidates(props.item)"
                                                    ></users-overview>
                                            </td>
                                            <td
                                                v-else-if="'status' === header.value" :key="i"
                                                class="text-xs-center"
                                            >
                                                <v-chip small outline label disabled :color="displayStatusColor(props.item.status)" class="cursor-pointer">
                                                    {{ displayStatusName(props.item.status) }}
                                                </v-chip>
                                            </td>
                                            <td
                                                v-else-if="'task_created_at' === header.value" :key="i"
                                                class="text-xs-center"
                                            >
                                                {{ props.item.task_created_at | formatDateYmdHm(true) }}
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                                <template slot="no-data">
                                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                                </template>
                            </v-data-table>
                            <!-- table -->
                            <progress-circular v-if="loading"></progress-circular>
                            <alert-dialog ref="alert"></alert-dialog>
                        </div>
                    </v-card-text>
                    <v-card-actions>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>
    </div>
    <!-- /作業一覧モーダル -->
</template>

<script>
import UsersOverview from '../../Molecules/Users/UsersOverview'
import ProgressCircular from '../Progress/ProgressCircular'
import AlertDialog from '../Dialogs/AlertDialog'

export default {
    components: {
        UsersOverview,
        ProgressCircular,
        AlertDialog
    },
    data: () => ({
        modal: false,
        loading: false,
        pagination: {
            sortBy:'',
            descending: false,
            rowsPerPage: -1,
        },
        paginate:{
            data_count_total: 0
        },
        searchParams: {},
        works: [],
        items: []
    }),
    computed: {
        headers: function () {
            return [
                { text: Vue.i18n.translate('list.column.client_name'), value: 'client_name', align: 'center', width: '150px' },
                { text: Vue.i18n.translate('list.column.subject'), value: 'request_work_name', align: 'center', width: '400px' },
                { text: Vue.i18n.translate('list.column.worker'), value: 'user_id', align: 'center', width: '100px' },
                { text: Vue.i18n.translate('list.column.status'), value: 'status', align: 'center', width: '100px' },
                { text: Vue.i18n.translate('list.column.task_created_at'), value: 'task_created_at', align: 'center', width: '100px' },
            ]
        }
    },
    methods: {
        goToRequestPage (requestId) {
            window.location.href = this.requestDetailUri(requestId)
        },
        getWorkList (requestWorkId) {
            const params = this.searchParams
            params.request_work_ids = [requestWorkId]
            // データ取得
            this.loading = true
            axios.post('/api/works/getWorkList', params)
                .then((res) => {
                    this.works[requestWorkId] = this.items = res.data.list
                }).catch((error) => {
                    console.log(error)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }).finally(() => {
                    this.loading = false
                })
        },
        setWorkList (requestWorkId) {
            // 取得済データを参照
            if (this.works[requestWorkId]) {
                this.items = this.works[requestWorkId]
                return false
            }
            // 作業一覧を取得する
            this.getWorkList(requestWorkId)
        },
        show (requestWorkId, params = {}) {
            this.searchParams = params
            this.setWorkList(requestWorkId)
            this.modal = true
        },
        close: function () {
            this.modal = false
            this.items = []
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
        workerUsers (item) {
            const users = {
                allocated_user_ids: [item.user_id],
                completed_user_ids: [],
            }
            return users
        },
        workerCandidates (item) {
            if (item.user_id != null) {
                return [{
                    id: item.user_id,
                    name: item.user_name,
                    user_image_path: item.user_image_path
                }]

            } else {
                return []
            }
        },
        requestDetailUri (id) {
            let uri = '/management/requests/' + id
            return uri
        },
        displayStatusName (status) {
            let name = ''
            switch (status) {
            case _const.TASK_STATUS.NONE:
                name = Vue.i18n.translate('works.list.task_status.none')
                break
            case _const.TASK_STATUS.ON:
                name = Vue.i18n.translate('works.list.task_status.on')
                break
            case _const.TASK_STATUS.DONE:
                name = Vue.i18n.translate('works.list.task_status.done')
                break
            }
            return name
        },
        displayStatusColor (status) {
            let color = ''
            switch (status) {
            case _const.TASK_STATUS.NONE:
                color = 'red'
                break
            case _const.TASK_STATUS.ON:
                color = 'blue'
                break
            case _const.TASK_STATUS.DONE:
                color = 'secondary'
                break
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
        },
    }
}
</script>

<style scoped>
.cursor-pointer >>> span {
    cursor: pointer;
}
.data-content {
    position: relative;
}
</style>
