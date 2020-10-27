<template>
<div>
    <!-- 依頼作業単位の進捗バー -->
    <div class="step-progress-bar-wrap">
        <v-container fluid>
            <div class="progress-bar-wrap with-process">
                <div class="progress-bar">

                    <!-- バー -->
                    <div class="progress-part-bar-wrap">
                        <!-- 受付 -->
                        <template v-if="importComplete(requestWork.completed_process)">
                            <span class="progress-part-bar progress-part-bar-left completed-progress"></span>
                        </template>
                        <template v-else>
                            <span class="progress-part-bar progress-part-bar-left"></span>
                        </template>

                        <!-- 手配 -->
                        <template v-if="allocationComplete(requestWork.completed_process)">
                            <span class="progress-part-bar completed-progress"></span>
                        </template>
                        <template v-else>
                            <span class="progress-part-bar"></span>
                        </template>

                        <!-- 作業 -->
                        <template v-if="tasksComplete(requestWork.completed_process)">
                            <span class="progress-part-bar completed-progress"></span>
                        </template>
                        <template v-else>
                            <span class="progress-part-bar"></span>
                        </template>

                        <!-- 検証 -->
                        <template v-if="approvalComplete(requestWork.completed_process)">
                            <span class="progress-part-bar progress-part-bar-right completed-progress"></span>
                        </template>
                        <template v-else>
                            <span class="progress-part-bar progress-part-bar-right"></span>
                        </template>
                    </div>
                    <!-- / バー -->

                </div>


                <!-- マーク -->
                <div class="process-mark-wrap">

                    <!-- 受付 -->
                    <span v-if="importComplete(requestWork.completed_process)">
                        <v-tooltip top>
                            <v-icon
                                slot="activator"
                                class="completed-process-mark"
                            >mdi-check-bold</v-icon>
                            <span>{{ $t('client.requests.process_completed.import') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else>
                        <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                    </span>


                    <!-- 手配 -->
                    <span v-if="allocationComplete(requestWork.completed_process)">
                        <v-tooltip top>
                            <v-icon
                                slot="activator"
                                class="completed-process-mark"
                            >mdi-check-bold</v-icon>
                            <span>{{ $t('client.requests.process_completed.allocation') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else-if="!allocationComplete(requestWork.completed_process) && inProgress(requestWork.process, 'allocation')" class="arrow-right-circle-wrap">
                        <span>
                            <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                        </span>
                        <v-tooltip top>
                            <span slot="activator" class="arrow-right-circle">
                                <v-icon color="#fff">chevron_right</v-icon>
                            </span>
                            <span>{{ $t('client.requests.process_in_progress.allocation') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else>
                        <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                    </span>


                    <!-- 作業 -->
                    <span v-if="tasksComplete(requestWork.completed_process)">
                        <v-tooltip top>
                            <v-icon
                                slot="activator"
                                class="completed-process-mark"
                            >mdi-check-bold</v-icon>
                            <span>{{ $t('client.requests.process_completed.work') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else-if="!tasksComplete(requestWork.completed_process) && inProgress(requestWork.process, 'work')" class="arrow-right-circle-wrap">
                        <span>
                            <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                        </span>
                        <v-tooltip top>
                            <span slot="activator" class="arrow-right-circle">
                                <v-icon color="#fff">chevron_right</v-icon>
                            </span>
                            <span>{{ $t('client.requests.process_in_progress.work') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else>
                        <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                    </span>


                    <!-- 検証 -->
                    <span v-if="approvalComplete(requestWork.completed_process)">
                        <v-tooltip top>
                            <v-icon
                                slot="activator"
                                class="completed-process-mark"
                            >mdi-check-bold</v-icon>
                            <span>{{ $t('client.requests.process_completed.approval') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else-if="!approvalComplete(requestWork.completed_process) && inProgress(requestWork.process, 'approval')" class="arrow-right-circle-wrap">
                        <span>
                            <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                        </span>
                        <v-tooltip top>
                            <span slot="activator" class="arrow-right-circle">
                                <v-icon color="#fff">chevron_right</v-icon>
                            </span>
                            <span>{{ $t('client.requests.process_in_progress.approval') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else>
                        <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                    </span>


                    <!-- 納品 -->
                    <span v-if="deliveryComplete(requestWork.completed_process)">
                        <v-tooltip top>
                            <v-icon
                                slot="activator"
                                class="completed-process-mark"
                            >mdi-check-bold</v-icon>
                            <span>{{ $t('client.requests.process_completed.delivery') }}</span>
                        </v-tooltip>
                    </span>
                    <span v-else>
                        <v-icon class="completed-process-mark opacity-0">mdi-check-bold</v-icon>
                    </span>
                </div>
                <!-- / マーク -->
            </div>
        </v-container>
        <v-spacer></v-spacer>
        <v-btn
            depressed
            small
            class="step-progress-toggle-btn"
            @click="showStepDetail = !showStepDetail">
            <v-icon v-show="showStepDetail">keyboard_arrow_up</v-icon>
            <v-icon v-show="!showStepDetail">keyboard_arrow_down</v-icon>
        </v-btn>
    </div>
    <!-- / 依頼作業単位の進捗バー -->


    <!-- 依頼作業単位の詳細 -->
    <div class="step-progress-detail-wrap" v-if="showStepDetail">
       <div class="step-progress-detail">
           <!-- 受付 -->
           <div class="ma-1 pa-2 process-block">
                <div class="process-status-icon-wrap">
                    <div class="text-xs-center mt-2 mb-1">
                        <v-icon
                            v-if="importComplete(requestWork.completed_process)"
                            class="completed-process-icon-bg"
                        >mdi-check-bold</v-icon>
                        <v-icon v-else>mdi-import</v-icon>
                    </div>
                    <div class="text-xs-center mb-1 process-name">{{ $t('client.requests.process.import') }}</div>
                </div>
                <div class="text-xs-center">
                    <div class="text-xs-center mt-1 mb-1 process-date">
                        <span>{{ $t('client.requests.completed_at') }}：</span>
                        <span v-if="requestWork.import_info.executed_at">{{ requestWork.import_info.executed_at | formatDateYmdHm }}</span>
                    </div>
                </div>
            </div>
            <!-- / 受付 -->

            <div>
                <v-icon
                    class="arrow-right-process-detail"
                    :color="arrowRightProcessDetailIconColor(importComplete(requestWork.completed_process))"
                >arrow_right_alt</v-icon>
            </div>

            <!-- 手配 -->
            <div class="ma-1 pa-2 process-block">
                <div class="process-status-icon-wrap">
                    <div class="text-xs-center mt-2 mb-1">
                        <v-icon
                            v-if="allocationComplete(requestWork.completed_process)"
                            class="completed-process-icon-bg"
                            >mdi-check-bold</v-icon>
                        <v-icon v-else>people_alt</v-icon>
                    </div>
                    <div class="text-xs-center mb-1 process-name">{{ $t('client.requests.process.allocation') }}</div>
                </div>
                <div class="text-xs-center">
                    <div class="text-xs-center mt-1 mb-1 process-date">
                        <span>{{ $t('client.requests.completed_at') }}：</span>
                        <span v-if="Object.keys(requestWork.allocation_info).length > 0 && requestWork.allocation_info.executed_at">{{ requestWork.allocation_info.executed_at | formatDateYmdHm }}</span>
                    </div>
                </div>
            </div>
            <!-- / 手配 -->

            <div>
                <v-icon
                    class="arrow-right-process-detail"
                    :color="arrowRightProcessDetailIconColor(allocationComplete(requestWork.completed_process))"
                >arrow_right_alt</v-icon>
            </div>

            <!-- 作業 -->
            <div class="ma-1 pa-2 process-block">
                <div class="process-status-icon-wrap">
                    <div class="text-xs-center mt-2 mb-1">
                        <v-icon
                            v-if="tasksComplete(requestWork.completed_process)"
                            class="completed-process-icon-bg"
                        >mdi-check-bold</v-icon>
                        <v-icon v-else>mdi-desktop-mac</v-icon>
                    </div>
                    <div class="text-xs-center mb-1 process-name">{{ $t('client.requests.process.work') }}</div>
                </div>
                <div class="text-xs-center">
                    <div class="text-xs-center mt-1 mb-1 process-date">
                        <span>{{ $t('client.requests.completed_at') }}：</span>
                        <span v-if="completedTasksAt(requestWork.completed_process, requestWork.tasks_info)">{{ completedTasksAt(requestWork.completed_process, requestWork.tasks_info) | formatDateYmdHm }}</span>
                    </div>
                </div>
            </div>
            <!-- / 作業 -->

            <div>
                <v-icon
                    class="arrow-right-process-detail"
                    :color="arrowRightProcessDetailIconColor(tasksComplete(requestWork.completed_process))"
                >arrow_right_alt</v-icon>
            </div>

            <!-- 検証 -->
            <div class="ma-1 pa-2 process-block">
                <div class="process-status-icon-wrap">
                    <div class="text-xs-center mt-2 mb-1">
                        <v-icon
                            v-if="approvalComplete(requestWork.completed_process)"
                            class="completed-process-icon-bg"
                        >mdi-check-bold</v-icon>
                        <v-icon v-else>mdi-file-document-box-check</v-icon>
                    </div>
                    <div class="text-xs-center mb-1 process-name">{{ $t('client.requests.process.approval') }}</div>
                </div>
                <div class="text-xs-center">
                    <div class="text-xs-center mt-1 mb-1 process-date">
                        <span>{{ $t('client.requests.completed_at') }}：</span>
                        <span v-if="requestWork.approval_info && requestWork.approval_info.updated_at">{{ requestWork.approval_info.updated_at | formatDateYmdHm }}</span>
                    </div>
                </div>
            </div>
            <!-- / 検証 -->

            <div>
                <v-icon
                    class="arrow-right-process-detail"
                    :color="arrowRightProcessDetailIconColor(approvalComplete(requestWork.completed_process))"
                >arrow_right_alt</v-icon>
            </div>

            <!-- 納品 -->
            <div class="ma-1 pa-2 process-block">
                <div class="process-status-icon-wrap">
                    <div class="text-xs-center mt-2 mb-1">
                        <v-icon
                            v-if="deliveryComplete(requestWork.completed_process)"
                            class="completed-process-icon-bg"
                        >mdi-check-bold</v-icon>
                        <v-icon v-else>mdi-truck-delivery</v-icon>
                    </div>
                    <div class="text-xs-center mb-1 process-name">{{ $t('client.requests.process.delivery') }}</div>
                </div>
                <div class="text-xs-center">
                    <div class="text-xs-center mt-1 mb-1 process-date">
                        <span>{{ $t('client.requests.completed_at') }}：</span>
                        <span v-if="requestWork.delivery_info && requestWork.delivery_info.updated_at">{{ requestWork.delivery_info.updated_at | formatDateYmdHm }}</span>
                    </div>
                </div>
            </div>
            <!-- / 納品 -->
       </div>
    </div>
    <!-- / 依頼作業単位の詳細 -->

</div>
</template>

<script>
import requestDetailMixin from '../../../../../mixins/Admin/requestDetailMixin'

export default {
    mixins: [requestDetailMixin],
    props: {
        requestWork: { type: Object, required: true },
        candidates: { type: Array, required: true },
        stepUrl: { type: String },
        businessName: { type: String, required: true },
        stepName: { type: String, required: true }
    },
    data: () => ({
        //loading
        loading: false,
        revisionsDialog: false,
        showStepDetail: false,

        completedStepBgColor: 'rgba(0,0,0,.38)'
    }),
    components:{
        // ProgressCircular
    },
    computed: {
        importComplete() {
            return function(process) {
                return process >= 1 ? true : false;
            }
        },
        allocationComplete() {
            return function(process) {
                return process >= 2 ? true : false;
            }
        },
        tasksComplete() {
            return function(process) {
                return process >= 3 ? true : false;
            }
        },
        approvalComplete() {
            return function(process) {
                return process >= 4 ? true : false;
            }
        },
        deliveryComplete() {
            return function(process) {
                return process >= 5 ? true : false;
            }
        },
        arrowRightProcessDetailIconColor() {
            return function(isCompleted) {
                if (isCompleted) {
                    return '#d7d7d7'
                } else {
                    return 'rgba(0, 217, 173, 1)'
                }
            }
        }
    },
    methods: {
        isContact (task_result) {
            if (!task_result) {
                return false
            }

            if (JSON.parse(task_result.content).results.type === _const.TASK_RESULT_TYPE.CONTACT) {
                return true;
            } else {
                return false;
            }
        },
        completedTasksAt (competedProcess, tasks) {
            // タスクのプロセスが完了していれば、完了済みタスクの中の最終更新日時を返す
            if (this.tasksComplete(competedProcess)) {
                let updatedAtTimestamps = tasks.map(function(task) {
                    if (task.status === _const.TASK_STATUS.DONE) {
                        let updated_at = task.updated_at
                        return updated_at
                    }
                })
                let updatedAt = updatedAtTimestamps[0]
                updatedAtTimestamps.forEach(function(item){
                    if (updatedAt <= item) {
                        updatedAt = item
                    }
                })
                return updatedAt
            } else {
                return false
            }
        },
        inProgress(process, processName) {
            if (process == _const.PROCESS_TYPE.ALLOCATION && processName == 'allocation') {
                return true
            } else if (process == _const.PROCESS_TYPE.WORK && processName == 'work') {
                return true
            } else if (process == _const.PROCESS_TYPE.APPROVAL && processName == 'approval') {
                return true
            } else {
                return false
            }
        }
    }
}
</script>
