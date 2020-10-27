<template>
    <v-app id="verification">
        <app-menu :drawer="drawer"></app-menu>
        <app-header
            :title="request ? request.business_name : ''"
            :subtitle="'検証'"
        ></app-header>
        <v-content>
            <v-container fluid grid-list-md v-if="request">
                <v-layout row>
                    <!-- 実作業画面の左側と同じものを表示？ -->
                    <v-flex :class="showRequestContent ? 'xs12 md6' : ''">
                        <request-contents
                            :request-work-id="Number(inputs['request_work_id'])"
                            :request="request"
                            v-model="showRequestContent"
                            :candidates="businessesCandidates"
                            :class="requestContentsClass"
                            :label-data="labelData"
                            height="70vh + 50px"
                        >
                        </request-contents>
                    </v-flex>
                    <v-flex :class="showRequestContent ? 'xs12 md6' : 'width-excluding-pre-element'">
                        <task-result-comparative
                            ref="workComparative"
                            :short-display="showRequestContent"
                        ></task-result-comparative>
                    </v-flex>
                </v-layout>
                <v-layout row>
                    <v-flex xs12>
                        <v-card
                            class="edit-note"
                            @click="enterCommentIfNeeded(getComment())"
                        >
                            <v-card-text
                                :class="['global-comment', placeholder ? 'placeholder' : '']"
                                v-html="getComment()"
                                :data-placeholder="$t('verification.total_comment')"
                            ></v-card-text>
                            <div class="edit-note-icon">
                                <v-tooltip right>
                                    <v-icon
                                        slot="activator"
                                        color="grey"
                                    >
                                        mdi-pencil
                                    </v-icon>
                                    <span>{{ $t('verification.click_to_enter') }}</span>
                                </v-tooltip>
                            </div>
                        </v-card>
                    </v-flex>
                </v-layout>
                <v-layout row>
                    <v-flex xs12>
                        <div class="btn-center-block">
                            <v-btn v-if="edit"
                                color="primary"
                                @click="clickSave()"
                            >{{ $t('common.button.save') }}</v-btn>
                        </div>
                    </v-flex>
                </v-layout>
                <page-footer back-button></page-footer>
            </v-container>
            <alert-dialog ref="alert"></alert-dialog>
            <progress-circular v-if="loading"></progress-circular>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <editor-dialog
                :title="$t('verification.note')"
                :placeholder="$t('verification.enter_comment')"
                ref="editorDialog"
            ></editor-dialog>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import TaskResultComparative from '../../../Organisms/Work/Verification/TaskResultComparative'
import RequestContents from '../../../Organisms/Common/RequestContents'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import EditorDialog from '../../../Atoms/Dialogs/EditorDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import store from '../../../../stores/Work/Verification/store'

export default {
    props: {
        inputs: { type: Object },
    },
    components: {
        PageFooter,
        TaskResultComparative,
        RequestContents,
        ProgressCircular,
        AlertDialog,
        EditorDialog,
        ConfirmDialog
    },
    data: () => ({
        drawer: false,
        showRequestContent: false,
        dialog: false,
        placeholder: false,
    }),
    computed: {
        edit() {
            return JSON.parse(JSON.stringify(store.state.processingData.edit))
        },
        request() {
            return JSON.parse(JSON.stringify(store.state.processingData.request))
        },
        taskResults() {
            return JSON.parse(JSON.stringify(store.state.processingData.taskResults))
        },
        startedAt() {
            return JSON.parse(JSON.stringify(store.state.processingData.startedAt))
        },
        businessesCandidates() {
            return JSON.parse(JSON.stringify(store.state.processingData.businessesCandidates))
        },
        labelData() {
            return store.state.processingData.labelData
        },
        taskComment() {
            return JSON.parse(JSON.stringify(store.state.processingData.taskComment))
        },
        requestContentsClass() {
            let classText = []
            const requestMail = this.request.request_mail
            if (requestMail) {
                classText.push(requestMail.mail_attachments.length > 0 ? '' : 'no-mail-attachment')
            }
            classText.push(this.request.request_file ? 'exist-request-file' : '')
            return classText
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
    methods: {
        getComment () {
            const comment = this.taskComment.global_comment
            this.placeholder = comment ? false : true
            return comment
        },
        async enterCommentIfNeeded(comment) {
            if (!this.edit) return// 編集不可能の場合は処理しない
            const {value, isEnter} = await this.$refs.editorDialog.show(comment)
            this.setComment(isEnter ? value : comment)
        },
        setComment (comment) {
            let taskComment = this.taskComment
            taskComment.global_comment = comment
            store.commit('setProcessingData', {taskComment: taskComment})
        },
        async clickSave () {
            if (await this.$refs.confirm.show(Vue.i18n.translate('common.message.save_confirm'))) {
                const isSuccess = await this.save()
                if (isSuccess) this.$refs.alert.show(Vue.i18n.translate('common.message.saved'), () => {
                    if (window.history.length > 1) {
                        window.history.back()
                    } else {
                        window.location.href = '/tasks'
                    }
                })
            }
        },
        async save () {
            try {
                this.loading = true
                let params = new FormData()
                params.append('request_id', this.request.id)
                params.append('task_id', this.inputs.task_id)
                params.append('request_work_id', this.request.request_work_id)
                params.append('started_at', this.startedAt.date)
                params.append('task_comment', JSON.stringify(this.taskComment))

                const res = await axios.post('/api/verification/store',  params)
                this.loading = false
                if (res.data.result == 'success') {
                    return true
                } else {
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('common.message.internal_error')
                    )
                    return false
                }
            } catch (error) {
                console.log(error)
                this.loading = false
                // ダイアログ表示
                this.$refs.alert.show(
                    Vue.i18n.translate('common.message.internal_error')
                )
                return false
            }

        },
        async back () {
            if (store.getters.isNonUpdatedData || await this.$refs.confirm.show(Vue.i18n.translate('approvals.dialog.back.text'))) {
                if (window.history.length >= 2) {
                    // ブラウザバック
                    window.history.back()
                } else {
                    // タスク一覧へ
                    window.location.href = '/tasks'
                }
            }
        },
    },
    async created() {
        // メインカラー変更[primary ⇔ accent]
        const primaryColor = this.$vuetify.theme.primary
        this.$vuetify.theme.primary = this.$vuetify.theme.accent
        this.$vuetify.theme.accent = primaryColor

        store.commit('setProcessingData', {inputs: this.inputs})
        this.loading = true
        try {
            await store.dispatch('tryRefreshVerification', store.state.processingData.inputs)
        } catch (error) {
            console.log(error)
            // ダイアログ表示
            this.$refs.alert.show(
                Vue.i18n.translate('common.message.internal_error'),
                function () {
                    window.location.href = '/tasks'
                }
            )
        } finally {
            this.loading = false
        }
    },
}
</script>

<style scoped>
.width-excluding-pre-element {
    width: calc(100% - 64px)
}
.edit-note {
    cursor: pointer;
    margin: 10px 150px;
    border-radius: 0;
}
.edit-note-icon {
    position: absolute;
    display: none;
    bottom: 0px;
    right: 0px;
    padding: 8px;
}
.edit-note-icon:after {
    position: absolute;
}
.edit-note:hover .edit-note-icon {
    display: inline;
}
.global-comment {
    min-height: 90px;
    max-height: 150px;
    overflow-y: auto;
    white-space: normal;
    vertical-align: top;
}
.global-comment.placeholder::before {
    color: rgba(0,0,0,0.3);
    content: attr(data-placeholder);
    font-style: italic;
}
</style>
<style>
.global-comment p {
    margin-bottom: 0;
}
</style>