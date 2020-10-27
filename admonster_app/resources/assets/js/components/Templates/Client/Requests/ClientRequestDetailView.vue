<template>
<v-app id="inspire" :class="mainBgClass">
    <!-- <app-menu :drawer="drawer"></app-menu> -->
    <app-header
        :title="baseInfo.business_name"
        :subtitle="headerSubTitle"
        back_uri="/management/requests"
    ></app-header>
    <v-content>
        <v-container fluid grid-list-md>
            <v-layout row wrap mb-3>
                <v-flex xs12>
                    <page-header :alerts="alerts"></page-header>
                </v-flex>
                <v-flex xs12>
                    <client-step-progress :step-details="stepDetails" :candidates="candidates" :business-name="businessName" v-if="stepDetails.length > 0" :completed-percentage="completedPercentage"></client-step-progress>
                </v-flex>
            </v-layout>

            <v-layout row wrap mb-3>
                <v-flex xs12>
                    <v-layout align-center>
                        <v-spacer></v-spacer>
                        <span class="mr-2">{{ $t('requests.detail.deadline') }}:
                            <span v-if="deadline">{{ deadline | formatDateYmdHm(false, true) }}</span>
                            <span v-else>{{ $t('requests.detail.unset') }}</span>
                        </span>
                    </v-layout>
                </v-flex>
                <v-flex class="xs8">
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
                <v-flex xs4 id="appendices-wrap">
                    <v-layout row wrap>
                        <v-flex xs12>
                            <appendices :request-id="requestId" :candidates="candidates" :exist-appendices.sync="existAppendices"></appendices>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-container>

        <progress-circular v-if="loading"></progress-circular>
        <notify-modal></notify-modal>
    </v-content>
    <to-top></to-top>
    <app-footer back_uri="/management/requests"></app-footer>
</v-app>
</template>

<script>
import PageHeader from './../../../Organisms/Layouts/PageHeader'
import RequestContents from '../../../Organisms/Common/RequestContents'
import Appendices from './../../../Organisms/Client/Requests/Detail/Appendices/Index'
import ClientStepProgress from './../../../Organisms/Client/Requests/Detail/StepProgress'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import ToTop from '../../../Atoms/Buttons/ToTop'

export default {
    components:{
        PageHeader,
        RequestContents,
        Appendices,
        ClientStepProgress,
        ProgressCircular,
        NotifyModal,
        ToTop
    },
    props: {
        eventHub: eventHub,
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number, required: true },
    },
    data: ()=> ({
        // drawer: false,
        loading: false,
        dialog: false,
        editorDialog: false,
        existAppendices: false,

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
            is_excepted_request: {
                model: false,
                dismissible: false,
                color: '#fff',
                icon: 'info',
                message: Vue.i18n.translate('common.message.to_client.is_excepted_request'),
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
        stepDetails: [],
        candidates: [],
        relatedMails: [],
        requestFile: '',
        requestFileItemData: [],

        mainBgClass: '',

        // ラベルデータ
        labelData: {},

        completedPercentage: 0
    }),
    computed: {
        isGuest () {
            return document.getElementById('login-guest-user').value === 'true'
        },
        headerSubTitle () {
            if (this.isGuest) {
                return this.$t('client.requests.detail.title')
            } else {
                return this.$t('client.requests.detail.title') + '（' + this.$t('auth.for_client') + '）'
            }
        },
        deadline () {
            if (this.baseInfo.deadline) {
                return this.baseInfo.deadline
            } else if (!this.baseInfo.deadline && this.baseInfo.system_deadline) {
                return this.baseInfo.system_deadline
            } else {
                return null
            }
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
        }
    },
    created() {
        this.getRequestDetails()
        this.lastScrollY = sessionStorage.getItem(this.currentUrl + 'scroll-y')
    },
    mounted: function () {
        window.addEventListener('scroll', this.handleScroll);
    },
    methods: {
        openModal: function(type) {
            let message = ''
            let name = ''

            if (type === 'except') {
                message = this.$t('requests.detail._modal.p1')
                name = 'open-except-confirm-modal'
            } else {
                message = this.$t('requests.detail._modal.p2')
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
                this.alerts['is_excepted_request'].model = true
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
                    this.setArrDataInDataProperty(res.data.step_details, this.stepDetails)
                    this.candidates = res.data.candidates
                    this.businessName = this.baseInfo.business_name
                    this.relatedMails = res.data.related_mails
                    this.requestFile = res.data.request_file
                    this.requestFileItemData = res.data.request_file_item_data
                    this.labelData = res.data.label_data
                    this.completedPercentage = res.data.completed_percentage

                    this.setStyleByRequestStatus()

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
        handleScroll() {
            this.scrollY = window.scrollY;
            sessionStorage.setItem(this.currentUrl + 'scroll-y', this.scrollY)
        }
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
#appendices-wrap {
    max-height: 615px;
}
</style>
