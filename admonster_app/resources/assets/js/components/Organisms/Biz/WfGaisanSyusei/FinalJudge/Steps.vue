<template>
    <!-- 実作業 -->
    <div id="steps-block">
        <v-layout mt-0>
            <div class="mt-0 mb-5 mr-2">
                <v-flex xs12>
                    <v-chip
                        class="ml-0 mr-2"
                        color="grey darken-3"
                        style="color:#e0e0e0;"
                        label
                        medium
                    >STEP1
                    </v-chip>
                </v-flex>
            </div>
            <v-layout mt-0 row wrap>
                <v-flex xs12>
                    <v-layout row wrap mt-0 mb-5>
                        <v-flex xs12>
                            <!-- STEP1 -->
                            <v-flex xs12>
                                <v-layout align-start mb-2>
                                    <div style="width:100%;">
                                        <span class="step-description">{{ $t('biz.wf_gaisan_syusei.final_judge.step1.step_description') }}</span><br>
                                    </div>
                                </v-layout>
                                <v-layout row align-center justify-end mb-1>
                                    <v-icon>arrow_right</v-icon>
                                    <a href="javascript:void(0)" class="guide" @click="openGuide('/images/biz/wf_gaisan_syusei/final_judge_guide.jpg')">
                                        <span>{{ $t('biz.wf_gaisan_syusei.final_judge.link_guide') }}</span>
                                    </a>
                                </v-layout>
                                <div class="mt-4">
                                    <v-layout row wrap align-center>
                                        <v-flex xs12>
                                            <v-layout row wrap align-center>
                                                <v-data-table
                                                    :headers="headers"
                                                    :items="listForJudge"
                                                    hide-actions
                                                    class=""
                                                    id="all-steps-works"
                                                >
                                                    <template slot="headers" slot-scope="props">
                                                        <tr>
                                                            <th
                                                                v-for="header in props.headers"
                                                                :key="header.text"
                                                                :class="['text-xs-center']"
                                                            >
                                                            <v-tooltip top>
                                                                <span slot="activator">
                                                                    <v-icon v-if="header.class == 'exist-unclear'" small color="deep-orange accent-3">info</v-icon>
                                                                </span>
                                                                <span>{{ $t('biz.wf_gaisan_syusei.final_judge.step1.exist_unclear_tooltip_text') }}</span>
                                                            </v-tooltip>
                                                            <span v-html="header.text" @click="openTaskWindow(header.value)"></span>
                                                          </th>
                                                        </tr>
                                                      </template>
                                                    <template slot="items" slot-scope="props">
                                                        <td v-html="props.item.title" class="text-xs-center"></td>
                                                        <td class="text-xs-right">{{ props.item.agency_mail_data }}</td>
                                                        <td class="text-xs-right">{{ props.item.sas_data }}</td>
                                                        <td class="text-xs-right">{{ props.item.media_mail_data }}</td>
                                                        <td class="text-xs-right">{{ props.item.ppt_data }}</td>
                                                        <td class="text-xs-right">{{ props.item.master_data }}</td>
                                                    </template>
                                                </v-data-table>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </div>
                            </v-flex>
                        </v-flex>
                        <!-- / STEP1-->
                    </v-layout>
                </v-flex>

                <!-- 判定ボタン -->
                <v-flex xs12>
                    <v-layout row wrap>
                        <div class="layout row align-center mb-1"><i aria-hidden="true" class="v-icon material-icons theme--light">arrow_drop_down</i><span>{{ $t('biz.wf_gaisan_syusei.final_judge.step1.step_description_sub') }}</span></div>
                        <v-flex xs12 class="judge-btns-wrap">
                            <span v-for="(judgementBtn, index) in judgementBtns" :key="index">
                                <span v-if="!isDoneTask && !isDisabledTask">
                                    <v-btn v-html="judgementBtn.text" color="amber darken-3" :dark="valid" :disabled="!valid" @click="openModal(judgementBtn.judgeType)">
                                    </v-btn>
                                </span>
                                <span v-else-if="isDoneTask">
                                    <span v-if="(judgementBtn.judgeType == form.task_result.item01)">
                                        <v-badge
                                            color="amber darken-3"
                                            right
                                            overlap
                                        >
                                            <v-icon
                                              slot="badge"
                                              dark
                                              small
                                            >done</v-icon>
                                            <v-btn v-html="judgementBtn.text" color="amber darken-3" disabled>
                                            </v-btn>
                                        </v-badge>
                                        <span>{{ $t('biz.wf_gaisan_syusei.common.status_done') }}</span>
                                    </span>
                                </span>
                            </span>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <!-- / 判定ボタン -->
            </v-layout>
        </v-layout>
    </div>
    <!-- / 実作業 -->
