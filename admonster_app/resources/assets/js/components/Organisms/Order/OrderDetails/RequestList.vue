<template>
    <div class="elevation-1">
        <v-data-table
            :headers="headers"
            :items="requests"
            hide-actions
            :no-data-text="$t('order.order_details.show.request_list.no_data_text')"
        >
            <template slot="items" slot-scope="props">
                <tr
                    @click="goToRequestDetailPage(props.item.id, props.item.admin_flg)"
                    :style="tableTrStyle(props.item.admin_flg)"
                >
                    <td class="text-xs-center">{{ requestId(props.item.id) }}</td>
                    <td class="text-xs-center">{{ props.item.name }}</td>
                    <td class="text-xs-center">{{ props.item.business_name }}</td>
                    <td class="text-xs-center">{{ requestStatus(props.item.status) }}</td>
                    <td class="text-xs-center">{{ props.item.deadline }}</td>
                </tr>
            </template>
        </v-data-table>
    </div>
</template>

<script>
import requestInfoStore from '../../../../stores/Order/OrderDetails/Show/request-info'

export default {
    props: {
        orderDetailId: { type: Number, required: true },
    },
    created () {
        requestInfoStore.dispatch('searchRequestsInfo', {
            'order_detail_id': this.orderDetailId
        })
    },
    methods: {
        requestId (requestId) {
            return 'r' + requestId
        },
        tableTrStyle (isAdmin) {
            if (isAdmin) {
                return { cursor: 'pointer' }
            }
            return {}
        },
        goToRequestDetailPage (requestId, isAdmin) {
            // 業務の管理者であれば遷移可
            if (isAdmin) {
                window.location.href = '/management/requests/' + requestId
            }
        },
        requestStatus (statusCode) {
            switch (statusCode) {
            case _const.REQUEST_STATUS.DOING:
                return this.$t('list.column.wip')
            case _const.REQUEST_STATUS.FINISH:
                return this.$t('list.column.completed')
            case _const.REQUEST_STATUS.EXCEPT:
                return this.$t('list.column.excluded')
            }
            return ''
        }
    },
    computed: {
        requests () {
            return requestInfoStore.state.requests
        },
        headers () {
            return [
                { text: this.$t('list.column.request_id'), value: 'id', align: 'center', sortable: true },
                { text: this.$t('list.column.subject'), value: 'name', align: 'center', sortable: true },
                { text: this.$t('list.column.business_name'), value: 'business_name', align: 'center', sortable: true },
                { text: this.$t('list.column.status'), value: 'status', align: 'center', sortable: true },
                { text: this.$t('list.column.deadline'), value: 'deadline', align: 'center', sortable: true },
            ]
        },
    },
}
</script>
