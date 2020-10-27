<template>
   <!-- 編集モーダル -->
    <div id="edit-modal-block" v-if="edit_modal">
        <v-dialog v-model="edit_modal" persistent max-width="600px">
            <v-card style="overflow: hidden;">
                <v-card-title>
                    <span class="headline">{{ $t('businesses.detail.modal.title') }}</span>
                </v-card-title>
                <v-container py-0 id="business-overview-main">
                    <v-layout row wrap>
                        <v-flex xs6 md4 px-3>
                            <v-text-field
                                :label="$t('businesses.step_name')"
                                :value="step.step_name"
                                :disabled="true"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs6 md4 px-3>
                            <v-text-field
                                :label="$t('businesses.step_type')"
                                :value="showStepType(step.step_type)"
                                :disabled="true"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs6 md4 px-3>
                            <div style="display: flex;">
                                <v-text-field
                                    v-model="edit.deadline_limit"
                                    label="期限"
                                    type="number"
                                    maxlength="3"
                                    @keyup="validateTimeUnit()"
                                    disabled
                                ></v-text-field>
                                <v-select
                                    v-model="edit.time_unit"
                                    dense
                                    @change="validateTimeUnit()"
                                    :items="items"
                                    item-text="unit"
                                    item-value="value"
                                    disabled
                                ></v-select>
                                <!-- v-text-field標準のエラー表示ではボックスを超える幅のエラーメッセージが表示できないため、単独で記載 -->
                                <!-- <div class="v-messages theme--light error--text" style="height:12px">{{ error_message }}</div> -->
                            </div>
                        </v-flex>
                        <v-flex xs12 md12 px-3>
                            <v-text-field
                                :label="$t('businesses.description')"
                                :value="step.step_description"
                                :disabled="true"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md12 px-3>
                            <v-autocomplete
                                v-model="selected"
                                label="担当者候補"
                                :items="candidates"
                                item-text="name"
                                item-value="id"
                                dense
                                chips
                                multiple
                            >
                                <template slot="selection" slot-scope="data">
                                    <v-chip
                                        :selected="data.selected"
                                        close
                                        color="secondary"
                                        class="chip--select-multi"
                                        @input="remove(data.item)"
                                    >
                                        <v-avatar>
                                            <img :src="data.item.user_image_path">
                                        </v-avatar>
                                        {{ data.item.name }}
                                    </v-chip>
                                </template>
                                <!-- 選択肢にもアイコン表示したかったがこのままだと選択していることがわからないので保留 -->
                                <!-- <template slot="item" slot-scope="data">
                                    <template>
                                        <v-list-tile-avatar>
                                            <img :src="data.item.user_image_path">
                                        </v-list-tile-avatar>
                                        <v-list-tile-content>
                                            <v-list-tile-title v-html="data.item.name"></v-list-tile-title>
                                            <v-list-tile-sub-title v-if="data.item.id == 5" v-html="'管理者'"></v-list-tile-sub-title>
                                            <v-list-tile-sub-title v-else></v-list-tile-sub-title>
                                        </v-list-tile-content>
                                    </template>
                                </template> -->
                            </v-autocomplete>
                        </v-flex>
                    </v-layout>
                </v-container>
                <v-card-text class="text-xs-right py-0">
                    <span class="caption"><!-- ※担当者候補の追加・削除依頼はこちらまで &lt;support@admonster.jp&gt; --></span>
                </v-card-text>
                <v-card-actions class="btn-center-block">
                    <v-btn dark color="grey" @click="openConfirmModal('cancel')">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn color="primary" @click="openConfirmModal('submit')" :disabled="disabled_submit">{{ $t('common.button.confirm') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    props: {
        users: { type: Object },
        steps: { type: Array },
        candidates: { type: Array },
    },
    data: () => ({
        selected: [],
        disabled_submit: false,
        edit_modal: false,
        error_message: '',
        message: '',
        step: '',
        // user_ids:[],
        work_user_ids:[],
        // 検索条件
        edit: {
            description: '',
            deadline_limit: '',
            time_unit: 0,
            check_user_ids: {},
        },
        items: [
            { unit: eventHub.$t('common.datetime.minute'), value: 1 },
            { unit: eventHub.$t('common.datetime.hour.interval'), value: 2 },
            { unit: eventHub.$t('common.datetime.day'), value: 3 }
        ]
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-edit-modal', function(step_id) {
            self.edit_modal = true
            self.setData(step_id)
        })
        eventHub.$on('close-edit-modal', function() {
            self.edit_modal = false
            self.error_message = ''
        })
        eventHub.$on('submit-edit-modal', function() {
            self.submit()
        })
    },
    computed: {
        startedAt () {
            return document.getElementById('loaded-utc-datetime').value
        },
    },
    methods: {
        submit () {
            // 担当者候補でチェックが入っているユーザーIDを配列に格納
            let register_user_ids = this.selected

            this.edit_modal = false
            this.loading = true
            axios.post('/api/businesses/store',{
                started_at : this.startedAt,
                step_id : this.step.step_id,
                description : this.edit.description,
                time_unit : this.edit.time_unit,
                deadline_limit : this.edit.deadline_limit,
                register_user_ids : register_user_ids,
                work_user_ids : this.work_user_ids,
            })
                .then((res) => {
                //TOOD 成功時処理
                    this.$emit(res.data.result);
                    if (res.data.result == 'success'){
                        eventHub.$emit('reload')
                    }
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err);
                })
                .finally(() => {
                    this.loading = false
                });

        },
        cancel: function() {
            this.edit_modal = false;
        },
        showStepType (step_type) {
            if (step_type == _const.STEP_TYPE.INPUT){
                return eventHub.$t('businesses.detail.step_type.input')
            } else if (step_type == _const.STEP_TYPE.APPROVAL){
                return eventHub.$t('businesses.detail.step_type.approval')
            }
        },
        setData (step_id) {
            // リストで選択された作業IDに紐づく情報を格納
            let step = this.steps.filter(step => step_id == step.step_id)
            this.step = step[0]

            // 現在のDBの値から編集可能欄の初期値を取得
            this.edit.description = this.step.step_description
            this.edit.time_unit = this.step.time_unit
            this.edit.deadline_limit = this.step.deadline_limit

            // 業務担当者リストのユーザーが担当者になっていれば初期値でチェックを入れる
            this.work_user_ids = this.step.work_user_ids ? this.step.work_user_ids.split(',').map(Number) : []
            this.selected = this.work_user_ids
        },
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        // プルダウン変更の際もバリデーションチェックを呼び出す必要があるためrulesではなくメソッドで実装
        validateTimeUnit () {
            this.disabled_submit = true
            // const pattern = /^([1-9]\d*|0)$/

            // 必須
            if (!this.edit.deadline_limit){
                this.error_message = eventHub.$t('businesses.detail.error.required')
                return
            }

            // 整数
            // type="number"とし、チェック不要とする
            // if(!pattern.test(this.edit.deadline_limit)){
            //     this.error_message = eventHub.$t('businesses.detail.error.deadline')
            //     return
            // }

            // 0を超える値かどうか
            if (this.edit.deadline_limit <= 0){
                this.error_message = eventHub.$t('businesses.detail.error.minimum')
                return
            }
            // 入力値
            // maxlength="3"の制限のみで、詳細なチェックは不要とする（単位を合わせて登録したいかもしれないので）
            // if(this.edit.time_unit == _const.TIME_UNIT.MINUTE && this.edit.deadline_limit >59){
            //     this.error_message = eventHub.$t('businesses.detail.error.minute')
            //     return
            // }else if (this.edit.time_unit == _const.TIME_UNIT.HOUR && this.edit.deadline_limit >23) {
            //     this.error_message = eventHub.$t('businesses.detail.error.hour')
            //     return
            // }

            this.disabled_submit = false;
            this.error_message = ''
            return

        },

        openConfirmModal (process_type) {
            let message = ''
            let process = ''

            if (process_type == 'submit'){
                message = eventHub.$t('businesses.detail.confirm.submit')
                process = 'submit-edit-modal'

            } else {
                message = eventHub.$t('businesses.detail.confirm.cancel')
                process = 'close-edit-modal'
            }
            eventHub.$emit('open-confirm-modal',{
                'message': message,
                'process': process
            })
        },
        remove (item) {
            const index = this.selected.indexOf(item.id)
            if (index >= 0) this.selected.splice(index, 1)
        }
    }
}
</script>
