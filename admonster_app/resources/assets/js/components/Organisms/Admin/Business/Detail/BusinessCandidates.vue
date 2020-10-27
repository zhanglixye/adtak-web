<template>
    <div id="business-candidates" class="elevation-1">
        <v-list>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>assignment_ind</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t('businesses.detail.candidates') }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
        <v-container grid-list-md pt-0 id="business-candidates-main">
            <v-layout column wrap>
                <v-flex xs12>
                    <v-card class="elevation-2">
                        <v-card-title>
                            {{ $t('businesses.admin') + '（' + admins.length + $t('businesses.person') + '）' }}
                        </v-card-title>
                        <v-card-actions style="overflow-x: auto;">
                            <template v-for="admin in admins">
                                <div :key="admin.id" class="px-2">
                                    <v-avatar slot="activator" size="42px" class="ma-1">
                                        <img :src="admin.user_image_path">
                                    </v-avatar>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ admin.name }}</div>
                                </div>
                            </template>
                        </v-card-actions>
                    </v-card>
                </v-flex>
                <v-flex xs12>
                    <v-card class="elevation-2">
                        <v-card-title>
                            {{ $t('businesses.operator') + '（' + candidates.length + $t('businesses.person') + '）' }}
                        </v-card-title>
                        <v-card-actions style="overflow-x: auto;">
                            <template v-for="candidate in candidates">
                                <div :key="candidate.id" class="px-2">
                                    <v-avatar slot="activator" size="42px" class="ma-1">
                                        <img :src="candidate.user_image_path">
                                    </v-avatar>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ candidate.name }}</div>
                                </div>
                            </template>
                        </v-card-actions>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
    </div>
</template>

<script>
export default {
    components: {
    },
    props: {
        candidates: { type: Array, require: true },
        admins: { type: Array, require: true }
    }
}
</script>

<style scoped>
#business-candidates {
    background-color: #ffffff;
}
#business-candidates-main {
    height: 350px;
}
</style>
