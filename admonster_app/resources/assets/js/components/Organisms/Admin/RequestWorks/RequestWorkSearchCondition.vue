<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div>
                <!-- Main -->
                <div id="search-condition-main">
                    <v-layout row wrap>
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-text-field
                                v-model="searchParams.business_name"
                                prepend-icon="business"
                                hide-details
                                :label="$t('list.search_condition.business_name')"
                                :placeholder="$t('list.search_condition.partial_search')"
                                @keyup.enter="search"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm12 md6 d-flex align-center>
                            <v-tooltip top>
                                <v-btn
                                    slot="activator"
                                    outline
                                    small
                                    fab
                                    color="primary"
                                    @click="toggleDateType">
                                    <v-icon>event</v-icon>
                                </v-btn>
                                <span>{{ $t('list.search_condition.switch_date_type') }}</span>
                            </v-tooltip>
                            <date-time-picker
                                v-model="searchParams.from"
                                :label="dateTypeLabel + $t('list.search_condition.from')"
                            >
                            </date-time-picker>
                            <v-spacer>～</v-spacer>
                            <date-time-picker
                                v-model="searchParams.to"
                                :label="dateTypeLabel + $t('list.search_condition.to')"
                                date-to
                            ></date-time-picker>
                        </v-flex>
                        <v-spacer></v-spacer>
                        <div class="btn-center-block">
                            <v-btn
                                v-show="!show"
                                :disabled="!valid"
                                color="primary"
                                @click="search"
                            >{{ $t('common.button.search') }}</v-btn>
                            <v-btn icon @click="show = !show">
                                <v-icon>{{ toggleIcon }}</v-icon>
                            </v-btn>
                        </div>
                    </v-layout>
                </div>
                <!-- Main -->

                <!-- Detail -->
                <v-slide-y-transition>
                    <div id="search-condition-detail" v-show="show">

                        <v-layout row wrap>
                            <!-- Column1 -->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.request_work_name"
                                            prepend-icon="work_outline"
                                            hide-details
                                            :label="$t('request_works.search_condition.request_work_name')"
                                            :placeholder="$t('request_works.search_condition.partial_search')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.step_name"
                                            prepend-icon="work"
                                            hide-details
                                            :label="$t('request_works.search_condition.current_task')"
                                            :placeholder="$t('request_works.search_condition.partial_search')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.operator_name"
                                            prepend-icon="assignment_ind"
                                            hide-details
                                            :label="$t('request_works.search_condition.operator')"
                                            :placeholder="$t('request_works.search_condition.partial_search')"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!-- Column1 -->
                            <!-- Column2 -->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.request_file_name"
                                            prepend-icon="file_copy"
                                            hide-details
                                            :label="$t('request_works.search_condition.request_file.label')"
                                            :placeholder="$t('request_works.search_condition.partial_search')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.request_mail_subject"
                                            prepend-icon="email"
                                            hide-details
                                            :label="$t('request_works.search_condition.request_mail.subject')"
                                            :placeholder="$t('request_works.search_condition.partial_search')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.request_mail_to"
                                            prepend-icon="email"
                                            hide-details
                                            :label="$t('request_works.search_condition.request_mail.to_address')"
                                            :placeholder="$t('request_works.search_condition.partial_search')"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!-- Column2 -->
                            <!-- Column3 -->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <!-- status -->
                                        <v-menu
                                            offset-y
                                            :full-width="true"
                                            :close-on-content-click="false"
                                        >
                                            <v-text-field
                                                v-model="showStatuses"
                                                slot="activator"
                                                prepend-icon="local_shipping"
                                                append-icon="arrow_drop_down"
                                                readonly
                                                :label="$t('request_works.search_condition.status.label')"
                                                :placeholder="$t('request_works.search_condition.plural_selected')"
                                                hide-details
                                            ></v-text-field>
                                            <v-card>
                                                <v-list dense>
                                                    <template v-for="(item, i) in statuses">
                                                        <!-- divider -->
                                                        <v-divider v-if="item.divider" class="my-0" :key="i"></v-divider>
                                                        <!-- title -->
                                                        <v-list-tile v-else-if="item.title" :key="i">
                                                            <v-list-tile-title>
                                                                <v-icon>{{ item.icon }}</v-icon>
                                                                {{ $t(item.title) }}
                                                            </v-list-tile-title>
                                                        </v-list-tile>
                                                        <!-- checkboxes -->
                                                        <v-list-tile v-else-if="item.checkboxes" :key="i">
                                                            <v-layout justify-space-around row>
                                                                <v-list-tile-action
                                                                    v-for="(checkbox, i) in item.checkboxes"
                                                                    :key="i"
                                                                >
                                                                    <v-checkbox
                                                                        v-model="searchParams.status"
                                                                        :label="$t(checkbox.text)"
                                                                        :value="checkbox.value"
                                                                        :color="checkbox.color"
                                                                        hide-details
                                                                    ></v-checkbox>
                                                                </v-list-tile-action>
                                                            </v-layout>
                                                        </v-list-tile>
                                                    </template>
                                                </v-list>
                                            </v-card>
                                        </v-menu>
                                        <!-- status -->
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-switch
                                            v-model="searchParams.self"
                                            :label="$t('request_works.search_condition.self_assignment_or_approval_show')"
                                            color="primary"
                                            hide-details
                                        ></v-switch>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-switch
                                            v-model="searchParams.excluded"
                                            :label="$t('request_works.search_condition.excluded_show')"
                                            color="primary"
                                            hide-details
                                        ></v-switch>
                                    </v-flex>
                                    <v-flex md12 pr-3 d-flex>
                                        <!-- <v-layout row wrap> -->
                                            <!-- <v-flex md6> -->
                                                <v-switch
                                                    v-model="searchParams.inactive"
                                                    :label="$t('request_works.search_condition.inactive_show')"
                                                    color="primary"
                                                    hide-details
                                                ></v-switch>
                                            <!-- </v-flex> -->
                                            <!-- <v-flex md6> -->
                                                <v-switch
                                                    v-model="searchParams.completed"
                                                    :label="$t('request_works.search_condition.completed_show')"
                                                    color="primary"
                                                    hide-details
                                                ></v-switch>
                                            <!-- </v-flex> -->
                                        <!-- </v-layout> -->
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!-- Column3 -->
                        </v-layout>
                        <div class="btn-center-block pt-2">
                            <v-btn
                                dark
                                color="grey"
                                @click="clear"
                            >{{ $t('common.button.reset') }}</v-btn>
                            <v-btn
                                :disabled="!valid"
                                color="primary"
                                @click="search"
                            >{{ $t('common.button.search') }}</v-btn>
                        </div>
                    </div>
                </v-slide-y-transition>
                <!-- Detail -->
            </div>
        </v-form>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/RequestWorks/store.js'
