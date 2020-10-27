<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('approvals.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <approval-search-condition></approval-search-condition>
                </v-flex>
                <v-flex xs12>
                    <approval-search-list></approval-search-list>
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
import ApprovalSearchCondition from '../../../Organisms/Admin/Approvals/ApprovalSearchCondition'
// 検索結果
import ApprovalSearchList from '../../../Organisms/Admin/Approvals/ApprovalSearchList'

import store from '../../../../stores/Admin/Approvals/store'

export default {
    props: {
        inputs: { type: Object }
    },
    components:{
        ApprovalSearchCondition,
        ApprovalSearchList
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
            const convertedApprovalStatuses = this.inputs.approval_status? this.inputs.approval_status.map(approval_status => Number(approval_status)): []
            if (convertedApprovalStatuses.indexOf(_const.APPROVAL_STATUS.NONE) != -1) {
                status.push({ text: Vue.i18n.translate('approvals.list.approval_status.none'), value: _const.APPROVAL_STATUS.NONE })
            }
            if (convertedApprovalStatuses.indexOf(_const.APPROVAL_STATUS.ON) != -1) {
                status.push({ text: Vue.i18n.translate('approvals.list.approval_status.on'), value: _const.APPROVAL_STATUS.ON })
            }
            if (convertedApprovalStatuses.indexOf(_const.APPROVAL_STATUS.DONE) != -1) {
                status.push({ text: Vue.i18n.translate('approvals.list.approval_status.approval'), value: _const.APPROVAL_STATUS.APPROVAL })
            }

            Object.assign(this.inputs, {approval_status: status})
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
