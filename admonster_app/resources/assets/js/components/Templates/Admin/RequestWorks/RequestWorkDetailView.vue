<template>
<v-app id="request-work-detail" :class="mainBgClass">
    <app-menu :drawer="drawer"></app-menu>
    <app-header
        :title="businessName"
        :subtitle="$t('request_works.detail.title')"
    ></app-header>
    <v-content>
        <v-container fluid grid-list-md>
            <v-layout row wrap mb-3>
                <v-flex xs12>
                    <page-header back-button :alerts="alerts" :dark="completed">
                        <template slot="rightHeader">
                            <span>{{ $t('requests.detail.deadline') }}:
                                <span v-if="deadline">{{ deadline | formatDateYmdHm }}</span>
                                <span v-else>{{ $t('requests.detail.unset') }}</span>
                            </span>
                            <v-btn
                                color="info"
                                :href="requestUrl"
                            ><v-icon class="mr-1">open_in_browser</v-icon>
                            <span>{{ $t('request_works.detail.link_to_request_page') }}</span>
                            </v-btn>
                        </template>
                    </page-header>
                </v-flex>
                <v-flex :class="(toBeCheckedData.length < 1 && requestLogs.length < 1) ? 'xs12' : 'xs8'">
                    <div id="request-content-wrap" class="elevation-1">
                        <request-contents
                            :request-work-id="requestWorkId"
                            :request="requestContent"
                            :selected-content="selectContent"
                            :label-data="labelData"
                            :candidates="candidates"
                            height="auto"
                            max-height="608px"
                            hide-close-icon
                            displayInit
                        ></request-contents>
                    </div>
                </v-flex>
                <v-flex xs4 id="timeline-wrap">
                    <v-layout row wrap v-if="toBeCheckedData.length > 0" id="to-be-checked-wrap">
                        <v-flex xs12>
                            <to-be-checked :to-be-checked-data="toBeCheckedData" :candidates="candidates" v-if="toBeCheckedData"></to-be-checked>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap v-show="requestLogs" id="status-summary-wrap">
                        <v-flex xs12>
                            <status-summary :request-logs="requestLogs" :candidates="candidates" v-if="requestLogs.length > 0"></status-summary>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>

            <v-layout row wrap mb-2 v-if="relatedMails.length < 1">
                <v-flex xs12>
                    <create-mail-alias
                        v-if="requestWork"
                        :request-id="requestWork.request_id"
                        :request-work-id="requestWork.id"
                    ></create-mail-alias>
                </v-flex>
            </v-layout>
            <v-layout row wrap mb-4 v-else>
                <v-flex xs12>
                    <related-mails
                        v-if="requestWork"
                        :request-id="requestWork.request_id"
                        :request-work-id="requestWork.id"
                        :request-related-mails="relatedMails"
                        v-on:update-related-mails="updateRelatedMails"
                    ></related-mails>
                </v-flex>
            </v-layout>

            <v-layout row wrap mb-4>
                <v-flex xs12>
                    <additional-info
                        v-if="requestWork"
                        :request-id="requestWork.request_id"
                        :request-work-id="requestWork.id"
                        :candidates="candidates"
                    >
                    </additional-info>
                </v-flex>
            </v-layout>

            <v-layout row wrap>
                <v-flex xs12>
                    <request-work-progress :request-work="requestWork" :candidates="candidates" :business-name="businessName" v-if="requestWork"></request-work-progress>
                </v-flex>
            </v-layout>

            <page-footer back-button></page-footer>

        </v-container>
        <progress-circular v-if="loading"></progress-circular>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import PageHeader from './../../../Organisms/Layouts/PageHeader'
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ToBeChecked from './../../../Organisms/Admin/Requests/Detail/ToBeChecked'
import StatusSummary from './../../../Organisms/Admin/Requests/Detail/StatusSummary'
import AdditionalInfo from './../../../Organisms/Admin/Requests/Detail/AdditionalInfo'
import CreateMailAlias from './../../../Organisms/Admin/Requests/Detail/CreateMailAlias'
import RelatedMails from './../../../Organisms/Admin/Requests/Detail/RelatedMails'
import RequestWorkProgress from './../../../Organisms/Admin/RequestWorks/Detail/RequestWorkProgress'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components:{
        PageHeader,
        PageFooter,
        RequestContents,
        ToBeChecked,
        StatusSummary,
        AdditionalInfo,
        CreateMailAlias,
        RelatedMails,
        RequestWorkProgress,
        ProgressCircular
    },
    props: {
        eventHub: eventHub,
        requestWorkId: { type: Number, required: true },
    },
    data: ()=> ({
        drawer: false,
        loading: false,
        editorDialog: false,

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
        requestMail: null,
        toBeCheckedData: [],
        requestLogs: [],
        statusSummary: null,
        stepDetails: [],
        candidates: [],
        relatedMails: [],
        requestWork: '',
        requestFile: null,

        mainBgClass: '',

        // ラベルデータ
        labelData: {},
    }),
    computed: {
        deadline () {
            if (this.requestWork.deadline) {
                return this.requestWork.deadline
            } else if (!this.requestWork.deadline && this.requestWork.system_deadline) {
                return this.requestWork.system_deadline
            } else {
                return null
            }
        },
        completed (){
            return this.requestWork.status === _const.REQUEST_STATUS.FINISH ? true : false
        },
        requestUrl () {
            return '/management/requests/' + this.requestWork.request_id
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
        }
    },
    methods: {
        completedRequest () {
            if (this.requestWork.status === _const.REQUEST_STATUS.FINISH) {
                this.alerts['is_completed_request'].model = true
            }
        },
        getRequestWorkDetails () {
            this.loading = true
            axios.post('/api/request_works/show',{
                request_work_id: this.requestWorkId
            })
                .then((res) => {
                    this.requestWork = res.data.request_work
                    this.requestMail = res.data.request_mail
                    this.toBeCheckedData = res.data.to_be_checked_data
                    this.requestLogs = res.data.request_logs
                    this.relatedMails = res.data.related_mails
                    this.candidates = res.data.candidates
                    this.businessName = res.data.request_work.request.business.name
                    this.requestFile = res.data.request_file
                    this.labelData = res.data.label_data
                    this.setStyleByRequestStatus()
                    this.setTimelineWrapHeight()

                    this.$nextTick(function () {
                        // 前回のスクロール位置に移動
                        window.scrollTo(0 , this.lastScrollY);
                    })
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        setStyleByRequestStatus () {
            if (this.requestWork.request.status === _const.REQUEST_STATUS.FINISH && this.requestWork.request.is_deleted === _const.FLG.INACTIVE) {
                this.mainBgClass = 'bg-completed'
                this.alerts['is_completed_request'].model = true
            } else if (this.requestWork.request.status === _const.REQUEST_STATUS.EXCEPT && this.requestWork.request.is_deleted === _const.FLG.INACTIVE) {
                this.mainBgClass = 'bg-deleted'
                this.alerts['is_deleted_request'].model = true
            } else {
                this.mainBgClass = 'lime lighten-4'
            }
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
        updateRelatedMails(data) {
            this.relatedMails = data
        }
    },
    created() {
        this.getRequestWorkDetails()
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
