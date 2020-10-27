<template>
<v-app id="inspire">
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('business_states.list.title')"></app-header>
    <v-content>
        <v-container fluid grid-list-md>
            <v-layout row wrap>

                <!-- Main -->
                <v-flex xs12>
                    <page-header></page-header>
                </v-flex>
                <v-flex xs12>
                    <business-state-search-condition></business-state-search-condition>
                </v-flex>
                <v-flex xs12>
                    <business-state-search-list></business-state-search-list>
                </v-flex>
                <!-- Main -->

            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import BusinessStateSearchCondition from '../../../Organisms/Admin/BusinessStates/BusinessStateSearchCondition'
import BusinessStateSearchList from '../../../Organisms/Admin/BusinessStates/BusinessStateSearchList'

import store from '../../../../stores/Admin/BusinessStates/store.js'

export default {
    props: {
        inputs: { type: Object }
    },
    components: {
        PageHeader,
        BusinessStateSearchCondition,
        BusinessStateSearchList,
    },
    data: () => ({
        drawer: null,
    }),
    created () {
        if (this.inputs) {
            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新
            store.commit('resetSearchParams')
            store.commit('setSearchParams', { params: this.inputs })

            console.log(this.inputs)
        }
    }
}
</script>
