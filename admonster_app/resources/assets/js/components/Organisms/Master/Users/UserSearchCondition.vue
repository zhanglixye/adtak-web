<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div id="search-condition-main">
                <v-layout row wrap>
                    <v-flex xs12 sm12 md3 pr-3>
                        <v-text-field
                            :label="$t('list.search_condition.user_id')"
                            prepend-icon="account_balance_wallet"
                            v-model="searchParams.id"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12 sm12 md3 pr-3>
                        <v-text-field
                            prepend-icon="person"
                            :label="$t('list.search_condition.user_name')"
                            :placeholder="$t('list.search_condition.partial_search')"
                            v-model="searchParams.name"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12 sm12 md3 pr-3>
                        <v-text-field
                                prepend-icon="email"
                                :label="$t('list.search_condition.user_mail')"
                                :placeholder="$t('list.search_condition.partial_search')"
                                v-model="searchParams.email"
                        >
                        </v-text-field>
                    </v-flex>
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
                        <v-btn icon disabled @click="show = !show">
                            <!-- <v-icon>{{ toggleIcon }}</v-icon> -->
                        </v-btn>
                    </div>
                </v-layout>

            </div>
        </v-form>
    </div>
</template>

<script>
import store from '../../../../stores/Master/Users/store'
export default {
    name: 'UserSearchCondition',
    data: () =>({
        valid: false,
        show: false,
        searchParams: store.getters.getSearchParams,
    }),
    methods: {
        search(){
            this.searchParams.page = 1

            // ソート情報は別コンポーネントで更新をかけているため、最新状態の取得が必要
            this.searchParams.sort_by = store.getters.getSearchParams.sort_by
            this.searchParams.descending = store.getters.getSearchParams.descending
            this.searchParams.rows_per_page = store.getters.getSearchParams.rows_per_page

            // 検索イベント通知
            eventHub.$emit('search',{
                searchParams: this.searchParams
            })
        }
    }
}
</script>

<style scoped>

</style>