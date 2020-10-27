<template>
    <v-app>
        <app-menu :drawer="drawer"></app-menu>
        <app-header :title="$t('order.order_details.list.title')"></app-header>
        <v-content id="list">
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <v-flex xs12>
                        <order-info :orderId="order_id"></order-info>
                    </v-flex>
                    <!-- Main -->
                    <v-flex xs12>
                        <order-detail-search-condition :orderId="order_id"></order-detail-search-condition>
                    </v-flex>
                    <v-flex xs12>
                        <order-detail-search-list :orderId="order_id"></order-detail-search-list>
                    </v-flex>
                    <!-- Main -->
                </v-layout>
            </v-container>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import OrderDetailSearchCondition from '../../../Organisms/Order/OrderDetails/OrderDetailSearchCondition'
import OrderDetailSearchList from '../../../Organisms/Order/OrderDetails/OrderDetailSearchList'
import OrderInfo from '../../../Organisms/Order/OrderDetails/OrderInfo'

export default {
    props: {
        order_id: { type: Number, required: true }
    },
    components: {
        OrderDetailSearchCondition,
        OrderDetailSearchList,
        OrderInfo,
    },
    data: () => ({
        drawer: null,
    }),
}
</script>
