<template>
    <v-app>
        <app-menu :drawer="drawer"></app-menu>
        <app-header :title="$t('master.businesses.list.title')"></app-header>
        <v-content id="list">
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <!-- Main -->
                    <v-flex xs12>
                        <businesses-search-list></businesses-search-list>
                    </v-flex>
                    <!-- Main -->
                </v-layout>
            </v-container>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import BusinessesSearchList from '../../../Organisms/Master/Businesses/SearchList'
export default {
    components:{
        BusinessesSearchList
    },
    data: () => ({
        drawer: null,
    }),
}
</script>

<style scoped>

</style>