<template>
    <!-- 実作業 -->
    <div id="steps-block">
        <v-layout mt-0 ml-3 mb-5>
            <v-flex xs8>

                <!-- STEP1 -->
                <v-flex xs12 mb-5>
                    <v-layout align-center mb-2>
                        <v-chip
                            class="ml-0 mr-2"
                            label
                            color="grey darken-3"
                            style="color:#e0e0e0;"
                            medium
                        >STEP1
                        </v-chip>
                        <div>
                            <span class="step-description">{{ $t('biz.wf_gaisan_syusei.assort_mail.step1.step_description') }}</span>
                        </div>
                    </v-layout>
                    <v-layout align-center mt-1 class="caption step-sub-description">
                        <v-icon color="primary" class="mr-1">info</v-icon>
                        <span>{{ $t('biz.wf_gaisan_syusei.assort_mail.step1.step_description_sub_1') }}</span>
                    </v-layout>
                    <div class="mt-4">
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">DAC営業担当</v-subheader>
                            </v-flex>
                            <v-flex xs8>
                                <v-text-field v-model="form.task_result.item01" :disabled="isDoneTask" hide-details></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">代理店名</v-subheader>
                            </v-flex>
                            <v-flex xs8>
                                <v-text-field v-model="form.task_result.item02" :disabled="isDoneTask" hide-details></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">広告主名</v-subheader>
                            </v-flex>
                            <v-flex xs8>
                                <v-text-field v-model="form.task_result.item03" :disabled="isDoneTask" hide-details></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">キャンペーン名</v-subheader>
                            </v-flex>
                            <v-flex xs8>
                                <v-text-field v-model="form.task_result.item04" :disabled="isDoneTask" hide-details></v-text-field>
                            </v-flex>
                        </v-layout>
                    </div>
                </v-flex>
                <!-- / STEP1 -->

                <!-- STEP2 -->
                <v-flex xs12>
                    <v-layout align-center>
                        <v-chip
                            class="ml-0 mr-2"
                            color="grey darken-3"
                            style="color:#e0e0e0;"
                            label
                            medium
                        >STEP2
                        </v-chip>
                        <div>
                            <span class="step-description">{{ $t('biz.wf_gaisan_syusei.assort_mail.step2.step_description') }}</span><br>
                        </div>
                    </v-layout>
                    <div class="mt-3">
                        <ul>
                            <li v-for="(item, index) in form.task_result.item05" v-bind:key="item.id">
                                <v-layout row align-center>
                                    <v-flex xs4>
                                        <v-subheader>
                                            <span class="mr-1">JOBNO</span>
                                            <span class="circle-num">{{ index + 1 }}</span>
                                        </v-subheader>
                                    </v-flex>
                                    <v-flex xs7>
                                        <v-text-field v-model="form.task_result.item05[index]" :disabled="isDoneTask" hide-details></v-text-field>
                                    </v-flex>
                                    <v-flex xs1 v-if="!isDoneTask">
                                        <v-btn v-if="form.task_result.item05.length > 1" color="primary" icon small @click="removeJobNoForm(index)">
                                            <v-icon>remove</v-icon>
                                        </v-btn>
                                        <v-btn v-else color="primary" icon small @click="appendJobNoForm" class="text-xs-right">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-flex>
                                </v-layout>
                            </li>
                            <li>
                                <v-layout row align-center v-if="!isDoneTask">
                                    <v-flex xs4></v-flex>
                                    <v-flex xs7></v-flex>
                                    <v-flex xs1 v-if="form.task_result.item05.length > 1">
                                        <v-btn color="primary" icon small @click="appendJobNoForm" class="text-xs-right">
                                            <v-icon>add</v-icon>
                                        </v-btn>
                                    </v-flex>
                                </v-layout>
                            </li>
                        </ul>
                    </div>
                </v-flex>
            </v-flex>
            <!-- / STEP2 -->

            <!-- 不明案内テキスト -->
            <v-flex xs4>
                <v-card>
                    <v-card-text class="">
                        <v-layout align-center mb-2>
                            <v-icon mr-1 color="primary" style="font-size:15px;">info</v-icon>
                            <span>{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.title') }}</span>
                        </v-layout>
                        <v-divider class="ma-0"></v-divider>
                        <div class="pt-2" style="font-size: 13px;">
                            <v-layout align-start ma-0>
                                <div class="mr-2">①</div>
                                <div class="">{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.ptn_list.1') }}</div>
                            </v-layout>
                            <v-layout align-start ma-0>
                                <div class="mr-1">②</div>
                                <div class="">{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.ptn_list.2') }}</div>
                            </v-layout>
                            <v-layout align-start ma-0>
                                <div class="mr-1">③</div>
                                <div class="">{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.ptn_list.3') }}</div>
                            </v-layout>
                            <v-layout align-start ma-0>
                                <div class="mr-1">④</div>
                                <div class="">{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.ptn_list.4') }}</div>
                            </v-layout>
                            <v-layout align-start ma-0>
                                <div class="mr-1">⑤</div>
                                <div class="">{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.ptn_list.5') }}</div>
                            </v-layout>
                            <v-layout align-start ma-0>
                                <div class="mr-1">⑥</div>
                                <div class="">{{ $t('biz.wf_gaisan_syusei.assort_mail.unclear_ptn_info.ptn_list.6') }}</div>
                            </v-layout>
                        </div>
                    </v-card-text>
                </v-card>
            </v-flex>
            <!-- / 不明案内テキスト -->
        </v-layout>

        <!-- 処理ボタン -->
        <v-layout wrap ml-3>
            <v-flex xs12 class="text-xs-right">
                <span v-if="!isDoneTask">
                    <v-btn color="amber darken-3" :dark="valid" :disabled="!valid" @click="openModal('')">
                        {{ $t('biz.wf_gaisan_syusei.common.btn_submit') }}
                    </v-btn>
                </span>
                <span v-else class="ml-3">
                    <span v-if="form.task_result_type_info.irregular_comment == ''">
                        <v-icon mr-1 style="font-size:15px;">done</v-icon>
                        <span>{{ $t('biz.wf_gaisan_syusei.common.status_done') }}</span>
                    </span>
                </span>
            </v-flex>
        </v-layout>

        <v-layout wrap ml-3 class="justify-end align-center">
            <v-flex xs12 class="text-xs-right" v-if="!isDoneTask">
                <a @click="showIrregularBlock = !showIrregularBlock">
                    <span>{{ $t('biz.wf_gaisan_syusei.assort_mail.irregularBtnsBox_trigger') }}</span>
                    <v-icon small v-if="!showIrregularBlock">expand_more</v-icon>
                    <v-icon small v-else>expand_less</v-icon>
                </a>
            </v-flex>
            <v-expand-transition>
                <div class="mt-3 text-xs-right" v-if="showIrregularBlock || (isDoneTask && form.task_result_type_info.irregular_comment !== '')">
                    <v-card color="" class="text-xs-right">
                        <v-card-text>
                            <v-flex xs12 class="judge-btns-wrap">
                                <span v-for="(irregularBtn, index) in irregularBtns" :key="index">
                                    <span v-if="!isDoneTask">
                                        <v-btn v-html="irregularBtn.text" color="amber darken-3" :dark="valid" :disabled="!valid" @click="openModal(irregularBtn.resultType)">
                                        </v-btn>
                                    </span>
                                    <span v-else class="text-xs-left">
                                        <span v-if="(irregularBtn.resultType == form.task_result_type_info.result_type)"> 
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
                                                <v-btn v-html="irregularBtn.text" color="amber darken-3" disabled>
                                                </v-btn>
                                            </v-badge>
                                            <span>{{ $t('biz.wf_gaisan_syusei.common.status_done') }}</span>
                                            <br>
                                            <div v-if="form.task_result_type_info.irregular_comment" class="text-xs-left">   <span>[{{ $t('biz.wf_gaisan_syusei.common.comment') }}] </span><span>{{ form.task_result_type_info.irregular_comment }}</span>
                                                
                                            </div>
                                        </span>
                                    </span>
                                </span>
                            </v-flex>
                        </v-card-text>
                      </v-card>
                </div>
            </v-expand-transition>
        </v-layout>
    </div>
    <!-- / 実作業 -->
</template>

<script>
import motionMixin from '../../../../../mixins/motionMixin'

export default {
    mixins: [motionMixin],
    props: {
        loading: Boolean,
        alert: Object,
        resData: Object
    },
    data: () => ({
        valid: true,
        showIrregularBlock: false,
        isDoneTask: false,
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
                item05: [''
                ]
            },
            task_result_type_info: {
                result_type: '',
                irregular_comment: '',
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

        eventHub.$on('submitFromIrregularModal', function(data) {
            self.form.task_result_type_info.irregular_comment = data.comment
            self.submit()
        })

        const resData = JSON.parse(JSON.stringify(this.resData))
        this.setData(resData)
    },
    computed: {
        irregularBtns () {
            return [
                { text: this.$t('biz.wf_gaisan_syusei.common.task_result_type_text.prefix' + _const.TASK_RESULT_TYPE.CANCEL), resultType: _const.TASK_RESULT_TYPE.CANCEL, align: 'center' },
                // { text: this.$t('biz.wf_gaisan_syusei.common.task_result_type_text.prefix' + _const.TASK_RESULT_TYPE.HOLD), resultType: _const.TASK_RESULT_TYPE.HOLD, align: 'center' },
                { text: this.$t('biz.wf_gaisan_syusei.common.task_result_type_text.prefix' + _const.TASK_RESULT_TYPE.CONTACT), resultType: _const.TASK_RESULT_TYPE.CONTACT, align: 'center' },
            ]
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
        },
        // JOBNO入力枠を追加
        appendJobNoForm: function() {
            this.form.task_result.item05.push('')
        },
        // JOBNO入力枠を削除
        removeJobNoForm: function(index) {
            this.form.task_result.item05.splice(index, 1)
        },
        // 保存
        submit: function() {
            this.$emit('input', true)
            axios.post('/api/biz/wf_gaisan_syusei/assort_mail/store',  this.form)
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
        openModal: function(resultType) {
            let message = ''
            this.form.task_result_type_info.result_type = (resultType == '') ? _const.TASK_RESULT_TYPE.DONE : resultType

            if (resultType == _const.TASK_RESULT_TYPE.CANCEL) {
                // 「対応不要」の場合
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.CANCEL
                message = this.$t('biz.wf_gaisan_syusei.common._modal.irregular.prefix' + resultType)
                eventHub.$emit('open-irregular-modal',{
                    'message': message,
                })
            } else if (resultType == _const.TASK_RESULT_TYPE.CONTACT) {
                // 「不明あり」の場合
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.CANCEL
                message = this.$t('biz.wf_gaisan_syusei.common._modal.irregular.prefix' + resultType)
                eventHub.$emit('open-irregular-modal',{
                    'message': message,
                })
            } else if (resultType == _const.TASK_RESULT_TYPE.HOLD) {
                // 「保留」の場合
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.HOLD
                message = this.$t('biz.wf_gaisan_syusei.common._modal.irregular.prefix' + resultType) + this.$t('biz.wf_gaisan_syusei.assort_mail._modal.unclear.p2')
                eventHub.$emit('open-modal',{
                    'message': message,
                })
            } else {
                message = this.$t('biz.wf_gaisan_syusei.assort_mail._modal.fix.p1') + this.$t('biz.wf_gaisan_syusei.assort_mail._modal.fix.p2')
                eventHub.$emit('open-modal',{
                    'message': message
                })
            }
        },
        closeModal: function() {
            eventHub.$emit('close-modal')
        },
        setHiddenFormData: function(data) {
            this.form.task_id = data.task_id
            this.form.step_id = data.request_work.step_id
            this.form.started_at = data.started_at.date
            this.form.request_work_id = data.request_work.id
            this.form.request_id = data.request_work.request_id
            this.form.request_mail_id = data.request_mail.id
        },
        // DB登録後の実作業データをセット
        setFixedData: function(data) {
            this.form.task_result = {
                item01: data.item01,
                item02: data.item02,
                item03: data.item03,
                item04: data.item04,
                item05: data.item05
            }
            this.form.task_result_type_info = {
                result_type: data.result_type,
                irregular_comment: data.irregular_comment
            }
        }
    }
}
</script>
