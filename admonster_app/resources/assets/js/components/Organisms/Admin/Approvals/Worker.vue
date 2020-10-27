<template>
    <div class="head-component mb-2">
        <!-- 横向き -->
        <template v-if="isHorizontalOrientation === true">
            <div :class="['head-border', (selected.includes(task.user_id) ? 'bg-success' : '')]">
                <!-- icons -->
                <v-layout justify-space-around row >
                    <div v-if="edit" class="mt-1 ml-1">
                        <v-tooltip top>
                            <template slot="activator">
                                <v-icon color="gray" @click="canAgainWorkRequest">mdi-undo-variant</v-icon>
                            </template>
                            <span v-html="$t('approvals.tooltip_text.again_work')"></span>
                        </v-tooltip>
                    </div>
                    <v-spacer></v-spacer>
                    <div class="mt-1 mr-1">
                        <v-tooltip top>
                            <template slot="activator">
                                <v-icon color="gray" class="pin-icon" @click="clickPin">mdi-pin</v-icon>
                            </template>
                            <span v-html="$t('approvals.tooltip_text.fix_to_head')"></span>
                        </v-tooltip>
                    </div>
                </v-layout>
                <!-- /icons -->
                <v-layout row align-space-between justify-start fill-height >
                    <v-checkbox
                        v-model="selected"
                        name="user_ids"
                        :value="task.user_id"
                        :input-value="task.user_id"
                        :disabled="!edit || isDoneTaskResultType"
                        hide-details
                        color="primary"
                        class="head-check-box mt-0 ml-1 align-items-center"
                    >
                    </v-checkbox>
                    <v-flex xs12>
                        <v-layout row align-center justify-start>
                            <!-- 処理済みタスク -->
                            <template v-if="isDoneTask">
                                <div class="pa-2">
                                    <v-tooltip top class="mouse-hover-pointer">
                                        <template slot="activator">
                                            <v-avatar class="user-image" :size="iconSize" tile>
                                                <img :src="task.user_image_path"  @click="openTaskResult()">
                                            </v-avatar>
                                        </template>
                                        <span v-html="$t('approvals.window_open.biz')"></span>
                                    </v-tooltip>
                                </div>
                                <v-tooltip top class="mouse-hover-pointer">
                                    <template slot="activator">
                                        <div class="mr-2 word-break-all white-space-nomal user-name" @click="openTaskResult()">
                                            {{ task.user_name }}
                                        </div>
                                    </template>
                                    <span v-html="$t('approvals.window_open.biz')"></span>
                                </v-tooltip>
                            </template>
                            <!-- /処理済みタスク -->
                            <!-- 未処理タスク -->
                            <template v-else>
                                <div class="pa-2 user-image">
                                    <v-avatar :size="iconSize" tile>
                                        <img :src="task.user_image_path">
                                    </v-avatar>
                                </div>
                                <div class="mr-2 word-break-all white-space-nomal user-name">
                                    {{ task.user_name }}
                                </div>
                            </template>
                            <!-- /未処理タスク -->
                        </v-layout>
                    </v-flex>
                </v-layout>
            </div>
        </template>
        <!-- /横向き -->
        <!-- 縦向き -->
        <template v-else>
            <v-layout row wrap align-center justify-center>
                <v-checkbox
                    v-model="selected"
                    name="user_ids"
                    :value="task.user_id"
                    :input-value="task.user_id"
                    :disabled="!edit || !isDoneTaskResultType"
                    hide-details
                    color="primary"
                    class="justify-center head-check-box mt-0 pt-0"
                >
                </v-checkbox>
            </v-layout>
            <v-layout row wrap align-center justify-center>
                <div :class="['head-border', (selected.includes(task.user_id) ? 'bg-success' : '')]">
                    <!-- icons -->
                    <v-flex xs12 >
                        <div class="head-icons">
                            <v-layout align-center justify-space-around row fill-heigh>
                                <v-tooltip top>
                                    <template slot="activator">
                                        <v-icon color="gray" class="pin-icon" @click="clickPin">mdi-pin</v-icon>
                                    </template>
                                    <span v-html="$t('approvals.tooltip_text.fix_to_head')"></span>
                                </v-tooltip>
                                <v-spacer></v-spacer>
                                <template v-if="edit">
                                    <v-tooltip top>
                                        <template slot="activator">
                                            <v-icon color="gray" @click="canAgainWorkRequest">mdi-undo-variant</v-icon>
                                        </template>
                                        <span v-html="$t('approvals.tooltip_text.again_work')"></span>
                                    </v-tooltip>
                                </template>
                            </v-layout>
                        </div>
                    </v-flex>
                    <!-- /icons -->
                    <!-- ユーザ情報 -->
                    <v-flex xs12>
                        <!-- 処理済みタスク -->
                        <template v-if="isDoneTask">
                            <v-tooltip top class="mouse-hover-pointer">
                                <template slot="activator" >
                                    <v-avatar class="user-image" :size="iconSize" tile >
                                        <img :src="task.user_image_path"  @click="openTaskResult()">
                                    </v-avatar>
                                    <div class="white-space-nomal word-break-all user-name" @click="openTaskResult()">{{ task.user_name }}</div>
                                </template>
                                <span v-html="$t('approvals.window_open.biz')"></span>
                            </v-tooltip>
                        </template>
                        <!-- /処理済みタスク -->
                        <!-- 未処理タスク -->
                        <template v-else>
                            <v-avatar class="user-image" :size="iconSize" tile >
                                <img :src="task.user_image_path">
                            </v-avatar>
                            <div class="white-space-nomal word-break-all user-name">{{ task.user_name }}</div>
                        </template>
                        <!-- /未処理タスク -->
                    </v-flex>
                    <!-- /ユーザ情報 -->
                    <!-- 承認結果 -->
                    <v-flex xs12>
                        <v-layout justify-center>
                            <v-flex>
                                <div class="approval-result-checkbox ok-checkbox">
                                    <v-checkbox
                                        v-model="isOK"
                                        color="green"
                                        label="OK"
                                        :disabled="!edit || selected.includes(task.user_id) || !isDoneTaskResultType"
                                    ></v-checkbox>
                                </div>
                            </v-flex>
                            <v-flex>
                                <div class="approval-result-checkbox ng-checkbox">
                                    <v-checkbox v-model="isNG" color="red" label="NG" :disabled="!edit"></v-checkbox>
                                </div>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                    <!-- /承認結果 -->
                </div>
            </v-layout>
        </template>
        <!-- /縦向き -->
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <again-work-confirm-dialog ref="againWorkConfirm"></again-work-confirm-dialog>
        <order-work-dialog ref="orderWorkConfirm"></order-work-dialog>
    </div>
