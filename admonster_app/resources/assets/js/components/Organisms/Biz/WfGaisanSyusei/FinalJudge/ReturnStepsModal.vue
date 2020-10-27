<template>
    <div id="return-steps-modal-block" v-if="modal">
        <v-layout wrap justify-center>
            <v-dialog v-model="modal" persistent max-width="650px">
                <v-expand-transition>
                    <v-flex xs12 mt-3>
                        <v-card color="">
                            <v-card-title pb-0 class="headline grey lighten-2">
                                <span class="headline">{{ $t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.header') }}</span>
                            </v-card-title>
                            <v-card-text style="">
                                <template>
                                    <v-slide-y-transition>
                                        <v-alert
                                            v-model="alert.error"
                                            outline
                                            dismissible
                                            type="error"
                                        >{{ $t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.alert.no_steps_selected') }}</v-alert>
                                    </v-slide-y-transition>
                                </template>
                                <v-container grid-list-md>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-checkbox
                                                v-model="next_step[0].step_id"
                                                :label="resData.steps[0].name"
                                                :value="resData.steps[0].id"
                                                hide-details
                                                @change="next_step[0].step_id ? next_step[0].step_id = next_step[0].step_id : null"
                                                :disabled="unselectableFirstStep"
                                            ></v-checkbox>
                                            <span v-if="unselectableFirstStep" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.01') }}</span>
                                            <v-expand-transition>
                                                <v-layout wrap v-if="next_step[0].step_id">
                                                    <v-flex xs6>
                                                        <v-select
                                                            :items="resData.operators[next_step[0].step_id]"
                                                            v-model="next_step[0].operator[0].user_id"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.select_operators_label')"
                                                            item-value="user_id"
                                                            item-text="user_name"
                                                        ></v-select>
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        <v-textarea
                                                            name=""
                                                            v-model="next_step[0].operator[0].message"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.comment_label')"
                                                      ></v-textarea>
                                                    </v-flex>
                                                </v-layout>
                                            </v-expand-transition>
                                        </v-flex>
                                    </v-layout>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-checkbox
                                                v-model="next_step[1].step_id"
                                                :label="resData.steps[1].name"
                                                :value="resData.steps[1].id"
                                                hide-details
                                                :disabled="(next_step[0].step_id !== null)"
                                                @change="next_step[1].step_id ? next_step[1].step_id = next_step[1].step_id : null"
                                            ></v-checkbox>
                                            <span v-if="(next_step[0].step_id !== null)" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.02') }}</span>
                                            <v-expand-transition>
                                                <v-layout wrap v-if="next_step[1].step_id">
                                                    <v-flex xs6>
                                                        <v-select
                                                            :items="resData.operators[next_step[1].step_id]"
                                                            v-model="next_step[1].operator[0].user_id"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.select_operators_label')"
                                                            item-value="user_id"
                                                            item-text="user_name"
                                                        ></v-select>
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        <v-textarea
                                                            name=""
                                                            v-model="next_step[1].operator[0].message"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.comment_label')"
                                                      ></v-textarea>
                                                    </v-flex>
                                                </v-layout>
                                            </v-expand-transition>
                                        </v-flex>
                                    </v-layout>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-checkbox
                                                v-model="next_step[2].step_id"
                                                :label="resData.steps[2].name"
                                                :value="resData.steps[2].id"
                                                hide-details
                                                :disabled="(next_step[0].step_id !== null)"
                                                @change="next_step[2].step_id ? next_step[2].step_id = next_step[2].step_id : null"
                                            ></v-checkbox>
                                            <span v-if="(next_step[0].step_id !== null)" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.02') }}</span>
                                            <v-expand-transition>
                                                <v-layout wrap v-if="next_step[2].step_id">
                                                    <v-flex xs6>
                                                        <v-select
                                                            :items="resData.operators[next_step[2].step_id]"
                                                            v-model="next_step[2].operator[0].user_id"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.select_operators_label')"
                                                            item-value="user_id"
                                                            item-text="user_name"
                                                        ></v-select>
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        <v-textarea
                                                            name=""
                                                            v-model="next_step[2].operator[0].message"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.comment_label')"
                                                      ></v-textarea>
                                                    </v-flex>
                                                </v-layout>
                                            </v-expand-transition>
                                        </v-flex>
                                    </v-layout>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-checkbox
                                                v-model="next_step[3].step_id"
                                                :label="resData.steps[3].name"
                                                :value="resData.steps[3].id"
                                                hide-details
                                                :disabled="(next_step[4].step_id !== null || next_step[0].step_id !== null)"
                                                @change="next_step[3].step_id ? next_step[3].step_id = next_step[3].step_id : null"
                                            ></v-checkbox>
                                            <span v-if="(next_step[4].step_id !== null)" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.04') }}</span>
                                            <span v-if="(next_step[0].step_id !== null)" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.02') }}</span>
                                            <v-expand-transition>
                                                <v-layout wrap v-if="next_step[3].step_id">
                                                    <v-flex xs6>
                                                        <v-select
                                                            :items="resData.operators[next_step[3].step_id]"
                                                            v-model="next_step[3].operator[0].user_id"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.select_operators_label')"
                                                            item-value="user_id"
                                                            item-text="user_name"
                                                        ></v-select>
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        <v-textarea
                                                            name=""
                                                            v-model="next_step[3].operator[0].message"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.comment_label')"
                                                        ></v-textarea>
                                                    </v-flex>
                                                </v-layout>
                                            </v-expand-transition>
                                        </v-flex>
                                    </v-layout>
                                    <v-layout wrap>
                                        <v-flex xs12>
                                            <v-checkbox
                                                v-model="next_step[4].step_id"
                                                :label="resData.steps[4].name"
                                                :value="resData.steps[4].id"
                                                hide-details
                                                :disabled="(next_step[3].step_id !== null || next_step[0].step_id !== null)"
                                                @change="next_step[4].step_id ? next_step[4].step_id = next_step[4].step_id : null"
                                            ></v-checkbox>
                                            <span v-if="(next_step[3].step_id !== null)" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.03') }}</span>
                                            <span v-if="(next_step[0].step_id !== null)" class="caption return-step-valid-msg">{{ $t('biz.wf_gaisan_syusei.final_judge.return_step_valid_msg.02') }}</span>
                                            <v-expand-transition>
                                                <v-layout wrap v-if="next_step[4].step_id">
                                                    <v-flex xs6>
                                                        <v-select
                                                            :items="resData.operators[next_step[4].step_id]"
                                                            v-model="next_step[4].operator[0].user_id"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.select_operators_label')"
                                                            item-value="user_id"
                                                            item-text="user_name"
                                                        ></v-select>
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        <v-textarea
                                                            name=""
                                                            v-model="next_step[4].operator[0].message"
                                                            :label="$t('biz.wf_gaisan_syusei.final_judge._return_steps_modal.comment_label')"
                                                      ></v-textarea>
                                                    </v-flex>
                                                </v-layout>
                                            </v-expand-transition>
                                        </v-flex>
                                    </v-layout>
                                </v-container>
                            </v-card-text>
                            <v-divider class="ma-0"></v-divider>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="grey" dark @click="closeModal()">{{ $t('common.button.cancel') }}</v-btn>
                                <v-btn color="amber darken-3" dark @click="openModal()">{{ $t('common.button.save') }}</v-btn>
                            </v-card-actions>
                          </v-card>
                    </v-flex>
                </v-expand-transition>
            </v-dialog>
        </v-layout>
    </div>
</template>

<script>
export default {
    props: {
        resData: Object
    },
    data: () => ({
        modal: false,
        message: '',
        alert: {
            error: false
        },
        judgeType: null,
        step_id: null,
        next_step_default:[
            // { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]}
        ],
        // この画面では存在しないが、他画面では一つの作業に対し
        // 複数のオペレーターを設定し個別にメッセージを送るため、operatorは配列型で宣言
        next_step:[
            // { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]},
            { step_id: null, operator: [{ user_id:'', message: ''}]}
        ]
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-return-steps-modal', function(data) {
            self.modal = true
            self.message = data.message
            self.judgeType = data.judgeType
        })
        eventHub.$on('close-modal', function() {
            self.modal = false
        })
    },
    computed: {
        unselectableFirstStep: function() {
            let self = this
            let selectedOthers = 0
            this.next_step.forEach(function (value) {
                if (value.step_id !== self.resData.step_ids.step1 && value.step_id) {
                    selectedOthers += 1
                }
            });
            if (selectedOthers > 0) {
                return true
            } else {
                return false
            }
        },
        existStepToReturn: function() {
            let selectedSteps = 0
            this.next_step.forEach(function (value) {
                if (value.step_id) {
                    selectedSteps += 1
                }
            });
            if (selectedSteps > 0) {
                return true
            } else {
                return false
            }
        }
    },
    methods: {
        openModal: function() {
            if (!this.existStepToReturn) {
                this.alert.error = true
            } else {
                eventHub.$emit('set-return-steps-form',{
                    'next_step': this.next_step
                })

                const textPath = 'biz.wf_gaisan_syusei.final_judge._modal.messages.judge_type.' + this.judgeType
                let message = this.$t(textPath)
                // メール仕訳を戻す場合
                if (this.next_step[0].step_id) {
                    let relatedActiveJobnos = this.resData.related_active_jobnos
                    if (relatedActiveJobnos.length > 0) {
                        message = message + '<br><br>' + this.$t('biz.wf_gaisan_syusei.final_judge._modal.messages.attention_for_return_to_assort_mail') + '<br>' + '【' + this.$t('biz.wf_gaisan_syusei.final_judge._modal.messages.related_jobno') + '】: ' + relatedActiveJobnos.join(', ') + '<br><br>'
                    }
                }

                message = message + this.$t('biz.wf_gaisan_syusei.final_judge._modal.fix.p2')
                eventHub.$emit('open-modal',{
                    'message': message,
                    'judgeType': this.judgeType
                })
            }
        },
        closeModal: function() {
            this.next_step = this.next_step_default
            eventHub.$emit('set-return-steps-form',{
                'next_step': this.next_step
            })
            this.modal = false
            this.alert.error = false
        }
    }
}
</script>

<style scoped>
.return-step-valid-msg {
    color: rgba(0,0,0,.54);
}
</style>
