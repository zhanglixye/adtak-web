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
                                v-model="searchParams.business_name"
                                prepend-icon="work"
                                hide-details
                                :label="$t('requests.search_condition.business_name')"
                                :placeholder="$t('requests.search_condition.partial_search')"
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
                                <span>{{ $t('requests.search_condition.switch_date_type') }}</span>
                            </v-tooltip>
                            <date-picker
                                v-model="searchParams.from"
                                :enterEvent="search"
                                hide-details
                                :label="dateTypeLabel + $t('requests.search_condition.from')"
                            ></date-picker>
                            <span style="text-align: center;">～</span>
                            <date-picker
                                v-model="searchParams.to"
                                date-to
                                :enterEvent="search"
                                hide-details
                                :label="dateTypeLabel + $t('requests.search_condition.to')"
                            ></date-picker>
                        </v-flex>
                        <!--row2-->
                        <!--row3-->
                        <v-flex sm12 md3>
                            <v-layout column wrap>
                                <v-flex md12 pr-3>
                                    <v-select
                                        class="caption"
                                        v-model="searchParams.status"
                                        prepend-icon="local_shipping"
                                        hide-details
                                        :items="options"
                                        item-value="value"
                                        item-text="text"
                                        :label="$t('requests.search_condition.status.label')"
                                        return-masked-value
                                        dense
                                    ></v-select>
                                </v-flex>
                            </v-layout>
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
                            <v-flex sm12 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.client_name"
                                            prepend-icon="person"
                                            hide-details
                                            :label="$t('requests.search_condition.client_name')"
                                            :placeholder="$t('requests.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <v-flex sm12 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.request_name"
                                            prepend-icon="subject"
                                            hide-details
                                            :label="$t('requests.search_condition.request_name')"
                                            :placeholder="$t('requests.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <v-flex sm12 md2>
                                <v-layout column wrap>
                                    <v-flex md12 pr-3>
                                        <v-text-field
                                            v-model="searchParams.request_file_id"
                                            prepend-icon="insert_drive_file"
                                            hide-details
                                            :label="$t('list.search_condition.request_file_id')"
                                            :placeholder="$t('list.search_condition.exact_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <!--row1-->
                        </v-layout>
                        <v-card-actions class="justify-center">
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
                        </v-card-actions>
                    </div>
                </v-slide-y-transition>
                <!--detail-->
            </div>
        </v-form>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/Requests/store'
import DatePicker from '../../../Atoms/Pickers/DatePicker'

export default {
    data: () => ({
        valid: false,

        // 詳細な検索条件の開閉フラグ
        show: false,
        //検索条件
        searchParams: store.getters.getSearchParams,
    }),
    components: {
        DatePicker
    },
    computed: {
        dateTypeLabel () {
            if (this.searchParams.date_type === 1) {
                return eventHub.$t('requests.search_condition.created_at');
            } else {
                return eventHub.$t('requests.search_condition.deadline');
            }
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },
        options () {
            return [
                {value: _const.REQUEST_STATUS.ALL, text:eventHub.$t('requests.search_condition.status.all')},
                {value: _const.REQUEST_STATUS.DOING, text:eventHub.$t('requests.search_condition.status.doing')},
                {value: _const.REQUEST_STATUS.FINISH, text:eventHub.$t('requests.search_condition.status.finished')},
                {value: _const.REQUEST_STATUS.EXCEPT, text:eventHub.$t('requests.search_condition.status.Exclusion')}
            ]
        }
    },
    methods: {
        toggleDateType () {
            this.searchParams.date_type = this.searchParams.date_type === 1 ? 2 : 1;
        },
        search () {
            // 詳細検索条件を閉じる
            // this.show = false
            // ページを初期化
            this.searchParams.page = 1

            // ソート情報は別コンポーネントで更新をかけているため、最新状態の取得が必要
            this.searchParams.sort_by = store.getters.getSearchParams.sort_by
            this.searchParams.descending = store.getters.getSearchParams.descending
            this.searchParams.rows_per_page = store.getters.getSearchParams.rows_per_page

            // 検索イベント通知
            eventHub.$emit('search',{
                searchParams: this.searchParams
            })
        },
        clear () {
            // 検索条件を初期状態に戻す
            store.dispatch('setSearchParams')
            // 検索イベント通知
            eventHub.$emit('search',{
                searchParams: this.searchParams
            })
        },
    }
}
</script>
