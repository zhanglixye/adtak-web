<template>
    <div id="allocation-list" class="elevation-1">
        <v-list>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>assignment_ind</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t('education_allocations.allocation_list.operator') }}</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-text-field
                        v-model="search"
                        append-icon="search"
                        :label="$t('education_allocations.allocation_list.search')"
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
                    <v-checkbox
                        v-if="props.header.input == 'checkbox'"
                        :input-value="isCheckedAllDoneItems(props.header.inputName, props.header.inputValue)"
                        :indeterminate="isIndeterminate(props.header.inputName, props.header.inputValue)"
                        :style="[props.header.detail ? {'justify-content': 'center'} : '']"
                        primary
                        hide-details
                        color="primary"
                        @click.native="toggleAll(props.header.inputName, props.header.inputValue)"
                    >
                        <template slot="label">
                            <v-tooltip top v-if="props.header.detail">
                                <span slot="activator" class="header-text">{{props.header.text}}</span>
                                <span>{{props.header.detail}}</span>
                            </v-tooltip>
                        </template>
                    </v-checkbox>
                    <v-tooltip top v-else-if="props.header.detail">
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
                                <span>{{ $t('education_allocations.list.notice.unallocated_status') }}</span>
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
                        <td class="text-xs-center">
                            <v-checkbox
                                :input-value="!props.item.is_display_educational"
                                :disabled="disabledCheckbox(props.item.on_task_count, props.item.done_task_count)"
                                primary
                                hide-details
                                color="primary"
                                style="justify-content: center;"
                                @click.native="props.item.is_display_educational = !props.item.is_display_educational"
                            ></v-checkbox>
                        </td>
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
        usersAll: [],
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
    computed: {
        headers () {
            return [
                { text: '', value: 'user_id', align: 'center', sortable: false, input: 'checkbox' },
                { text: this.$t('education_allocations.allocation_list.headers.user_name'), value: 'user_name', align: 'center' },
                { text: this.$t('education_allocations.allocation_list.headers.work_in_process_count'), value: 'work_in_process_count', align: 'center', detail: this.$t('education_allocations.allocation_list.headers.work_in_process_count_detail')  },
                { text: this.$t('education_allocations.allocation_list.headers.estimated_time'), value: 'estimated_time', align: 'center', detail: this.$t('education_allocations.allocation_list.headers.estimated_time_detail') },
                { text: eventHub.$t('education_allocations.allocation_list.headers.completed_count'), value: 'completed_count', align: 'center', detail:  this.$t('education_allocations.allocation_list.headers.completed_count_detail') },
                { text: this.$t('education_allocations.allocation_list.headers.average'), value: 'average', align: 'center', detail: this.$t('education_allocations.allocation_list.headers.average_detail') },
                { text: this.$t('education_allocations.allocation_list.headers.percentage'), value: 'percentage', align: 'center', detail: eventHub.$t('education_allocations.allocation_list.headers.percentage_detail') },
                { text: this.$t('education_allocations.allocation_list.headers.education_work'), value: 'is_display_educational', align: 'center', input: 'checkbox', inputName: 'is_display_educational', inputValue: false, sortable: false, detail: this.$t('education_allocations.allocation_list.headers.education_work_detail') },
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
        isIndeterminate () {
            return (column, value) => {
                const [totalCount, checkCount] = this.getCheckedCounts(column, value)
                return checkCount > 0 && checkCount < totalCount
            }
        },
        isCheckedAllDoneItems () {
            return (column, value) => {
                const [totalCount, checkCount] = this.getCheckedCounts(column, value)
                return totalCount > 0 && checkCount == totalCount
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
        usersAll (users) {
            this.$emit('update:operators', users)
        },
        operators (operators) {
            this.usersAll = operators
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
    },
    methods: {
        reset (operators) {
            // 絞り込み
            this.search = ''
            // ソート
            this.pagination.sortBy = 'average'
            this.pagination.descending = false
            this.usersAll = operators
            this.users = []
        },
        toggleAll (column, value) {
            if (column) {
                const isCheckedAll = this.isCheckedAllDoneItems(column, value)
                this.usersAll = this.usersAll.map(user => {
                    if (!this.disabledCheckbox(user.on_task_count, user.done_task_count)) {
                        user[column] = isCheckedAll ? !value : value
                    }
                    return user
                })
            } else {
                this.users = this.users.length != this.operators.length ? this.operators : []
            }
        },
        getCheckedCounts (column, value) {
            let totalCount = this.operators.length
            let checkCount = this.users.length
            if (column) {
                checkCount = this.usersAll.filter(user => user[column] == value).length
            }
            return [totalCount, checkCount]
        }
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
.header-text {
    font-size: 12px;
    font-weight: 500;
    color: rgba(0, 0, 0, 0.87);
}
</style>
