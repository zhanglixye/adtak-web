<template>
    <v-dialog v-model="revisionsDialog" fullscreen hide-overlay transition="dialog-bottom-transition">
        <v-toolbar dark color="">
            <v-btn icon dark @click="close()">
              <v-icon>close</v-icon>
            </v-btn>
            <v-toolbar-title>{{ $t('requests.detail.past_revisions_modal.header') }}</v-toolbar-title>
        </v-toolbar>
        <div id="step-progress" class="past-revisions">
            <v-card>
                <v-toolbar dense flat color="grey lighten-2">
                    <v-toolbar-title>{{ targetStepData.stepName }}<span class="ml-2 caption" v-if="pastRevisionsRequestWorks[0].code">(依頼コード: {{ pastRevisionsRequestWorks[0].code }})</span></v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <v-divider class="ma-0"></v-divider>
                <v-card-text>
                    <div v-if="pastRevisionsRequestWorks">
                        <div v-for="(request_work, index) in pastRevisionsRequestWorks" :key="index">

                            <v-layout wrap align-start>
                                <v-flex xs2>
                                    <div>{{ $t('requests.detail.past_revisions_modal.last_updated_at') }}: {{ request_work.updated_at | formatDateYmdHm }}</div>
                                    <div v-if="request_work.import_info.executed_at">
                                        <span>{{ $t('requests.detail.deadline') }}:
                                            <span v-if="deadline(request_work)">{{ deadline(request_work) | formatDateYmdHm }}</span>
                                            <span v-else>{{ $t('requests.unset') }}</span>
                                        </span>
                                    </div>
                                </v-flex>
                                <v-flex xs10>
                                    <v-stepper non-lenear :alt-labels="true" class="elevation-0">
                                        <step-progress-inner
                                            :request-work="request_work"
                                            :candidates="candidates"
                                            :business-name="businessName"
                                            :step-url="targetStepData.stepUrl"
                                            :step-name="targetStepData.stepName"
                                        ></step-progress-inner>
                                    </v-stepper>
                                </v-flex>
                            </v-layout>

                            <v-divider v-if="(pastRevisionsRequestWorks.length) > (index + 1) "></v-divider>
                        </div>
                    </div>
                </v-card-text>
                <v-divider class="ma-0"></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" flat @click="close()">Close</v-btn>
                </v-card-actions>
            </v-card>
        </div>
    </v-dialog>
</template>

<script>
import StepProgressInner from '../../../../Organisms/Admin/Requests/Detail/StepProgressInner'

export default {
    props: {
        revisionsDialog: { type: Boolean },
        pastRevisionsRequestWorks: { type: Array },
        candidates: { type: Array },
        businessName: { type: String },
        targetStepData: { type: Object }
    },
    data: () => ({
        //loading
        loading: false,
    }),
    components:{
        // ProgressCircular
        StepProgressInner
    },
    computed: {
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
        }
    },
    methods: {
        close () {
            this.$emit('update:revisionsDialog', false)
        }
    }
}
</script>

<style scoped>
    .v-stepper__content {
        display: block!important;
        padding-right: 0px;
        padding-left: 0px;
    }
    .v-stepper--alt-labels .v-stepper__step {
        flex-basis: 221px;
    }
</style>