</template>

<script>
import formatMixin from '../../../../../mixins/Biz/formatMixin'
import motionMixin from '../../../../../mixins/motionMixin'

export default {
    mixins: [formatMixin, motionMixin],
    props: {
        loading: Boolean,
        alert: Object,
        resData: Object
    },
    data: () => ({
        valid: true,
        showUnclearBlock: false,
        isDoneTask: false,
        isInactiveTask: false,
        isDisabledTask: false,
        form: {
            task_id: null,
            step_id: null,
            started_at: null,
            request_work_id: null,
            request_work_code: null,
            request_id: null,
            request_mail_id: null,
            judge_type: null,
            master_data: '',
            sas_data: '',
            evidence_attachment_file_id: null,
            task_result: {
                item01: ''
            },
            process_info: {
                results: {
                    type: _const.TASK_RESULT_TYPE.DONE,
                    next_step: []
                }
            }
        }
    }),
    created: function () {
        let self = this;
        eventHub.$on('submit', function() {
            self.submit()
        })
        eventHub.$on('set-return-steps-form', function(data) {
            self.form.process_info.results.next_step = data.next_step
        })

        const resData = JSON.parse(JSON.stringify(this.resData))
        this.setData(resData)
    },
    computed: {
        listForJudge () {
            const deliveryDataSet = this.resData.delivery_data_set
            return [
                {
                    title: '申込金額G<br>実施料金(SAS)',
                    agency_mail_data: this.formatYen(deliveryDataSet.agency_mail_data.item07),
                    sas_data: this.formatYen(deliveryDataSet.sas_data.item11),
                    media_mail_data: this.formatYen(deliveryDataSet.media_mail_data.item04),
                    ppt_data: '-',
                    master_data: this.resData.master_data.order_amount_gross
                },
                {
                    title: '確定金額G<br>実施料金(変更後G)(ﾏｽﾀ)',
                    agency_mail_data: this.formatYen(deliveryDataSet.agency_mail_data.item08),
                    sas_data: '-',
                    media_mail_data: this.formatYen(deliveryDataSet.media_mail_data.item05),
                    ppt_data: '-',
                    master_data: this.resData.master_data.commit_amount_gross,
                },
                {
                    title: '確定金額N<br>原価(変更後ネット)(ﾏｽﾀ)',
                    agency_mail_data: '-',
                    sas_data: '-',
                    media_mail_data: this.formatYen(deliveryDataSet.media_mail_data.item06),
                    ppt_data: this.formatYen(deliveryDataSet.ppt_data.item03),
                    master_data: this.resData.master_data.commit_amount_net,
                },
                {
                    title: '開始日',
                    agency_mail_data: deliveryDataSet.agency_mail_data.item05,
                    sas_data: deliveryDataSet.sas_data.item07,
                    media_mail_data: '-',
                    ppt_data: deliveryDataSet.ppt_data.item01,
                    master_data: this.resData.master_data.from,
                },
                {
                    title: '終了日',
                    agency_mail_data: deliveryDataSet.agency_mail_data.item06,
                    sas_data: deliveryDataSet.sas_data.item08,
                    media_mail_data: '-',
                    ppt_data: deliveryDataSet.ppt_data.item02,
                    master_data: this.resData.master_data.to,
                }
            ]
        },
        headers () {
            const deliveryDataSet = this.resData.delivery_data_set
            return [
                { text: '', value: '', align: 'center', sortable: false },
                { text: '<a>代理店メール</a>', value: deliveryDataSet.agency_mail_data.task_url, align: 'center', class: deliveryDataSet.agency_mail_data.unclear_points_cnt > 0 ? 'exist-unclear' : '', sortable: false },
                { text: '<a>SAS</a>', value: deliveryDataSet.sas_data.task_url, class: deliveryDataSet.sas_data.unclear_points_cnt > 0 ? 'exist-unclear' : '', align: 'center', sortable: false },
                { text: '<a>媒体メール</a>', value: deliveryDataSet.media_mail_data.task_url, class: deliveryDataSet.media_mail_data.unclear_points_cnt > 0 ? 'exist-unclear' : '', align: 'center', sortable: false },
                { text: '<a>パワポ</a>', value: deliveryDataSet.ppt_data.task_url, class: deliveryDataSet.ppt_data.unclear_points_cnt > 0 ? 'exist-unclear' : '', align: 'center', sortable: false },
                { text: 'マスタ', value: '', align: 'center', sortable: false },
            ]
        },
        judgementBtns () {
            return [
                { text: 'WF申請', judgeType: this.resData.judge_types.wf_apply, align: 'center' },
                { text: '<span>WF申請</span><br><span class="btn-sub-text">(案件概要変更あり)<span>', judgeType: this.resData.judge_types.wf_apply_w_change, align: 'center' },
                { text: '満額請求', judgeType: this.resData.judge_types.full_charge, align: 'center' },
                { text: '満額消化', judgeType: this.resData.judge_types.full_digestion, align: 'center' },
                { text: 'SAS修正済', judgeType: this.resData.judge_types.sas_corrected, align: 'center' },
                { text: '保留', judgeType: this.resData.judge_types.hold, align: 'center' },
                { text: '作業を戻す', judgeType: this.resData.judge_types.return_steps, align: 'center' }
            ]
        }
    },
    methods: {
        setData: function(data) {
            this.setParamData(data)
            if (data.task_result_content) {
                this.setFixedData(data.task_result_content)
            }
            if (data.is_done_task) {
                this.isDoneTask = true
            }
            if (data.is_inactive_task) {
                this.isInactiveTask = true
            }
            if (data.is_done_task || data.is_inactive_task) {
                this.isDisabledTask = true
            }
        },
        // 保存
        submit: function() {
            this.$emit('input', true)
            axios.post('/api/biz/wf_gaisan_syusei/final_judge/store',  this.form)
                .then((res) => {
                    if (res.data.result == 'success') {
                        // 確定データで画面を更新
                        this.$emit('getData', 'fixed')
                        this.$emit('alert-type', 'success')
                    } else if (res.data.result == 'inactivated') {
                        this.$emit('alert-type', 'inactivated')
                    } else {
                        this.$emit('alert-type', 'error')
                    }
                    this.$emit('input', false)
                })
                .catch((err) => {
                    console.log(err);
                    this.$emit('input', false)
                    this.$emit('alert-type', 'error')
                });
            this.closeModal()
            this.scrollToTop()
        },
        openModal: function(judgeType) {
            this.form.judge_type = judgeType
            this.form.task_result.item01 = judgeType

            let message = ''

            if (judgeType == this.resData.judge_types.return_steps) {
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.RETURN
                eventHub.$emit('open-return-steps-modal',{
                    'message': message,
                    'judgeType': judgeType
                })
            } else {
                if (judgeType == this.resData.judge_types.hold) {
                    this.form.process_info.results.type = _const.TASK_RESULT_TYPE.HOLD
                }
                const textPath = 'biz.wf_gaisan_syusei.final_judge._modal.messages.judge_type.' + judgeType
                message = this.$t(textPath) + this.$t('biz.wf_gaisan_syusei.final_judge._modal.fix.p2')
                eventHub.$emit('open-modal',{
                    'message': message,
                    'judgeType': judgeType
                })
            }
        },
        closeModal: function() {
            eventHub.$emit('close-modal')
        },
        setParamData: function(data) {
            this.form.task_id = data.task_id
            this.form.step_id = data.request_work.step_id
            this.form.started_at = data.started_at.date
            this.form.request_work_id = data.request_work.id
            this.form.request_work_code = data.request_work.code
            this.form.request_id = data.request_work.request_id
            this.form.request_mail_id = data.request_mail_id
            this.form.master_data = data.master_data
            this.form.sas_data = data.delivery_data_set.sas_data
            this.form.evidence_attachment_file_id = data.delivery_data_set.media_mail_data.item02
        },
        // DB登録後の実作業データをセット
        setFixedData: function(data) {
            this.form.task_result = {
                item01: data.item01,
            }
        },
        openGuide: function(path) {
            window.open(path,  null, 'width=1000, height=700, resizable=1')
        },
        openTaskWindow: function(taskUrl) {
            if (taskUrl !== '') {
                window.open(taskUrl,  null, 'width=1200, height=800, resizable=1')
            }
        }
    }
}
</script>
