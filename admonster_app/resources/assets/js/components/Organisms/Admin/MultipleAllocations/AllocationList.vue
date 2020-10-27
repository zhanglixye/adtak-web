<template>
    <div id="allocation-list" class="elevation-1">
        <v-list>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>assignment_ind</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t('allocations.allocation_list.operator') }}</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-text-field
                        v-model="search"
                        append-icon="search"
                        :label="$t('allocations.allocation_list.search')"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-list-tile-action>
            </v-list-tile>
        </v-list>
        <div id="allocation-list-main">
            <v-data-table
                v-model="users"
                :headers="headers"
                :items="operators"
                :pagination.sync="pagination"
                :search="search"
                item-key="user_id"
                hide-actions
                must-sort
            >
                <template slot="headerCell" slot-scope="props">
                    <v-tooltip top v-if="props.header.detail">
                        <span slot="activator">{{ props.header.text }}</span>
                        <span>{{ props.header.detail }}</span>
                    </v-tooltip>
                    <span v-else>{{ props.header.text }}</span>
                </template>
                <template slot="items" slot-scope="props">
                    <tr :active="props.selected">
                        <td>
                            <v-tooltip v-if="disabledCheckbox(props.item.on_task_count, props.item.done_task_count)" top>
                                <span slot="activator"><v-icon disabled>block</v-icon></span>
                                <span>対象案件がすべて作業中、あるいは作業済みのため操作できません</span>
                            </v-tooltip>
                            <v-checkbox
                                v-else
                                :input-value="props.selected"
                                :disabled="disabledCheckbox(props.item.on_task_count, props.item.done_task_count)"
                                primary
                                hide-details
                                color="primary"
                                @click="props.selected = !props.selected"
                            ></v-checkbox>
                        </td>
                        <td class="text-xs-left">
                            <template>
                                <v-avatar size="32px" :tile="false">
                                    <img :src="userImageSrc(props.item.user_image_path)">
                                </v-avatar>
                                <span class="pa-2">
                                    <a :href="workerDetailPage(props.item.user_id)" class="text-underline">{{ props.item.user_name }}</a>
                                </span>
                            </template>
                        </td>
                        <td class="text-xs-center">{{ props.item.work_in_process_count ? props.item.work_in_process_count : 0 }}</td>
                        <td class="text-xs-center">{{ props.item.estimated_time ? props.item.estimated_time : 0 }}</td>
                        <td class="text-xs-center">{{ props.item.completed_count ? props.item.completed_count : 0 }}</td>
                        <td class="text-xs-center">{{ props.item.average ? Math.floor(props.item.average * 60) : 0 }}</td>
                        <td class="text-xs-center">{{ props.item.percentage ? props.item.percentage : 0 }}</td>
                    </tr>
                </template>
            </v-data-table>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        requests: { type: Array, require: true },
        operators: { type: Array, require: true },
        candidates: { type: Array, require: true },
        selected: { type: Array, require: true }
    },
    data: () => ({
        users: [],
        search: '',
        selectedUsers: [],

        // 一覧
        pagination: {
            sortBy: 'average',
            descending: false,
            rowsPerPage: -1, // ALL
        },
        dummu: 1,
    }),
    methods: {
        reset () {
            // 絞り込み
            this.search = ''
            // ソート
            this.pagination.sortBy = 'average'
            this.pagination.descending = false
        }
    },
    computed: {
        headers () {
            return [
                { text: '', value: 'user_id', align: 'center', sortable: false },
                { text: this.$t('allocations.allocation_list.headers.user_name'), value: 'user_name', align: 'center' },
                { text: this.$t('allocations.allocation_list.headers.work_in_process_count'), value: 'work_in_process_count', align: 'center', detail: this.$t('allocations.allocation_list.headers.work_in_process_count_detail')  },
                { text: this.$t('allocations.allocation_list.headers.estimated_time'), value: 'estimated_time', align: 'center', detail: this.$t('allocations.allocation_list.headers.estimated_time_detail') },
                { text: eventHub.$t('allocations.allocation_list.headers.completed_count'), value: 'completed_count', align: 'center', detail:  this.$t('allocations.allocation_list.headers.completed_count_detail') },
                { text: this.$t('allocations.allocation_list.headers.average'), value: 'average', align: 'center', detail: this.$t('allocations.allocation_list.headers.average_detail') },
                { text: this.$t('allocations.allocation_list.headers.percentage'), value: 'percentage', align: 'center', detail: eventHub.$t('allocations.allocation_list.headers.percentage_detail') },
            ]
        },
        userImageSrc () {
            return function (user_image_path) {
                if (user_image_path) {
                    return user_image_path
                } else {
                    return location.origin + '/images/dummy_icon.png'
                }
            }
        },
        disabledCheckbox () {
            // let self = this
            return function (on_task_count, done_task_count) {
                return on_task_count + done_task_count == this.requests.length
            }
        },
        workerDetailPage () {
            return function (userId) {
                return '/management/workers/' + userId
            }
        },
    },
    watch: {
        users () {
            this.$emit('update:selected', this.users)
        },
        selected () {
            this.users = this.selected
        },
        selectedUsers () {
            this.users = this.selectedUsers
        }
    },
    created () {
        this.users = this.selected
    }
}
</script>

<style scoped>
#allocation-list {
    background-color: #ffffff;
}
#allocation-list-main {
    overflow-y: auto;
}
</style>
