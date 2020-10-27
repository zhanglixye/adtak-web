<template>
    <div id="step-progress">
        <v-card>
            <v-toolbar dense dark flat color="grey darken-1">
                <v-toolbar-title>{{ $t('requests.detail.step_progress.header') }}</v-toolbar-title>
            </v-toolbar>
            <v-divider class="ma-0"></v-divider>
            <v-card-text>

                <v-list
                    v-if="openDetails"
                >
                    <v-layout row wrap align-center>
                        <v-flex>
                            <div class="step-with-right-border">
                                <v-btn depressed round large color="grey lighten-1">{{ requestWork.step.name }}</v-btn>
                            </div>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap align-start>
                        <v-flex xs2>
                        </v-flex>
                        <v-flex xs10>
                            <v-stepper non-lenear :alt-labels="true" class="elevation-0">
                                <div v-if="requestWork.is_active">
                                    <div :id="'request-work-id-' + requestWork.id" class="request-work-code-bar mt-0 mb-2 body-1">
                                        <v-flex class="request-work-code-wrap">
                                            <div class="request-work-code">{{ $t('request_works.id') }}&#058;&#032;<span class="select-all">{{ requestWorkCode(requestWork) }}</span></div>
                                            <v-spacer></v-spacer>
                                            <div v-if="requestWork.import_info.executed_at">
                                                <span>{{ $t('requests.detail.deadline') }}:
                                                    <span v-if="deadline(requestWork)">{{ deadline(requestWork) | formatDateYmdHm }}</span>
                                                    <span v-else>{{ $t('requests.detail.unset') }}</span>
                                                </span>
                                            </div>
                                        </v-flex>
                                    </div>
                                    <step-progress-inner
                                        :request-work="requestWork"
                                        :candidates="candidates"
                                        :business-name="businessName"
                                        :step-url="requestWork.step.url"
                                        :step-name="requestWork.step.name"
                                    ></step-progress-inner>
                                </div>
                                <div v-else>
                                    <div class="text-xs-center mt-4 mb-3">{{ $t('requests.detail.not_exist_active_data') }}</div>
                                </div>
                            </v-stepper>
                        </v-flex>
                    </v-layout>
                </v-list>

            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import StepProgressInner from '../../../../Organisms/Admin/Requests/Detail/StepProgressInner'

export default {
    name: 'RequestWorkProgress',
    components:{
        // ProgressCircular
        StepProgressInner
    },
    props: {
        requestWork: { type: Object },
        candidates: { type: Array },
        businessName: { type: String }
    },
    data: () => ({
        //loading
        loading: false,
        revisionsDialog: false,
        openDetails: true,

        pastRevisionsRequestWorks: [],
        targetStepData: {
            stepUrl: '',
            stepName: ''
        },
    }),
    computed: {
        revisionNum() {
            return function(requestWorks) {
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
        existInactiveData() {
            return function(requestWorks) {
                let num = 0
                if ('is_inactive' in requestWorks) {
                    num = requestWorks.is_inactive.length
                }

                return num > 0 ? true : false;
            }
        },
        deadline () {
            return function(requestWorks) {
                if (requestWorks.deadline) {
                    return requestWorks.deadline
                } else if (!requestWorks.deadline && requestWorks.system_deadline) {
                    return requestWorks.system_deadline
                } else {
                    return null
                }
            }
        },
        requestWorkCode() {
            return function(item) {
                if (!item.id) return ''
                const prefix = _const.MASTER_ID_PREFIX
                return prefix.REQUEST_ID + item.request_id + prefix.SEPARATOR + prefix.REQUEST_WORKS_ID + item.id
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
        }
    }
}
</script>

<style scoped>
    #step-progress {
        position: relative;
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
