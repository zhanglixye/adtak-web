<template>
<v-app>
    <app-menu :drawer="drawer"></app-menu>
    <app-header :title="$t('workers.detail.title')"></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>

                <!-- Main -->
                <worker-overview v-if="workerId" :worker-id="workerId"></worker-overview>
                <performance-list v-if="workerId" :worker-id="workerId"></performance-list>
                <!-- / Main -->

            </v-layout>
            <page-footer back-button></page-footer>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import WorkerOverview from '../../../Organisms/Admin/Workers/Detail/WorkerOverview'
import PerformanceList from '../../../Organisms/Admin/Workers/Detail/PerformanceList'

export default {
    components: {
        PageFooter,
        WorkerOverview,
        PerformanceList
    },
    props: {
        workerId: { type: Number }
    },
    data: () => ({
        drawer: null,
    })
}
</script>
