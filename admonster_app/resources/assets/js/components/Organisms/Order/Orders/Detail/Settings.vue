<template>
    <div id="settings">
        <v-text-field
            :label="$t('order.orders.setting.order_name')"
            v-model="orderName"
            counter="256"
            :rules="rules.orderName"
        ></v-text-field>
        <a @click="openOrderItemEdit()">{{ $t('order.orders.setting.link_edit_item_name') }}</a>
        <custom-status-setting :orderId="orderId"></custom-status-setting>
    </div>
</template>

<script>
import CustomStatusSetting from './CustomStatusSetting'
import store from '../../../../../stores/Order/Orders/Detail/store'

export default {
    components: {
        CustomStatusSetting,
    },
    props: {
        orderId: { type: String, required: true },
    },
    data: () => ({
        rules: {
            orderName: [
                v => v.length !== 0 || Vue.i18n.translate('order.orders.setting.error.no_order_name'),// min
                v => v.length <= 256 || Vue.i18n.translate('order.orders.setting.error.limit_order_name', {number: 256})// max
            ]
        }
    }),
    computed: {
        orderName: {
            set(val) {
                store.dispatch('updateOrderName', val)
            },
            get() {
                return store.state.processingData.orderName
            },
        },
    },
    methods: {
        openOrderItemEdit () {
            window.location.href = '/order/orders/' + this.orderId + '/item/edit/'
        }
    },
};
</script>

<style scoped>
#business-overview {
    background-color: #ffffff;
}
#business-overview-main {
    height: 350px;
}
</style>
