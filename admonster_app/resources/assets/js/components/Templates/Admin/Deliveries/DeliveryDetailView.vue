<template>
    <v-app>
        <app-menu :drawer="sideMenuDrawer" :reference-mode="referenceMode"></app-menu>
        <app-header :title="requestInfo.business_name" :subtitle="$t('deliveries.detail.title')" :reference-mode="referenceMode"></app-header>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <re-delivery-confirm-dialog ref="reDeliveryConfirm"></re-delivery-confirm-dialog>
        <v-content :style="contentStyle">
            <v-container fluid grid-list-md v-if="requestInfo">
                <v-layout row wrap>

                    <v-flex xs12>
                        <page-header back-button :reference-mode="referenceMode"></page-header>
                    </v-flex>

                    <v-flex shrink :class="showRequestContent ? 'xs12 md6' : ''" ref="requestContent">
                        <!-- 依頼内容コンポーネント -->
                        <request-contents
                            :request-work-id="requestWorkId"
                            v-model="showRequestContent"
                            height="70vh + 10px"
                            :request="requestInfo"
                            :selectedContent="selectContent"
                            :labelData="labelData"
                            :candidates="candidates"
                        >
                        </request-contents>
                    </v-flex>

                    <v-flex grow :class="showRequestContent ? 'xs12 md6' : ''" :style="{maxWidth : componentMaxWidth}">
                        <deliveries-overview
                            :request="requestInfo"
                            :task-result="taskResult"
                            :delivery-info="deliveryInfo"
                            ref="deliveriesOverview"
                        ></deliveries-overview>
                        <div v-if="canRedelivery" class="mt-4" style="text-align: right;">
                            <span class="ma-0" style="cursor: pointer;color: gray;" @click="redelivery()">{{ $t('deliveries.detail.redelivery') }}</span>
                        </div>
                    </v-flex>
                </v-layout>

                <v-layout row>
                    <v-flex xs12>
                        <div class="btn-center-block">
                            <v-btn v-if="isCreate" color="primary" @click="deliveryStore()">{{ $t('common.button.delivery') }}</v-btn>
                            <v-btn v-else-if="isEdit" color="primary" @click="deliveryUpdate()">{{ $t('common.button.update') }}</v-btn>
                        </div>
                    </v-flex>
                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer :reference-mode="referenceMode"></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import RequestContents from '../../../Organisms/Common/RequestContents'
import DeliveriesOverview from '../../../Organisms/Admin/Deliveries/DeliveriesOverview'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ReDeliveryConfirmDialog from '../../../Organisms/Admin/Deliveries/ReDeliveryConfirmDialog'

