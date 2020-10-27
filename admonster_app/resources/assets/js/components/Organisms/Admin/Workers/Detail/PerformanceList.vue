<template>
    <v-flex xs12>
        <v-card>
            <v-card-title>
                <span class="title">担当業務・実績</span>
            </v-card-title>

            <template>
                <div class="text-xs-right ml-2 mr-2">
                    <span class="caption">※過去1週間分のデータです</span>
                </div>
            </template>

            <v-data-table
                :headers="headers"
                :items="items"
                :search="searchTrigger"
                ref="summary"
                hide-actions
                must-sort
                class="elevation-1"
              >
                <template slot="headers" slot-scope="props">
                    <tr id="work_sort_key">
                        <th
                            v-for="header in props.headers"
                            :key="header.text"
                            :class="['column sortable', 'desc', '', 'text-xs-' + header.align]"
                            :style="{ minWidth: header.width + 'px' }"
                        >
                            {{ header.text }}
                            <v-icon small>arrow_drop_up</v-icon>
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr v-if="adminBusiness(props.item.business_id)">
                        <td>
                            <span>{{ props.item.business_name }}</span>
                        </td>
                        <td class="text-xs-center">
                            <span>
                                <span>{{ taskCountInBusiness(props.item.steps) }}</span>
                            </span>
                        </td>
                        <td class="text-xs-center">-</td>
                        <td class="text-xs-center">-</td>
                    </tr>
                    <tr v-else class="text-grayout">
                        <td>
                            <v-tooltip top>
                                <span slot="activator">********</span>
                                <span>※{{ $t('workers.captions.is_not_admin_business') }}</span>
                            </v-tooltip>
                        </td>
                        <td class="text-xs-center">
                            <span>-</span>
                        </td>
                        <td class="text-xs-center"><span>-</span></td>
                        <td class="text-xs-center"><span>-</span></td>
                    </tr>
                </template>
                <template slot="footer">
                    <td class="font-weight-bold">{{ $t('workers.whole') }}</td>
                    <td class="text-xs-center font-weight-bold">
                        <span>{{ wholeData.taskCountSum }}</span>
                    </td>
                    <td class="text-xs-center font-weight-bold">{{ wholeData.average }}</td>
                    <td class="text-xs-center font-weight-bold">{{ wholeData.workTime }}</td>
                </template>
            </v-data-table>
        </v-card>
    </v-flex>
</template>

<script>
export default {
    props: {
        workerId: { type: Number }
    },
    data: () => ({
        drawer: null,
        search: '',
        items: [],
        adminBusinessIds: [],
        headers: [
            { text: Vue.i18n.translate('list.column.business_name'), align: 'center', sortable: false, value: 'business_name', width: '150' },
            { text: Vue.i18n.translate('workers.performance_list.headers.completed_task_count'), align: 'center', value: 'tasks', width: '100' },
            { text: Vue.i18n.translate('workers.performance_list.headers.average'), align: 'center', value: 'average', width: '100' },
            { text: Vue.i18n.translate('workers.performance_list.headers.work_time'), align: 'center', value: 'time', width: '100' },
        ],
        wholeData: {
            taskCountSum: '',
            average: '',
            workTime: ''
        }
    }),
    computed: {
        searchTrigger: function() {
            return this.search
        }
    },
    created() {
        this.getPerformanceList()
    },
    methods: {
        adminBusiness (businessId) {
            if (businessId) {
                return this.adminBusinessIds.includes(Number(businessId))
            }
        },
        taskCountInBusiness (steps) {
            let count = 0
            if (steps) {
                steps.forEach(function(step) {
                    count = count + step.completed_task_count
                });
            }
            return count
        },
        getPerformanceList () {
            this.loading = true
            axios.post('/api/workers/performance',{
                worker_id: this.workerId
            })
                .then((res) => {
                    this.items = res.data.list
                    this.adminBusinessIds = res.data.admin_business_ids
                    this.calcWholeData(res.data.list)
                })
                .catch((err) => {
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        calcWholeData (list) {
            let self = this
            let taskCountSum = 0
            list.forEach(function(data) {
                let steps = data.steps
                steps.forEach(function(step) {
                    if (self.adminBusiness(step.business_id)) {
                        taskCountSum = taskCountSum + step.completed_task_count
                    } else {
                        return false
                    }
                })
            })
            this.wholeData.taskCountSum = taskCountSum
            this.wholeData.average = '-'
            this.wholeData.workTime = '-'
        },
    }
}
</script>

<style scoped>
tr.text-grayout td{
    opacity: 0.4;
}
</style>
