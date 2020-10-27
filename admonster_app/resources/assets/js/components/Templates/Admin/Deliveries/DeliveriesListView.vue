<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('deliveries.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <deliveries-search-condition></deliveries-search-condition>
                </v-flex>
                <v-flex xs12>
                    <deliveries-search-list></deliveries-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
// 検索条件(search criteria)
import DeliveriesSearchCondition from '../../../Organisms/Admin/Deliveries/DeliveriesSearchCondition'
// 検索結果
import DeliveriesSearchList from '../../../Organisms/Admin/Deliveries/DeliveriesSearchList'

import store from '../../../../stores/Admin/Deliveries/store'

export default {
    props: {
        inputs: { type: Object }
    },
    components:{
        DeliveriesSearchCondition,
        DeliveriesSearchList
    },
    data: ()=> ({
        drawer: null,
    }),
    created () {
        if (this.inputs) {

            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新
            store.commit('resetSearchParams')

            // 条件の設定
            const status = []
            const convertedStatus = this.inputs.status? this.inputs.status.map(status => Number(status)): []
            if (convertedStatus.indexOf(_const.ALLOCATION_STATUS.NONE) != -1) {
                status.push({ text: Vue.i18n.translate('allocations.list.status.none'), value: _const.ALLOCATION_STATUS.NONE })
            }
            if (convertedStatus.indexOf(_const.ALLOCATION_STATUS.DONE) != -1) {
                status.push({ text: Vue.i18n.translate('allocations.list.status.done'), value: _const.ALLOCATION_STATUS.DONE })
            }
            if (convertedStatus.indexOf(_const.DELIVERY_STATUS.SCHEDULED) != -1) {
                status.push({ text: this.$t('deliveries.list.status.before'), value: _const.DELIVERY_STATUS.SCHEDULED })
            }
            Object.assign(this.inputs, {delivery_status: status})
            store.commit('setSearchParams', { params: this.inputs })

        } else {
            // メニューからの遷移
            const params = JSON.parse(JSON.stringify(store.state.searchParams))
            if (params.request_work_ids) params.request_work_ids = []
            store.commit('setSearchParams', { params: params })
        }
    }
}
</script>