export default {
    components: {
        PageHeader,
        ProgressCircular,
        RequestContents,
        DeliveriesOverview,
        AlertDialog,
        ConfirmDialog,
        ReDeliveryConfirmDialog
    },
    props: {
        requestWorkId: { type: Number, require: true },
        delivery: { type: [Object, Array], required: false, default: () => ({}) },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        loading: false,
        requestInfo: {},
        taskResult: {},
        approvalTasks: [],
        startedAt: {},
        candidates: [],
        labelData: {},
        showRequestContent: false,
        sideMenuDrawer: false,
        componentMaxWidth: '',
        canRedelivery: false,
    }),
    computed: {
        selectContent () {
            // RequestContent内の条件分岐に合わせて記載
            if ('request_mail' in this.requestInfo) return 'mail'
            if ('request_file' in this.requestInfo) return 'file'
            return ''
        },
        contentStyle () {
            return this.referenceMode ? {padding: 0} : {}
        },
        deliveryInfo () {
            return Array.isArray(this.delivery) ? {} : this.delivery
        },
        isCreate () {
            return this.deliveryInfo.delivery_status == _const.DELIVERY_STATUS.NONE
        },
        isEdit () {
            return this.deliveryInfo.delivery_status == _const.DELIVERY_STATUS.SCHEDULED
        },
    },
    watch: {
        showRequestContent: function () {
            this.$nextTick(function () {
                // メールiconの下にstep要素が改行されて表示されるのを防ぐ（原因:動画要素）
                if (this.requestInfo.step_id === 8) {
                    if (this.showRequestContent) {
                        this.componentMaxWidth = ''
                    } else {
                        this.componentMaxWidth = `calc(100% - ${this.$refs.requestContent.offsetWidth}px)`
                    }
                }
            })
        }
    },
    created () {
        this.init()
    },
    methods: {
        init () {
            this.loading = true

            let formData = new FormData()
            formData.append('request_work_id', this.requestWorkId)

            axios.post('/api/approvals', formData)
                .then((res) => {
                    const task_id = res.data.approval_tasks.find(approvalTask => approvalTask.approval_result == _const.APPROVAL_RESULT.OK).task_id
                    let taskResult = res.data.task_results.find(taskResult => taskResult.task_id == task_id)
                    taskResult.content = JSON.parse(taskResult.content)
                    this.taskResult = taskResult

                    this.requestInfo = res.data.request
                    this.startedAt = res.data.started_at
                    this.candidates = res.data.businesses_candidates
                    this.labelData = res.data.label_data

                    // 再納品を行える作業かチェック
                    if (this.deliveryInfo.delivery_status == _const.DELIVERY_STATUS.DONE) {
                        this.setRedelivery(res.data.request.step_id)
                    }
                })
                .catch((err) => {
                    console.log(err)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                })
                .finally(() => {
                    this.loading = false
                });
        },
        async setRedelivery (stepId) {
            let formData = new FormData()
            formData.append('step_id', stepId)
            const canRedeliveryRes = await axios.post('/api/deliveries/canRedelivery', formData)
            if (canRedeliveryRes.data.result === 'success') {
                this.canRedelivery = true
            }
        },
        async deliveryStore () {
            if (await this.$refs.confirm.show(this.$t('approvals.dialog.delivery.text'))) {
                this.loading = true
                let params = new FormData()
                params.append('request_work_id', this.taskResult.request_work_id)
                params.append('content', JSON.stringify(this.taskResult.content))
                params.append('task_id', this.taskResult.task_id)
                params.append('started_at', this.startedAt.date)
                if (!this.$refs.deliveriesOverview.isCancel) {
                    const deliveryDate = this.$refs.deliveriesOverview.deliveryDate
                    params.append('assign_delivery_at', deliveryDate)
                }

                axios.post('/api/deliveries/store', params)
                    .then((res) => {
                        if (res.data.status == 200) {
                            this.$refs.alert.show(this.$t('common.message.saved'), () =>  window.location.href = '/deliveries')
                        } else {
                            this.$refs.alert.show(this.$t('common.message.internal_error'))
                        }
                    })
                    .catch((err) => {
                        console.log(err)
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    })
                    .finally(() => {
                        this.loading = false
                    })
            }
        },
        async deliveryUpdate () {
            this.loading = true
            let params = new FormData()
            params.append('delivery_id', this.deliveryInfo.id)
            params.append('request_work_id', this.taskResult.request_work_id)
            params.append('updated_at', this.deliveryInfo.updated_at)
            if (!this.$refs.deliveriesOverview.isCancel) {
                const deliveryDate = this.$refs.deliveriesOverview.deliveryDate
                params.append('assign_delivery_at', deliveryDate)
            }

            axios.post('/api/deliveries/changeDeliveryAssignDate', params)
                .then((res) => {
                    if (res.data.status == 200) {
                        this.$refs.alert.show(this.$t('common.message.updated'), () =>  window.location.href = '/deliveries')
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                })
                .catch((err) => {
                    console.log(err)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                })
                .finally(() => {
                    this.loading = false
                })
        },
        async redelivery () {
            // 一つでもキャンセルがあれば処理を終了
            if (!await this.$refs.confirm.show(Vue.i18n.translate('deliveries.detail.message.redelivery_1'))) return
            if (!await this.$refs.confirm.show(`<h4>${Vue.i18n.translate('deliveries.detail.message.redelivery_2_1')}</h4>
                <div style="color: red;">${Vue.i18n.translate('deliveries.detail.message.redelivery_2_2')}</div>
                <div style="color: red;">${Vue.i18n.translate('deliveries.detail.message.redelivery_2_3')}</div>`)) return
            if (!await this.$refs.reDeliveryConfirm.show(`<h4>${Vue.i18n.translate('deliveries.detail.message.redelivery_3_1')}</h4>
                <div style="color: red;">${Vue.i18n.translate('deliveries.detail.message.redelivery_3_2')}</div>
                <div style="color: red;">${Vue.i18n.translate('deliveries.detail.message.redelivery_3_3')}</div>`)) return

            this.loading = true
            try {
                const formData = new FormData()
                formData.append('request_work_id', this.requestWorkId)
                const res = await axios.post('/api/deliveries/redelivery', formData)
                this.loading = false
                if (res.data.result === 'success') {
                    this.$refs.alert.show(this.$t('common.message.success'), () =>  window.location.href = '/deliveries')
                } else if (res.data.result === 'error') {
                    this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
                }
            } catch (error) {
                console.log(error)
                this.loading = false
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
            }
        },
    },
}
</script>
