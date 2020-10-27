<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="title"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <request-work-search-condition></request-work-search-condition>
                </v-flex>
                <v-flex xs12>
                    <request-work-search-list></request-work-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import RequestWorkSearchCondition from '../../../Organisms/Admin/RequestWorks/RequestWorkSearchCondition';
import RequestWorkSearchList from '../../../Organisms/Admin/RequestWorks/RequestWorkSearchList';

import store from '../../../../stores/Admin/RequestWorks/store.js'

export default {
    props: {
        inputs: { type: Object }
    },
    components: {
        RequestWorkSearchCondition,
        RequestWorkSearchList,
    },
    data: () => ({
        drawer: null,

        title: Vue.i18n.translate('request_works.list.title')
    }),
    created: function () {
        if (this.inputs) {
            // タイトル指定がある場合は任意タイトルに変更
            this.title = this.inputs.title ? this.inputs.title : this.title

            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新
            store.commit('resetSearchParams')
            
            this.inputs.self = Number(this.inputs.self) === _const.FLG.ACTIVE ? true : false
            this.inputs.completed = Number(this.inputs.completed) === _const.FLG.ACTIVE ? true : false
            this.inputs.inactive = Number(this.inputs.inactive) === _const.FLG.ACTIVE ? true : false
            this.inputs.excluded = Number(this.inputs.excluded) === _const.FLG.ACTIVE ? true : false
            store.commit('setSearchParams', { params: this.inputs })
        }
    }
}
</script>
