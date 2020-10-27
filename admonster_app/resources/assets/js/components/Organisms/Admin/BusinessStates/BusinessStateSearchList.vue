<template>
    <div id="business-list">
        <!-- toolbar -->
        <v-container fluid grid-list-md py-0 px-2>
            <v-layout row wrap align-center>
                <v-spacer></v-spacer>
                <v-tooltip top disabled>
                    <v-btn flat icon disabled color="primary" slot="activator">
                        <v-icon>settings</v-icon>
                    </v-btn>
                    <span>{{ $t('list.config') }}</span>
                </v-tooltip>
            </v-layout>
        </v-container>
        <!-- toolbar -->

        <div id="list-content">
            <!-- table -->
            <v-data-table
                :headers="headers"
                :items="business_states"
                hide-actions
                class="elevation-1"
            >
                <template slot="headerCell" slot-scope="props">
                    <v-tooltip top v-if="props.header.detail">
                        <span slot="activator">{{ props.header.text }}</span>
                        <span>{{ props.header.detail ? props.header.detail : props.header.text }}</span>
                    </v-tooltip>
                    <span v-else>{{ props.header.text }}</span>
                </template>
                <template slot="items" slot-scope="props">
                    <tr :style="{'backgroundColor': (next_step_id_group.length > 0 && (next_step_id_group.indexOf(props.item.step_id) > -1) ? '#B2DFDB' : '')}">
                        <td class="text-xs-left">
                            <span>
                                <span>{{ props.item.step_name }}</span>
                            </span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="'imported_count' in props.item">
                                <a :href="requestWorkUri(props.item.step_name, 'imported', total=true)">{{ props.item.imported_count }}</a>
                            </span>
                            <span v-else-if="'request_count' in props.item">
                                <a :href="requestUri(props.item.step_name, 'all')">{{ props.item.request_count }}</a>
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="'excluded_count' in props.item">
                                <a :href="requestWorkUri(props.item.step_name, 'excluded', total=true)">{{ props.item.excluded_count }}</a>
                            </span>
                            <span v-else-if="'all_excluded_count' in props.item">
                                <a :href="requestUri(props.item.step_name, 'excluded')">{{ props.item.all_excluded_count }}</a>
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.allocation_count">
                                <a :href="requestWorkUri(props.item.step_name, 'allocation')">{{ props.item.allocation_count }}</a>
                                /
                                <a :href="requestWorkUri(props.item.step_name, 'allocation', total=true)">{{ props.item.allocation_total }}</a>
                            </span>
                            <span v-else-if="'allocation_total' in props.item">
                                <a :href="requestWorkUri(props.item.step_name, 'allocation', total=true)">{{ props.item.allocation_total }}</a>
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.work_count">
                                <a :href="requestWorkUri(props.item.step_name, 'task')">{{ props.item.work_count }}</a>
                                /
                                <a :href="requestWorkUri(props.item.step_name, 'task', total=true)">{{ props.item.work_total }}</a>
                            </span>
                            <span v-else-if="'work_total' in props.item">
                                <a :href="requestWorkUri(props.item.step_name, 'task', total=true)">{{ props.item.work_total }}</a>
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.approval_count">
                                <a :href="requestWorkUri(props.item.step_name, 'approval')">{{ props.item.approval_count }}</a>
                                /
                                <a :href="requestWorkUri(props.item.step_name, 'approval', total=true)">{{ props.item.approval_total }}</a>
                            </span>
                            <span v-else-if="'approval_total' in props.item">
                                <a :href="requestWorkUri(props.item.step_name, 'approval', total=true)">{{ props.item.approval_total }}</a>
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.delivery_count">
                                <a :href="requestWorkUri(props.item.step_name, 'delivery')">{{ props.item.delivery_count }}</a>
                                /
                                <a :href="requestWorkUri(props.item.step_name, 'delivery', total=true)">{{ props.item.delivery_total }}</a>
                            </span>
                            <span v-else-if="'delivery_total' in props.item">
                                <a :href="requestWorkUri(props.item.step_name, 'delivery', total=true)">{{ props.item.delivery_total }}</a>
                            </span>
                            <span v-else-if="'all_completed_count' in props.item">
                                <a :href="requestUri(props.item.step_name, 'completed')">{{ props.item.all_completed_count }}</a>
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center" >
                            <v-btn
                                small
                                fab
                                dark
                                @click="fixedNextSteps(props.item.next_step_id_group, props.item.step_id,fixed)"
                                @mouseenter="fixed ? '' : showNextSteps(props.item.next_step_id_group, props.item.step_id)"
                                @mouseleave="fixed ? '':leave()"
                                :color="props.item.step_id == step_id ? 'primary': 'grey'"
                                v-if="props.item.next_step_id_group">
                                <v-icon dark>skip_next</v-icon>
                            </v-btn>
                            <span v-else>-</span>
                        </td>
                    </tr>
                </template>
                <template slot="no-data">
                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                </template>
            </v-data-table>
            <!-- table -->

            <progress-circular v-if="loading"></progress-circular>
        </div>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/BusinessStates/store.js'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    data: () => ({
        business_states: [],
        search_conditions: [],

        //loading
        loading: false,
        next_step_id_group: [],
        step_id: null,
        fixed: false

    }),
    components: {
        ProgressCircular,
    },
    created () {
        let self = this;

        // 検索イベント登録
        eventHub.$on('search', function({ searchParams }) {
            self.searchBusinessListAsync(searchParams)
        })

        // 検索
        this.searchBusinessListAsync(this.searchParams)
    },
    computed: {
        searchParams () {
            return store.state.searchParams
        },
        requestWorkUri () {
            let self = this
            return function (step_name, process, total=false) {
                let uri = '/management/request_works?business_name=' + encodeURIComponent(self.searchParams.business_name)
                uri = uri + '&date_type=' + encodeURIComponent(self.searchParams.date_type)
                uri = uri + '&from=' + encodeURIComponent(self.searchParams.from)
                uri = uri + '&to=' + encodeURIComponent(self.searchParams.to)
                uri = uri + '&step_name=' + encodeURIComponent(step_name)
                uri = uri + '&inactive=' + self.booleanNumber(self.searchParams.inactive)
                uri = uri + '&excluded=' + self.booleanNumber(process === 'excluded')
                let statusList = self.statusList(process, total)
                uri = uri + '&completed=' + self.booleanNumber(total)
                statusList.forEach(status => {
                    if (status === '42') return
                    uri = uri + '&' + encodeURIComponent('status[]') + '=' + encodeURIComponent(status)
                })
                uri = uri + '&title=' + encodeURIComponent(self.displayTitle(process))

                return uri
            }
        },
        requestUri () {
            let self = this
            return function (step_name, status) {
                let uri = '/management/requests?business_name=' + encodeURIComponent(self.searchParams.business_name)
                uri = uri + '&status=' + encodeURIComponent(self.requestStatus(status))
                uri = uri + '&date_type=' + encodeURIComponent(self.searchParams.date_type)
                uri = uri + '&from=' + encodeURIComponent(self.searchParams.from)
                uri = uri + '&to=' + encodeURIComponent(self.searchParams.to)

                return uri
            }
        },
        headers (){
            return  [
                { text: Vue.i18n.translate('business_states.step_name'), value: 'step_name', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.imported'), detail: Vue.i18n.translate('business_states.count'), value: 'imported_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.excluded'), detail: Vue.i18n.translate('business_states.count'), value: 'excluded_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.allocation'), detail: Vue.i18n.translate('business_states.wip_count'), value: 'allocation_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.task'), detail: Vue.i18n.translate('business_states.wip_count'), value: 'task_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.approval'), detail: Vue.i18n.translate('business_states.wip_count'), value: 'approval_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.delivery'), detail: Vue.i18n.translate('business_states.wip_count'), value: 'delivery_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.next_step'), value: 'next_step', align: 'center', sortable: false },
            ]
        },
        numArr () {
            return function (concatNums) {
                let numArr = concatNums.split(',')
                return numArr
            }
        }
    },
    methods: {
        booleanNumber (bool) {
            // TRUE:  1
            // FALSE: 0
            return bool ? 1 : 0
        },
        displayTitle (process) {
            switch (process) {
            case 'imported':
                return '取込一覧'
            case 'excluded':
                return '除外一覧'
            case 'allocation':
                return '割振一覧'
            case 'task':
                return 'タスク一覧'
            case 'approval':
                return '承認一覧'
            case 'delivery':
                return '納品一覧'
            default:
                return '依頼作業一覧'
            }
        },
        statusList (process, total) {
            switch (process) {
            case 'allocation':
                return total ? ['11','12'] : ['11']
            case 'task':
                return total ? ['21', '22'] : ['21']
            case 'approval':
                return total ? ['31', '32'] : ['31']
            case 'delivery':
                return total ? ['41', '42'] : ['41']
            default:
                return ['11', '12', '21', '22', '31', '32', '41', '42']
            }
        },
        requestStatus (status) {
            switch (status) {
            case 'all':
                return 0
            case 'wip':
                return 1
            case 'completed':
                return 2
            case 'excluded':
                return 3
            default:
                return 0
            }
        },
        searchBusinessListAsync (searchParams) {
            this.loading = true
            axios.post('/api/business_states', searchParams)
                .then((res) => {
                // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: searchParams })
                    // 検索結果を画面に反映
                    this.business_states = res.data.business_states
                    this.search_conditions = res.data.search_conditions
                    this.loading = false

                    console.log(res.data)
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        completed_count (item) {
            if (item.completed_count === item.wip_count) {
                return item.completed_count
            } else {
                return item.completed_count + '/' + item.wip_count
            }
        },
        showNextSteps (next_step_id_group,step_id) {
            this.step_id = step_id
            this.next_step_id_group = (next_step_id_group != null) ?  next_step_id_group : []
        },
        fixedNextSteps (next_step_id_group,step_id,fixed) {
            this.fixed = !fixed
            if (this.fixed){
                this.step_id = step_id
                this.next_step_id_group = (next_step_id_group != null) ?  next_step_id_group : []
            }
            if (this.fixed==false && this.step_id != step_id) {
                this.fixed = true
                this.step_id = step_id
                this.next_step_id_group = (next_step_id_group != null) ?  next_step_id_group : []
            }
            if ( this.fixed==false && this.step_id == step_id){
                this.step_id =null
                this.next_step_id_group = []
            }

        },
        leave () {
            this.step_id =null
            this.next_step_id_group = []
        }

    },
}
</script>
<style>
.tipMessage {
    font-size: 10px;
}
#list-content {
    position: relative;
}
</style>
