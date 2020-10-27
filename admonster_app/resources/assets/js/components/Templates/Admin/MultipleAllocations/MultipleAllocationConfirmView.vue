<template>
    <v-app id="inspire">
        <app-menu :drawer="drawer"></app-menu>
        <app-header
            :title="businessName"
            :subtitle="$t('allocations.collective_assignment_confirm')"
        ></app-header>
        <v-content id="data-content">
                <v-container fluid grid-list-md>
                    <v-layout row wrap mb-3>
                        <!-- Main -->
                        <v-flex xs12>
                            <page-header :alerts="alerts"></page-header>
                        </v-flex>

                        <v-layout row wrap>
                            <v-flex xs3>
                                <v-card class="allocation-condition text-xs-center">
                                    <v-card-text class="px-0">対象案件数</v-card-text>
                                    <v-divider class="ma-0"></v-divider>
                                    <v-card-text class="px-0">
                                        <template>
                                            <v-layout pr-2 pl-2 align-center>
                                                <v-flex style="">
                                                    <a><span class="display-1 font-weight-bold mr-1" @click="targetRequestListDialog = !targetRequestListDialog">{{ requests.length }}</span></a>件
                                                </v-flex>
                                            </v-layout>
                                        </template>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <v-flex xs3>
                                <v-card class="allocation-condition text-xs-center">
                                    <v-card-text class="px-0">発生作業件数
                                        <v-tooltip top>
                                            <span slot="activator"><v-icon small>contact_support</v-icon></span>
                                            <span class="text-xs-center">対象案件数 × 並列度<br>（作業中、完了済みを除く）</span>
                                        </v-tooltip>
                                    </v-card-text>
                                    <v-divider class="ma-0"></v-divider>
                                    <v-card-text class="px-0">
                                        <template>
                                            <v-layout pr-2 pl-2 align-center>
                                                <v-flex style="">
                                                    <a><span class="display-1 font-weight-bold mr-1">{{ newTasksCnt }}</span></a>件
                                                </v-flex>
                                            </v-layout>
                                        </template>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <v-flex xs3>
                                <v-card class="allocation-condition text-xs-center">
                                    <v-card-text class="px-0">並列度</v-card-text>
                                    <v-divider class="ma-0"></v-divider>
                                    <v-card-text class="px-0">
                                        <template>
                                            <v-layout pr-2 pl-2 align-center>
                                                <v-flex>
                                                    <span>1件につき</span><span class="display-1 font-weight-bold pl-2 pr-2">{{ parallel }}</span><span>人を割振る</span>
                                                </v-flex>
                                            </v-layout>
                                        </template>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <v-flex xs3>
                                <v-card class="allocation-condition text-xs-center">
                                    <v-card-text class="px-0">均等度</v-card-text>
                                    <v-divider class="ma-0"></v-divider>
                                    <v-card-text class="px-0">
                                        <span v-if="!evennessIndividual" class="headline font-weight-bold mr-1">{{ $t('allocations.evenness_type.even') }}</span>
                                        <span v-else class="headline font-weight-bold mr-1">{{ $t('allocations.evenness_type.individual') }}
                                            <v-icon v-if="individualPanel" @click="individualPanel = !individualPanel">keyboard_arrow_up</v-icon>
                                            <v-icon v-else @click="individualPanel = !individualPanel">keyboard_arrow_down</v-icon>
                                        </span>
                                    </v-card-text>
                                    <v-flex xs12 sm12 md12 v-show="individualPanel">
                                        <div v-if="selectedUsersInfos.length > 0">
                                            <v-list>
                                                <v-list-tile
                                                    v-for="(item) in selectedUsersInfos"
                                                    avatar
                                                    :key="item.user_name"
                                                    >
                                                    <v-list-tile-avatar>
                                                        <v-avatar size="32px" :tile="false">
                                                            <img :src="userImageSrc(item.user_image_path)">
                                                        </v-avatar>
                                                    </v-list-tile-avatar>
                                                    <v-list-tile-content>
                                                        <v-list-tile-sub-title>{{ item.user_name }}</v-list-tile-sub-title>
                                                    </v-list-tile-content>
                                                    <v-list-tile-action>
                                                        <span>{{ ratios[item.user_id] }} %</span>
                                                    </v-list-tile-action>
                                                </v-list-tile>
                                            </v-list>
                                            <v-divider class="ma-0"></v-divider>
                                            <v-list>
                                                <v-list-tile>
                                                    <v-list-tile-content>
                                                        <v-list-tile-sub-title>合計</v-list-tile-sub-title>
                                                    </v-list-tile-content>
                                                    <v-list-tile-action>
                                                        <span>{{ ratioSum }} %</span>
                                                    </v-list-tile-action>
                                                </v-list-tile>
                                            </v-list>
                                        </div>
                                    </v-flex>
                                </v-card>
                            </v-flex>
                            <!-- <v-flex xs4>
                                <v-card class="allocation-condition text-xs-center">
                                    <v-card-text class="px-0 pr-5 pl-5">発生作業件数 : &nbsp<span class="display-1 font-weight-bold mr-1">{{ requests.length }}</span>件</v-card-text>
                                </v-card>
                            </v-flex> -->
                        </v-layout>
                        <!-- Main -->

                    </v-layout>
                    <v-toolbar tabs color="grey darken-1">
                        <v-tabs
                            v-model="tabs"
                            fixed-tabs
                            color="transparent"
                            left
                        >
                            <v-tabs-slider></v-tabs-slider>
                            <v-tab href="#tabs-request-works" class="primary--text">
                                <v-icon color="grey lighten-3">work</v-icon>
                                <span class="tab-title ml-1">案件別</span>
                            </v-tab>
                            <v-tab href="#tabs-operators" class="primary--text">
                                <v-icon color="grey lighten-3">assignment_ind</v-icon>
                                <span class="tab-title ml-1">担当者別</span>
                            </v-tab>
                        </v-tabs>
                    </v-toolbar>
                    <v-tabs-items v-model="tabs" class="white elevation-1">
                        <v-tab-item
                          :key="1"
                          :value="'tabs-request-works'"
                        >
                            <request-work :requests="provisinalRequests" :candidates="candidates"></request-work>
                        </v-tab-item>
                        <v-tab-item
                          :key="2"
                          :value="'tabs-operators'"
                        >
                            <allocation-expecting-users
                                :selected-users-infos="selectedUsersInfos"
                                :candidates="candidates"
                                :requests="provisinalRequests"
                            ></allocation-expecting-users>
                        </v-tab-item>
                    </v-tabs-items>
                    <v-layout row wrap justify-center mt-2>
                        <v-btn
                            color="primary"
                            @click="save"
                        >{{ $t('allocations.allocation_list.allocation') }}</v-btn>
                    </v-layout>

                    <page-footer back-button></page-footer>

                </v-container>
            <target-request-list-dialog v-if="targetRequestListDialog" :requests="requests" :candidates="candidates"></target-request-list-dialog>
            <alert-dialog ref="alert"></alert-dialog>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer></app-footer>

    </v-app>
