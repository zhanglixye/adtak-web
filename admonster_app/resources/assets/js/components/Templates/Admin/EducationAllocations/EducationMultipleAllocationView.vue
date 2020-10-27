<template>
    <v-app id="inspire" class="multiple-allocation">
        <app-menu :drawer="drawer"></app-menu>
        <app-header
            :title="businessName"
            :subtitle="$t('education_allocations.multi_allocate')"
        ></app-header>
        <v-content id="data-content">
            <v-form
                v-model="valid"
                :action="actionPath"
                method="post"
                @submit.prevent="formSubmit"
                ref="form">
                <input type="hidden" name="_token" :value="csrf">
                <v-container fluid grid-list-md>
                    <v-layout row wrap mb-3>
                        <!-- Main -->
                        <v-flex xs12>
                            <page-header :alerts="alerts"></page-header>
                        </v-flex>
                        <v-card class="allocation-condition text-xs-center">
                            <v-card-text class="px-0 pr-5 pl-5">{{ $t('education_allocations.allocation_list.number_of_eligible_projects') }} : &nbsp;<a><span class="display-1 font-weight-bold mr-1" @click="targetRequestListDialog = !targetRequestListDialog">{{ requests.length }}</span></a>{{ $t('list.case') }}</v-card-text>
                        </v-card>
                    </v-layout>
                    <v-layout row wrap>
                        <div style="width:100%;">
                            <v-stepper v-model="e1">
                                <v-stepper-items>
                                    <v-stepper-content step="1">
                                        <allocation-list
                                            :requests="requests"
                                            :operators.sync="operators"
                                            :candidates="candidates"
                                            :selected.sync="selected"
                                            ref="allocationList"
                                        ></allocation-list>
                                        <v-layout row wrap>
                                            <v-flex xs12 mt-2 class="text-xs-center">
                                                <v-btn color="grey" dark @click="reset">{{ $t('education_allocations.allocation_list.reset') }}</v-btn>
                                                <v-btn color="primary" @click="nextStep(1)">{{ $t('education_allocations.allocation_list.next') }}</v-btn>
                                            </v-flex>
                                        </v-layout>
                                    </v-stepper-content>
                                    <v-stepper-content step="2">
                                        <v-layout row wrap pt-1 pl-1 pr-1>
                                            <v-flex xs6>
                                                <v-card class="allocation-condition text-xs-center">
                                                    <v-card-text class="px-0">{{ $t('list.column.parallel') }}</v-card-text>
                                                    <v-divider class="ma-0"></v-divider>
                                                    <v-card-text class="px-0">
                                                        <template>
                                                            <v-layout pr-2 pl-2 align-center>
                                                                <v-flex>
                                                                    <span>{{ $t('education_allocations.allocation_list.per_case') }}</span>
                                                                </v-flex>
                                                                <v-flex style="max-width: 5rem;">
                                                                    <v-text-field
                                                                        v-model="parallel"
                                                                        name="parallel"
                                                                        required
                                                                        reverse
                                                                        :hint="$t('education_allocations.allocation_list.default_value') + initialParallel"
                                                                        persistent-hint
                                                                        class="headline font-weight-bold text-xs-center parallel-input"
                                                                    ></v-text-field>
                                                                </v-flex>
                                                                <v-flex>
                                                                    <span>{{ $t('education_allocations.allocation_list.assign_people') }}</span>
                                                                </v-flex>
                                                            </v-layout>
                                                        </template>
                                                    </v-card-text>
                                                </v-card>
                                            </v-flex>
                                            <v-flex xs6>
                                                <v-card class="allocation-condition text-xs-center">
                                                    <v-card-text class="px-0">{{ $t('education_allocations.evenness') }}</v-card-text>
                                                    <v-divider class="ma-0"></v-divider>
                                                    <v-card-text class="px-0">
                                                        <v-radio-group row class="justify-center" v-model="evenness">
                                                            <v-radio
                                                                v-for="item in evennessTypes"
                                                                :key="item.label"
                                                                :label="item.label"
                                                                name="evenness"
                                                                :value="item.value"
                                                            ></v-radio>
                                                        </v-radio-group>
                                                    </v-card-text>
                                                    <v-flex xs12 sm12 md12 v-show="evennessIndividual">
                                                        <div v-if="selected.length > 0">
                                                            <v-list>
                                                                <v-list-tile
                                                                    v-for="item in selected"
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
                                                                        <v-text-field
                                                                            v-model="ratios[item.user_id]"
                                                                            reverse
                                                                            prefix="%"
                                                                        ></v-text-field>
                                                                    </v-list-tile-action>
                                                                </v-list-tile>
                                                            </v-list>
                                                            <v-divider class="ma-0"></v-divider>
                                                            <v-list>
                                                                <v-list-tile>
                                                                    <v-list-tile-content>
                                                                        <v-list-tile-sub-title>{{ $t('education_allocations.total') }}</v-list-tile-sub-title>
                                                                    </v-list-tile-content>
                                                                    <v-list-tile-action>
                                                                        <span>{{ ratioSum }} %</span>
                                                                    </v-list-tile-action>
                                                                </v-list-tile>
                                                            </v-list>
                                                        </div>
                                                        <div v-else>
                                                            <span class="red--text">{{ $t('education_allocations.allocation_list.not_selected') }}</span>
                                                        </div>
                                                    </v-flex>
                                                </v-card>
                                            </v-flex>
                                        </v-layout>
                                        <v-layout row wrap>
                                            <v-flex xs12 mt-2 class="text-xs-center">
                                                <v-btn color="grey" dark @click="nextStep(2)">{{ $t('common.button.back') }}</v-btn>
                                                <v-btn
                                                    color="primary"
                                                    type="submit"
                                                >
                                                {{ $t('common.button.confirm') }}
                                                </v-btn>
                                            </v-flex>
                                        </v-layout>
                                    </v-stepper-content>
                                </v-stepper-items>
                            </v-stepper>
                        </div>
                    </v-layout>

                    <page-footer back-button></page-footer>

                </v-container>
                <input type="hidden" name="request_work_ids" :value="inputs.request_work_ids">
                <input type="hidden" name="user_ids" :value="selectedUserIds">
                <input type="hidden" name="ratios" :value="JSON.stringify(ratios)">
                <input type="hidden" name="initialParallel" :value="initialParallel">
                <input type="hidden" name="is_display_educationals" :value="isDisplayEducational">
            </v-form>
            <target-request-list-dialog v-if="targetRequestListDialog" :requests="requests" :candidates="candidates"></target-request-list-dialog>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer></app-footer>

    </v-app>
