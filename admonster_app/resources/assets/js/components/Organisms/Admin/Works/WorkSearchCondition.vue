<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div>
                <!--main-->
                <div id="search-condition-main">
                    <v-layout row wrap>
                        <!--row1-->
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-text-field
                                v-model="searchParams.worker"
                                prepend-icon="assignment_ind"
                                hide-details
                                :label="$t('list.search_condition.worker')+' / '+$t('list.search_condition.user_id')"
                                :placeholder="$t('list.search_condition.partial_search')"
                                @keyup.enter="search"
                            ></v-text-field>
                        </v-flex>
                        <!--row1-->
                        <!--row2-->
                        <v-flex xs12 sm12 md4 pr-3 d-flex align-center>
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
                            <date-picker
                                v-model="searchParams.from"
                                :enterEvent="search"
                                hide-details
                                :label="dateTypeLabel + $t('list.search_condition.from')"
                            ></date-picker>
                            <span style="text-align: center;">～</span>
                            <date-picker
                                v-model="searchParams.to"
                                date-to
                                :enterEvent="search"
                                hide-details
                                :label="dateTypeLabel + $t('list.search_condition.to')"
                            ></date-picker>
                        </v-flex>
                        <!--row2-->
                        <!--row3-->
                        <v-flex xs12 sm12 md3>
                            <v-combobox
                                v-model="searchParams.status"
                                :items="task_statuses"
                                item-text="text"
                                item-value="value"
                                :label="$t('list.search_condition.status')"
                                :placeholder="$t('list.search_condition.multiple_select')"
                                prepend-icon="local_shipping"
                                dense
                                hide-details
                                multiple
                                small-chips
                                deletable-chips
                            ></v-combobox>
                        </v-flex>
                        <!--row3-->
                        <v-spacer></v-spacer>
                        <div class="btn-center-block">
                            <v-tooltip top>
                                <v-btn
                                    v-show="!show"
                                    :disabled="!valid"
                                    color="primary"
                                    fab
                                    small
                                    @click="search"
                                    slot="activator"
                                >
                                    <v-icon>search</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.search') }}</span>
                            </v-tooltip>
                            <v-btn icon @click="show = !show">
                                <v-icon>{{ toggleIcon }}</v-icon>
                            </v-btn>
                        </div>
                    </v-layout>
                </div>
                <!--main-->

                <!--detail-->
                <v-slide-y-transition>
                    <div id="search-condition-detail" v-show="show">

                        <v-layout row wrap>
                            <!--row1-->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.step_name"
                                            prepend-icon="work"
                                            hide-details
                                            :label="$t('list.search_condition.step_name')"
                                            :placeholder="$t('list.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.client_name"
                                            prepend-icon="person"
                                            hide-details
                                            :label="$t('list.search_condition.client_name')"
                                            :placeholder="$t('list.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!--row1-->
                            <!--row2-->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.subject"
                                            prepend-icon="subject"
                                            hide-details
                                            :label="$t('list.search_condition.subject')"
                                            :placeholder="$t('list.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.business_name"
                                            prepend-icon="business"
                                            hide-details
                                            :label="$t('list.search_condition.business_name')"
                                            :placeholder="$t('list.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!--row2-->
                            <!--row3-->
                            <v-flex sm12 md4>
                                <v-layout wrap>
                                    <v-flex sm6 md12 pr-3>
                                        <v-combobox
                                            v-model="searchParams.approval_status"
                                            :items="approval_statuses"
                                            item-text="text"
                                            item-value="value"
                                            :label="$t('list.search_condition.approval_status')"
                                            :placeholder="$t('works.list.description.approval_select')"
                                            prepend-icon="check_circle"
                                            dense
                                            hide-details
                                            multiple
                                            small-chips
                                            deletable-chips
                                        ></v-combobox>
                                    </v-flex>
                                    <v-flex sm6 md12 pr-3>
                                        <v-checkbox
                                            v-model="searchParams.task_contact"
                                            :label="$t('works.list.task_contact')"
                                            color="primary"
                                            hide-details
                                        ></v-checkbox>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!--row3-->
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
                <!--detail-->
            </div>
        </v-form>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/Works/store'