</template>

<script>
import store from '../../../../stores/Admin/MultipleAllocations/store.js'
import PageHeader from './../../../Organisms/Layouts/PageHeader'
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import TargetRequestListDialog from '../../../Organisms/Admin/MultipleAllocations/TargetRequestListDialog'
import RequestWork from '../../../Organisms/Common/RequestWork'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AllocationExpectingUsers from '../../../Organisms/Admin/MultipleAllocations/AllocationExpectingUsers'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'

export default {
    props: {
        inputs: { type: Object },
    },
    components: {
        PageHeader,
        PageFooter,
        TargetRequestListDialog,
        RequestWork,
        ProgressCircular,
        AllocationExpectingUsers,
        AlertDialog,
    },
    data: () => ({
        drawer: false,
        targetRequestListDialog: false,

        valid: false,
        edit: false,
        tabs: null,

        // form
        evenness: '',
        selected: [],
        ratios: {},

        ratioSum: 0,

        allocated: [], // 割振済ユーザを保持

        alerts: {
            internal_error: {
                model: false,
                dismissible: true,
                icon: 'warning',
                message: Vue.i18n.translate('common.message.internal_error'),
            }
        },

        // DBから取得
        requests: [],
        provisinalRequests: [],
        candidates: [],
        operators: [],
        parallel: null,
        businessName: '',
        newTasksCnt: '',

        initialParallel: null,
        individualPanel: false,

        // シミュレーションで生成された担当者データ
        selectedUsersInfos: [],

        //loading
        loading: false
    }),
    methods: {
        getInitData (param) {
            this.loading = true
            axios.post('/api/multiple_allocations/confirm',  param)
                .then((res) => {
                    this.requests = res.data.requests
                    this.provisinalRequests = res.data.provisinal_requests
                    this.candidates = res.data.candidates
                    this.operators = res.data.operators
                    this.initialParallel = res.data.parallel
                    this.parallel = res.data.parallel
                    this.evenness = res.data.evenness
                    this.ratios = res.data.ratios
                    this.businessName = res.data.requests[0].business_name
                    this.selectedUsersInfos = res.data.selected_users_infos
                    this.newTasksCnt = res.data.new_tasks_cnt

                    // 必須データが取得できない場合はアラート表示
                    if ( this.requests.length === 0 ) {
                        this.alerts['internal_error'].model = true
                    }
                    this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                    this.alerts['internal_error'].model = true
                });
        },
        save: function () {
            this.loading = true

            let params = new FormData()
            params.append('requests', JSON.stringify(this.requests))
            params.append('provisional_requests', JSON.stringify(this.provisinalRequests))
            params.append('started_at', this.startedAt)

            axios.post('/api/multiple_allocations/store',  params)
                .then((res) => {
                    if (res.data.result === 'success') {
                        store.commit('resetAllocationConditions')
                        // ダイアログ表示
                        this.$refs.alert.show(
                            this.$t('common.message.saved'),
                            function () {
                                if (window.history.length > 2) {
                                    window.history.go(-2)
                                } else {
                                    window.location.href = '/allocations'
                                }
                            }
                        )
                    } else if (res.data.result === 'updated_by_others') {
                    // ダイアログ表示
                        this.$refs.alert.show(
                            this.$t('common.message.updated_by_others'),
                            function () {}
                        )
                    } else {
                    // ダイアログ表示
                        this.$refs.alert.show(
                            this.$t('common.message.internal_error'),
                            function () {}
                        )
                    }
                })
                .catch((err) => {
                    console.log(err)
                    // ダイアログ表示
                    this.$refs.alert.show(
                        this.$t('common.message.internal_error'),
                        function () {}
                    )
                })
                .finally(() => {
                    this.loading = false
                });
        },
        reset: function () {
            // 選択状態をリセット
            this.selected = this.allocated

            this.$refs.allocationList.reset()
        },
        arraySum: function (arr) {
            let sum = 0;
            arr.forEach(function(elm) {
                sum += elm;
            });
            return sum;
        }
    },
    created() {
        this.getInitData(this.inputs)
    },
    computed: {
        parallelHint () {
            return '規定値:' + this.parallel
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
        evennessIndividual () {
            return this.evenness == _const.ALLOCATION_EVENNESS.INDIVIDUAL
        },
        disabledButton () {
            return (this.selected && this.selected.length === 0) || JSON.stringify(this.selected) === JSON.stringify(this.allocated)
        },
        startedAt () {
            return document.getElementById('loaded-utc-datetime').value
        },
    },
    watch: {
        selectedUsersInfos: {
            handler: function () {
                let ratios = [];
                this.selectedUsersInfos.forEach(function(item){
                    if (typeof item.ratio !== 'undefined') {
                        ratios.push(Number(item.ratio))
                    }
                });
                this.ratioSum = this.arraySum(ratios)
            },
            deep: true
        }
    }
}
</script>
<style scoped>
#data-content {
    position: relative;
}
.allocation-condition {
    height: 100%;
}
.parallel-input input {
    font-size: 18px;
}
#inspire a.back-btn {
    color: #fff!important;
}
.tab-title {
    color: #fff;
}
</style>
