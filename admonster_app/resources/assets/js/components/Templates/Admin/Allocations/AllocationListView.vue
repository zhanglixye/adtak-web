<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('allocations.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <allocation-search-condition></allocation-search-condition>
                </v-flex>
                <v-flex xs12>
                    <allocation-search-list></allocation-search-list>
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
import AllocationSearchCondition from '../../../Organisms/Admin/Allocations/AllocationSearchCondition'
// 検索結果
import AllocationSearchList from '../../../Organisms/Admin/Allocations/AllocationSearchList'

import store from '../../../../stores/Admin/Allocations/store'

export default {
    props: {
        inputs: { type: Object }
    },
    components:{
        AllocationSearchCondition,
        AllocationSearchList
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
            Object.assign(this.inputs, {status: status})
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
