<template>
    <!-- table -->
    <v-data-table
        :headers="headers"
        :items="steps"
        hide-actions
    >
        <template slot="headerCell" slot-scope="props">
            <v-tooltip top>
                <span slot="activator">
                    {{ props.header.text }}
                </span>
                <span>
                    {{ props.header.text }}
                </span>
                <span v-if="props.header.detail">
                    {{ props.header.detail }}
                </span>
            </v-tooltip>
        </template>
        <template slot="items" slot-scope="props">
            <tr :active="props.selected" @click="openEditModal(props.item.step_id)" style="cursor:pointer;">
                <td class="text-xs-left">{{ props.item.step_name }}</td>
                <td class="text-xs-center">{{ showStepType(props.item.step_type) }}</td>
                <td class="text-xs-left" style="min-width: 400px;">{{ props.item.step_description }}</td>
                <td class="text-xs-center">{{ props.item.deadline_limit + showTimeUnit(props.item.time_unit) }}</td>
                <td class="text-xs-center">{{ getUsersLength (props.item) + $t('businesses.person')}}</td>
            </tr>
        </template>
        <template slot="no-data">
            <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
        </template>
    </v-data-table>
    <!-- table -->
</template>

<script>
export default {
    props: {
        steps: { type: Array }
    },
    computed: {
        headers (){
            return  [
                { text: eventHub.$t('businesses.step_name'), value: 'step_name', align: 'center', sortable: false,  width: '20%'},
                { text: eventHub.$t('businesses.step_type'), value: 'step_type', align: 'center', sortable: false},
                { text: eventHub.$t('businesses.description'), value: 'step_description', align: 'center', sortable: false},
                { text: eventHub.$t('businesses.deadline'), value: 'deadline_limit', align: 'center', sortable: false},
                { text: eventHub.$t('businesses.candidate'), value: 'work_user_ids', align: 'center', sortable: false}
            ]
        },
    },
    methods: {
        getUsersLength (step) {
            let work_user_ids = step.work_user_ids ? step.work_user_ids.split(',') : [];
            return work_user_ids.length;
        },
        showTimeUnit (time_unit) {
            if (time_unit ===  _const.TIME_UNIT.MINUTE){
                return eventHub.$t('common.datetime.minute')
            } else if (time_unit ===  _const.TIME_UNIT.HOUR){
                return eventHub.$t('common.datetime.hour.interval')
            } else if (time_unit ===  _const.TIME_UNIT.DAY){
                return eventHub.$t('common.datetime.day')
            }
        },
        showStepType (step_type) {
            if (step_type === _const.STEP_TYPE.INPUT){
                return eventHub.$t('businesses.detail.step_type.input')
            } else if (step_type === _const.STEP_TYPE.APPROVAL){
                return eventHub.$t('businesses.detail.step_type.approval')
            }
        },
        openEditModal(step_id) {
            eventHub.$emit('open-edit-modal',step_id)
        }
    }
}
</script>
