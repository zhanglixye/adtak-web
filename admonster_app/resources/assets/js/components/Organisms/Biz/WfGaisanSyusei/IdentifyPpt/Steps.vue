<template>
    <!-- 実作業 -->
    <div id="steps-block">
        <v-layout mt-0 ml-3 mb-5>
            <v-flex xs12>

                <!-- STEP1 -->
                <v-flex xs12 mb-5>
                    <v-layout align-center mb-3>
                        <v-chip
                            class="ml-0 mr-2"
                            label
                            color="grey darken-3"
                            style="color:#e0e0e0;"
                            medium
                        >STEP1
                        </v-chip>
                        <div>
                            <span class="step-description">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step1.step_description') }}</span>
                        </div>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step1.target_mail_condition.element.01') }}</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <span>【確定金額＆レポート】～</span>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step1.target_mail_condition.element.02') }}</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <span>&nbsp;&nbsp;{{ $t('biz.wf_gaisan_syusei.identify_ppt.step1.target_mail_condition.content.02') }}</span>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <!-- ./ STEP1 -->

                <!-- STEP2 -->
                <v-flex xs12 mb-5>
                    <v-layout align-center mb-2>
                        <v-chip
                            class="ml-0 mr-2"
                            color="grey darken-3"
                            style="color:#e0e0e0;"
                            label
                            medium
                        >STEP2
                        </v-chip>
                        <div style="width:100%;">
                            <span class="step-description">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step2.step_description') }}</span><br>
                        </div>
                    </v-layout>
                    <v-layout row align-baseline ma-0 class="caption step-sub-description">
                        <div><v-icon class="mr-1" color="primary">info</v-icon></div><div class="caption">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step2.step_description_sub_1') }}</div>
                    </v-layout>
                    <v-layout row align-baseline ma-0 class="caption step-sub-description">
                        <div><v-icon class="mr-1" color="primary">info</v-icon></div><div class="caption">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step2.step_description_sub_2') }}</div>
                    </v-layout>
                    <div class="mt-4">
                        <v-layout row align-center justify-center>
                            <v-flex xs8>
                                <v-select
                                    :items="mailAttachments"
                                    v-model="form.task_result.item02"
                                    :menu-props="{ maxHeight: '400' }"
                                    :hint="$t('biz.wf_gaisan_syusei.identify_ppt.item02.hint')"
                                    persistent-hint
                                    item-text="name"
                                    item-value="id"
                                    :disabled="isDisabledTask"
                                    no-data-text=""
                                ></v-select>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center justify-center>
                            <v-flex xs8>
                                <v-checkbox
                                    v-model="form.task_result.item03"
                                    :label="$t('biz.wf_gaisan_syusei.identify_ppt.item03.label')"
                                    true-value="1"
                                    false-value="0"
                                    hide-details
                                    class="justify-end"
                                    :disabled="isDisabledTask"
                                    @change="form.task_result.item03 ? form.task_result.item02 = '' : form.task_result.item02 = form.task_result.item02"
                                ></v-checkbox>
                            </v-flex>
                        </v-layout>
                    </div>
                </v-flex>
                <!-- ./ STEP2 -->

                <!-- STEP3 -->
                <v-flex xs12>
                    <v-layout align-center>
                        <v-chip
                            class="ml-0 mr-2"
                            color="grey darken-3"
                            style="color:#e0e0e0;"
                            label
                            medium
                        >STEP3
                        </v-chip>
                        <div style="width:100%;">
                            <span class="step-description">{{ $t('biz.wf_gaisan_syusei.identify_ppt.step3.step_description') }}</span><br>
                        </div>
                    </v-layout>
                    <div class="mt-3">
                        <v-layout row align-center>
                            <v-flex xs5>
                                <v-subheader style="justify-content:center;">申込金額（グロス）</v-subheader>
                            </v-flex>
                            <v-flex xs5>
                                <v-text-field v-model="form.task_result.item04" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs5>
                                <v-subheader style="justify-content:center;">確定金額（グロス）</v-subheader>
                            </v-flex>
                            <v-flex xs5>
                                <v-text-field v-model="form.task_result.item05" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs5>
                                <v-subheader style="justify-content:center;">確定金額（ネット）</v-subheader>
                            </v-flex>
                            <v-flex xs5>
                                <v-text-field v-model="form.task_result.item06" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                            </v-flex>
                        </v-layout>
                    </div>
                </v-flex>
                <!-- / STEP3 -->

            </v-flex>

        </v-layout>

        <!-- 処理ボタン -->
        <v-layout wrap ml-3>
            <div class="title"><v-icon color="red">warning</v-icon>{{ $t('biz.wf_gaisan_syusei.identify_ppt.announce_text') }}</div>
            <v-expand-transition>
                <v-flex xs12 mt-3 v-if="showUnclearBlock">
                    <v-card color="">
                        <v-card-title pb-0 style="padding-bottom:0px;">
                            {{ $t('biz.wf_gaisan_syusei.identify_ppt.unclear_box.title') }}
                            <v-spacer></v-spacer>
                            <v-btn icon @click="showUnclearBlock = !showUnclearBlock">
                              <v-icon>{{ showUnclearBlock ? 'close' : '' }}</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text style="padding-top:0px;">
                            <v-layout row>
                                <v-flex xs12>
                                    <v-checkbox
                                        v-model="form.task_result_unclear.item07"
                                        v-bind:label="$t('biz.wf_gaisan_syusei.identify_ppt.item07.label')"
                                        true-value="1"
                                        false-value="0"
                                        hide-details
                                        :disabled="isDisabledTask"
                                        @change="form.task_result_unclear.item07 ? form.task_result_unclear.item08 = '' : form.task_result_unclear.item08 = form.task_result_unclear.item08"
                                    ></v-checkbox>
                                    <v-expand-transition>
                                        <v-text-field
                                        v-if="(form.task_result_unclear.item07 == true)"
                                        v-model="form.task_result_unclear.item08"
                                        v-bind:placeholder="$t('biz.wf_gaisan_syusei.identify_ppt.item08.placeholder')"
                                        :disabled="(form.task_result_unclear.item07 == false || isDisabledTask)"
                                      ></v-text-field>
                                    </v-expand-transition>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                      </v-card>
                </v-flex>
            </v-expand-transition>
            <v-flex xs12 class="text-xs-right">
                <a v-if="!showUnclearBlock" @click="showUnclearBlock = !showUnclearBlock">{{ $t('biz.wf_gaisan_syusei.identify_ppt.unclearBox_trigger') }}</a>
                <span v-if="!isDoneTask && !isInactiveTask">
                    <v-btn color="amber darken-3" :dark="valid" :disabled="!valid" @click="openModal">
                        {{ $t('biz.wf_gaisan_syusei.common.btn_submit') }}
                    </v-btn>
                </span>
                <span v-else-if="isDoneTask" class="ml-3">
                    <v-icon mr-1 style="font-size:15px;">done</v-icon>
                    <span>{{ $t('biz.wf_gaisan_syusei.common.status_done') }}</span>
                </span>

            </v-flex>
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
        selectedMail: '',
        form: {
            task_id: null,
            step_id: null,
            started_at: null,
            request_work_id: null,
            request_id: null,
            request_mail_id: null,
            target_mails: [],
            task_result: {
                item01: '',
                item02: '',
                item03: '',
                item04: '',
                item05: '',
                item06: '',
            },
            task_result_unclear: {
                item07: 0,
                item08: ''
            },
            process_info: {
                results: {
                    type: _const.TASK_RESULT_TYPE.DONE
                }
            }
        }
    }),
    created: function () {
        let self = this;
        eventHub.$on('submit', function() {
            self.submit()
        })

        const resData = JSON.parse(JSON.stringify(this.resData))
        this.setData(resData)

        eventHub.$on('setSelectedMail', this.setSelectedMail)
    },
    computed: {
        mailAttachments () {
            let array = [];
            if (this.selectedMail && this.selectedMail.request_mail_attachments.length > 0) {
                this.selectedMail.request_mail_attachments.forEach(function (item) {
                    array.push({
                        id: item.id,
                        name: item.name
                    });
                });
            }
            return array
        }
    },
    methods: {
        setData: function(data) {
            this.setHiddenFormData(data)
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
        setSelectedMail: function(selectedMail) {
            if (selectedMail.id !== this.form.task_result.item01) {
                this.selectedMail = selectedMail
                this.form.task_result.item01 = this.selectedMail.id
                this.form.task_result.item02 = ''
            }
        },
        // 保存
        submit: function() {
            this.$emit('input', true)
            axios.post('/api/biz/wf_gaisan_syusei/identify_ppt/store',  this.form)
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
        openModal: function() {
            let message = ''
            if (this.unclearPointExist()) {
                // 「不明あり」の場合
                message = this.$t('biz.wf_gaisan_syusei.identify_ppt._modal.unclear.p1') + this.$t('biz.wf_gaisan_syusei.identify_ppt._modal.unclear.p2')
            } else {
                message = this.$t('biz.wf_gaisan_syusei.identify_ppt._modal.fix.p1') + this.$t('biz.wf_gaisan_syusei.identify_ppt._modal.fix.p2')
            }
            eventHub.$emit('open-modal',{
                'message': message
            })
        },
        closeModal: function() {
            eventHub.$emit('close-modal')
        },
        // 不明があるかを確認
        unclearPointExist: function() {
            const unclearForms = [
                this.form.task_result_unclear.item07,
            ]
            if (unclearForms.indexOf('1') == -1){
                // 不明なし
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.DONE
                return false
            } else {
                // 不明あり
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.CONTACT
                return true
            }
        },
        isSubmitBtnAbleTo: function() {
            let result = true

            if (!this.unclearPointExist()) {
                return result
            }

            const unclearFormPairs = [
                {[this.form.task_result_unclear.item07] : this.form.task_result_unclear.item08}
            ]
            const targetPairs = unclearFormPairs.filter(function(e) {
                return Object.prototype.hasOwnProperty.call(e, '1')
            })

            targetPairs.forEach(function(val) {
                if (!val['1']) {
                    result = false
                    return result
                }
            })

            return result
        },
        setHiddenFormData: function(data) {
            this.form.task_id = data.task_id
            this.form.step_id = data.request_work.step_id
            this.form.started_at = data.started_at.date
            this.form.request_work_id = data.request_work.id
            this.form.request_id = data.request_work.request_id
            this.form.request_mail_id = data.request_mail_id

            this.target_mails = data.target_mails
        },
        // DB登録後の実作業データをセット
        setFixedData: function(data) {
            this.form.task_result = {
                item01: data.item01,
                item02: data.item02,
                item03: data.item03,
                item04: data.item04,
                item05: data.item05,
                item06: data.item06
            }
            this.form.task_result_unclear = {
                item07: data.item07,
                item08: data.item08
            }
            this.selectedMail = this.resData.target_mails.find(item => (item.id === data.item01))

            if (this.unclearPointExist()) {
                this.showUnclearBlock = true
            }
        }
    },
    watch: {
        // formオブジェクト内のすべての変更を監視
        form: {
            handler () {
                if (this.isSubmitBtnAbleTo()) {
                    this.valid = true
                } else {
                    this.valid = false
                }
            },
            deep: true
        },
        'form.task_result.item04': function (val) {
            if (val) {
                this.form.task_result.item04 = this.formatYen(val)
            }
        },
        'form.task_result.item05': function (val) {
            if (val) {
                this.form.task_result.item05 = this.formatYen(val)
            }
        },
        'form.task_result.item06': function (val) {
            if (val) {
                this.form.task_result.item06 = this.formatYen(val)
            }
        },
    },
}
</script>
