<template>
    <div>
        <div class="text-cut headline">
            {{ $t('order.order_details.order_info.order.name') }}:
            <v-tooltip top>
                <a v-if="isAdmin" slot="activator" @click="openOrderSetting()">
                    {{ orderName }}
                </a>
                <span v-else slot="activator">{{ orderName }}</span>
                <span>{{ orderName }}</span>
            </v-tooltip>
        </div>
    </div>
</template>

<script>
import store from '../../../../stores/Order/OrderDetails/store'

export default {
    props: {
        orderId: { type: Number, required: true }
    },
    computed: {
        isAdmin () {
            return store.state.processingData.isAdmin
        },
        orderName() {
            return store.state.processingData.orderName
        }
    },
    methods: {
        openOrderSetting () {
            window.location.href = '/order/orders/' + this.orderId + '/edit'
        }
    },
}
</script>

<style scoped>
.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
</style>
