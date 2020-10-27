<template>
    <v-app id="approvals">
        <app-menu :drawer="drawer" :reference-mode="isReference"></app-menu>
        <app-header
            :title="request ? request.business_name : ''"
            :subtitle="$t('approvals.title')"
            :reference-mode="isReference"
        ></app-header>
        <v-content :style="contentStyle">
            <v-container fluid grid-list-md v-if="request">
                <v-layout row>
                    <!-- Main -->
                    <v-flex :class="showRequestContent ? 'xs12 md6' : ''">
                        <request-contents
                            :request-work-id="Number(inputs['request_work_id'])"
                            :request="request"
                            v-model="showRequestContent"
                            :candidates="businessesCandidates"
                            :class="requestContentsClass"
                            :label-data="labelData"
                            height="70vh + 50px"
                        >
                        </request-contents>
                    </v-flex>
                    <v-flex :class="showRequestContent ? 'xs12 md6' : 'width-excluding-pre-element'">
                        <task-result-comparative
                            ref="workComparative"
                            :short-display="showRequestContent"
                        ></task-result-comparative>
                    </v-flex>
                </v-layout>
                <v-layout row>
                    <v-flex xs12>
                        <v-checkbox
                            v-show="edit"
                            v-model="deliveryLater"
                            :label="$t('approvals.not_immediate')"
                            class="justify-center"
                            hide-details
                        ></v-checkbox>
                    </v-flex>
                </v-layout>
                <v-layout row>
                    <v-flex xs12>
                        <div class="btn-center-block">
                            <v-btn
                                v-show="edit"
                                color="amber"
                                dark
                                @click="clickHold"
                            >{{ $t('common.button.hold') }}</v-btn>
                            <v-btn
                                v-show="edit"
                                color="primary"
                                @click="approval"
                            >{{ $t('common.button.approval') }}</v-btn>
                        </div>
                    </v-flex>
                    <!-- Main -->
                </v-layout>
                <page-footer back-button :reference-mode="isReference"></page-footer>
            </v-container>
            <alert-dialog ref="alert"></alert-dialog>
            <progress-circular v-if="loading"></progress-circular>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <confirm-check-dialog ref="confirmCheckDialog"></confirm-check-dialog>
        </v-content>
        <app-footer :reference-mode="isReference"></app-footer>
    </v-app>
</template>

<script>
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import TaskResultComparative from '../../../Organisms/Admin/Approvals/TaskResultComparative'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ConfirmCheckDialog from '../../../Atoms/Dialogs/ConfirmCheckDialog'
import store from '../../../../stores/Admin/Approvals/Detail/store'

