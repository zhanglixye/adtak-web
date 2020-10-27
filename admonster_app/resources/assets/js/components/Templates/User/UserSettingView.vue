<template>
    <v-app>
        <app-menu :drawer="drawer"></app-menu>
        <app-header :title="$t('user.list.title')"></app-header>
        <v-content id="list">
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                     <!--Main -->
                    <v-flex xs12>
                        <user-setting-detail></user-setting-detail>
                    </v-flex>
                     <!--Main -->
                </v-layout>
            </v-container>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import UserSettingDetail from '../../Organisms/User/UserSettingDetail';
export default {
    name: 'UserSettingView',
    data: () => ({
        drawer: null,
    }),
    components:{
        UserSettingDetail
    }
}
</script>

<style scoped>

</style>