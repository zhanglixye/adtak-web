<template>
    <v-app>
        <app-menu :drawer="drawer"></app-menu>
        <app-header :title="$t('master.steps.list.title')"></app-header>
        <v-content id="list">
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <!-- Main -->
                    <v-flex xs12>
                        <steps-search-list></steps-search-list>
                    </v-flex>
                    <!-- Main -->
                </v-layout>
            </v-container>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import StepsSearchList from '../../../Organisms/Master/Steps/SearchList';

export default {
    components: {
        StepsSearchList,
    },
    data: () => ({
        drawer: null,
    }),
}
</script>