import DateTimePicker from '../../../Atoms/Pickers/DateTimePicker'

const statuses = [
    { title: 'request_works.search_condition.process.allocation', icon: 'assignment' },
    {
        checkboxes: [
            { text: 'request_works.search_condition.status.in_process', value: '11', color: 'primary' },
            { text: 'request_works.search_condition.status.completed', value: '12', color: 'primary' },
        ],
    },
    { divider: true },
    { title: 'request_works.search_condition.process.work', icon: 'work' },
    {
        checkboxes: [
            { text: 'request_works.search_condition.status.in_process', value: '21', color: 'primary' },
            { text: 'request_works.search_condition.status.completed', value: '22', color: 'primary' },
        ],
    },
    { divider: true },
    { title: 'request_works.search_condition.process.approval', icon: 'visibility' },
    {
        checkboxes: [
            { text: 'request_works.search_condition.status.in_process', value: '31', color: 'primary' },
            { text: 'request_works.search_condition.status.completed', value: '32', color: 'primary' },
        ],
    },
]

export default {
    components: {
        DateTimePicker
    },
    data: () => ({
        // 入力フラグ
        valid: false,
        // 詳細な検索条件の開閉フラグ
        show: false,

        // combobox
        statuses: statuses,

        // 検索条件
        searchParams: {
            business_name: store.state.searchParams.business_name,
            request_work_name: store.state.searchParams.request_work_name,
            step_name: store.state.searchParams.step_name,
            operator_name: store.state.searchParams.operator_name,
            date_type: store.state.searchParams.date_type,
            from: store.state.searchParams.from,
            to: store.state.searchParams.to,
            request_file_name: store.state.searchParams.request_file_name,
            request_mail_subject: store.state.searchParams.request_mail_subject,
            request_mail_to: store.state.searchParams.request_mail_to,
            status: store.state.searchParams.status,
            self: store.state.searchParams.self,
            completed: store.state.searchParams.completed,
            inactive: store.state.searchParams.inactive,
            excluded: store.state.searchParams.excluded,
            page: store.state.searchParams.page,
            sort_by: store.state.searchParams.sort_by,
            descending: store.state.searchParams.descending,
            rows_per_page: store.state.searchParams.rows_per_page,
        },
    }),
    methods: {
        search () {
            // 詳細検索条件を閉じる
            // this.show = false
            // ページを初期化
            this.searchParams.page = 1
            // 検索イベント通知
            let searchParamsForStore = Vue.util.extend({}, this.searchParams)
            eventHub.$emit('search', {
                searchParams: searchParamsForStore
            })
        },
        clear () {
            // 検索条件を初期状態に戻す
            store.commit('resetSearchParams')
            this.setSearchParams()
            // 検索イベント通知
            eventHub.$emit('search', {
                searchParams: this.searchParams
            })
        },
        toggleDateType () {
            this.searchParams.date_type = this.searchParams.date_type === _const.DATE_TYPE.CREATED ? _const.DATE_TYPE.DEADLINE : _const.DATE_TYPE.CREATED;
        },
        setSearchParams () {
            this.searchParams.business_name = store.state.searchParams.business_name
            this.searchParams.request_work_name = store.state.searchParams.request_work_name
            this.searchParams.step_name = store.state.searchParams.step_name
            this.searchParams.operator_name = store.state.searchParams.operator_name
            this.searchParams.date_type = store.state.searchParams.date_type
            this.searchParams.from = store.state.searchParams.from
            this.searchParams.to = store.state.searchParams.to
            this.searchParams.request_file_name = store.state.searchParams.request_file_name
            this.searchParams.request_mail_subject = store.state.searchParams.request_mail_subject
            this.searchParams.request_mail_to = store.state.searchParams.request_mail_to
            this.searchParams.status = store.state.searchParams.status
            this.searchParams.self = store.state.searchParams.self
            this.searchParams.completed = store.state.searchParams.completed
            this.searchParams.inactive = store.state.searchParams.inactive
            this.searchParams.excluded = store.state.searchParams.excluded
            this.searchParams.page = store.state.searchParams.page
            this.searchParams.sort_by = store.state.searchParams.sort_by
            this.searchParams.descending = store.state.searchParams.descending
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page
        },
    },
    computed: {
        dateTypeLabel () {
            if (this.searchParams.date_type === _const.DATE_TYPE.CREATED) {
                return eventHub.$t('request_works.search_condition.created_at');
            } else {
                return eventHub.$t('request_works.search_condition.deadline');
            }
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },
        showStatuses () {
            // TODO: 検討
            let status_map = {
                11: this.$t('request_works.search_condition.status.assignment_unfinished'),
                12: this.$t('request_works.search_condition.status.assignment_finished'),
                21: this.$t('request_works.search_condition.status.work_unfinished'),
                22: this.$t('request_works.search_condition.status.work_finished'),
                31: this.$t('request_works.search_condition.status.approval_unfinished'),
                32: this.$t('request_works.search_condition.status.approval_finished'),
            }

            let array = [];
            this.searchParams.status.forEach(function(value) {
                // 未納品は除外
                if (value === '41') return
                array.push(status_map[value])
            }, this)

            if (array.length === Object.keys(status_map).length) {
                array = [this.$t('request_works.search_condition.status.all_selected')]
            }

            return array.join(', ')
        }
    }
}
</script>
