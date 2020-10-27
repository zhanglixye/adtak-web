<template>
    <div>
        <div v-for="item in selectedUsersInfos" class="pa-3" :key="item.user_name">
            <v-container fluid>
                <v-layout row wrap mb-2>
                    <v-flex xs12 sm6 md3 align-center style="display:flex;">
                        <v-card tile flat>
                            <template>
                                <v-avatar size="64px" :tile="false">
                                    <img :src="userImageSrc(item.user_image_path)">
                                </v-avatar>
                                <span class="pa-2 title">{{ item.user_name }}</span>
                            </template>
                        </v-card>
                    </v-flex>
                    <v-flex xs12 sm6 md3>
                        <v-card tile flat>
                            <v-card-text class="text-xs-center">割振予定件数<br><span class="headline font-weight-bold mr-1">{{ item.expecting_allocation_requests.length }}</span>件</v-card-text>
                        </v-card>
                    </v-flex>
                    <v-flex xs12 sm6 md3>
                        <v-card tile flat color="darken-3">
                            <v-card-text class="text-xs-center"><span>残作業予定件数</span><br><span class="headline font-weight-bold mr-1">{{ item.work_in_process_count + item.expecting_allocation_requests.length }}</span>件</v-card-text>
                        </v-card>
                    </v-flex>
                    <v-flex xs12 sm6 md3>
                        <v-card tile flat>
                            <v-card-text><span>作業完了予定日時（見込み）</span><br><span></span></v-card-text>
                        </v-card>
                    </v-flex>
                </v-layout>
                <v-layout row wrap v-if="item.expecting_allocation_requests.length > 0" color="grey darken-1">
                    <v-expansion-panel>
                        <v-expansion-panel-content class="text-xs-center" color="grey darken-1" style="background-color:#E0E0E0;">
                            <v-icon slot="actions">$vuetify.icons.expand</v-icon>
                            <div slot="header">割振予定の<span>{{ item.expecting_allocation_requests.length }}</span>件を見る</div>
                            <request-work :requests="item.expecting_allocation_requests" :candidates="candidates"></request-work>
                        </v-expansion-panel-content>
                    </v-expansion-panel>
                </v-layout>
            </v-container>
            <v-divider></v-divider>
        </div>
    </div>
</template>

<script>
import RequestWork from '../../../Organisms/Common/RequestWork'

export default {
    props: {
        selectedUsersInfos: { type: Array },
        requests: { type: Array },
        candidates: { type: Array }
    },
    components: {
        RequestWork
    },
    data: () => ({
        // dialog: true
    }),
    computed: {
        userImageSrc () {
            return function (user_image_path) {
                if (user_image_path) {
                    return user_image_path
                } else {
                    return location.origin + '/images/dummy_icon.png'
                }
            }
        }
    },
}
</script>
