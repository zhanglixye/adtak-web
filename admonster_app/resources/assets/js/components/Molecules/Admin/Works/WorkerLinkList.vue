<template>
    <v-list dense>
        <v-list-tile @click="goToWorkerDetailPage(userId)">
            <v-list-tile-content>
                <v-list-tile-title>{{ $t('works.list.worker_detail_confirm') }}</v-list-tile-title>
            </v-list-tile-content>
        </v-list-tile>
        <v-list-tile @click="goToAllocationPage(requestWorkId)">
            <v-list-tile-content>
                <v-list-tile-title>{{ $t('works.list.change_worker') }}</v-list-tile-title>
            </v-list-tile-content>
        </v-list-tile>
    </v-list>
</template>

<script>
export default {
    props: {
        userId: { type: Number },
        requestWorkId: { type: Number }
    },
    methods: {
        goToWorkerDetailPage (userId) {
            window.location.href = '/management/workers/' + userId
        },
        goToAllocationPage (requestWorkId) {
            window.location.href = '/allocations/' + requestWorkId + '/edit'
        }
    }
}
</script>
