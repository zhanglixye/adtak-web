<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('businesses.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <business-search-condition></business-search-condition>
                </v-flex>
                <v-flex xs12>
                    <business-search-list></business-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import BusinessSearchCondition from '../../../Organisms/Admin/Business/BusinessSearchCondition';
import BusinessSearchList from '../../../Organisms/Admin/Business/BusinessSearchList';

export default {
    components: {
        BusinessSearchCondition,
        BusinessSearchList,
    },
    data: () => ({
        drawer: null,
    }),
}
</script>
