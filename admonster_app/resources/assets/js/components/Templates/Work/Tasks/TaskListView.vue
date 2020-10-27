<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('tasks.index.h1')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <task-search-condition></task-search-condition>
                </v-flex>
                <v-flex xs12>
                    <task-search-list></task-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import TaskSearchCondition from '../../../Organisms/Work/Tasks/TaskSearchCondition.vue';
import TaskSearchList from '../../../Organisms/Work/Tasks/TaskSearchList.vue';

import store from '../../../../stores/Work/Tasks/store.js'

export default {
    props: {
        inputs: { type: Object }
    },
    components: {
        TaskSearchCondition,
        TaskSearchList
    },
    data: () => ({
        drawer: null,
    }),
    created () {
        if (this.inputs) {
            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新
            store.commit('resetSearchParams')

            // 条件の設定
            const status = []
            const convertedStatuses = this.inputs.status? this.inputs.status.map(status => Number(status)): []

            if (convertedStatuses.indexOf(_const.TASK_STATUS.NONE) != -1) {
                status.push(_const.TASK_STATUS.NONE.toString())
            }
            if (convertedStatuses.indexOf(_const.TASK_STATUS.ON) != -1) {
                status.push(_const.TASK_STATUS.ON.toString())
            }
            if (convertedStatuses.indexOf(_const.TASK_STATUS.DONE) != -1) {
                status.push(_const.TASK_STATUS.DONE.toString())
            }
            Object.assign(this.inputs, {status: status})
            store.commit('setSearchParams', { params: this.inputs })

        } else {
            // メニューからの遷移
            const params = JSON.parse(JSON.stringify(store.state.searchParams))
            store.commit('setSearchParams', { params: params })
        }
    }
}
</script>
