<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('works.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <work-search-condition></work-search-condition>
                </v-flex>
                <v-flex xs12>
                    <work-search-list></work-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import WorkSearchCondition from '../../../Organisms/Admin/Works/WorkSearchCondition'
import WorkSearchList from '../../../Organisms/Admin/Works/WorkSearchList'

import store from '../../../../stores/Admin/Works/store'

export default {
    props: {
        inputs: { type: Function }
    },
    components:{
        WorkSearchCondition,
        WorkSearchList
    },
    data: ()=> ({
        drawer: null,
    }),
    created () {
        const params = this.inputs()
        if (params) {
            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新

            // 条件の設定
            const status = []
            const convertedStatus = params.status ? params.status.map(status => Number(status)): []
            if (convertedStatus.indexOf(_const.TASK_STATUS.NONE) != -1) {
                status.push({ text: Vue.i18n.translate('works.list.task_status.none'), value: _const.TASK_STATUS.NONE })
            }
            if (convertedStatus.indexOf(_const.TASK_STATUS.ON) != -1) {
                status.push({ text: Vue.i18n.translate('works.list.task_status.on'), value: _const.TASK_STATUS.ON })
            }
            if (convertedStatus.indexOf(_const.TASK_STATUS.DONE) != -1) {
                status.push({ text: Vue.i18n.translate('works.list.task_status.done'), value: _const.TASK_STATUS.DONE })
            }
            Object.assign(params, {status: status})

            const approvalStatus = []
            const convertedApprovalStatus = params['approval_status'] ? params['approval_status'].map(status => Number(status)): []
            if (convertedApprovalStatus.indexOf(_const.APPROVAL_STATUS.NONE) != -1) {
                approvalStatus.push({ text: Vue.i18n.translate('works.list.approval_status.none'), value: _const.APPROVAL_STATUS.NONE })
            }
            if (convertedApprovalStatus.indexOf(_const.APPROVAL_STATUS.ON) != -1) {
                approvalStatus.push({ text: Vue.i18n.translate('works.list.approval_status.on'), value: _const.APPROVAL_STATUS.ON })
            }
            Object.assign(params, {approval_status: approvalStatus})

            store.commit('resetSearchParams')
            store.commit('setSearchParams', { params: params })
        } else {
            // メニューからの遷移
            const params = JSON.parse(JSON.stringify(store.state.searchParams))
            if (params.request_work_ids) params.request_work_ids = []
            store.commit('setSearchParams', { params: params })
        }
    }
}
</script>
