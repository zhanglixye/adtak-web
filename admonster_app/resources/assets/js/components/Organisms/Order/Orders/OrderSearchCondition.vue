<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div>
                <!-- main -->
                <div id="search-condition-main">
                    <v-layout row wrap>
                        <!-- 案件名 -->
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-text-field
                                v-model="searchParams.order_name"
                                prepend-icon="subject"
                                hide-details
                                :label="$t('list.search_condition.order_name')"
                                :placeholder="$t('list.search_condition.partial_search')"
                                @keyup.enter="search"
                            ></v-text-field>
                        </v-flex>
                        <!-- 案件名 -->
                        <!-- ファイル取込日時 -->
                        <v-flex xs12 sm12 md4 pr-3 d-flex align-center>
                            <date-picker
                                v-model="searchParams.from"
                                :enterEvent="search"
                                hide-details
                                :label="$t('list.search_condition.import_at') + $t('list.search_condition.from')"
                            ></date-picker>
                            <span style="text-align: center;">～</span>
                            <date-picker
                                v-model="searchParams.to"
                                date-to
                                :enterEvent="search"
                                hide-details
                                :label="$t('list.search_condition.import_at') + $t('list.search_condition.to')"
                            ></date-picker>
                        </v-flex>
                        <!-- ファイル取込日時 -->
                        <!-- ステータス -->
                        <v-flex sm12 md3>
                            <v-layout column wrap>
                                <v-flex md12 pr-3>
                                    <v-select
                                        v-model="searchParams.status"
                                        :items="status"
                                        item-text="text"
                                        item-value="value"
                                        :label="$t('list.search_condition.status')"
                                        prepend-icon="local_shipping"
                                        dense
                                        hide-details
                                    ></v-select>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <!-- ステータス -->
                        <v-spacer></v-spacer>
                        <div class="btn-center-block">
                            <v-tooltip top>
                                <v-btn
                                    v-show="!show"
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
                <!-- main -->

                <!-- detail -->
                <v-slide-y-transition>
                    <div id="search-condition-detail" v-show="show">
                        <v-layout row wrap>
                            <!-- 取込ファイル名 -->
                            <v-flex xs12 sm12 md3 pr-3>
                                <v-text-field
                                    v-model="searchParams.imported_file_name"
                                    prepend-icon="insert_drive_file"
                                    hide-details
                                    :label="$t('list.search_condition.request_file_name')"
                                    :placeholder="$t('list.search_condition.partial_search')"
                                    @keyup.enter="search"
                                ></v-text-field>
                            </v-flex>
                            <!-- 取込ファイル名 -->
                            <!-- 案件ID -->
                            <v-flex xs12 sm12 md3 pr-3>
                                <v-text-field
                                    v-model="searchParams.order_id"
                                    prepend-icon="insert_drive_file"
                                    hide-details
                                    :label="$t('list.search_condition.order_id')"
                                    :placeholder="$t('list.search_condition.exact_search')"
                                    @keyup.enter="search"
                                ></v-text-field>
                            </v-flex>
                            <!-- 案件ID -->
                            <!-- 取込ファイル担当 -->
                            <v-flex xs12 sm12 md3 pr-3>
                                <v-text-field
                                    v-model="searchParams.importer"
                                    prepend-icon="assignment_ind"
                                    hide-details
                                    :label="$t('list.search_condition.importer')"
                                    :placeholder="$t('list.search_condition.partial_search')"
                                    @keyup.enter="search"
                                ></v-text-field>
                            </v-flex>
                            <!-- 取込ファイル担当 -->
                        </v-layout>
                        <div class="btn-center-block pt-2">
                            <v-btn dark color="grey" @click="clear">
                                {{ $t('common.button.reset') }}
                            </v-btn>
                            <v-btn color="primary" @click="search">
                                {{ $t('common.button.search') }}
                            </v-btn>
                        </div>
                    </div>
                </v-slide-y-transition>
                <!-- detail -->
            </div>
        </v-form>
    </div>
</template>

<script>
import DatePicker from '../../../Atoms/Pickers/DatePicker'
import store from '../../../../stores/Order/Orders/store'


export default {
    components: {
        DatePicker,
    },
    data: () => ({
        valid: false,
        // 検索条件（詳細）の開閉フラグ
        show: false,
        //検索条件
        searchParams: JSON.parse(JSON.stringify(store.state.searchParams)),
    }),
    computed: {
        status () {
            return  [
                { text: this.$t('list.search_condition.active_status.all'), value: null },
                { text: this.$t('list.search_condition.active_status.active'), value: _const.FLG.ACTIVE },
                { text: this.$t('list.search_condition.active_status.inactive'), value: _const.FLG.INACTIVE },
            ]
        },
        stateSearchParams () {
            return store.state.searchParams
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },
    },
    watch: {
        searchParams: {
            handler: function (val) {
                store.commit('setSearchParams', val)
            },
            deep: true
        },
        stateSearchParams: {// state内のsearchParamsを監視
            handler: function (val) {
                Object.assign(this.searchParams, val)
            },
            deep: true
        },
    },
    methods: {
        search () {
            store.commit('setSearchParams', { page : 1 })
            store.dispatch('searchOrderList')
        },
        clear () {
            //検索条件を初期状態に戻す
            store.commit('resetSearchParams')
            store.dispatch('searchOrderList')
        },
    },
}
</script>
