<template>
<v-app id="request-detail" :class="mainBgClass">
    <app-menu :drawer="drawer" :reference-mode="referenceMode"></app-menu>
    <app-header
        :title="baseInfo.business_name"
        :subtitle="$t('requests.detail.title')"
        :reference-mode="referenceMode"
    ></app-header>
    <v-content :style="contentStyle">
        <v-container fluid grid-list-md>
            <v-layout row wrap mb-3>
                <v-flex xs12>
                    <page-header back-button :alerts="alerts" :dark="!doing" :reference-mode="referenceMode">
                        <template slot="rightHeader">
                            <span class="mr-2">{{ $t('requests.detail.deadline') }}:
                                <span v-if="deadline">{{ deadline | formatDateYmdHm }}</span>
                                <span v-else>{{ $t('requests.detail.unset') }}</span>
                                <!-- <v-icon small @click="editorDialog = true">edit</v-icon> -->
                            </span>
                            <!-- 処理ボタン -->
                            <template v-if="!referenceMode">
                                <v-btn
                                    color="error"
                                    @click="openModal('except')"
                                    v-if="doing"
                                >{{ $t('requests.detail.btn_except') }}</v-btn>
                                <v-btn
                                    color="info"
                                    @click="guestClientDialog = !guestClientDialog"
                                >{{ $t('requests.detail.btn_share_status') }}</v-btn>
                                <v-btn
                                    color="warning"
                                    @click="openModal('replicate')"
                                >{{ $t('requests.detail.btn_replicate') }}</v-btn>
                            </template>
                            <!-- / 処理ボタン -->
                        </template>
                    </page-header>
                    <except-confirm-dialog></except-confirm-dialog>
                    <replicate-confirm-dialog></replicate-confirm-dialog>
                    <guest-client-dialog
                        v-if="guestClientDialog"
                        :guest-client-dialog.sync="guestClientDialog"
                        :request-id="requestId"
                        :request-mail="requestMail"
                        :candidates="candidates"
                    ></guest-client-dialog>
                    <replicate-notify-modal></replicate-notify-modal>
                </v-flex>
                <v-flex
                    :class="(toBeCheckedData.length < 1 && requestLogs.length < 1) ? 'xs12' : 'xs8'"
                >
                    <div id="request-content-wrap" class="elevation-1">
                        <request-contents
                            :request-work-id="requestWorkId"
                            :request="requestContent"
                            :selected-content="selectContent"
                            :label-data="labelData"
                            :candidates="candidates"
                            :count-summary="countSummary"
                            height="auto"
                            max-height="608px"
                            parent-component="RequestDetailView"
                            hide-close-icon
                            displayInit
                        ></request-contents>
                    </div>
                </v-flex>
                <v-flex xs4 id="timeline-wrap">
                    <!-- 確認事項 -->
                    <v-layout row wrap v-if="toBeCheckedData.length > 0" id="to-be-checked-wrap">
                        <v-flex xs12>
                            <to-be-checked :to-be-checked-data="toBeCheckedData" :candidates="candidates" v-if="toBeCheckedData"></to-be-checked>
                        </v-flex>
                    </v-layout>
                    <!-- 確認事項 -->
                    <!-- ステータスサマリー -->
                    <v-layout row wrap v-show="requestLogs" id="status-summary-wrap">
                        <v-flex xs12>
                            <status-summary :request-logs="requestLogs" :candidates="candidates" v-if="requestLogs.length > 0" :reference-mode="referenceMode"></status-summary>
                        </v-flex>
                    </v-layout>
                    <!-- / ステータスサマリー -->
                </v-flex>
            </v-layout>

            <!-- 関連メール -->
            <v-layout row wrap mb-4 v-if="relatedMails.length > 0">
                <v-flex xs12>
                    <related-mails
                        :request-id="requestId"
                        :request-related-mails="relatedMails"
                        v-on:update-related-mails="updateRelatedMails"
                    ></related-mails>
                </v-flex>
            </v-layout>
            <v-layout row wrap mb-2 v-else>
                <v-flex xs12>
                    <create-mail-alias :request-id="requestId"></create-mail-alias>
                </v-flex>
            </v-layout>
            <!-- / 関連メール -->

            <!-- 補足情報 -->
            <v-layout row wrap mb-4>
                <v-flex xs12>
                    <additional-info
                        :request-id="requestId"
                        :candidates="candidates"
                    >
                    </additional-info>
                </v-flex>
            </v-layout>
            <!-- / 補足情報 -->

            <!-- 作業進捗状況 -->
            <v-layout row wrap>
                <v-flex xs12>
                    <step-progress :step-details="stepDetails" :count-summary="countSummary" :candidates="candidates" :business-name="businessName" :request-id="requestId" v-if="stepDetails.length > 0" :reference-mode="referenceMode"></step-progress>
                </v-flex>
            </v-layout>
            <!-- / 作業進捗状況 -->

            <template>
                <v-dialog v-model="editorDialog" persistent width="300">
                    <v-card>
                        <v-card-title class="headline" primary-title>{{ $t('requests.confirm') }}</v-card-title>
                        <v-card-text>
                            {{ $t('common.message.save_confirm') }}
                            <date-time-picker
                                v-model="changeableDeadline"
                                :label="$t('requests.search_condition.from')"
                            ></date-time-picker>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn flat color="primary" @click="update()">{{ $t('common.button.ok') }}</v-btn>
                            <v-btn flat color="primary" @click="editorDialog = false">{{ $t('common.button.cancel') }}</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </template>

            <page-footer back-button :reference-mode="referenceMode"></page-footer>
        </v-container>

        <progress-circular v-if="loading"></progress-circular>
    </v-content>
    <to-top></to-top>
    <app-footer back_uri="/management/requests" :reference-mode="referenceMode"></app-footer>
