<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('master.users.list.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <!-- Main -->
                <v-flex xs12>
                    <user-search-condition></user-search-condition>
                </v-flex>
                <v-flex xs12>
                    <user-search-list></user-search-list>
                </v-flex>
                <!-- Main -->
            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>

import UserSearchList from '../../../Organisms/Master/Users/UserSearchList';
import UserSearchCondition from '../../../Organisms/Master/Users/UserSearchCondition';

export default {
    components: {
        UserSearchList,
        UserSearchCondition,
    },
    data: () => ({
        drawer: null,
    }),
}
</script>
