<template>
    <div id="step-progress">
        <v-card>
            <v-toolbar dense dark flat color="grey darken-1">
                <v-toolbar-title>{{ $t('requests.detail.step_progress.header') }}</v-toolbar-title>
                <v-spacer></v-spacer>
                <div v-if="openDetails" class="text-xs-right">
                    <v-tooltip top v-if="openAllStepsProgressBtn">
                        <v-btn
                            slot="activator"
                            icon
                            dark
                            color="grey darken-3"
                        >
                            <v-icon @click="openAllStepsProgress()">unfold_more</v-icon>
                        </v-btn>
                        <span>全て展開</span>
                    </v-tooltip>
                    <v-tooltip top v-else>
                        <v-btn
                            slot="activator"
                            icon
                            dark
                            color="grey darken-3"
                        >
                            <v-icon @click="closeAllStepsProgress()">unfold_less</v-icon>
                        </v-btn>
                        <span>全て折りたたむ</span>
                    </v-tooltip>
                </div>
                <v-tooltip top>
                    <v-btn slot="activator" icon dark color="grey darken-3" @click="switchStepProgressView()"><v-icon>swap_horiz</v-icon>
                    </v-btn>
                    <span>{{ $t('requests.detail.step_progress.btn_change_view') }}</span>
                </v-tooltip>
            </v-toolbar>
            <v-divider class="ma-0"></v-divider>
            <v-card-text>

                <count-summary :count-summary="countSummary" :business-name="businessName" v-if="!openDetails" :request-id="requestId"></count-summary>

                <div v-if="openDetails">
                    <v-list
                        v-for="(item, steps_index) in stepDetails"
                        :key="item.id"
                    >
                        <v-layout row wrap align-center>
                            <v-flex>
                                <v-layout row wrap align-center>
                                    <v-flex>
                                        <div class="step-with-right-border">
                                            <v-btn
                                                depressed
                                                round
                                                large
                                                color="grey lighten-1"
                                                @click="showReferenceView(steps_index)"
                                            >{{ item.step_name }}</v-btn>
                                        </div>
                                    </v-flex>
                                    <v-btn
                                        depressed
                                        small
                                        class="step-progress-toggle-btn"
                                        @click="toggleStepProgress(item.step_id)">
                                        <v-icon v-if="openedStepProgress(item.step_id)">keyboard_arrow_up</v-icon>
                                        <v-icon v-else>keyboard_arrow_down</v-icon>
                                    </v-btn>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                        <div class="each-step-progress" v-show="openedStepProgress(item.step_id)">
                            <div v-for="(request_works, code, index) in item.code_divided_request_works" class="mb-3" :key="index">
                                <v-stepper non-lenear :alt-labels="true" class="elevation-0">
                                    <div v-if="request_works.is_active">
                                        <div
                                            v-for="request_work in request_works.is_active"
                                            :id="'request-work-id-' + request_work.id"
                                            :key="request_work.id"
                                        >
                                            <v-layout row wrap align-center>
                                                <v-flex xs2></v-flex>
                                                <v-flex xs10>
                                                    <div
                                                        v-if="(Object.keys(item.code_divided_request_works).length > 0)"
                                                        :class="(index > 0) ? 'request-work-code-bar mt-2 mb-2 body-1' : 'request-work-code-bar mt-0 mb-2 body-1'"
                                                    >
                                                        <v-flex class="request-work-code-wrap">
                                                            <div class="request-work-code">{{ $t('request_works.id') }}&#058;&#032;<span class="select-all">{{ requestWorkCode(request_work) }}</span></div>
                                                            <v-spacer></v-spacer>
                                                            <div v-if="request_work.import_info.executed_at">
                                                                <span>{{ $t('requests.detail.deadline') }}:
                                                                    <span v-if="deadline(request_work)">{{ deadline(request_work) | formatDateYmdHm }}</span>
                                                                    <span v-else>{{ $t('requests.detail.unset') }}</span>
                                                                </span>
                                                            </div>
                                                        </v-flex>
                                                    </div>

                                                    <step-progress-inner
                                                        :request-work="request_work"
                                                        :candidates="candidates"
                                                        :business-name="businessName"
                                                        :step-url="item.step_url"
                                                        :step-name="item.step_name"
                                                        :reference-mode="referenceMode"
                                                        ref="stepProgressInner"
                                                    ></step-progress-inner>

                                                    <div class="text-xs-right">
                                                        <div v-if="revisionNum(request_works) > 1">
                                                            <div>
                                                                <span>{{ $t('requests.detail.revision') }}: {{ revisionNum(request_works) }}</span>
                                                                <a class="mt-2 ml-2" style="color:#1867c0!important;"
                                                                    v-if="existInactiveData(request_works)"
                                                                    @click.stop="showPastRevisions(item.step_url, item.step_name, request_works.is_inactive)"
                                                                >{{ $t('requests.detail.check_past_revisions') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </v-flex>
                                            </v-layout>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <v-layout row wrap align-center>
                                            <v-flex xs2></v-flex>
                                            <v-flex xs10>
                                                <div
                                                    v-if="(Object.keys(item.code_divided_request_works).length > 0)"
                                                    :class="(index > 0) ? 'request-work-code-bar mt-2 mb-2 body-1' : 'request-work-code-bar mt-0 mb-2 body-1'"
                                                >
                                                    <v-flex class="request-work-code-wrap">
                                                        <div class="request-work-code">{{ $t('request_works.id') }}&#058;&#032;{{ MASTER_ID_PREFIX.REQUEST_WORKS_ID + request_work.id }}</div>

                                                    </v-flex>
                                                </div>
                                                <div class="text-xs-center mt-4 mb-3">{{ $t('requests.detail.not_exist_active_data') }}</div>
                                                <div class="text-xs-right">
                                                    <div v-if="revisionNum(request_works) > 1">
                                                        <div>
                                                            <span>{{ $t('requests.detail.revision') }}: {{ revisionNum(request_works) }}</span>
                                                            <a class="mt-2 ml-2"
                                                                v-if="existInactiveData(request_works)"
                                                                style="color:#1867c0!important;"
                                                                @click.stop="showPastRevisions(item.step_url, item.step_name, request_works.is_inactive)"
                                                            >{{ $t('requests.detail.check_past_revisions') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div v-if="revisionNum(request_works) == 1">
                                                        <div>
                                                            <a class="mt-2 ml-2"
                                                                v-if="existInactiveData(request_works)"
                                                                style="color:#1867c0!important;"
                                                                @click.stop="showPastRevisions(item.step_url, item.step_name, request_works.is_inactive)"
                                                            >{{ $t('requests.detail.check_past_revisions') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </v-flex>
                                        </v-layout>
                                    </div>
                                </v-stepper>
                            </div>
                        </div>
                    </v-list>
                </div>

                <past-revisions-modal :revisions-dialog.sync="revisionsDialog" :past-revisions-request-works="pastRevisionsRequestWorks" :candidates="candidates" :business-name="businessName" :target-step-data="targetStepData" v-if="revisionsDialog"></past-revisions-modal>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import CountSummary from '../../../../Organisms/Admin/Requests/Detail/CountSummary'
// import RequestWorkStepStatus from '../../../../Organisms/Admin/Requests/Detail/RequestWorkStepStatus'
import StepProgressInner from '../../../../Organisms/Admin/Requests/Detail/StepProgressInner'
import PastRevisionsModal from '../../../../Organisms/Admin/Requests/Detail/PastRevisionsModal'

import store from '../../../../../stores/Admin/Requests/Detail/store'

export default {
    name: 'StepProgress',
    props: {
        stepDetails: { type: Array },
        countSummary: { type: Array },
        candidates: { type: Array },
        businessName: { type: String },
        requestId: { type: Number },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        //loading
        loading: false,
        revisionsDialog: false,

        openAllStepsProgressBtn: true,
        openDetails: store.state.openDetails,

        pastRevisionsRequestWorks: [],
        targetStepData: {
            stepUrl: '',
            stepName: ''
        },
        stepIds: [],
        progressClosedStepIds: store.state.progressClosedStepIds,
    }),
    components:{
        // ProgressCircular
        CountSummary,
        StepProgressInner,
        PastRevisionsModal
    },
    created () {
        this.setStepIds(this.stepDetails)

        // ステータスサマリーからの自動スクロール準備
        eventHub.$on('openDetailsFromStatusSummary', this.openDetailsTrue);
    },
    computed: {
        revisionNum () {
            return function (requestWorks) {
                let num = 0
                if ('is_active' in requestWorks) {
                    num = num + requestWorks.is_active.length
                }
                if ('is_inactive' in requestWorks) {
                    num = num + requestWorks.is_inactive.length
                }

                return num
            }
        },
        existInactiveData () {
            return function (requestWorks) {
                let num = 0
                if ('is_inactive' in requestWorks) {
                    num = requestWorks.is_inactive.length
                }

                return num > 0 ? true : false;
            }
        },
        deadline () {
            return function (requestWorks) {
                if (requestWorks.deadline) {
                    return requestWorks.deadline
                } else if (!requestWorks.deadline && requestWorks.system_deadline) {
                    return requestWorks.system_deadline
                } else {
                    return null
                }
            }
        },
        openedStepProgress () {
            return function (stepId) {
                if (this.progressClosedStepIds.indexOf(stepId) == -1) {
                    return true
                } else {
                    return false
                }
            }
        },
        requestWorkCode () {
            return function (item) {
                if (!item.id) return ''
                const prefix = _const.MASTER_ID_PREFIX
                return prefix.REQUEST_ID + this.requestId + prefix.SEPARATOR + prefix.REQUEST_WORKS_ID + item.id
            }
        },
    },
    methods: {
        // UsersOverviewコンポーネント内と共通。mixinにする
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
        setStepIds(data) {
            let self = this
            data.forEach(function(value){
                self.stepIds.push(value.step_id)
            })
        },
        switchStepProgressView() {
            this.openDetails = !this.openDetails
            store.commit('setStateObj', { openDetails: this.openDetails })
        },
        showdet(de) {
            console.log(de)
        },
        showtime(time) {
            console.log(time)
        },
        showPastRevisions(stepUrl, stepName, inactiveRewquestWorks) {
            this.pastRevisionsRequestWorks = inactiveRewquestWorks
            this.revisionsDialog = true
            this.targetStepData.stepUrl = stepUrl
            this.targetStepData.stepName = stepName
        },
        // 全step枠展開
        openAllStepsProgress() {
            this.progressClosedStepIds = []
            let storeStepIds = store.state.progressClosedStepIds
            this.stepIds.forEach(function(id){
                if (storeStepIds.indexOf(id) !== -1) {
                    storeStepIds = storeStepIds.filter(n => n !== id)
                }
            })
            store.commit('setStateObj', { progressClosedStepIds: storeStepIds })
            this.openAllStepsProgressBtn = !this.openAllStepsProgressBtn
        },
        // 全step枠折りたたむ
        closeAllStepsProgress() {
            let storeStepIds = store.state.progressClosedStepIds
            this.progressClosedStepIds = this.stepIds
            this.stepIds.forEach(function(id){
                if (storeStepIds.indexOf(id) == -1) {
                    storeStepIds.push(id)
                }
            })
            store.commit('setStateObj', { progressClosedStepIds: storeStepIds })
            this.openAllStepsProgressBtn = !this.openAllStepsProgressBtn
        },
        // step枠開閉トグル
        toggleStepProgress(stepId) {
            const arr = this.progressClosedStepIds
            if (arr.indexOf(stepId) !== -1) {
                const newArr = arr.filter(n => n !== stepId)
                this.progressClosedStepIds = newArr
            } else {
                this.progressClosedStepIds.push(stepId)
            }
            store.commit('setStateObj', { progressClosedStepIds: this.progressClosedStepIds })
        },
        // 個別step枠展開
        openStepProgress(stepId) {
            const arr = this.progressClosedStepIds
            if (arr.indexOf(stepId) !== -1) {
                const newArr = arr.filter(n => n !== stepId)
                this.progressClosedStepIds = newArr
            }
            store.commit('setStateObj', { progressClosedStepIds: this.progressClosedStepIds })
        },
        openDetailsTrue(params) {
            this.openDetails = true
            store.commit('setStateObj', { openDetails: this.openDetails })
            this.openStepProgress(params.stepId)
        },
        showReferenceView (index) {
            const requestWork = this.stepDetails[index].code_divided_request_works[''].is_active.slice(0).shift()
            const task = requestWork.tasks_info.filter(task => task && task.status !== 0 && task.updated_at).slice(0).shift()
            if (task) {
                const uri =  this.$refs.stepProgressInner[index].taskUri(requestWork.id, task.id)
                this.$refs.stepProgressInner[index].showReferenceView([uri])
            }
        },
    }
}
</script>

<style scoped>
    #step-progress {
        position: relative;
    }
    #step-progress .step-progress-toggle-btn {
        min-width: 40px;
    }
    .v-stepper__content {
        display: block!important;
        padding-right: 0px;
        padding-left: 0px;
    }
    .v-stepper--alt-labels .v-stepper__step {
        flex-basis: 221px;
    }
    .step-with-right-border {
        display: flex;
        align-items: center;
    }
    .step-with-right-border:after {
        border-top: 1px solid;
        content: "";
        flex-grow: 1;
        margin-left: 1rem;
        height:0.8rem;
        background-color: #e0e0e0;
        border-color: #e0e0e0;
    }
    .select-all {
        user-select: all;
        -webkit-user-select: all;
        -moz-user-select: all;
    }
</style>
