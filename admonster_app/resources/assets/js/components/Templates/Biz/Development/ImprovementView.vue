<template>
    <v-app>
        <app-menu :drawer="drawer" :reference-mode="referenceMode"></app-menu>
        <app-header :title="$t('biz.development.improvement.title')" :reference-mode="referenceMode"></app-header>

        <confirm-modal></confirm-modal>
        <notify-modal></notify-modal>

        <v-content id="biz" :style="contentStyle">
            <v-container fluid grid-list-md v-if="resData">
                <v-layout row wrap>
                    <v-flex v-if="!referenceMode" xs12>
                        <v-alert
                            v-model="alert.returnMessage"
                            dismissible
                            outline
                            type="warning"
                        >
                            {{ returnMessage }}
                        </v-alert>
                        <v-btn flat small class="ma-0" href="/tasks">
                            <v-icon left>arrow_back</v-icon>{{ $t('common.button.back') }}
                        </v-btn>
                    </v-flex>
                    <v-flex shrink :class="showRequestContent ? 'xs12 md6' : ''">
                        <request-contents
                            :request-work-id="requestWorkId"
                            v-model="showRequestContent"
                            selectedContent="mail"
                            height="auto"
                            max-height="640px"
                            min-height="640px"
                            :displayInit="isRequestContentsDisplayInit"
                            is-additional
                        >
                        </request-contents>
                    </v-flex>
                    <v-flex grow :class="showRequestContent ? 'xs12 md6' : ''">
                        <!-- 位置指定は仮対応 -->
                        <v-layout justify-space-between column fill-height pa-3>
                            <div class=""></div>
                            <div class="send-mail-info">
                                <v-text-field
                                    :label="$t('biz.development.improvement.status')"
                                    :value="status()"
                                    readonly
                                    disabled
                                >
                                </v-text-field>
                                <!-- TODO 除外時の時刻表示をどうするか検討が必要。-->
                                <v-text-field
                                    :label="$t('biz.development.improvement.development_started_at')"
                                    :value="(isOnTask || isDoneTask) ? dateFormat(resData.task_result_content.development_started_at.date) : ' ' "
                                    readonly
                                    disabled
                                >
                                </v-text-field>
                                <v-text-field
                                    :label="$t('biz.development.improvement.development_finished_at')"
                                    :value=" isDoneTask ? dateFormat(resData.task_result_content.development_finished_at.date) : ' ' "
                                    readonly
                                    disabled
                                >
                                </v-text-field>
                            </div>
                            <div style="text-align: right;">
                                <template v-if="!isDoneTask && !isInactiveTask">
                                    <v-btn color="primary" @click="openModal" :disabled="isOnTask">
                                        {{ $t('common.button.start') }}
                                    </v-btn>
                                    <v-btn color="primary" @click="openModal" :disabled="!isOnTask">
                                        {{ $t('common.button.finish') }}
                                    </v-btn>
                                </template>
                                <template v-else>
                                    <v-icon>done</v-icon>
                                    <span>処理済み</span>
                                </template>
                            </div>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer :reference-mode="referenceMode"></app-footer>
    </v-app>
</template>

<script>
import ConfirmModal from '../../../Organisms/Biz/Development/ConfirmModal.vue'
import NotifyModal from '../../../Organisms/Biz/Development/Improvement/NotifyModal.vue'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        ConfirmModal,
        NotifyModal,
        RequestContents,
        ProgressCircular
    },
    props: {
        requestWorkId: { type: Number, require: true },
        taskId: { type: Number, require: true },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        drawer: false,
        showRequestContent: false,
        loading: false,
        isOnTask: false,
        isDoneTask: false,
        isInactiveTask: false,
        message: '',
        inactiveMessage: '',
        returnMessage: '',
        resData: null,
        process_info: {
            results: {
                type: _const.TASK_RESULT_TYPE.DONE
            }
        },
        notify_modal_emit_message: 'success',
        alert: {
            returnMessage: false
        },
    }),
    created () {
        window.getApp = this;
        this.getData('initial')

        let self = this;
        eventHub.$on('submit', function() {
            self.submit()
        })

        eventHub.$on(this.notify_modal_emit_message, function(){
            location = location.origin + '/tasks'
        })
    },
    computed: {
        contentStyle () {
            return this.referenceMode ? {padding: 0} : {}
        },
        isRequestContentsDisplayInit () {
            return !this.referenceMode
        },
    },
    methods: {
        dateFormat (utcDate) {
            let ISOStringDate = moment.utc(utcDate).toISOString()
            return moment(ISOStringDate).format('YYYY/MM/DD HH:mm')
        },
        status () {
            // TODO 表記ステータスにも休憩や別作業中が必要？
            if (this.isOnTask){
                return this.$t('biz.development.improvement.statuses.on');
            } else if (this.isDoneTask){
                return this.$t('biz.development.improvement.statuses.done');
            } else if (this.isInactiveTask){
                return this.$t('biz.development.improvement.statuses.inactive');
            } else {
                return this.$t('biz.development.improvement.statuses.none');
            }
        },
        getData: function(type) {
            this.loading = true
            axios.get('/api/biz/development/improvement/' + this.requestWorkId + '/' + this.taskId + '/create')
                .then((res) => {
                    if (type == 'initial') {
                        // 初回ロード時
                        this.resData = res.data

                        if (this.resData.message != null){
                            this.returnMessage = this.resData.message
                            this.alert.returnMessage = true
                        }

                        if (this.resData.is_on_task) {
                            this.isOnTask = true
                        } else if (this.resData.is_done_task){
                            this.isDoneTask = true
                        } else if (this.resData.is_inactive_task){
                            this.isInactiveTask = true
                        }
                    } else {
                        // DB登録完了時
                        // this.$refs.steps.setData(JSON.parse(JSON.stringify(res.data)))
                    }
                })
                .catch((err) => {
                    console.log(err);
                })
                .finally(() => {
                    this.loading = false
                });
        },
        openModal: function() {
            let message = ''

            if (!this.resData.is_on_task) {
                message = this.$t('biz.development.improvement._modal.p1');
            } else {
                message = this.$t('biz.development.improvement._modal.p2');
            }

            eventHub.$emit('open-modal',{
                'message': message
            })
        },
        closeModal: function() {
            eventHub.$emit('close-modal')
        },
        // 保存
        submit: function() {
            this.loading = true
            this.resData['process_info'] = this.process_info
            axios.post('/api/biz/development/improvement/store', this.resData)
                .then((res) => {
                    if (res.data.result == 'success') {
                        this.$emit('input', false)
                        eventHub.$emit('open-notify-modal', {message: this.$t('common.message.saved'), emitMessage: this.notify_modal_emit_message})
                    } else if (res.data.result == 'inactivated') {
                        eventHub.$emit('open-notify-modal', {message: this.$t('biz.development.improvement._modal.notify')})
                    } else {
                        eventHub.$emit('open-notify-modal', {message: this.$t('biz.development.improvement._modal.notify')})
                    }
                })
                .catch((err) => {
                    console.log(err);
                })
                .finally(() => {
                    this.loading = false
                });
            this.closeModal()
        },
    }
}
</script>

<style scoped>
.v-btn {
    text-decoration: none;
}
</style>
