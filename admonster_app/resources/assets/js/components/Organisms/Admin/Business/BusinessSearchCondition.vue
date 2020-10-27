<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div>
                <!-- Main -->
                <div id="search-condition-main">
                    <v-layout row wrap>
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-text-field
                                v-model="searchParams.company_name"
                                :label="$t('list.search_condition.company_name')"
                                prepend-icon="location_city"
                                hide-details
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
                                :enterEvent="search"
                                :label="dateTypeLabel + $t('list.search_condition.from')"
                            >
                            </date-time-picker>
                            <v-spacer>～</v-spacer>
                            <date-time-picker
                                v-model="searchParams.to"
                                :enterEvent="search"
                                :label="dateTypeLabel + $t('list.search_condition.to')"
                                date-to
                            ></date-time-picker>
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
                <!-- Main -->

                <!-- Detail -->
                <!-- <v-slide-y-transition>
                    <div id="task-condition-detail" v-show="show">
                        <v-layout row wrap>
                            <v-flex sm12 md4>
                                <v-layout column wrap>
                                    <v-flex md12 pr-5>
                                        <v-switch
                                            v-model="searchParams.completed"
                                            :label="$t('businesses.search_condition.show_completed')"
                                            color="primary"
                                            hide-details
                                        ></v-switch>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
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
                </v-slide-y-transition> -->
                <!-- Detail -->
            </div>
        </v-form>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/Businesses/store.js'
import DateTimePicker from '../../../Atoms/Pickers/DateTimePicker'

export default {
    components:{
        DateTimePicker
    },
    data: () => ({
        valid: false,
        show: false,

        // 検索条件を保持するオブジェクト
        searchParams: {
            company_name: store.state.searchParams.company_name,
            date_type: store.state.searchParams.date_type,
            from: store.state.searchParams.from,
            to: store.state.searchParams.to,
        }
    }),
    methods: {
        search () {
            // 詳細検索条件を閉じる
            // this.show = false
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
            this.searchParams.date_type = this.searchParams.date_type === _const.DATE_TYPE.CREATED ? _const.DATE_TYPE.DEADLINE : _const.DATE_TYPE.CREATED
        },
        setSearchParams () {
            this.searchParams.company_name = store.state.searchParams.company_name
            this.searchParams.date_type = store.state.searchParams.date_type
            this.searchParams.from = store.state.searchParams.from
            this.searchParams.to = store.state.searchParams.to
        },
    },
    computed: {
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
