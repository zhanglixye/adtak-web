<template>
    <v-app>
        <app-menu drawer></app-menu>
        <app-header :title="$t('order.orders.list.title')"></app-header>
        <v-content id="list">
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <!-- Main -->
                    <v-flex xs12>
                        <order-search-condition></order-search-condition>
                    </v-flex>
                    <v-flex xs12>
                        <order-search-list></order-search-list>
                    </v-flex>
                    <!-- Main -->
                </v-layout>
            </v-container>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import OrderSearchCondition from '../../../Organisms/Order/Orders/OrderSearchCondition'
import OrderSearchList from '../../../Organisms/Order/Orders/OrderSearchList'

export default {
    components: {
        OrderSearchCondition,
        OrderSearchList,
    },
}
</script>
