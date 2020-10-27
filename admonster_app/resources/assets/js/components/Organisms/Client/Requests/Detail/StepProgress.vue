<template>
    <div id="step-progress" class="client-step-progress">
        <v-card>
            <v-toolbar dense flat color="#fff">
                <v-toolbar-title><v-icon class="mr-3">mdi-widgets</v-icon><span class="subheading">{{ $t('requests.detail.step_progress.header') }}</span></v-toolbar-title>
            </v-toolbar>

            <v-divider class="ma-0"></v-divider>
            <v-card-text>
                <v-layout row wrap align-center>
                    <v-flex xs12>
                        <div class="progress-type-title font-weight-bold">{{ $t('client.requests.progress.block_title.request') }}</div>
                        <div class="progress-content">

                            <v-container fluid>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar">
                                        <div
                                            class="progress-bar-inner"
                                            :style="'width:' + completedPercentage +'%;'"
                                        >
                                            <span
                                                v-if="completedPercentage > 0 && completedPercentage < 100"
                                                class="arrow-right-circle"
                                            >
                                                <v-icon color="#fff">chevron_right</v-icon>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </v-container>

                            <div class="percentage-wrap text-xs-right mr-4">
                               <span class="font-weight-bold">{{ completedPercentage }}%</span>
                            </div>
                            <v-spacer></v-spacer>
                            <v-btn
                                depressed
                                small
                                class="step-progress-toggle-btn"
                                @click="toggleAllStepProgress()"
                                >
                                <v-icon v-if="showWholeStepsBlock">keyboard_arrow_up</v-icon>
                                <v-icon v-else>keyboard_arrow_down</v-icon>
                            </v-btn>
                        </div>
                    </v-flex>
                </v-layout>

            </v-card-text>
            <v-divider class="ma-0"></v-divider>
            <v-card-text v-show="showWholeStepsBlock">
                <v-layout row wrap align-center>
                    <v-flex xs12>
                        <div class="progress-type-title font-weight-bold mb-2">{{ $t('client.requests.progress.block_title.each_steps') }}</div>
                    </v-flex>
                </v-layout>
                <div v-if="openDetails">
                    <v-list
                        v-for="item in stepDetails"
                        :key="item.id"
                    >
                        <div class="step-progress-box pl-4 pt-3 pr-4 pb-3">
                            <v-layout row>
                                <v-flex xs12>
                                    <div class="step-name font-weight-bold">{{ item.step_name }}</div>
                                </v-flex>
                            </v-layout>
                            <v-layout row>
                                <v-flex xs12>
                                    <div class="each-step-progress" v-show="openedStepProgress(item.step_id)">
                                        <div v-for="(request_works, code, index) in item.code_divided_request_works" class="mb-3" :key="index">
                                            <div v-if="request_works.is_active">
                                                <div
                                                    v-for="request_work in request_works.is_active"
                                                    :id="'request-work-id-' + request_work.id"
                                                    :key="request_work.id"
                                                >
                                                    <v-layout row wrap>
                                                        <v-flex xs12>
                                                            <step-progress-inner
                                                                :request-work="request_work"
                                                                :candidates="candidates"
                                                                :business-name="businessName"
                                                                :step-url="item.step_url"
                                                                :step-name="item.step_name"
                                                            ></step-progress-inner>
                                                        </v-flex>
                                                    </v-layout>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <v-layout row wrap align-center>
                                                    <v-flex xs12>
                                                        <div class="text-xs-center mt-4 mb-3">{{ $t('requests.detail.not_exist_active_data') }}</div>
                                                    </v-flex>
                                                </v-layout>
                                            </div>
                                        </div>
                                    </div>
                                </v-flex>
                            </v-layout>
                        </div>

                    </v-list>
                </div>

            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import StepProgressInner from '../../../../Organisms/Client/Requests/Detail/StepProgressInner'
import store from '../../../../../stores/Client/Requests/Detail/store'

export default {
    props: {
        stepDetails: { type: Array, required: true },
        candidates: { type: Array, required: true },
        businessName: { type: String, required: true },
        completedPercentage: { type: Number, required: true }
    },
    data: () => ({
        //loading
        loading: false,

        openDetails: true,
        showWholeStepsBlock: store.state.showWholeStepsBlock,

        stepIds: [],
        progressClosedStepIds: store.state.progressClosedStepIds
    }),
    components:{
        // ProgressCircular
        StepProgressInner
    },
    created () {
        this.setStepIds(this.stepDetails)

        // 作業が1つだけの場合は依頼全体のステータスバーは非表示
        if (this.stepCount == 1) {
            this.showWholeStepsBlock = false
        }
    },
    computed: {
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
        stepCount () {
            return this.stepDetails.length
        }
    },
    methods: {
        setStepIds(data) {
            let self = this
            data.forEach(function(value){
                self.stepIds.push(value.step_id)
            })
        },
        // 全step枠開閉トグル
        toggleAllStepProgress() {
            this.showWholeStepsBlock = !this.showWholeStepsBlock
            store.commit('setStateObj', { showWholeStepsBlock: this.showWholeStepsBlock })
        }
    }
}
</script>
