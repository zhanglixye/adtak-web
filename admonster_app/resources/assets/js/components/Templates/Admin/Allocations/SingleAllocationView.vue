<template>
    <v-app id="allocations">
        <app-menu :drawer="drawer" :reference-mode="isReference"></app-menu>
        <app-header
            :title="request ? request['business_name'] : ''"
            :subtitle="$t('allocations.single_allocate')"
            :reference-mode="isReference"
        ></app-header>
        <v-content id="data-content" :style="contentStyle">
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
                            :displayInit="isRequestContentsDisplayInit"
                        ></request-contents>
                    </v-flex>
                    <v-flex grow :class="showRequestContent ? 'xs12 md6' : ''">
                        <allocation-list
                            :request="request"
                            :operators="operators"
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
                            >{{ $t('allocations.allocation_list.allocation') }}</v-btn>
                        </div>
                    </v-flex>
                    <!-- Main -->

                </v-layout>
                <page-footer back-button :reference-mode="isReference"></page-footer>
            </v-container>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <alert-dialog ref="alert"></alert-dialog>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer :reference-mode="isReference"></app-footer>
    </v-app>
</template>

<script>
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import AllocationList from '../../../Organisms/Admin/Allocations/AllocationList'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'


export default {
    props: {
        inputs: { type: Object },
    },
    components: {
        PageFooter,
        AllocationList,
        RequestContents,
        ProgressCircular,
        ConfirmDialog,
        AlertDialog,
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
        candidates: [],

        // ラベル
        labelData: {},

        //loading
        loading: false
    }),
    methods: {
        getAllocations (param) {
            this.loading = true
            axios.post('/api/allocations',  param)
                .then((res) => {
                    this.startedAt = res.data['started_at']
                    this.operators = res.data['operators']
                    this.candidates = res.data['candidates']
                    this.request = res.data['request']
                    this.labelData = res.data['label_data']

                    // 画面表示制御（承認済、無効の場合は参照画面）
                    if (this.request['process'] === _const.PROCESS_TYPE.DELIVERY
                    || this.request['request_work_is_active'] === _const.FLG.INACTIVE
                    || this.request['request_is_deleted'] === _const.FLG.ACTIVE
                    || this.request['request_status'] === _const.REQUEST_STATUS.EXCEPT
                    || this.isReference) {
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
            if (await this.$refs.confirm.show(Vue.i18n.translate('allocations.dialog.allocate.text'))) {
                this.loading = true

                let params = new FormData()
                params.append('request_id', this.request['id'])
                params.append('request_work_id', this.request['request_work_id'])
                params.append('before_user_ids', this.allocated.map(user => user['user_id']))
                params.append('after_user_ids', this.selected.map(user => user['user_id']))
                params.append('started_at', this.startedAt['date'])

                axios.post('/api/allocations/store',  params)
                    .then((res) => {
                        if (res.data.result === 'success') {
                            // ダイアログ表示
                            this.$refs.alert.show(
                                Vue.i18n.translate('common.message.saved'),
                                function () {
                                    if (window.history.length > 1) {
                                        window.history.back()
                                    } else {
                                        window.location.href = '/allocations'
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

            this.$refs.allocationList.reset()
        },
    },
    created() {
        this.getAllocations(this.inputs)
    },
    computed: {
        disabledButton () {
            return (this.selected && this.selected.length === 0) || JSON.stringify(this.selected) === JSON.stringify(this.allocated) || this.isReference
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
        },
        contentStyle () {
            return this.isReference ? {padding: 0} : {}
        },
        isRequestContentsDisplayInit () {
            return !this.isReference
        },
        isReference () {
            return this.inputs.reference_mode == 'true' ? true : false
        },
    },
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
