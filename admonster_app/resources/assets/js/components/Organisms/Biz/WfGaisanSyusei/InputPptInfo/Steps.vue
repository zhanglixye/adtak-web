<template>
    <!-- 実作業 -->
    <div id="steps-block">
        <v-layout mt-0 ml-3 mb-5>
            <v-flex xs12>

                <!-- STEP1 -->
                <v-flex xs12>
                    <v-layout align-center mb-3>
                        <v-chip
                            class="ml-0 mr-2"
                            color="grey darken-3"
                            style="color:#e0e0e0;"
                            label
                            medium
                        >STEP1
                        </v-chip>
                        <div style="width:100%;">
                            <span class="step-description">{{ $t('biz.wf_gaisan_syusei.input_ppt_info.step1.step_description') }}</span><br>
                        </div>
                    </v-layout>
                    <div class="mt-4">
                        <v-layout row align-center>
                            <v-flex xs5>
                                <v-subheader style="justify-content:center;">開始日</v-subheader>
                            </v-flex>
                            <v-flex xs5>
                                <date-picker :isActive="isDisabledTask" :dateValue="form.task_result.item01" @dateValue="dateItem01"></date-picker>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs5>
                                <v-subheader style="justify-content:center;">終了日</v-subheader>
                            </v-flex>
                            <v-flex xs5>
                                <date-picker :isActive="isDisabledTask" :dateValue="form.task_result.item02" @dateValue="dateItem02"></date-picker>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs5>
                                <v-subheader style="justify-content:center;">金額</v-subheader>
                            </v-flex>
                            <v-flex xs5>
                                <v-text-field v-model="form.task_result.item03" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                            </v-flex>
                        </v-layout>
                    </div>
                </v-flex>
                <!-- / STEP1 -->

            </v-flex>

        </v-layout>

        <!-- 処理ボタン -->
        <v-layout wrap ml-3>
            <div class="title"><v-icon color="red">warning</v-icon>{{ $t('biz.wf_gaisan_syusei.input_ppt_info.announce_text') }}</div>
            <v-expand-transition>
                <v-flex xs12 mt-3 v-if="showUnclearBlock">
                    <v-card color="">
                        <v-card-title pb-0 style="padding-bottom:0px;">
                            {{ $t('biz.wf_gaisan_syusei.input_ppt_info.unclear_box.title') }}
                            <v-spacer></v-spacer>
                            <v-btn icon @click="showUnclearBlock = !showUnclearBlock">
                              <v-icon>{{ showUnclearBlock ? 'close' : '' }}</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-card-text style="padding-top:0px;">
                            <v-layout row>
                                <v-flex xs12>
                                    <v-checkbox
                                        v-model="form.task_result_unclear.item04"
                                        v-bind:label="$t('biz.wf_gaisan_syusei.input_ppt_info.item04.label')"
                                        true-value="1"
                                        false-value="0"
                                        hide-details
                                        :disabled="isDisabledTask"
                                        @change="form.task_result_unclear.item04 ? form.task_result_unclear.item05 = '' : form.task_result_unclear.item05 = form.task_result_unclear.item05"
                                    ></v-checkbox>
                                    <v-expand-transition>
                                        <v-text-field
                                        v-if="(form.task_result_unclear.item04 == true)"
                                        v-model="form.task_result_unclear.item05"
                                        v-bind:placeholder="$t('biz.wf_gaisan_syusei.input_ppt_info.item05.placeholder')"
                                        :disabled="(form.task_result_unclear.item04 == false || isDisabledTask)"
                                      ></v-text-field>
                                    </v-expand-transition>
                                </v-flex>
                            </v-layout>
                            <v-layout row>
                                <v-flex xs12>
                                    <v-checkbox
                                        v-model="form.task_result_unclear.item06"
                                        v-bind:label="$t('biz.wf_gaisan_syusei.input_ppt_info.item06.label')"
                                        true-value="1"
                                        false-value="0"
                                        hide-details
                                        :disabled="isDisabledTask"
                                        @change="form.task_result_unclear.item06 ? form.task_result_unclear.item07 = '' : form.task_result_unclear.item07 = form.task_result_unclear.item07"
                                    ></v-checkbox>
                                    <v-expand-transition>
                                        <v-text-field
                                        v-if="(form.task_result_unclear.item06 == true)"
                                        v-model="form.task_result_unclear.item07"
                                        v-bind:placeholder="$t('biz.wf_gaisan_syusei.input_ppt_info.item07.placeholder')"
                                        :disabled="(form.task_result_unclear.item06 == false || isDisabledTask)"
                                      ></v-text-field>
                                    </v-expand-transition>
                                </v-flex>
                            </v-layout>
                            <v-layout row>
                                <v-flex xs12>
                                    <v-checkbox
                                        v-model="form.task_result_unclear.item08"
                                        v-bind:label="$t('biz.wf_gaisan_syusei.input_ppt_info.item08.label')"
                                        true-value="1"
                                        false-value="0"
                                        hide-details
                                        :disabled="isDisabledTask"
                                        @change="form.task_result_unclear.item08 ? form.task_result_unclear.item09 = '' : form.task_result_unclear.item09 = form.task_result_unclear.item09"
                                    ></v-checkbox>
                                    <v-expand-transition>
                                        <v-text-field
                                        v-if="(form.task_result_unclear.item08 == true)"
                                        v-model="form.task_result_unclear.item09"
                                        v-bind:placeholder="$t('biz.wf_gaisan_syusei.input_ppt_info.item09.placeholder')"
                                        :disabled="(form.task_result_unclear.item08 == false || isDisabledTask)"
                                      ></v-text-field>
                                    </v-expand-transition>
                                </v-flex>
                            </v-layout>

                        </v-card-text>
                      </v-card>
                </v-flex>
            </v-expand-transition>
            <v-flex xs12 class="text-xs-right">
                <a v-if="!showUnclearBlock" @click="showUnclearBlock = !showUnclearBlock">{{ $t('biz.wf_gaisan_syusei.input_ppt_info.unclearBox_trigger') }}</a>
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
import datePicker from  '../DatePicker'

