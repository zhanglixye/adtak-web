<template>
    <v-app id="allocations">
        <app-menu :drawer="drawer"></app-menu>
        <app-header
            :title="request ? request['business_name'] : ''"
            :subtitle="$t('education_allocations.single_allocate')"
        ></app-header>
        <v-content id="data-content">
            <v-container fluid grid-list-md v-if="request">
                <v-layout row wrap>

                    <!-- Main -->
                    <v-flex shrink :class="showRequestContent ? 'xs12 md6' : ''">
                        <request-contents
                            :request-work-id="Number(inputs['request_work_id'])"
                            :request="request"
                            v-model="showRequestContent"
                            :class="requestContentsClass"
                            :candidates="candidates"
                            :label-data="labelData"
                            :selected-content="selectContent"
                            height="66vh + 49px"
                            displayInit
                        ></request-contents>
                    </v-flex>
                    <v-flex grow :class="showRequestContent ? 'xs12 md6' : ''">
                        <allocation-list
                            :request="request"
                            :operators.sync="operators"
                            :allocated="allocated"
                            :edit="edit"
                            :selected.sync="selected"
                            ref="allocationList"
                        ></allocation-list>
                    </v-flex>
                    <v-flex xs12 v-show="edit">
                        <div class="btn-center-block">
                            <v-btn
                                color="grey"
                                dark
                                @click="reset"
                            >{{ $t('common.button.reset') }}</v-btn>
                            <v-btn
                                slot="activator"
                                color="primary"
                                :disabled="disabledButton"
                                @click="allocate"
                            >{{ $t('education_allocations.allocation_list.allocation') }}</v-btn>
                        </div>
                    </v-flex>
                    <!-- Main -->

                </v-layout>

                <page-footer back-button></page-footer>
            </v-container>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <alert-dialog ref="alert"></alert-dialog>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import AllocationList from '../../../Organisms/Admin/EducationAllocations/AllocationList'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'

