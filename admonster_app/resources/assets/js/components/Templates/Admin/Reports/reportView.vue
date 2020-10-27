<template>
    <v-app>
        <app-menu :drawer="sideMenuDrawer"></app-menu>
        <app-header :title="$t('reports.title')"></app-header>
        <v-content>
            <v-container fluid grid-list-md>
                <v-tabs v-model="selectedCategory" dark slider-color="white">
                    <v-tab v-for="category in reportCategory" :key="category" ripple>{{ category }}</v-tab>
                    <v-tab-item v-for="n in reportCategory" :key="n">
                        <v-card flat>
                            <v-card-text>
                                <business-worktime-report v-if="selectedCategory == 0" :businesses="businesses" :steps="steps"></business-worktime-report>
                                <operator-worktime-report v-else-if="selectedCategory == 1" :candidates="candidates"></operator-worktime-report>
                                <custom-report v-else-if="selectedCategory == 2" :reports="customReports"></custom-report>
                            </v-card-text>
                        </v-card>
                    </v-tab-item>
                </v-tabs>
            </v-container>
        </v-content>
        <app-footer></app-footer>
        <progress-circular v-if="loading"></progress-circular>
    </v-app>
</template>

<script>
import BusinessWorktimeReport from '../../../Organisms/Admin/Reports/BusinessWorktimeReport'
import OperatorWorktimeReport from '../../../Organisms/Admin/Reports/OperatorWorktimeReport'
import CustomReport from '../../../Organisms/Admin/Reports/CustomReport'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        BusinessWorktimeReport,
        OperatorWorktimeReport,
        CustomReport,
        ProgressCircular
    },
    data: () => ({
        sideMenuDrawer: false,
        selectedCategory: null,
        loading: false,
        initData: null,
    }),
    computed: {
        reportCategory () {
            return [
                Vue.i18n.translate('reports.workload_by_business'),
                Vue.i18n.translate('reports.workload_by_operator'),
                Vue.i18n.translate('reports.custom')
            ]
        },
        businesses () {
            return this.initData ? this.initData['businesses'] : []
        },
        candidates () {
            return this.initData ? this.initData['candidates'] : []
        },
        customReports () {
            return this.initData ? this.initData['reports'] : []
        },
        steps () {
            return this.initData ? this.initData['steps'] : []
        },
    },
    created () {
        this.init()
    },
    methods: {
        init () {
            this.loading = true
            axios.get('/api/reports/getReportInfo')
                .then((res) => {
                    this.initData = res.data
                })
                .catch((err) => {
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                })
        }
    },
}
</script>

<style scoped>
.v-tabs >>> .v-tabs__item {
    text-decoration: none;
    color: inherit;
}
</style>
