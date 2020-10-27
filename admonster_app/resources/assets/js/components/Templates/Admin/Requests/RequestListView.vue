<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('requests.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <request-search-condition></request-search-condition>
                </v-flex>
                <v-flex xs12>
                    <request-search-list></request-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import RequestSearchCondition from '../../../Organisms/Admin/Requests/RequestSearchCondition'
import RequestSearchList from '../../../Organisms/Admin/Requests/RequestSearchList'

import store from '../../../../stores/Admin/Requests/store'

export default {
    props: {
        inputs: { type: Object }
    },
    components:{
        RequestSearchCondition,
        RequestSearchList
    },
    data: ()=> ({
        drawer: null,
    }),
    created () {
        if (this.inputs) {
            // GETパラメータの検索条件をセット
            store.dispatch('setRequestParams', this.inputs)
        }
    }
}
</script>

<style scoped>

</style>
