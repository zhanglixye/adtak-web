<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('education_allocations.list.title')"></app-header>
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
import AllocationSearchCondition from '../../../Organisms/Admin/EducationAllocations/AllocationSearchCondition'
import AllocationSearchList from '../../../Organisms/Admin/EducationAllocations/AllocationSearchList'

import store from '../../../../stores/Admin/Allocations/store'

export default {
    components:{
        AllocationSearchCondition,
        AllocationSearchList
    },
    props: {
        inputs: { type: Object }
    },
    data: ()=> ({
        drawer: null,
    }),
    created () {
        // メインカラー変更[primary ⇔ accent]
        const primaryColor = this.$vuetify.theme.primary
        this.$vuetify.theme.primary = this.$vuetify.theme.accent
        this.$vuetify.theme.accent = primaryColor

        if (this.inputs) {
            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新
            store.commit('resetSearchParams')

            // 条件の設定
            const status = []
            const convertedStatus = this.inputs.status? this.inputs.status.map(status => Number(status)): []
            if (convertedStatus.indexOf(_const.ALLOCATION_STATUS.NONE) != -1) {
                status.push({ text: Vue.i18n.translate('education_allocations.list.status.none'), value: _const.ALLOCATION_STATUS.NONE })
            }
            if (convertedStatus.indexOf(_const.ALLOCATION_STATUS.DONE) != -1) {
                status.push({ text: Vue.i18n.translate('education_allocations.list.status.done'), value: _const.ALLOCATION_STATUS.DONE })
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
