<template>
    <v-stepper-header>
        <!-- 取込 -->
        <v-stepper-step
            :complete='importComplete(requestWork.completed_process)'
            :color="(importComplete(requestWork.completed_process) ? completedStepBgColor : '')"
            complete-icon=''
            step=""
        >
            <span class="process-label">{{ $t('requests.processes.import') }}</span>
            <div v-if="requestWork.import_info">
                <v-list>
                    <v-list-tile>
                        <v-list-tile-avatar v-if="(requestWork.import_info.operator_id != null)">
                            <v-tooltip top v-if="isHuman(requestWork.import_info.operator_id) && (requestWork.import_info.operator_id != null)">
                                <v-avatar slot="activator" size="26px" class="ma-1">
                                    <img :src="user_image_path(requestWork.import_info.operator_id)">
                                </v-avatar>
                                <span>{{ user_name(requestWork.import_info.operator_id) }}</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-icon slot="activator" size="26px">android</v-icon>
                                <span>{{ $t('requests.auto') }}</span>
                            </v-tooltip>
                        </v-list-tile-avatar>

                        <v-list-tile-action v-if="requestWork.import_info.executed_at">
                            <span class="caption">{{ requestWork.import_info.executed_at | formatDateYmdHm }}</span>
                        </v-list-tile-action>
                    </v-list-tile>
                </v-list>
            </div>
        </v-stepper-step>
        <!-- / 取込 -->

        <v-divider></v-divider>

        <!-- 割振 -->
        <v-stepper-step
            :complete='allocationComplete(requestWork.completed_process)'
            :color="(allocationComplete(requestWork.completed_process) ? completedStepBgColor : '')"
            complete-icon=''
            step=""
        >
            <span class="process-label-wrap">
                <v-tooltip top v-if="importComplete(requestWork.completed_process) && !referenceMode">
                    <a :href="allocateUri(requestWork.id)" class="text-underline" slot="activator">
                        <span>{{ $t('requests.processes.allocation') }}</span>
                    </a>
                    <span>{{ $t('requests.detail.step_progress.links.to_allocation_edit') }}</span>
                </v-tooltip>
                <span v-else class="process-label">{{ $t('requests.processes.allocation') }}</span>
            </span>
            <div v-if="requestWork.allocation_info && Object.keys(requestWork.allocation_info).length > 0">
                <v-list>
                    <v-list-tile
                        @click.stop.exact="requestWork.allocation_info.executed_at ? showReferenceView([allocateUri(requestWork.id)]) : ''"
                        @click.stop.shift="requestWork.allocation_info.executed_at ? showReferenceView([allocateUri(requestWork.id)], 'window') : ''"
                        @click.stop.ctrl="requestWork.allocation_info.executed_at ? showReferenceView([allocateUri(requestWork.id)], 'tab') : ''"
                    >
                        <v-list-tile-avatar v-if="requestWork.allocation_info.operator_id">
                            <v-tooltip top v-if="isHuman(requestWork.allocation_info.operator_id)">
                                <v-avatar slot="activator" size="26px" class="ma-1">
                                    <img :src="user_image_path(requestWork.allocation_info.operator_id)">
                                </v-avatar>
                                <span>{{ user_name(requestWork.allocation_info.operator_id) }}</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-icon slot="activator" size="26px">android</v-icon>
                                <span>{{ $t('requests.auto') }}</span>
                            </v-tooltip>
                        </v-list-tile-avatar>

                        <v-list-tile-action v-if="requestWork.allocation_info.executed_at">
                            <span class="caption">{{ requestWork.allocation_info.executed_at | formatDateYmdHm }}</span>
                        </v-list-tile-action>
                    </v-list-tile>
                  </v-list>
            </div>
        </v-stepper-step>
        <!-- / 割振 -->

        <v-divider></v-divider>

        <!-- 作業 -->
        <v-stepper-step
            :complete='tasksComplete(requestWork.completed_process)'
            :color="(tasksComplete(requestWork.completed_process) ? completedStepBgColor : '')"
            complete-icon=''
            step=""
        >
            <span class="process-label-wrap">
                <v-tooltip top v-if="allocationComplete(requestWork.completed_process) && !referenceMode" >
                    <a :href="requestWorkUri(stepName, [requestWork.id], 'work', total=true)"
                    class="text-underline" slot="activator">
                        <span>{{ $t('requests.processes.work') }}</span>
                    </a>
                    <span>{{ $t('requests.detail.step_progress.links.to_works') }}</span>
                </v-tooltip>
                <span v-else class="process-label">{{ $t('requests.processes.work') }}</span>
            </span>
            <div v-if="requestWork.tasks_info && Object.keys(requestWork.tasks_info).length > 0">
                <v-list>
                    <v-list-tile
                        v-for="task in requestWork.tasks_info"
                        :key="task.id"
                        @click.stop.exact="(task.status !== 0 && task.updated_at) ? showReferenceView([taskUri(requestWork.id, task.id)]) : ''"
                        @click.stop.shift="(task.status !== 0 && task.updated_at) ? showReferenceView(requestWork.tasks_info.map(task => taskUri(requestWork.id, task.id)), 'window') : ''"
                        @click.stop.ctrl="(task.status !== 0 && task.updated_at) ? showReferenceView(requestWork.tasks_info.map(task => taskUri(requestWork.id, task.id)), 'tab') : ''"
                    >
                        <v-list-tile-avatar v-if="task.user_id">
                            <v-badge left overlap color="red">
                                <v-tooltip top slot="badge" dark v-if="isContact(task.task_result)">
                                    <v-icon  slot="activator" style="vertical-align : middle" dark>priority_high</v-icon>
                                    <span>{{ $t('requests.detail.step_progress.task_result_type_contact') }}</span>
                                </v-tooltip>
                                <v-tooltip top v-if="isHuman(task.user_id)">
                                    <v-avatar slot="activator" size="26px" class="ma-1">
                                        <img :src="user_image_path(task.user_id)">
                                    </v-avatar>
                                    <span>{{ user_name(task.user_id) }}</span>
                                </v-tooltip>
                                <v-tooltip top v-else>
                                    <v-icon slot="activator" size="26px">android</v-icon>
                                    <span>{{ $t('requests.auto') }}</span>
                                </v-tooltip>
                            </v-badge>
                        </v-list-tile-avatar>

                        <v-list-tile-action>
                            <span class="caption" v-if="(task.status !== 0 && task.updated_at)">{{ task.updated_at | formatDateYmdHm }}</span>
                        </v-list-tile-action>

                    </v-list-tile>
                </v-list>
            </div>
        </v-stepper-step>
        <!-- / 作業 -->

        <v-divider></v-divider>

        <!-- 承認 -->
        <v-stepper-step
            :complete='approvalComplete(requestWork.completed_process)'
            :color="(approvalComplete(requestWork.completed_process) ? completedStepBgColor : '')"
            complete-icon=''
            step=""
        >
            <span class="process-label-wrap">
                <v-tooltip top v-if="existCompletedTask && !referenceMode">
                    <a :href="approvalUri(requestWork.id)" class="text-underline" slot="activator">
                        <span>{{ $t('requests.processes.approval') }}</span>
                    </a>
                    <span>{{ $t('requests.detail.step_progress.links.to_approval_edit') }}</span>
                </v-tooltip>
                <span v-else class="process-label">{{ $t('requests.processes.approval') }}</span>
            </span>
            <div v-if="requestWork.approval_info && requestWork.approval_info.status === 2">
                <v-list>
                    <v-list-tile
                        @click.stop.exact="requestWork.approval_info && requestWork.approval_info.updated_at ? showReferenceView([approvalUri(requestWork.id)]) : ''"
                        @click.stop.shift="requestWork.approval_info && requestWork.approval_info.updated_at ? showReferenceView([approvalUri(requestWork.id)], 'window') : ''"
                        @click.stop.ctrl="requestWork.approval_info && requestWork.approval_info.updated_at ? showReferenceView([approvalUri(requestWork.id)], 'tab') : ''"
                    >
                        <v-list-tile-avatar v-if="(requestWork.approval_info.updated_user_id != null)">
                            <v-tooltip top v-if="isHuman(requestWork.approval_info.updated_user_id)">
                                <v-avatar slot="activator" size="26px" class="ma-1">
                                    <img :src="user_image_path(requestWork.approval_info.updated_user_id)">
                                </v-avatar>
                                <span>{{ user_name(requestWork.approval_info.updated_user_id) }}</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-icon slot="activator" size="26px">android</v-icon>
                                <span>{{ $t('requests.auto') }}</span>
                            </v-tooltip>
                        </v-list-tile-avatar>
                        <v-list-tile-action v-if="requestWork.approval_info && requestWork.approval_info.updated_at">
                            <span class="caption">{{ requestWork.approval_info.updated_at | formatDateYmdHm }}</span>
                        </v-list-tile-action>
                    </v-list-tile>
                </v-list>
            </div>
        </v-stepper-step>
        <!-- / 承認 -->

        <v-divider></v-divider>

        <!-- 納品 -->
        <v-stepper-step
            :complete='deliveryComplete(requestWork.completed_process)'
            :color="(deliveryComplete(requestWork.completed_process) ? completedStepBgColor : '')"
            complete-icon=''
            step=""
        >
            <v-tooltip top v-if="deliveryComplete(requestWork.completed_process) && !referenceMode">
                <a :href="deliveryUri(requestWork.id)" class="text-underline" slot="activator">
                    <span>{{ $t('requests.processes.delivery') }}</span>
                </a>
                <span>{{ $t('requests.detail.step_progress.links.to_delivery_detail') }}</span>
            </v-tooltip>
            <span v-else class="process-label">{{ $t('requests.processes.delivery') }}</span>
            <div v-if="isDelivered(requestWork)">
                <v-list>
                    <v-list-tile
                        @click.stop.exact="(requestWork.delivery_info && requestWork.delivery_info.updated_at) ? showReferenceView([deliveryUri(requestWork.id)]) : ''"
                        @click.stop.shift="(requestWork.delivery_info && requestWork.delivery_info.updated_at) ? showReferenceView([deliveryUri(requestWork.id)], 'window') : ''"
                        @click.stop.ctrl="(requestWork.delivery_info && requestWork.delivery_info.updated_at) ? showReferenceView([deliveryUri(requestWork.id)], 'tab') : ''"
                    >
                        <v-list-tile-avatar v-if="(requestWork.delivery_info.updated_user_id != null)">
                            <v-tooltip top v-if="isHuman(requestWork.delivery_info.updated_user_id)">
                                <v-avatar slot="activator" size="26px" class="ma-1">
                                    <img :src="user_image_path(requestWork.delivery_info.updated_user_id)">
                                </v-avatar>
                                <span>{{ user_name(requestWork.delivery_info.updated_user_id) }}</span>
                            </v-tooltip>
                            <v-tooltip top v-else>
                                <v-icon slot="activator" size="26px">android</v-icon>
                                <span>{{ $t('requests.auto') }}</span>
                            </v-tooltip>
                        </v-list-tile-avatar>
                        <v-list-tile-action v-if="requestWork.delivery_info && requestWork.delivery_info.created_at">
                            <span class="caption">{{ deliveredDate(requestWork) | formatDateYmdHm }}</span>
                        </v-list-tile-action>
                    </v-list-tile>
                </v-list>
            </div>
        </v-stepper-step>
        <!-- 納品 -->
        <reference-view-dialog ref="referenceViewDialog"></reference-view-dialog>
    </v-stepper-header>