</template>

<script>
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AgainWorkConfirmDialog from './AgainWorkConfirmDialog'
import OrderWorkDialog from '../../../Organisms/Admin/Approvals/OrderWorkDialog'
import store from '../../../../stores/Admin/Approvals/Detail/store'


export default {
    components: {
        AlertDialog,
        ConfirmDialog,
        AgainWorkConfirmDialog,
        OrderWorkDialog
    },
    props:{
        task: {type: Object, required: true},
        stepPath: {type: String, required: false, default: ''},
        iconSize: {type: [String, Number], required: false, default: 48},
        isHorizontalOrientation: {type: Boolean, required: false, default: false},
    },
    computed: {
        isDoneTaskResultType() {
            if (this.task.content === null) return false
            return this.task.content.results.type === _const.TASK_RESULT_TYPE.DONE
        },
        isDoneTask() {
            return this.task.status === _const.TASK_STATUS.DONE
        },
        edit() {
            return this.isDoneTask && store.state.processingData.edit
        },
        selected: {
            set(val) {
                // チェックで選択される場合は、最終承認に持っていきたいので、自動でOKにチェックを入れる
                if (val.includes(this.task.user_id)) this.isOK = true
                store.commit('setProcessingData', {selected: val})
            },
            get() {
                return JSON.parse(JSON.stringify(store.state.processingData.selected))
            }
        },
        approvalTasks: {
            set(val) {
                store.commit('setProcessingData', {approvalTasks: val})
            },
            get() {
                return JSON.parse(JSON.stringify(store.state.processingData.approvalTasks))
            }
        },
        isOK: {
            set(val) {
                const approvalTasks = this.approvalTasks

                // OKは処理速度を考慮し、for...ofを使用
                for (const appTask of approvalTasks) {
                    if (appTask.task_id === this.task.task_id) {
                        appTask.approval_result = val? _const.APPROVAL_RESULT.OK : null
                        break
                    }
                }
                this.approvalTasks = approvalTasks

            },
            get() {
                const appTask = this.approvalTasks.find(appTask => appTask.task_id === this.task.task_id)
                return (appTask.approval_result === _const.APPROVAL_RESULT.OK)

            }
        },
        isNG: {
            set(val) {
                // チェックの書き換え
                const approvalTasks = this.approvalTasks
                const approvalTask = approvalTasks.find( appTask => appTask.task_id === this.task.task_id)
                approvalTask.approval_result = val? _const.APPROVAL_RESULT.NG : null
                this.approvalTasks = approvalTasks

                // 承認チェックが入っている状態では、承認チェックを外す
                if (val && this.selected.includes(this.task.user_id)) {
                    const excludedArray = this.selected.filter( id => id !== this.task.user_id)
                    this.selected = excludedArray
                }
            },
            get() {
                const appTask = this.approvalTasks.find(appTask => appTask.task_id === this.task.task_id)
                return (appTask.approval_result === _const.APPROVAL_RESULT.NG)

            }
        },
        request_work_updated_at: {
            set(val) {
                store.commit('setProcessingData', {request_work_updated_at: val})
            },
            get() {
                return store.state.processingData.request_work_updated_at
            }
        },
        checkApprovalTasks: {
            set(val) {
                store.commit('setCheckData', {approvalTasks: val})
            },
            get() {
                return JSON.parse(JSON.stringify(store.state.checkData.approvalTasks))
            }
        },
        loading: {
            set(val) {
                store.commit('setProcessingData', {loading: val})
            },
            get() {
                return store.state.processingData.loading
            }
        }
    },
    created() {
    },
    methods: {
        async canAgainWorkRequest() {

            // 再作業を依頼するか訪ねるダイアログを表示
            // const canProcess = await this.$refs.againWorkConfirm.show(this.task.user_name + Vue.i18n.translate('approvals.dialog.reject.text'))
            // if (!canProcess) return

            let needsOpenOWConfirm = true
            let orderComment = ''
            while (needsOpenOWConfirm) {

                // 担当者へのコメントのダイアログ
                const {result, comment} = await this.$refs.orderWorkConfirm.show(this.task.user_name, orderComment)
                if (!result) return
                orderComment = comment

                // 最終確認
                if (await this.$refs.confirm.show(this.task.user_name + Vue.i18n.translate('approvals.dialog.order_work.text'))) needsOpenOWConfirm = false
            }

            try {
                this.loading = true

                // 再作業の依頼
                let params = new FormData()
                params.append('request_work_id', store.state.processingData.request.request_work_id)
                params.append('user_id', this.task.user_id)
                params.append('message', orderComment)
                params.append('task_id', this.task.task_id)
                params.append('request_work_updated_at', this.request_work_updated_at)
                const res = await axios.post('/api/approvals/againTask',  params)

                if (res.data.status === _const.API_STATUS_CODE.SUCCESS) {
                    this.request_work_updated_at = res.data.request_work.updated_at
                    // 情報の最新化
                    await store.dispatch('tryRefreshApprovals', store.state.processingData.inputs)

                    // 完了アラート
                    this.$refs.alert.show(
                        Vue.i18n.translate('approvals.alert.order_work.text')
                    )
                } else if (res.data.status === _const.API_STATUS_CODE.EXCLUSIVE) {
                    this.$refs.alert.show(
                        '<div>' + this.$t('common.message.updated_by_others') + '</div>'
                        + '<div>' + this.$t('common.message.reload_screen') + '</div>',
                        () => window.location.reload()
                    )
                } else {
                    this.$refs.alert.show(
                        Vue.i18n.translate('common.message.internal_error')
                    )
                }
            } catch (error) {
                this.$refs.alert.show(
                    Vue.i18n.translate('common.message.internal_error')
                )
            } finally {
                this.loading = false
            }
        },
        openTaskResult () {
            // 作業画面を別ウインドウ表示
            const stepPath =  this.stepPath.replace(/\./g,'/')
            const uri = '/' + [stepPath, this.task.request_work_id, this.task.task_id, 'create'].join('/')
            window.open(uri)
        },
        clickPin() {
            this.$emit('click-pin', {task_id: this.task.task_id})
        },
        clickDelete() {
            this.$emit('click-delete', {user_id: this.task.user_id})
        },
        async saveHold (apptask) {
            try {
                this.loading = true
                let params = new FormData()
                params.append('request_id', store.state.processingData.request.id)
                params.append('request_work_id', store.state.processingData.request.request_work_id)
                params.append('approval_status', _const.APPROVAL_STATUS.ON)
                params.append('approval_tasks', JSON.stringify([apptask]))
                params.append('request_work_updated_at', this.request_work_updated_at)

                const res = await axios.post('/api/approvals/store',  params)
                this.loading = false

                if (res.data.status === _const.API_STATUS_CODE.SUCCESS) {
                    this.request_work_updated_at = res.data.request_work.updated_at
                    // store.state.checkDataを編集
                    const checkApprovalTasks = this.checkApprovalTasks
                    checkApprovalTasks.forEach(checkAppTask => {
                        if (checkAppTask.task_id === apptask.task_id) checkAppTask.approval_result = apptask.approval_result
                    });
                    this.checkApprovalTasks = checkApprovalTasks

                    return true
                } else if (res.data.status === _const.API_STATUS_CODE.EXCLUSIVE) {
                    this.$refs.alert.show(
                        '<div>' + this.$t('common.message.updated_by_others') + '</div>'
                        + '<div>' + this.$t('common.message.reload_screen') + '</div>',
                        () => window.location.reload()
                    )
                    return false
                } else {
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('common.message.internal_error')
                    )
                    return false
                }
            } catch (error) {
                this.loading = false
                // ダイアログ表示
                this.$refs.alert.show(
                    Vue.i18n.translate('common.message.internal_error')
                )
                return false
            }
        },
    }
}
</script>

<style>
.head-component .head-check-box .v-input--selection-controls__input {
    margin-right: 0px;
}
</style>

<style scoped>
.approval-result-checkbox  .v-input--selection-controls {
    margin-top: 0;
    justify-content: center;
}
.approval-result-checkbox.ok-checkbox  .v-input--selection-controls {
    justify-content: flex-end;
}
.approval-result-checkbox.ng-checkbox  .v-input--selection-controls {
    justify-content: start;
}
.approval-result-checkbox >>> .v-input__slot {
    margin: 0;
}
.approval-result-checkbox >>> .v-messages {
    min-height: 0;
}

.align-items-center {
    align-items: center;
}

.head-border {
    width: 100%;
    border-style: solid;
    border-width: thin;
}
.bg-OK {
    background-color: #a7ffeb;
}

.head-icons {
    justify-content: space-between;
}

.white-space-nomal {
    white-space: normal
}

.word-break-all {
    word-break: break-all;
}

.mouse-hover-pointer:hover {
    cursor: pointer;
}
</style>