export default {
    components: {
        PageFooter,
        AllocationList,
        RequestContents,
        ProgressCircular,
        ConfirmDialog,
        AlertDialog,
    },
    props: {
        inputs: { type: Object },
    },
    data: () => ({
        drawer: false,
        dialog: false,
        showRequestContent: false,
        edit: true,
        selected: [],
        allocated: [], // 割振済ユーザを保持

        // DBから取得
        startedAt: null,
        request: null,
        operators: [],
        initOperators: [],
        candidates: [],

        // ラベル
        labelData: {},

        //loading
        loading: false
    }),
    computed: {
        disabledButton () {
            // 割振り済み担当者の教育作業表示に変更があれば、割振ボタン有効
            for (const operator of this.selected) {
                for (const init of this.initOperators) {
                    if (init['user_id'] === operator['user_id'] && init['is_display_educational'] !== operator['is_display_educational']) {
                        return false
                    }
                }
            }

            // 担当者が選択されていないまたは、割振り担当者に変更がない場合、割振ボタン無効
            return (this.selected && this.selected.length === 0) || JSON.stringify(this.selected) === JSON.stringify(this.allocated)
        },
        requestContentsClass() {
            let classText = []
            const requestMail = this.request.request_mail
            if (requestMail) {
                classText.push(requestMail.mail_attachments.length > 0 ? '' : 'no-mail-attachment')
            }
            return classText
        },
        selectContent () {
            // RequestContent内の条件分岐に合わせて記載
            if (this.request.request_mail) return 'mail'
            if (this.request.request_file) return 'file'
            return ''
        }
    },
    created() {
        // メインカラー変更[primary ⇔ accent]
        const primaryColor = this.$vuetify.theme.primary
        this.$vuetify.theme.primary = this.$vuetify.theme.accent
        this.$vuetify.theme.accent = primaryColor

        this.getAllocations(this.inputs)
    },
    methods: {
        getAllocations (param) {
            this.loading = true
            axios.post('/api/education/allocations',  param)
                .then((res) => {
                    // 表示非表示の制御（デフォルトは表示）
                    for (let operator of res.data.operators) {
                        if (operator.is_display_educational === null || Number(operator.is_display_educational) ===  _const.FLG.ACTIVE) {
                            operator.is_display_educational = true
                            this.initOperators.push(Object.assign({}, operator))
                        } else {
                            operator.is_display_educational = false
                            this.initOperators.push(Object.assign({}, operator))
                        }
                    }
                    this.startedAt = res.data['started_at']
                    this.operators = res.data['operators']
                    this.candidates = res.data['candidates']
                    this.request = res.data['request']
                    this.labelData = res.data['label_data']

                    // 画面表示制御（無効の場合は参照画面）
                    if (this.request['request_work_is_active'] === _const.FLG.INACTIVE
                    || this.request['request_is_deleted'] === _const.FLG.ACTIVE
                    || this.request['request_status'] === _const.REQUEST_STATUS.EXCEPT) {
                        this.edit = false
                    }

                    // 割振済ユーザを設定
                    this.selected = this.allocated = this.operators.filter(operator => operator.status != null)

                    // 必須データが取得できない場合はアラート表示
                    if ( !this.request || this.operators.length === 0 ) {
                    // this.alerts['internal_error'].model = true
                    }
                })
                .catch((err) => {
                    console.log(err)
                // this.alerts['internal_error'].model = true
                })
                .finally(() => {
                    this.loading = false
                });
        },
        async allocate () {
            if (await this.$refs.confirm.show(Vue.i18n.translate('education_allocations.dialog.allocate.text'))) {
                this.loading = true

                let beforeUserInfos = []
                const beforeUserIds = this.allocated.map(user => user['user_id'])
                for (const operator of this.initOperators) {
                    if (beforeUserIds.includes(operator['user_id'])) {
                        beforeUserInfos.push(operator)
                    }
                }

                let allocation = []
                for (const beforeUserInfo of beforeUserInfos) {
                    for (const afterUserInfo of this.selected) {
                        if (beforeUserInfo['user_id'] === afterUserInfo['user_id'] && beforeUserInfo['is_display_educational'] === afterUserInfo['is_display_educational']) {
                            allocation.push(beforeUserInfo['user_id'])
                        }
                    }
                }

                const sendAllocations = this.selected.filter(operator => !allocation.includes(operator['user_id']))

                let params = new FormData()
                params.append('request_id', this.request['id'])
                params.append('request_work_id', this.request['request_work_id'])
                params.append('after_user_ids', this.selected.map(user => user['user_id']))
                params.append('sendAllocations', JSON.stringify(sendAllocations))
                params.append('started_at', this.startedAt['date'])

                axios.post('/api/education/allocations/store',  params)
                    .then((res) => {
                        if (res.data.result === 'success') {
                            // ダイアログ表示
                            this.$refs.alert.show(
                                Vue.i18n.translate('common.message.saved'),
                                function () {
                                    if (window.history.length > 1) {
                                        window.history.back()
                                    } else {
                                        window.location.href = '/education/allocations'
                                    }
                                }
                            )
                        } else {
                        // ダイアログ表示
                            this.$refs.alert.show(
                                Vue.i18n.translate('common.message.internal_error'),
                                function () {}
                            )
                        }
                    })
                    .catch((err) => {
                        console.log(err)
                        // ダイアログ表示
                        this.$refs.alert.show(
                            Vue.i18n.translate('common.message.internal_error'),
                            function () {}
                        )
                    })
                    .finally(() => {
                        this.loading = false
                    })
            }
        },
        reset: function () {
            // 選択状態をリセット
            this.selected = this.allocated

            let initOperatorsCoppy = []
            for (const index in this.initOperators) {
                initOperatorsCoppy.push(Object.assign({}, this.initOperators[index]))
            }

            this.operators = initOperatorsCoppy


            this.$refs.allocationList.reset(this.operators)
        },
    }
}
</script>
<style>
#allocations a:hover {
    text-decoration: none;
}
#data-content {
    position: relative;
}
</style>