export default {
    mixins: [formatMixin, motionMixin],
    props: {
        loading: Boolean,
        alert: Object,
        resData: Object
    },
    components:{
        datePicker
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
            request_id: null,
            request_mail_id: null,
            task_result: {
                item01: '',
                item02: '',
                item03: ''
            },
            task_result_unclear: {
                item04: 0,
                item05: '',
                item06: 0,
                item07: '',
                item08: 0,
                item09: ''
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
    },
    computed: {
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
        // 保存
        submit: function() {
            this.$emit('input', true)
            axios.post('/api/biz/wf_gaisan_syusei/input_ppt_info/store',  this.form)
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
                message = this.$t('biz.wf_gaisan_syusei.input_ppt_info._modal.unclear.p1') + this.$t('biz.wf_gaisan_syusei.input_ppt_info._modal.unclear.p2')
            } else {
                message = this.$t('biz.wf_gaisan_syusei.input_ppt_info._modal.fix.p1') + this.$t('biz.wf_gaisan_syusei.input_ppt_info._modal.fix.p2')
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
                this.form.task_result_unclear.item04,
                this.form.task_result_unclear.item06,
                this.form.task_result_unclear.item08,
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
                {[this.form.task_result_unclear.item04] : this.form.task_result_unclear.item05},
                {[this.form.task_result_unclear.item06] : this.form.task_result_unclear.item07},
                {[this.form.task_result_unclear.item08] : this.form.task_result_unclear.item09}
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
        },
        // DB登録後の実作業データをセット
        setFixedData: function(data) {
            this.form.task_result = {
                item01: data.item01,
                item02: data.item02,
                item03: data.item03
            }
            this.form.task_result_unclear = {
                item04: data.item04,
                item05: data.item05,
                item06: data.item06,
                item07: data.item07,
                item08: data.item08,
                item09: data.item09,
            }

            if (this.unclearPointExist()) {
                this.showUnclearBlock = true
            }
        },
        dateItem01: function (msg) {
            this.form.task_result.item01 = msg
        },
        dateItem02: function (msg) {
            this.form.task_result.item02 = msg
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
        'form.task_result.item03': function (val) {
            if (val) {
                this.form.task_result.item03 = this.formatYen(val)
            }
        }
    },
}
</script>
