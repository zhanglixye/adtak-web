<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div>
                <!-- Main -->
                <div id="search-condition-main">
                    <v-layout row wrap>
                        <!--row1-->
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-text-field
                                v-model="searchParams.business_name"
                                prepend-icon="business"
                                hide-details
                                :label="$t('tasks.search_condition.business_name')"
                                :placeholder="$t('tasks.search_condition.partial_search')"
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
                        <v-flex xs12 sm12 md3 pr-3>
                            <!-- Custom checkbox -->
                            <v-menu
                                offset-y
                                :full-width="true"
                                :close-on-content-click="false">
                                <v-text-field
                                    slot="activator"
                                    v-model="showStatuses"
                                    readonly
                                    hide-details
                                    append-icon="arrow_drop_down"
                                    prepend-icon="local_shipping"
                                    :label="$t('tasks.search_condition.status.label')"
                                    :placeholder="$t('tasks.search_condition.partial_search')"
                                ></v-text-field>
                                <v-card>
                                    <v-list dense>
                                        <v-list-tile v-for="(item, index) in statuses" :key="index">
                                            <v-list-tile-action>
                                                <v-checkbox
                                                    v-model="searchParams.status"
                                                    hide-details
                                                    color="primary"
                                                    :label="$t(item.text)"
                                                    :value="item.value"
                                                ></v-checkbox>
                                            </v-list-tile-action>
                                        </v-list-tile>
                                    </v-list>
                                </v-card>
                            </v-menu>
                            <!-- Custom checkbox -->
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
                <!-- Main -->

                <!-- Detail -->
                <v-slide-y-transition>
                    <div id="search-condition-detail" v-show="show">
                        <v-layout row wrap>
                            <!-- row1 -->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.step_name"
                                            prepend-icon="work"
                                            hide-details
                                            :label="$t('tasks.search_condition.step_name')"
                                            :placeholder="$t('tasks.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!-- row1 -->
                            <!-- row2 -->
                            <v-flex sm6 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3 my-2>
                                        <v-checkbox
                                            v-model="searchParams.unverified"
                                            color="primary"
                                            :label="$t('tasks.search_condition.unverified')"
                                            hide-details
                                            @change="addStatusDone()"
                                        >
                                        </v-checkbox>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!-- row2 -->
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
import store from '../../../../stores/Work/Tasks/store.js'
import DatePicker from '../../../Atoms/Pickers/DatePicker'

export default {
    components:{
        DatePicker
    },
    data: () => ({
        valid: false,
        show: false,

        statuses: [
            { text:'tasks.search_condition.status.none', value: '0' },
            { text: 'tasks.search_condition.status.on', value: '1' },
            { text: 'tasks.search_condition.status.done', value: '2' },
        ],

        // 検索条件を保持するオブジェクト
        searchParams: {
            business_name: store.state.searchParams.business_name,
            step_name: store.state.searchParams.step_name,
            date_type: store.state.searchParams.date_type,
            from: store.state.searchParams.from,
            to: store.state.searchParams.to,
            status: store.state.searchParams.status,
            unverified: store.state.searchParams.unverified,
            page: store.state.searchParams.page,
            sort_by: store.state.searchParams.sort_by,
            descending: store.state.searchParams.descending,
            rows_per_page: store.state.searchParams.rows_per_page,
        },
    }),
    methods: {
        // 検索ボタン押下
        search () {
            // 詳細検索条件を閉じる
            this.show = false
            // ページを初期化
            this.searchParams.page = 1

            // ソート情報は別コンポーネントで更新をかけているため、最新状態の取得が必要
            this.searchParams.sort_by = store.state.searchParams.sort_by
            this.searchParams.descending = store.state.searchParams.descending
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page

            // 検索イベント通知
            eventHub.$emit('search', {
                searchParams: this.searchParams
            })
        },
        // リセットボタン押下
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
            // TODO: 定数定義
            this.searchParams.date_type = this.searchParams.date_type === 1 ? 2 : 1;
        },
        setSearchParams () {
            this.searchParams.business_name = store.state.searchParams.business_name
            this.searchParams.step_name = store.state.searchParams.step_name
            this.searchParams.date_type = store.state.searchParams.date_type
            this.searchParams.from = store.state.searchParams.from
            this.searchParams.to = store.state.searchParams.to
            this.searchParams.status = store.state.searchParams.status
            this.searchParams.unverified = store.state.searchParams.unverified
            this.searchParams.page = store.state.searchParams.page
            this.searchParams.sort_by = store.state.searchParams.sort_by
            this.searchParams.descending = store.state.searchParams.descending
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page
        },
        addStatusDone () {
            if (this.searchParams.unverified && !this.searchParams.status.includes((_const.TASK_STATUS.DONE).toString())) {
                this.searchParams.status.push((_const.TASK_STATUS.DONE).toString())// 既存に合わせるためのtoString
            }
        },
    },
    computed: {
        showStatuses () {
            // valueから表示名に変換
            if (!this.searchParams.status) return
            let array = []
            this.searchParams.status.forEach(function(value) {
                array.push(eventHub.$t(this.statuses[value].text))
            }, this);
            return array.join(', ')
        },
        dateTypeLabel () {
            if (this.searchParams.date_type === _const.DATE_TYPE.CREATED) {
                return Vue.i18n.translate('list.search_condition.created_at');
            } else {
                return Vue.i18n.translate('list.search_condition.deadline');
            }
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        }
    }
}
</script>
