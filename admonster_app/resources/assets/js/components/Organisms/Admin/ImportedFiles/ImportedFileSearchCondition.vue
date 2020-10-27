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
                                v-model="searchParams.imported_file_name"
                                prepend-icon="insert_drive_file"
                                hide-details
                                :label="$t('list.search_condition.request_file_name')"
                                :placeholder="$t('list.search_condition.partial_search')"
                                @keyup.enter="search"
                            ></v-text-field>
                        </v-flex>
                        <!--row1-->
                        <!--row2-->
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
                                        :items="status"
                                        item-value="value"
                                        item-text="text"
                                        :label="$t('list.search_condition.status')"
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
                            <!--row1-->
                            <!--row2-->
                            <v-flex xs12 sm12 md3 pr-3>
                                <v-text-field
                                    v-model="searchParams.importer"
                                    prepend-icon="business"
                                    hide-details
                                    :label="$t('list.search_condition.importer')"
                                    :placeholder="$t('list.search_condition.partial_search')"
                                    @keyup.enter="search"
                                ></v-text-field>
                            </v-flex>
                            <!--row2-->
                            <!--row3-->
                            <v-flex xs12 sm12 md3 pr-3>
                                <v-text-field
                                    v-model="searchParams.imported_file_id"
                                    prepend-icon="insert_drive_file"
                                    hide-details
                                    :label="$t('list.search_condition.request_file_id')"
                                    :placeholder="$t('list.search_condition.exact_search')"
                                    @keyup.enter="search"
                                ></v-text-field>
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
import store from '../../../../stores/Admin/ImportedFiles/store'
import DatePicker from '../../../Atoms/Pickers/DatePicker'

const IMPORTED_FILE_STATUS = [
    { text: Vue.i18n.translate('imported_files.search_condition.status.all'), value: _const.IMPORTED_FILE_STATUS.ALL },
    { text: Vue.i18n.translate('imported_files.search_condition.status.doing'), value: _const.IMPORTED_FILE_STATUS.DOING },
    { text: Vue.i18n.translate('imported_files.search_condition.status.finished'), value: _const.IMPORTED_FILE_STATUS.FINISH },
];

export default {
    components: {
        DatePicker
    },
    props: {
        // GETパラメータでの検索時のみtrueとするため、更新もしないしstoreでも管理しない
        searchConditionDetailShow: { type: Boolean }
    },
    data: () => ({
        valid: false,
        // 検索条件（詳細）の開閉フラグ
        show: false,
        //検索条件
        searchParams: {
            imported_file_name: store.state.searchParams.imported_file_name,
            imported_file_id: store.state.searchParams.imported_file_id,
            business_name: store.state.searchParams.business_name,
            from: store.state.searchParams.from,
            to: store.state.searchParams.to,
            importer: store.state.searchParams.importer,
            status: store.state.searchParams.status,

            page: store.state.searchParams.page,
            sort_by: store.state.searchParams.sort_by,
            descending: store.state.searchParams.descending,
            rows_per_page: store.state.searchParams.rows_per_page,
        },
        status: IMPORTED_FILE_STATUS,
    }),
    computed: {
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },
    },
    mounted () {
        this.show = this.searchConditionDetailShow
    },
    methods: {
        search () {
            // ページを初期化
            this.searchParams.page = 1

            // ソート情報は別コンポーネントで更新をかけているため、最新状態の取得が必要
            this.searchParams.sort_by = store.state.searchParams.sort_by
            this.searchParams.descending = store.state.searchParams.descending
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page

            // 検索結果一覧を表示
            store.commit('openSearchList')
            // 最新の取り込みファイル一覧を非表示
            store.commit('closeLatestImportedFileList')
            //storeへ登録
            store.commit('setSearchParams', { params: this.searchParams })
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
            this.searchParams.imported_file_name = store.state.searchParams.imported_file_name
            this.searchParams.imported_file_id = store.state.searchParams.imported_file_id
            this.searchParams.business_name = store.state.searchParams.business_name
            this.searchParams.from = store.state.searchParams.from
            this.searchParams.to = store.state.searchParams.to
            this.searchParams.importer = store.state.searchParams.importer
            this.searchParams.status = store.state.searchParams.status

            this.searchParams.page = store.state.searchParams.page
            this.searchParams.sort_by = store.state.searchParams.sort_by
            this.searchParams.descending = store.state.searchParams.descending
            this.searchParams.rows_per_page = store.state.searchParams.rows_per_page
        },
    },
}
</script>