import DatePicker from '../../../Atoms/Pickers/DatePicker'

const TASK_STATUSES = [
    { text: Vue.i18n.translate('works.list.task_status.none'), value: _const.TASK_STATUS.NONE },
    { text: Vue.i18n.translate('works.list.task_status.on'), value: _const.TASK_STATUS.ON },
    { text: Vue.i18n.translate('works.list.task_status.done'), value: _const.TASK_STATUS.DONE },
];

const APPROVAL_STATUSES = [
    { text: Vue.i18n.translate('works.list.approval_status.none'), value: _const.APPROVAL_STATUS.NONE },
    { text: Vue.i18n.translate('works.list.approval_status.on'), value: _const.APPROVAL_STATUS.ON },
    { text: Vue.i18n.translate('works.list.approval_status.done'), value: _const.APPROVAL_STATUS.DONE },
];

export default {
    data: () => ({
        valid: false,
        // 検索条件（詳細）の開閉フラグ
        show: false,
        //検索条件
        searchParams: {
            request_work_ids: store.state.searchParams.request_work_ids,
            business_name: store.state.searchParams.business_name,
            date_type: store.state.searchParams.date_type,
            from: store.state.searchParams.from,
            to: store.state.searchParams.to,
            client_name: store.state.searchParams.client_name,
            worker: store.state.searchParams.worker,
            subject: store.state.searchParams.subject,
            step_name: store.state.searchParams.step_name,
            status: store.state.searchParams.status,
            approval_status: store.state.searchParams.approval_status,
            task_contact: store.state.searchParams.task_contact,

            page: store.state.searchParams.page,
            sort_by: store.state.searchParams.sort_by,
            descending: store.state.searchParams.descending,
            rows_per_page: store.state.searchParams.rows_per_page,
        },
        task_statuses: TASK_STATUSES,
        approval_statuses: APPROVAL_STATUSES,
    }),
    components: {
        DatePicker
    },
    computed: {
        dateTypeLabel () {
            if (this.searchParams.date_type === _const.DATE_TYPE.CREATED) {
                return Vue.i18n.translate('list.search_condition.task_created_at');
            } else {
                return Vue.i18n.translate('list.search_condition.deadline');
            }
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },
    },
    methods: {
        toggleDateType () {
            if (this.searchParams.date_type === _const.DATE_TYPE.CREATED) {
                this.searchParams.date_type = _const.DATE_TYPE.DEADLINE
            } else {
                this.searchParams.date_type = _const.DATE_TYPE.CREATED
            }
        },
        search () {
            // 詳細検索条件を閉じる
            // this.show = false
            // ページを初期化
            this.searchParams.page = 1

            // ソート情報は別コンポーネントで更新をかけているため、最新状態の取得が必要
            this.searchParams.sort_by = store.state.searchParams.sort_by
            this.searchParams.descending = store.state.searchParams.descending
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page

            // 検索イベント通知
            eventHub.$emit('search',{
                searchParams: this.searchParams
            })
        },
        clear () {
            //検索条件を初期状態に戻す
            store.commit('resetSearchParams')
            this.setSearchParams()
            // 検索イベント通知
            eventHub.$emit('search',{
                searchParams: this.searchParams
            })
        },
        setSearchParams () {
            this.searchParams.request_work_ids = store.state.searchParams.request_work_ids
            this.searchParams.business_name = store.state.searchParams.business_name
            this.searchParams.date_type = store.state.searchParams.date_type
            this.searchParams.from = store.state.searchParams.from
            this.searchParams.to = store.state.searchParams.to
            this.searchParams.client_name = store.state.searchParams.client_name
            this.searchParams.worker = store.state.searchParams.worker
            this.searchParams.subject= store.state.searchParams.subject
            this.searchParams.step_name = store.state.searchParams.step_name
            this.searchParams.status = store.state.searchParams.status
            this.searchParams.approval_status = store.state.searchParams.approval_status
            this.searchParams.task_contact = store.state.searchParams.task_contact

            this.searchParams.page = store.state.searchParams.page
            this.searchParams.sort_by = store.state.searchParams.sort_by,
            this.searchParams.descending = store.state.searchParams.descending,
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page
        },
    },
}
</script>
