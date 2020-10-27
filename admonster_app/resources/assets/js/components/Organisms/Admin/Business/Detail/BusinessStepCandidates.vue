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
            <tr :active="props.selected"  @click="openEditModal(props.item.step_id)" style="cursor:pointer;">
                <td class="center">{{ props.item.step_name }}</td>
                <td class="text-xs-left">
                    <!-- 名前も表示 -->
                    <!-- <div d-flex style="display: flex;">
                        <template v-for="user_id in users(props.item)">
                            <div :key="user_id" class="px-2">
                                <v-avatar slot="activator" size="42px" class="ma-1">
                                    <img :src="user_image_path(user_id)">
                                </v-avatar>
                                <div class="caption text-xs-center" style="white-space: nowrap;">{{ user_name(user_id) }}</div>
                            </div>
                        </template>
                    </div> -->
                    <!-- 名前はツールチップで表示 -->
                    <div d-flex style="display: flex;">
                        <template v-for="user_id in users(props.item)">
                            <div :key="user_id" class="px-2">
                                <v-tooltip top>
                                    <v-avatar slot="activator" size="32px" class="ma-1">
                                        <img :src="user_image_path(user_id)">
                                    </v-avatar>
                                    <span>{{ user_name(user_id) }}</span>
                                    <!-- <div class="caption text-xs-center" style="white-space: nowrap;">{{ user_name(user_id) }}</div> -->
                                </v-tooltip>
                            </div>
                        </template>
                    </div>
                </td>
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
    components: {
    },
    props: {
        steps: { type: Array },
        candidates: { candidates: Array }
    },
    computed: {
        headers (){
            return  [
                { text: eventHub.$t('businesses.step_name'), value: 'step_name', align: 'center', sortable: false, width: '20%'},
                { text: eventHub.$t('businesses.candidate'), value: 'occurred_count', align: 'center', sortable: false},
            ]
        }
    },
    methods: {
        users (step) {
            if (step){
                let work_user_ids = step.work_user_ids ? step.work_user_ids.split(',') : [];
                return work_user_ids;
            }
        },
        openEditModal(step_id) {
            eventHub.$emit('open-edit-modal',step_id)
        },
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
    },
}
</script>