</template>

<script>
import store from '../../../../stores/Admin/education/MultipleAllocations/store.js'
import PageHeader from './../../../Organisms/Layouts/PageHeader'
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import AllocationList from '../../../Organisms/Admin/EducationAllocations/MultipleAllocationsList'
import TargetRequestListDialog from '../../../Organisms/Admin/MultipleAllocations/TargetRequestListDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        PageHeader,
        PageFooter,
        AllocationList,
        TargetRequestListDialog,
        ProgressCircular,
    },
    props: {
        inputs: { type: Object },
    },
    data: () => ({
        e1: 1,
        steps: 2,

        drawer: false,
        targetRequestListDialog: false,

        valid: true,
        edit: false,

        evennessTypes: [
            { label : eventHub.$t('education_allocations.evenness_type.even'), value: _const.ALLOCATION_EVENNESS.EVEN },
            { label : eventHub.$t('education_allocations.evenness_type.individual'), value: _const.ALLOCATION_EVENNESS.INDIVIDUAL }
        ],

        // form
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        evenness: store.state.conditions.evenness,
        selected: store.state.conditions.selected,
        ratios: store.state.conditions.ratios,
        user_ids: '',
        ratioSum: store.state.conditions.ratioSum,
        afterOperators: store.state.conditions.afterOperators,

        alerts: {
            internal_error: {
                model: false,
                dismissible: true,
                icon: 'warning',
                message: Vue.i18n.translate('common.message.internal_error'),
            },
            updated_by_others: {
                model: false,
                dismissible: true,
                color: '#ffc107',
                icon: 'priority_high',
                message: Vue.i18n.translate('common.message.updated_by_others'),
            },
            not_reach_parallel: {
                model: false,
                dismissible: false,
                color: '#ffc107',
                icon: 'priority_high',
                message: Vue.i18n.translate('common.message.not_reach_parallel'),
            },
            not_correctly_set_evenness: {
                model: false,
                dismissible: false,
                color: '#ffc107',
                icon: 'priority_high',
                message: Vue.i18n.translate('common.message.not_correctly_set_evenness'),
            },
            not_set_evenness: {
                model: false,
                dismissible: false,
                color: '#ffc107',
                icon: 'priority_high',
                message: Vue.i18n.translate('common.message.not_set_evenness'),
            },
        },

        // DBから取得
        requests: [],
        candidates: [],
        operators: [],
        initOperators: [],
        initialParallel: null,
        parallel: store.state.conditions.parallel,
        businessName: '',

        //loading
        loading: false
    }),
    computed: {
        userImageSrc () {
            return function (user_image_path) {
                if (user_image_path) {
                    return user_image_path
                } else {
                    return location.origin + '/images/dummy_icon.png'
                }
            }
        },
        actionPath () {
            return '/education/allocations/confirm'
        },
        selectedUserIds () {
            let user_ids = []
            this.selected.forEach(function (item) {
                user_ids.push(item.user_id)
            })
            return user_ids
        },
        isDisplayEducational () {
            let isDisplayEducationals = []
            this.selected.forEach(function (item) {
                isDisplayEducationals.push(item.is_display_educational)
            })
            return isDisplayEducationals
        },
        evennessIndividual () {
            return this.evenness == _const.ALLOCATION_EVENNESS.INDIVIDUAL
        }
    },
    watch: {
        // TODO : できれば、別途のratiosオブジェクトを用意するのではなくselectedオブジェクトの中に均等比率を入れて、watch処理を減らしてapi側でもループ処理を減らすようにする
        selected: {
            handler: function (val) {
                let userIds = []
                val.forEach(function(selectedUser) {
                    userIds.push(selectedUser.user_id)
                })

                let self = this
                Object.keys(self.ratios).forEach(function(key) {
                    if (userIds.indexOf(Number(key)) == -1){
                        self.$delete(self.ratios, key)
                    }
                }, self.ratios);
            },
            deep: true
        },
        ratios: {
            handler: function (val) {
                let ratioArr = [];
                let parsedRatios = JSON.parse(JSON.stringify(val))

                Object.keys(parsedRatios).forEach(function(key) {
                    let val = this[key]
                    ratioArr.push(Number(val))
                }, parsedRatios);

                this.ratioSum = this.arraySum(ratioArr)
            },
            deep: true
        }
    },
    created() {
        // メインカラー変更[primary ⇔ accent]
        const primaryColor = this.$vuetify.theme.primary
        this.$vuetify.theme.primary = this.$vuetify.theme.accent
        this.$vuetify.theme.accent = primaryColor

        this.getInitData(this.inputs)
    },
    methods: {
        getInitData (param) {
            this.loading = true

            axios.post('/api/education/multiple_allocations',  param)
                .then((res) => {
                // 表示、非表示の制御（未割り振りは表示）
                    for (let operator of res.data.operators) {
                        operator.is_display_educational = true
                        this.initOperators.push(Object.assign({}, operator))
                    }
                    this.requests = res.data.requests
                    this.candidates = res.data.candidates
                    if (this.afterOperators.length > 0) {
                        this.operators = this.afterOperators
                    } else {
                        this.operators = res.data.operators
                    }
                    if (this.selected.length >= 0) {
                        let select = []
                        for (let operator of this.operators) {
                            for (const index in store.state.conditions.selected) {
                                if (operator['user_id'] === store.state.conditions.selected[index]['user_id']) {
                                    select.push(operator)
                                }
                            }
                        }
                        this.selected = select
                    }
                    this.initialParallel = res.data.parallel
                    this.parallel = res.data.parallel
                    this.businessName = res.data.requests[0].business_name

                    this.alerts['not_reach_parallel'].additional_info = { 'count': this.parallel }

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
        formSubmit: function () {
            let error_cnt = 0

            if (this.selected.length < this.parallel) {
                this.alerts['not_reach_parallel'].model = true
                this.valid = true
                error_cnt += 1
            } else {
                this.alerts['not_reach_parallel'].model = false
            }

            // 均等度チェック
            if (this.evenness == '' || this.evenness == 'undefined') {
                this.alerts['not_set_evenness'].model = true
                this.valid = true
                error_cnt += 1
            } else {
                this.alerts['not_set_evenness'].model = false
            }

            if (this.evenness == _const.ALLOCATION_EVENNESS.INDIVIDUAL) {
                if (this.ratioSum !== 100) {
                    this.alerts['not_correctly_set_evenness'].model = true
                    this.valid = true
                    error_cnt += 1
                } else {
                    this.alerts['not_correctly_set_evenness'].model = false
                }
            }

            if (error_cnt < 1) {
                this.valid = false

                // 設定条件をstoreに保存
                store.commit({
                    type: 'setAllocationConditions',
                    selected: this.selected,
                    parallel: this.parallel,
                    evenness: this.evenness,
                    afterOperators: this.operators,
                    ratios: this.ratios,
                    ratioSum: this.ratioSum
                })
                this.$refs.form.$el.submit()
            } else {
                this.$vuetify.goTo('#app')
            }
        },
        reset: function () {
            let initOperatorsCoppy = []
            for (const index in this.initOperators) {
                initOperatorsCoppy.push(Object.assign({}, this.initOperators[index]))
            }

            this.operators = initOperatorsCoppy
            // formをリセット
            this.$refs.form.reset()
            store.commit('resetAllocationConditions')

            // 初期値を再セット
            this.parallel = this.initialParallel
            this.$refs.allocationList.reset(this.operators)
        },
        arraySum: function (arr) {
            let sum = 0;
            arr.forEach(function(elm) {
                sum += elm;
            });
            return sum;
        },
        nextStep (n) {
            if (n === this.steps) {
                this.e1 = 1
            } else {
                this.e1 = n + 1
            }
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
.allocation-condition .v-input .v-input__control .v-input__slot input {
    font-size: 24px!important;
}
</style>