export default {
    props: {
        inputs: { type: Object },
    },
    components: {
        PageFooter,
        TaskResultComparative,
        RequestContents,
        ProgressCircular,
        AlertDialog,
        ConfirmDialog,
        ConfirmCheckDialog,
    },
    data: () => ({
        drawer: false,
        showRequestContent: false,
        dialog: false,
        deliveryLater: false,
    }),
    computed: {
        edit() {
            return JSON.parse(JSON.stringify(store.state.processingData.edit))
        },
        selected() {
            return JSON.parse(JSON.stringify(store.state.processingData.selected))
        },
        request() {
            return JSON.parse(JSON.stringify(store.state.processingData.request))
        },
        taskResults() {
            return JSON.parse(JSON.stringify(store.state.processingData.taskResults))
        },
        approvalTasks() {
            return JSON.parse(JSON.stringify(store.state.processingData.approvalTasks))
        },
        businessesCandidates() {
            return JSON.parse(JSON.stringify(store.state.processingData.businessesCandidates))
        },
        labelData() {
            return store.state.processingData.labelData
        },
        requestContentsClass() {
            let classText = []
            const requestMail = this.request.request_mail
            if (requestMail) {
                classText.push(requestMail.mail_attachments.length > 0 ? '' : 'no-mail-attachment')
            }
            classText.push(this.request.request_file ? 'exist-request-file' : '')
            return classText
        },
        loading: {
            set(val) {
                store.commit('setProcessingData', {loading: val})
            },
            get() {
                return store.state.processingData.loading
            }
        },
        contentStyle () {
            return this.isReference ? {padding: 0} : {}
        },
        isReference () {
            return this.inputs.reference_mode == 'true' ? true : false
        },
        request_work_updated_at: {
            set(val) {
                store.commit('setProcessingData', {request_work_updated_at: val})
            },
            get() {
                return store.state.processingData.request_work_updated_at
            }
        }
    },
    methods: {
        async approval () {
            if (this.selected.length === 1) {

                // 処理中の作業者がいる場合、承認処理を続けるか確認
                if (this.taskResults.filter( task => task.status !==  _const.TASK_STATUS.DONE && task.is_active === _const.FLG.ACTIVE).length > 0) {
                    const canProcess = await this.$refs.confirm.show(Vue.i18n.translate('approvals.dialog.approval.attention.other_worker_editing'))
                    if (!canProcess) return
                }
                // 承認前最終確認
                let checkLabels = this.$t('approvals.dialog.approval.check_list')
                if (!this.deliveryLater) checkLabels.push(this.$t('approvals.dialog.approval.check_immediate'))
                if (!await this.$refs.confirmCheckDialog.show(checkLabels)) {
                    return
                }

                this.loading = true
                let params = new FormData()
                params.append('request_id', this.request.id)
                params.append('request_work_id', this.request.request_work_id)
                params.append('approval_status', _const.APPROVAL_STATUS.DONE)
                const activeTaskIds = this.taskResults.filter(task => task.is_active === _const.FLG.ACTIVE).map(task => task.task_id)
                const sendApprovalTasks  = this.approvalTasks.filter(approvalTask => activeTaskIds.includes(approvalTask.task_id))
                params.append('approval_tasks', JSON.stringify(sendApprovalTasks))
                params.append('result_type', _const.TASK_RESULT_TYPE.DONE)
                params.append('request_work_updated_at', this.request_work_updated_at)
                const taskResult = this.taskResults.find(taskResult => taskResult.user_id == this.selected[0])
                params.append('content', JSON.stringify(taskResult.content))
                params.append('task_id', taskResult.task_id)
                if (this.deliveryLater) {
                    params.append('delivery_later', this.deliveryLater)
                }

                axios.post('/api/approvals/store', params)
                    .then((res) => {
                        if (res.data.status === _const.API_STATUS_CODE.SUCCESS) {
                            this.$refs.alert.show(
                                this.$t('common.message.saved'),
                                () => window.location.href = '/approvals'
                            )
                        } else if (res.data.status === _const.API_STATUS_CODE.EXCLUSIVE) {
                            this.$refs.alert.show(
                                '<div>' + this.$t('common.message.updated_by_others') + '</div>'
                                + '<div>' + this.$t('common.message.reload_screen') + '</div>',
                                () => window.location.reload()
                            )
                        } else {
                            this.$refs.alert.show(this.$t('common.message.internal_error'))
                        }
                    })
                    .catch(() => {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    })
                    .finally(() => {
                        this.loading = false
                    })

            } else {
                // ダイアログ表示
                this.$refs.alert.show(
                    this.$t('approvals.dialog.approval.error.selected_count'),
                )
            }
        },
        async clickHold () {
            if (await this.$refs.confirm.show(this.$t('common.message.save_confirm'))) {
                const isSuccess = await this.hold()
                if (isSuccess) {
                    if (window.history.length > 1) {
                        window.history.back()
                    } else {
                        window.location.href = '/approvals'
                    }
                }
            }
        },
        async hold () {
            try {
                this.loading = true
                let params = new FormData()
                params.append('request_id', this.request.id)
                params.append('request_work_id', this.request.request_work_id)
                params.append('approval_status', _const.APPROVAL_STATUS.ON)
                const activeTaskIds = this.taskResults.filter(task => task.is_active === _const.FLG.ACTIVE).map( task => task.task_id)
                const sendApprovalTasks  = this.approvalTasks.filter( approvalTask => activeTaskIds.includes(approvalTask.task_id))
                params.append('approval_tasks', JSON.stringify(sendApprovalTasks))
                params.append('request_work_updated_at', this.request_work_updated_at)

                const res = await axios.post('/api/approvals/store',  params)
                this.loading = false
                if (res.data.status === _const.API_STATUS_CODE.SUCCESS) {
                    this.request_work_updated_at = res.data.request_work.updated_at
                    return true
                } else if (res.data.status === _const.API_STATUS_CODE.EXCLUSIVE) {
                    this.$refs.alert.show(
                        '<div>' + this.$t('common.message.updated_by_others') + '</div>'
                        + '<div>' + this.$t('common.message.reload_screen') + '</div>',
                        () => window.location.reload()
                    )
                    return false
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                    return false
                }
            } catch (error) {
                this.loading = false
                // ダイアログ表示
                this.$refs.alert.show(
                    this.$t('common.message.internal_error')
                )
                return false
            }
        },
    },
    async created() {
        store.commit('setProcessingData', {inputs: this.inputs})
        this.loading = true
        try {
            await store.dispatch('tryRefreshApprovals', store.state.processingData.inputs)
            this.request_work_updated_at = this.request.request_work_updated_at
        } catch (error) {
            // ダイアログ表示
            this.$refs.alert.show(
                this.$t('common.message.internal_error'),
                () => window.location.href = '/approvals'
            )
        } finally {
            this.loading = false
        }
    },
}
</script>

<style scoped>
.width-excluding-pre-element {
    width: calc(100% - 64px)
}
</style>
