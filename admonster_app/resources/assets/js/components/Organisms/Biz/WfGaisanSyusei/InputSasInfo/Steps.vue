<template>
    <!-- 実作業 -->
    <div id="steps-block">
        <v-layout ma-0 mb-5>

            <!-- STEP1 -->
            <v-flex xs12 sm6 md6 pr-5>
                <v-layout align-center mb-2>
                    <v-chip
                        class="ml-0 mr-2"
                        color="grey darken-3"
                        style="color:#e0e0e0;"
                        label
                        medium
                    >STEP1
                    </v-chip>
                    <div style="width:100%;">
                        <span class="step-description">{{ $t('biz.wf_gaisan_syusei.input_sas_info.step1.step_description') }}</span><br>
                    </div>
                </v-layout>
                <v-layout row align-baseline ma-0 class="step-sub-description">
                    <div><v-icon class="mr-1" color="primary">info</v-icon></div><div class="caption" v-html="$t('biz.wf_gaisan_syusei.input_sas_info.step1.step_description_sub')"></div>
                </v-layout>
                <v-layout row align-baseline ma-0 class="step-sub-description">
                    <div><v-icon class="mr-1" color="primary">info</v-icon></div><div class="caption">{{ $t('biz.wf_gaisan_syusei.input_sas_info.step1.guide_breadcrumb') }}</div>
                </v-layout>
                <v-layout row align-center justify-end mb-2>
                    <div class="guide-link mb-1">
                        <v-icon>arrow_right</v-icon>
                        <a href="javascript:void(0)" class="guide" @click="openGuide('/images/biz/wf_gaisan_syusei/input_sas_info_guide01.png')">
                            <span>{{ $t('biz.wf_gaisan_syusei.input_sas_info.step1.link_guide') }}</span>
                        </a>
                    </div>
                </v-layout>
                <div class="mt-4">
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">営業担当</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item01" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">代理店名</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item02" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">広告主名</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item03" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">キャンペーン名</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item04" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">サイト名</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item05" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">メニュー名</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item06" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">期間（開始日）</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <date-picker :isActive="isDisabledTask" :dateValue="form.task_result.item07" @dateValue="dateItem07"></date-picker>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">期間（終了日）</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <date-picker :isActive="isDisabledTask" :dateValue="form.task_result.item08" @dateValue="dateItem08"></date-picker>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">代理店手数料率</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item09" :disabled="isDisabledTask" hide-details reverse @change="formatPercent(form.task_result.item09, 'item09')"></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">原価率</v-subheader>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field v-model="form.task_result.item10" :disabled="isDisabledTask" hide-details reverse @change="formatPercent(form.task_result.item10, 'item10')"></v-text-field>
                        </v-flex>
                    </v-layout>
                </div>
            </v-flex>
            <!-- ./ STEP1 -->

            <!-- STEP2 -->
            <v-flex xs12 sm6 md6>
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
                        <span class="step-description">{{ $t('biz.wf_gaisan_syusei.input_sas_info.step2.step_description') }}</span><br>
                    </div>
                </v-layout>
                <v-layout row align-baseline ma-0 class="step-sub-description">
                    <div><v-icon class="mr-1" color="primary">info</v-icon></div><div class="caption">{{ $t('biz.wf_gaisan_syusei.input_sas_info.step2.step_description_sub') }}</div>
                </v-layout>
                <v-layout row align-baseline ma-0 class="step-sub-description">
                    <div><v-icon class="mr-1" color="primary">info</v-icon></div><div class="caption">{{ $t('biz.wf_gaisan_syusei.input_sas_info.step2.guide_breadcrumb') }}</div>
                </v-layout>
                <v-layout row align-center justify-end mb-2>
                    <div class="guide-link mb-1">
                        <v-icon>arrow_right</v-icon>
                        <a href="javascript:void(0)" class="guide" @click="openGuide('/images/biz/wf_gaisan_syusei/input_sas_info_guide02.png')">
                            <span>{{ $t('biz.wf_gaisan_syusei.input_sas_info.step2.link_guide') }}</span>
                        </a>
                    </div>
                </v-layout>
                <div class="mt-4">
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">実施料金</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <v-text-field v-model="form.task_result.item11" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">代理店手数料</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <v-text-field v-model="form.task_result.item12" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">原価</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <v-text-field v-model="form.task_result.item13" :disabled="isDisabledTask" hide-details reverse></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">受注明細番号①</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <v-text-field v-model="form.task_result.item14" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">受注明細番号②</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <v-text-field v-model="form.task_result.item15" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row align-center>
                        <v-flex xs5>
                            <v-subheader style="justify-content:center;">受注明細番号③</v-subheader>
                        </v-flex>
                        <v-flex xs5>
                            <v-text-field v-model="form.task_result.item16" :disabled="isDisabledTask" hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                </div>
            </v-flex>
            <!-- / STEP2-->
        </v-layout>

        <!-- 処理ボタン -->
        <v-layout wrap ml-3>
            <v-flex xs7 offset-xs5 offset-md2 offset-lg5>
                <div class="title"><v-icon color="red">warning</v-icon>{{ $t('biz.wf_gaisan_syusei.input_sas_info.announce_text') }}</div>
                <v-expand-transition>
                    <v-flex xs12 mt-3 v-if="showUnclearBlock">

                        <v-card color="">
                            <v-card-title class="pb-0">
                                {{ $t('biz.wf_gaisan_syusei.input_sas_info.unclear_box.title') }}
                                <v-spacer></v-spacer>
                                <v-btn icon @click="showUnclearBlock = !showUnclearBlock">
                                  <v-icon>{{ showUnclearBlock ? 'close' : '' }}</v-icon>
                                </v-btn>
                            </v-card-title>
                            <v-card-text class="pt-0">
                                <v-layout row>
                                    <v-flex xs12>
                                        <v-checkbox
                                            ref="form.task_result_unclear.item17"
                                            v-model="form.task_result_unclear.item17"
                                            v-bind:label="$t('biz.wf_gaisan_syusei.input_sas_info.item17.label')"
                                            hide-details
                                            true-value="1"
                                            false-value="0"
                                            :disabled="isDisabledTask"
                                            @change="form.task_result_unclear.item17 ? form.task_result_unclear.item18 = '':form.task_result_unclear.item18 = form.task_result_unclear.item18"
                                        ></v-checkbox>
                                        <v-expand-transition>
                                            <v-text-field
                                                v-if="(form.task_result_unclear.item17 == true)"
                                                v-model="form.task_result_unclear.item18"
                                                v-bind:placeholder="$t('biz.wf_gaisan_syusei.input_sas_info.item18.placeholder')"
                                                :disabled="(form.task_result_unclear.item17 == false || isDisabledTask)"
                                              ></v-text-field>
                                        </v-expand-transition>
                                    </v-flex>
                                </v-layout>
                                <v-layout row>
                                    <v-flex xs12>
                                        <v-checkbox
                                            v-model="form.task_result_unclear.item19"
                                            v-bind:label="$t('biz.wf_gaisan_syusei.input_sas_info.item19.label')"
                                            true-value="1"
                                            false-value="0"
                                            hide-details
                                            :disabled="isDisabledTask"
                                            @change="form.task_result_unclear.item19 ? form.task_result_unclear.item20 = '' : form.task_result_unclear.item20 = form.task_result_unclear.item20"
                                        ></v-checkbox>
                                        <v-expand-transition>
                                            <v-text-field
                                                v-if="(form.task_result_unclear.item19 == true)"
                                                v-model="form.task_result_unclear.item20"
                                                v-bind:placeholder="$t('biz.wf_gaisan_syusei.input_sas_info.item20.placeholder')"
                                                :disabled="(form.task_result_unclear.item19 == false || isDisabledTask)"
                                            ></v-text-field>
                                        </v-expand-transition>
                                    </v-flex>
                                </v-layout>
                                <v-layout row>
                                    <v-flex xs12>
                                        <v-checkbox
                                            v-model="form.task_result_unclear.item21"
                                            v-bind:label="$t('biz.wf_gaisan_syusei.input_sas_info.item21.label')"
                                            true-value="1"
                                            false-value="0"
                                            hide-details
                                            :disabled="isDisabledTask"
                                            @change="form.task_result_unclear.item21 ? form.task_result_unclear.item22 = '' : form.task_result_unclear.item22 = form.task_result_unclear.item22"
                                        ></v-checkbox>
                                        <v-expand-transition>
                                            <v-text-field
                                                v-if="(form.task_result_unclear.item21 == true)"
                                                v-model="form.task_result_unclear.item22"
                                                v-bind:placeholder="$t('biz.wf_gaisan_syusei.input_sas_info.item22.placeholder')"
                                                :disabled="(form.task_result_unclear.item21 == false || isDisabledTask)"
                                            ></v-text-field>
                                        </v-expand-transition>
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                          </v-card>
                    </v-flex>
                </v-expand-transition>
                <v-flex xs12 class="text-xs-right">
                    <a v-if="!showUnclearBlock" @click="showUnclearBlock = !showUnclearBlock">{{ $t('biz.wf_gaisan_syusei.input_sas_info.unclearBox_trigger') }}</a>
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
        code: null,
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
                item03: '',
                item04: '',
                item05: '',
                item06: '',
                item07: '',
                item08: '',
                item09: '',
                item10: '',
                item11: '',
                item12: '',
                item13: '',
                item14: '',
                item15: '',
                item16: ''
            },
            task_result_unclear: {
                item17: 0,
                item18: '',
                item19: 0,
                item20: '',
                item21: 0,
                item22: ''
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
            axios.post('/api/biz/wf_gaisan_syusei/input_sas_info/store',  this.form)
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
                message = this.$t('biz.wf_gaisan_syusei.input_sas_info._modal.unclear.p1') + this.$t('biz.wf_gaisan_syusei.input_sas_info._modal.unclear.p2')
            } else {
                message = this.$t('biz.wf_gaisan_syusei.input_sas_info._modal.fix.p1') + this.$t('biz.wf_gaisan_syusei.input_sas_info._modal.fix.p2')
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
                this.form.task_result_unclear.item17,
                this.form.task_result_unclear.item19,
                this.form.task_result_unclear.item21
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
                {[this.form.task_result_unclear.item17] : this.form.task_result_unclear.item18},
                {[this.form.task_result_unclear.item19] : this.form.task_result_unclear.item20},
                {[this.form.task_result_unclear.item21] : this.form.task_result_unclear.item22}
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
        // APIから取得した、作業データ登録に必要なデータをformにセット
        setParamData: function(data) {
            this.form.task_id = data.task_id
            this.form.step_id = data.request_work.step_id
            this.form.started_at = data.started_at.date
            this.form.request_work_id = data.request_work.id
            this.form.request_id = data.request_work.request_id
            this.form.request_mail_id = data.request_mail_id
            this.code = data.request_work.code
        },
        // DB登録後の実作業データをセット
        setFixedData: function(data) {
            this.form.task_result = {
                item01: data.item01,
                item02: data.item02,
                item03: data.item03,
                item04: data.item04,
                item05: data.item05,
                item06: data.item06,
                item07: data.item07,
                item08: data.item08,
                item09: data.item09,
                item10: data.item10,
                item11: data.item11,
                item12: data.item12,
                item13: data.item13,
                item14: data.item14,
                item15: data.item15,
                item16: data.item16
            }
            this.form.task_result_unclear = {
                item17: data.item17,
                item18: data.item18,
                item19: data.item19,
                item20: data.item20,
                item21: data.item21,
                item22: data.item22,
            }
            if (this.unclearPointExist()) {
                this.showUnclearBlock = true
            }
        },
        openGuide: function(path) {
            window.open(path,  null, 'width=1000, height=700, resizable=1')
        },
        formatPercent: function(val, keyName) {
            let formatedVal = val.replace(/\s+/g, '');
            formatedVal = formatedVal.replace(/%/g, '');
            formatedVal = Number(formatedVal).toFixed(2);
            formatedVal = this.addPerMark(formatedVal);
            this.form.task_result[keyName] = formatedVal
        },
        dateItem07: function (msg) {
            this.form.task_result.item07 = msg
        },
        dateItem08: function (msg) {
            this.form.task_result.item08 = msg
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
        'form.task_result.item11': function (val) {
            if (val) {
                this.form.task_result.item11 = this.formatYen(val)
            }
        },
        'form.task_result.item12': function (val) {
            if (val) {
                this.form.task_result.item12 = this.formatYen(val)
            }
        },
        'form.task_result.item13': function (val) {
            if (val) {
                this.form.task_result.item13 = this.formatYen(val)
            }
        }
    }
}
</script>