</v-app>
</template>

<script>
import PageHeader from './../../../Organisms/Layouts/PageHeader'
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ToBeChecked from './../../../Organisms/Admin/Requests/Detail/ToBeChecked'
import StatusSummary from './../../../Organisms/Admin/Requests/Detail/StatusSummary'
import AdditionalInfo from './../../../Organisms/Admin/Requests/Detail/AdditionalInfo'
import StepProgress from './../../../Organisms/Admin/Requests/Detail/StepProgress'
import ExceptConfirmDialog from '../../../Organisms/Admin/Requests/Detail/ExceptConfirmDialog'
import ReplicateConfirmDialog from '../../../Organisms/Admin/Requests/Detail/ReplicateConfirmDialog'
import GuestClientDialog from '../../../Organisms/Admin/Requests/Detail/GuestClientDialog'
import ReplicateNotifyModal from '../../../Organisms/Admin/Requests/Detail/ReplicateNotifyModal'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import DateTimePicker from '../../../Atoms/Pickers/DateTimePicker'
import ToTop from '../../../Atoms/Buttons/ToTop'
import CreateMailAlias from './../../../Organisms/Admin/Requests/Detail/CreateMailAlias'
import RelatedMails from './../../../Organisms/Admin/Requests/Detail/RelatedMails'

