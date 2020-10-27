<template>
<div id="request-work" class="datatable-small-wrap">
    <v-data-table
        :headers="headers"
        :items="requests"
        hide-actions
        class="elevation-1"
    >
        <template slot="headers" slot-scope="props">
            <tr>
                <th
                    v-for="(header,i) in props.headers"
                    :key="i"
                    :class="['column', 'text-xs-' + header.align]"
                    :style="{ width: header.width }"
                >
                    {{ header.text }}
                </th>
            </tr>
        </template>
        <template slot="items" slot-scope="props">
            <tr :active="false">
            <td class="text-xs-left overflow">
                <v-tooltip top>
                    <span slot="activator">{{ props.item.request_work_name }}</span>
                    <span>{{ props.item.request_work_name }}</span>
                </v-tooltip>
            </td>
            <td class="text-xs-center">{{ props.item.step_name }}</td>
            <td class="text-xs-center">
                <template v-if="isDeleted(props.item.request_status)">
                    <v-chip small label disabled color="grey" text-color="white">除外</v-chip>
                </template>
                <template v-else-if="isInactive(props.item.request_work_is_active)">
                    <v-chip small outline label disabled color="grey">無効</v-chip>
                </template>
                <template v-else v-for="(process, i) in processes">
                    <v-tooltip top :key="i">
                        <v-icon slot="activator" :color="showProcess(process, props.item).color">forward</v-icon>
                        <span>{{ $t(process.title) + showProcess(process, props.item).suffix }}</span>
                    </v-tooltip>
                </template>
            </td>
            <td class="text-xs-center">
                <users-overview :users="users(props.item)" :candidates="candidates"></users-overview>
            </td>
            <td class="text-xs-center">{{ props.item.request_work_created_at | formatDateYmdHm(true) }}</td>
            <td class="text-xs-center">{{ props.item.deadline | formatDateYmdHm }}</td>
          </tr>
        </template>
    </v-data-table>
</div>
</template>

<script>
import UsersOverview from '../../Molecules/Users/UsersOverview'

const processes = [
    { id: 1, title: 'allocations.request_work.processes.allocation', link: '/allocations/' },
    { id: 2, title: 'allocations.request_work.processes.work', link: '/allocations/' },
    { id: 3, title: 'allocations.request_work.processes.approval', link: '/approvals/' },
    { id: 4, title: 'allocations.request_work.processes.delivery', link: '/deliveries/' },
];

export default {
    props: {
        requests: { type: Array },
        candidates: { type: Array },
    },
    data: () =>({
        valid: false,
        show: false,
        processes: processes,
    }),
    components: {
        UsersOverview,
    },
    methods: {
        showProcess: function (process, item) {
            let array = null;
            // DONE
            if ( process.id < item.process || (item.process === 4 && item.status === 2) ) {
                array = { link: process.link + item.id + '/edit', color: 'grey lighten-1', suffix: '：'+this.finished };
            // ON
            } else if ( process.id === item.process ) {
                array = { link: process.link + item.id + '/edit', color: 'teal darken-2', suffix: '' };
            // WAIT
            } else {
                array = { link: '#', color: 'teal lighten-4', suffix: '' };
            }
            return array;
        },
        users (item) {
            return {
                allocated_user_ids: item.user_ids ? item.user_ids.split(',') : [],
                completed_user_ids: item.completed_user_ids ? item.completed_user_ids.split(',') : [],
            }
        },
        isDeleted (request_status) {
            return request_status === _const.REQUEST_STATUS.EXCEPT
        },
        isInactive (request_work_is_active) {
            return request_work_is_active === _const.FLG.INACTIVE
        }
    },
    computed: {
        headers () {
            return [
                { text: Vue.i18n.translate('list.column.subject'), value: 'request_work_name', align: 'center', width: '', sortable: false },
                { text: Vue.i18n.translate('list.column.step_name'), value: 'step_name', align: 'center', width: '100px', sortable: false },
                { text: Vue.i18n.translate('list.column.status'), value: 'process', align: 'center', width: '100px', sortable: false },
                { text: Vue.i18n.translate('list.column.operator'), value: 'user_ids', align: 'center', width: '100px', sortable: false },
                { text: Vue.i18n.translate('list.column.created_at'), value: 'request_work_created_at', align: 'center', width: '100px', sortable: false },
                { text: Vue.i18n.translate('list.column.deadline'), value: 'deadline', align: 'center', width: '100px', sortable: false },
            ]
        },
        finished (){
            return  this.$t('allocations.request_work.processes.finished')
        }
    }
}
</script>

<style scoped>
#request-work table.v-table thead tr {
    /* height: 56px; */
    height: 40px;
}
</style>
