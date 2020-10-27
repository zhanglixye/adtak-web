<template>
    <div id="count-summary">
        <div id="list-content">
            <!-- table -->
            <v-data-table
                :headers="headers"
                :items="countSummary"
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
                    <tr :active="props.selected">
                        <td class="text-xs-left">
                            <span>
                                <span v-if="props.item.step_id">
                                    <span v-for="n in props.item.end_criteria" :key="n">&emsp;</span>
                                    <span class="circle-step-num mr-1">{{ props.item.step_id }}</span><span>{{ props.item.step_name }}</span>
                                    <template v-if="requestWorkCode(props.item)">&#040;</template><span class="select-all">{{ requestWorkCode(props.item) }}</span><template v-if="requestWorkCode(props.item)">&#041;</template>
                                </span>
                                <span v-else>{{ props.item.step_name }}</span>
                            </span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if=" 'imported_count' in props.item">{{ props.item.imported_count }}</span>
                            <span v-else-if=" 'request_count' in props.item">{{ props.item.request_count }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if=" 'excluded_count' in props.item">{{ props.item.excluded_count }}</span>
                            <span v-else-if=" 'all_excluded_count' in props.item">{{ props.item.all_excluded_count }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.allocation_count">{{ props.item.allocation_count }} / {{ props.item.allocation_total }}
                            </span>
                            <span v-else-if=" 'allocation_total' in props.item">{{ props.item.allocation_total }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.work_count">{{ props.item.work_count }} / {{ props.item.work_total }}</span>
                            <span v-else-if=" 'work_total' in props.item">{{ props.item.work_total }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.approval_count">{{ props.item.approval_count }} / {{ props.item.approval_total }}
                            </span>
                            <span v-else-if=" 'approval_total' in props.item">{{ props.item.approval_total }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="text-xs-center">
                            <span v-if="props.item.next_step_id_group">
                                <span><v-icon small>arrow_forward</v-icon></span>
                                <span v-for="num in numArr(props.item.next_step_id_group)" :key="num">
                                    <span class="circle-step-num">{{ num }}</span>
                                </span>
                            </span>
                            <span v-else-if=" 'all_completed_count' in props.item">
                                {{ props.item.all_completed_count }}
                            </span>
                            <span v-else>-</span>
                        </td>
                    </tr>
                </template>
                <template slot="no-data">
                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                </template>
            </v-data-table>
            <!-- table -->
        </div>
    </div>
</template>

<script>
export default {
    props: {
        countSummary: { type: Array },
        businessName: { type: String },
        requestId: { type: Number, required: false, default: 0 },
    },
    data: () => ({
        //loading
        loading: false,
    }),
    computed: {
        headers () {
            return  [
                { text: Vue.i18n.translate('business_states.step_name'), value: 'step_name', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.imported'), detail: Vue.i18n.translate('business_states.count'), value: 'imported_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.excluded'), detail: Vue.i18n.translate('business_states.count'), value: 'excluded_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.allocation'), detail: Vue.i18n.translate('business_states.count'), value: 'allocation_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.task'), detail: Vue.i18n.translate('business_states.count'), value: 'task_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.approval'), detail: Vue.i18n.translate('business_states.count'), value: 'approval_count', align: 'center', sortable: false },
                { text: Vue.i18n.translate('business_states.delivery'), detail: Vue.i18n.translate('business_states.count'), value: 'delivery_count', align: 'center', sortable: false },
            ]
        },
        numArr () {
            return function (nums) {
                let numArr = nums.split(',')
                return numArr
            }
        },
        requestWorkCode () {
            return function (item) {
                if (!item.request_work_ids) return ''
                const prefix = _const.MASTER_ID_PREFIX
                const requestWorkCode = prefix.REQUEST_ID + this.requestId + prefix.SEPARATOR + prefix.REQUEST_WORKS_ID
                return requestWorkCode + item.request_work_ids.split(',').join(',' + requestWorkCode)
            }
        },
    }
}
</script>
<style scoped>
.select-all {
    user-select: all;
    -webkit-user-select: all;
    -moz-user-select: all;
}
</style>