export default {
    components:{
        PageHeader,
        PageFooter,
        RequestContents,
        ToBeChecked,
        StatusSummary,
        AdditionalInfo,
        StepProgress,
        ExceptConfirmDialog,
        ReplicateConfirmDialog,
        GuestClientDialog,
        ReplicateNotifyModal,
        ProgressCircular,
        DateTimePicker,
        ToTop,
        CreateMailAlias,
        RelatedMails
    },
    props: {
        eventHub: eventHub,
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number, required: true },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: ()=> ({
        drawer: false,
        loading: false,
        dialog: false,
        editorDialog: false,
        guestClientDialog: false,

        // 画面スクロール位置管理用
        currentUrl: location.href,
        scrollY: 0,
        lastScrollY: 0,

        alerts: {
            is_completed_request: {
                model: false,
                dismissible: false,
                color: '#fff',
                icon: 'info',
                message: Vue.i18n.translate('common.message.is_completed_request'),
            },
            is_deleted_request: {
                model: false,
                dismissible: false,
                color: '#fff',
                icon: 'info',
                message: Vue.i18n.translate('common.message.is_deleted_request'),
            },
            overdue: {
                model: false,
                dismissible: false,
                icon: 'warning',
                message: Vue.i18n.translate('common.message.overdue'),
            }
        },
        businessName: '',
        baseInfo: [],
        requestMail: null,
        toBeCheckedData: [],
        requestLogs: [],
        countSummary: [],
        stepDetails: [],
        candidates: [],
        relatedMails: [],
        requestFile: '',

        mainBgClass: '',

        // ラベルデータ
        labelData: {},
        changeableDeadline: '',
    }),
    computed: {
        deadline () {
            if (this.baseInfo.deadline) {
                return this.baseInfo.deadline
            }
            if (this.baseInfo.system_deadline) {
                return this.baseInfo.system_deadline
            }
            return ''
        },
        doing () {
            return this.baseInfo.status === _const.REQUEST_STATUS.DOING ? true : false
        },
        requestContent () {
            if (this.requestMail) {
                return {request_mail: this.requestMail}
            } else if (this.requestFile) {
                return {request_file: this.requestFile}
            } else {
                return {}
            }
        },
        selectContent () {
            // RequestContent内の条件分岐に合わせて記載
            if (this.requestMail) return 'mail'
            if (this.requestFile) return 'file'
            return ''
        },
        contentStyle () {
            return this.referenceMode ? {padding: 0} : {}
        },
        startedAt () {
            return document.getElementById('loaded-utc-datetime').value
        },
    },
    methods: {
        openModal: function(type) {
            let message = ''
            let name = ''

            if (type === 'except') {
                message = Vue.i18n.translate('requests.detail._modal.p1')
                name = 'open-except-confirm-modal'
            } else {
                message = Vue.i18n.translate('requests.detail._modal.p2')
                name = 'open-replicate-confirm-modal'
            }

            eventHub.$emit(name, {
                'message': message
            })
        },
        setArrDataInDataProperty (data, targetData) {
            for (var i = 0; i < data.length; i++) {
                this.$set(targetData, i, data[i])
            }
        },
        setStyleByRequestStatus () {
            if (this.baseInfo.status === _const.REQUEST_STATUS.FINISH && this.baseInfo.is_deleted === _const.FLG.INACTIVE) {
                this.mainBgClass = 'bg-completed'
                this.alerts['is_completed_request'].model = true
            } else if (this.baseInfo.status === _const.REQUEST_STATUS.EXCEPT && this.baseInfo.is_deleted === _const.FLG.INACTIVE) {
                this.mainBgClass = 'bg-deleted'
                this.alerts['is_deleted_request'].model = true
            }
        },
        getRequestDetails () {
            this.loading = true
            axios.post('/api/requests/show',{
                request_id: this.requestId
            })
                .then((res) => {
                    this.baseInfo = res.data.base_info
                    this.requestMail = res.data.request_mail
                    this.setArrDataInDataProperty(res.data.step_details, this.stepDetails)
                    this.toBeCheckedData = res.data.to_be_checked_data
                    this.setArrDataInDataProperty(res.data.request_logs, this.requestLogs)
                    this.setArrDataInDataProperty(res.data.count_summary, this.countSummary)
                    this.setArrDataInDataProperty(res.data.step_details, this.stepDetails)
                    this.candidates = res.data.candidates
                    this.businessName = this.baseInfo.business_name
                    this.relatedMails = res.data.related_mails
                    this.requestFile = res.data.request_file
                    this.labelData = res.data.label_data
                    this.changeableDeadline = this.deadline

                    this.setStyleByRequestStatus()
                    this.setTimelineWrapHeight()

                    this.$nextTick(function () {
                    // 前回のスクロール位置に移動
                        window.scrollTo(0 , this.lastScrollY);
                    })
                })
                .catch((err) => {
                    console.log(err)
                // TODO エラー時処理
                })
                .finally(() => {
                    this.loading = false
                });
        },
        update: function() {
            // this.loading = true
            // axios.post('/api/requests/update',{
            //     request_id: this.requestId,
            //     deadline: this.changeableDeadline
            // })
            // .then((res) => {
            //     console.log('更新')
            //     console.log(res.data)
            // })
            // .catch((err) => {
            //     // TODO エラー時処理
            // })
            // .finally(() => {
            //     // this.loading = false
            //     console.log('final')
            // });
        },
        exceptRequest: function() {
            eventHub.$emit('close-except-confirm-modal')
            this.loading = true
            axios.post('/api/requests/delete',{
                request_id: this.requestId,
                started_at: this.startedAt
            })
                .then((res) => {
                    if (res.data.result === 'success') {
                        eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.success_exception'), emitMessage: 'click-close'})
                    } else if (res.data.result === 'warning') {
                        eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.updated_by_others')})
                    } else {
                        eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.internal_error')})
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.internal_error')})
                })
                .finally(() => {
                    this.loading = false
                });
        },
        replicateRequest: function(replicationFlags) {
            eventHub.$emit('close-replicate-confirm-modal')
            this.loading = true
            axios.post('/api/requests/replicate',{
                request_id: this.requestId,
                related_mail_replication_flag: replicationFlags.related_mail_replication_flag,
                additional_info_replication_flag: replicationFlags.additional_info_replication_flag,
                prefix: Vue.i18n.translate('requests.detail.prefix_url'),
            })
                .then((res) => {
                    if (res.data.result === 'success') {
                        eventHub.$emit('open-replicate-notify-modal', {message: Vue.i18n.translate('requests.detail._modal.replicated'), url: res.data.url})
                    } else if (res.data.result === 'warning') {
                        eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.updated_by_others')})
                    } else {
                        eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.internal_error')})
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', {message: Vue.i18n.translate('common.message.internal_error')})
                })
                .finally(() => {
                    this.loading = false
                });

        },
        // タイムライン各枠の高さ調整
        setTimelineWrapHeight: function() {
            let self = this
            self.$nextTick(function () {
                let timelineWrapHeight = $('#timeline-wrap').outerHeight()
                let toBeCheckedWrapHeight = $('#to-be-checked-wrap').outerHeight()
                let toBeCheckedCardHeight = $('#to-be-checked .v-card__title').outerHeight()
                if (self.requestLogs.length > 4) {
                    $('#status-summary-wrap').css('min-height', (timelineWrapHeight / 2) + 'px')
                    $('#to-be-checked-wrap').css('max-height', (timelineWrapHeight / 2) + 'px')
                    $('.to-be-checked-inner').css('max-height', (timelineWrapHeight / 2 - toBeCheckedCardHeight - 10) + 'px')
                }
                $('#status-summary-wrap').css('height', (timelineWrapHeight - toBeCheckedWrapHeight) + 'px')
            });
            self.$nextTick(function () {
                let statusSummaryWrapHeight = $('#status-summary-wrap').outerHeight()
                let statusSummaryCardHeight = $('#status-summary .v-card__title').outerHeight()
                $('.status-summary-inner').css('height', (statusSummaryWrapHeight - statusSummaryCardHeight) + 'px')
            });
        },
        handleScroll() {
            this.scrollY = window.scrollY;
            sessionStorage.setItem(this.currentUrl + 'scroll-y', this.scrollY)
        },
        updateRelatedMails(data) {
            this.relatedMails = data
        }
    },
    created() {
        this.getRequestDetails()

        let self = this
        eventHub.$on('exceptRequest', function() {
            self.exceptRequest()
        })
        eventHub.$on('replicateRequest', function(replicateCheckboxForm) {
            self.replicateRequest(replicateCheckboxForm)
        })

        this.lastScrollY = sessionStorage.getItem(this.currentUrl + 'scroll-y')

        eventHub.$on('click-close', function(){
            // 依頼除外処理成功後
            if (window.history.length > 1) {
                window.history.back()
            } else {
                window.location.href = '/management/requests'
            }
        })
    },
    mounted: function () {
        window.addEventListener('scroll', this.handleScroll);
    }
}
</script>

<style scoped>
#timeline-wrap {
    max-height: 615px;
}
#status-summary-wrap {
    height: 100%;
}
#request-content-wrap {
    overflow-x: hidden;
    overflow-y: auto;
    max-height: 615px;
}
</style>