</template>

<script>
import requestDetailMixin from '../../../../../mixins/Admin/requestDetailMixin'
import ReferenceViewDialog from '../../../../Atoms/Dialogs/ReferenceViewDialog'

export default {
    mixins: [requestDetailMixin],
    components:{
        ReferenceViewDialog,
    },
    props: {
        requestWork: { type: Object, required: true },
        candidates: { type: Array, required: true },
        stepUrl: { type: String },
        businessName: { type: String, required: true },
        stepName: { type: String, required: true },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        //loading
        loading: false,
        revisionsDialog: false,
        completedStepBgColor: 'rgba(0,0,0,.38)'
    }),
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
        isDelivered() {
            return function(requestWork) {
                return requestWork.delivery_info && requestWork.delivery_info.status == _const.DELIVERY_STATUS.DONE
            }
        },
        deliveredDate() {
            return function(requestWork) {
                const deliveryInfo = requestWork.delivery_info
                return deliveryInfo.is_assign_date == _const.FLG.ACTIVE ? deliveryInfo.assign_delivery_at : deliveryInfo.created_at
            }
        },
        allocateUri() {
            return function(requestWorkId) {
                return '/allocations/' + requestWorkId + '/edit'
            }
        },
        taskUri() {
            return function(requestWorkId, taskId) {
                return '/biz/' + this.stepUrl + '/' + requestWorkId + '/' + taskId + '/create'
            }
        },
        approvalUri() {
            return function(requestWorkId) {
                return '/approvals/' + requestWorkId + '/edit'
            }
        },
        deliveryUri() {
            return function(requestWorkId) {
                return '/deliveries/' + requestWorkId
            }
        },
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
        existCompletedTask() {
            let result = false
            if (this.requestWork.task) {
                let tasks = this.requestWork.task
                tasks.forEach(function(task) {
                    if (task.status == _const.TASK_STATUS.DONE) {
                        result = true
                    }
                })
            }
            return result
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
        deadline() {
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
        showReferenceView (uris, type) {
            if (!uris) return
            this.$refs.referenceViewDialog.show(uris, type)
        },
    }
}
</script>
<style scoped>
    .process-label-wrap {
        display: flex;
        position: relative;
    }
    .process-label-wrap .icon-next-to-process-label {
        position: absolute;
        font-size: 20px;
    }
    .process-label {
        color: initial;
    }
</style>
