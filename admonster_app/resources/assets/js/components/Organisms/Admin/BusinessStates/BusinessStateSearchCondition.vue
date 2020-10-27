<template>
    <div id="business-condition">
        <v-form v-model="valid">
            <v-card>
                <v-container fluid grid-list-md pa-3>

                    <!-- Main -->
                    <div id="business-condition-main">
                        <v-layout row wrap>
                            <v-flex xs12 sm12 md4 pr-5>
                                <v-text-field
                                    v-model="searchParams.business_name"
                                    prepend-icon="business"
                                    :label="$t('business_states.search_condition.business_name')"
                                    :placeholder="$t('business_states.search_condition.partial_search')"
                                    :disabled="true"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm12 md5 d-flex align-center>
                                <v-tooltip top>
                                    <v-btn
                                        slot="activator"
                                        outline
                                        small
                                        fab
                                        color="primary"
                                        class="ml-0 mr-3"
                                        @click="toggleDateType">
                                        <v-icon>event</v-icon>
                                    </v-btn>
                                    <span>{{ $t('business_states.switch_date_type') }}</span>
                                </v-tooltip>
                                <date-time-picker
                                    v-model="searchParams.from"
                                    :label="dateTypeLabel + $t('business_states.search_condition.from')"
                                ></date-time-picker>
                                <span class="ma-3">～</span>
                                <date-time-picker
                                    v-model="searchParams.to"
                                    :label="dateTypeLabel + $t('business_states.search_condition.to')"
                                ></date-time-picker>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-card-actions>
                                <v-btn
                                    v-show="!show"
                                    :disabled="!valid"
                                    color="primary"
                                    @click="search"
                                >{{ $t('common.button.search') }}</v-btn>
                            </v-card-actions>
                            <v-card-actions>
                                <v-btn icon @click="show = !show">
                                    <v-icon>{{ toggleIcon }}</v-icon>
                                </v-btn>
                            </v-card-actions>
                        </v-layout>
                    </div>
                    <!-- Main -->

                    <!-- Detail -->
                    <v-slide-y-transition>
                        <div id="task-condition-detail" v-show="show">
                            <v-layout row wrap>
                                <!-- 一旦コメント -->
                                <!-- <v-flex sm12 md4>
                                    <v-layout column wrap>
                                        <v-flex md12 pr-5>
                                            <v-switch
                                                v-model="searchParams.completed"
                                                :label="$t('business_states.search_condition.show_completed')"
                                                color="primary"
                                                hide-details
                                            ></v-switch>
                                        </v-flex>
                                    </v-layout>
                                </v-flex> -->
                                <v-flex sm12 md4>
                                    <v-layout column wrap>
                                        <v-flex md12 pr-5>
                                            <v-switch
                                                v-model="searchParams.inactive"
                                                :label="$t('business_states.search_condition.inactive_show')"
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
                    </v-slide-y-transition>
                    <!-- Detail -->

                </v-container>
            </v-card>
        </v-form>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/BusinessStates/store.js'
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
            business_name: store.state.searchParams.business_name,
            date_type: store.state.searchParams.date_type,
            from: store.state.searchParams.from,
            to: store.state.searchParams.to,
            inactive: store.state.searchParams.inactive,
        }
    }),
    methods: {
        search () {
            // 詳細検索条件を閉じる
            this.show = false
            // 検索イベント通知
            eventHub.$emit('search', {
                searchParams: this.searchParams
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
            this.searchParams.business_name = store.state.searchParams.business_name
            this.searchParams.date_type = store.state.searchParams.date_type
            this.searchParams.from = store.state.searchParams.from
            this.searchParams.to = store.state.searchParams.to
            this.searchParams.inactive = store.state.searchParams.inactive
        }
    },
    computed: {
        dateTypeLabel () {
            if (this.searchParams.date_type === _const.DATE_TYPE.CREATED) {
                return eventHub.$t('business_states.search_condition.created_at');
            } else {
                return eventHub.$t('business_states.search_condition.deadline');
            }
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        }
    }
}
</script>
