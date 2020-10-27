<template>
    <div id="related-mails">
        <!-- タイトル部分 -->
        <div class="related-mail-title">
            <span
                color="transparent"
                depressed
            >
                <span class="subheading font-weight-bold">{{ $t('requests.detail.related_mails.title') }}</span>
                <span>（{{ relatedMails.length }} {{ $t('list.case') }}）</span>
            </span>
            <v-tooltip top>
                <v-btn
                    icon
                    small
                    color="primary"
                    slot="activator"
                    @click="createFormDialog = true"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <span>{{ $t('requests.detail.related_mails.import') }}</span>
            </v-tooltip>
            <v-spacer></v-spacer>
            <v-btn
                icon
                color="primary"
                slot="activator"
                @click="openAllRelatedMails()"
            >
                <v-icon v-if="unfoldFlag">unfold_less</v-icon>
                <v-icon v-else>unfold_more</v-icon>
            </v-btn>
        </div>
        <create-mail-alias-dialog
            :create-form-dialog.sync="createFormDialog"
            :request-id="requestId"
        >
        </create-mail-alias-dialog>
        <!-- / タイトル部分 -->

        <div id="related-mail-list-wrap">
            <v-card>
                <v-card-text class="pt-2 pb-2">
                    <div v-for="(item, index) in relatedMails" :key="item.id" class="add-info-list pa-1">
                        <template >
                            <div class="related-mail-panel-header ma-0"
                                 @click="togglePanel(item.id)"
                            >
                                <div>
                                    <span><v-icon>mail_outline</v-icon></span>
                                </div>
                                <template v-if="openRelatedMail(item.id)">
                                    <div class="related-mail-from ml-3" style="min-width: 120px;max-width: 120px">
                                        <span class="font-weight-bold body-1">{{ from(item) }}</span>
                                    </div>
                                    <div class="related-mail-subject ml-3">
                                        <span class="ml-1 body-1 font-weight-bold">{{item.subject}}</span>
                                        <span class="caption">
                                            <span class="ml-1 mr-1">-</span>
                                            <div class="d-inline-block" v-html="item.body"></div>
                                        </span>
                                    </div>
                                    <v-spacer></v-spacer>

                                    <!-- ボタン -->
                                    <div>
                                        <v-tooltip top>
                                            <v-btn
                                                slot="activator"
                                                icon
                                                small
                                                flat
                                                color="primary"
                                                @click.stop="confirmChangeWorkOpen(item.id, index)"
                                            >
                                                <v-icon v-if="item.pivot.is_open_to_work">mdi-eye-outline</v-icon>
                                                <v-icon v-else>mdi-eye-off-outline</v-icon>
                                            </v-btn>
                                            <span v-if="item.pivot.is_open_to_work">{{ $t('requests.detail.work.is_status_open') }}</span>
                                            <span v-else>{{ $t('requests.detail.work.is_status_closed') }}</span>
                                        </v-tooltip>
                                        <v-tooltip top>
                                            <v-btn
                                                slot="activator"
                                                icon
                                                small
                                                @click.stop="confirmChangeOpenFlg(item.id, index, relatedMails[index].pivot.is_open_to_client)"
                                            >
                                                <v-icon v-if="item.pivot.is_open_to_client">mdi-eye-outline</v-icon>
                                                <v-icon v-else>mdi-eye-off-outline</v-icon>
                                            </v-btn>
                                            <span v-if="item.pivot.is_open_to_client">{{ $t('requests.detail.client.is_status_open') }}</span>
                                            <span v-else>{{ $t('requests.detail.client.is_status_closed') }}</span>
                                        </v-tooltip>
                                    </div>
                                    <div v-if="(userId == item.pivot.created_user_id) || (userId == item.pivot.updated_user_id)" class="content-detail">
                                        <v-tooltip top>
                                            <v-btn slot="activator" icon small @click.stop="openDeleteConfirmDialog('delete', item.id, 'deleteRelatedMail')"
                                            >
                                                <v-icon>delete_outline</v-icon>
                                            </v-btn>
                                            <span>{{ $t('common.button.delete') }}</span>
                                        </v-tooltip>
                                    </div>
                                    <!-- / ボタン -->

                                    <div class="related-mail-time ml-1" style="min-width:100px">
                                        <span class="caption ">{{item.created_at | formatDateYmdHm(false, true)}}</span>
                                    </div>
                                    <v-btn icon small>
                                        <v-icon>keyboard_arrow_down</v-icon>
                                    </v-btn>
                                </template>
                                <template v-else>
                                    <v-spacer></v-spacer>
                                    <v-btn icon small>
                                        <v-icon>keyboard_arrow_up</v-icon>
                                    </v-btn>
                                </template>
                            </div>

                            <!-- 添付ファイル -->
                            <div v-show="(openRelatedMails.indexOf(item.id) == -1) ? true : false">
                                <ul class="related-mail-files">
                                    <li v-for="(attachment, i) in item.request_mail_attachments" :key="i">
                                        <div>
                                            <v-tooltip top v-if="i == 0">
                                                <v-btn slot="activator" icon small @click="togglePanel(item.id)">
                                                    <v-icon>attachment</v-icon>
                                                </v-btn>
                                                <span>添付ファイル</span>
                                            </v-tooltip>
                                            <v-tooltip top v-if="i <= 2">
                                                <v-chip slot="activator" color="primary"  small outline  @click="mailAttachmentDownload(attachment)">
                                                    <span id="attachment-name">{{ attachment.name }}</span></v-chip>
                                                <span>{{ attachment.name }}</span>
                                            </v-tooltip>
                                            <v-tooltip top v-if="i == 3">
                                                <v-chip slot="activator" color="primary" small text-color="white"  @click="togglePanel(item.id)"><span style="cursor: pointer;">+{{ item.request_mail_attachments.length - 3 }}</span></v-chip>
                                                <span>他{{ item.request_mail_attachments.length - 3 }}件の添付ファイル</span>
                                            </v-tooltip>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <!-- / 添付ファイル -->
                            <v-flex xs12 v-if="(openRelatedMails.indexOf(item.id) == -1) ? false : true">
                                <div id="related-mail-content-wrap">
                                    <related-mail :mail="item"></related-mail>
                                </div>
                            </v-flex>
                        </template>

                        <v-divider color="#BDBDBD" v-if="index + 1 < relatedMails.length" class="ma-0"></v-divider>
                    </div>
                </v-card-text>
            </v-card>
        </div>
        <delete-confirm-dialog :dialog.sync="deleteConfirmDialog" :type="confirmType" :selected-id="selectedId" :callback="deleteConfirmCallback"></delete-confirm-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </div>
</template>

<script>
import store from '../../../../../stores/Admin/Requests/Detail/store'
import RelatedMail from '../../../../Molecules/Mails/RelatedMail'
import DeleteConfirmDialog from './DeleteConfirmDialog'
import CreateMailAliasDialog from './CreateMailAliasDialog'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'

export default {
    name: 'RelatedMails',
    components: {
        RelatedMail,
        DeleteConfirmDialog,
        CreateMailAliasDialog,
        ConfirmDialog
    },
    props: {
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number },
        requestRelatedMails: { type: Array, required: true }
    },
    data: ()=>({
        unfoldFlag: store.state.unfoldFlag,

        deleteConfirmDialog: false,
        confirmType: '',
        deleteConfirmCallback: '',
        selectedId: null,

        openRelatedMails: store.state.openRelatedMails,
        createFormDialog: false,
        relatedMails:[]
    }),
    computed: {
        openRelatedMail() {
            let self = this
            return function (relatedMailId) {
                return self.openRelatedMails.indexOf(relatedMailId) == -1
            }
        },
        from () {
            return function (mail) {
                const from = mail.pivot.from
                return from ? from : mail.from.split('&lt;')[0]
            }
        },
        userId () {
            return document.getElementById('login-user-id').value
        },
    },
    watch: {
        openRelatedMails (){
            if (this.openRelatedMails.length == this.relatedMails.length){
                this.unfoldFlag = true
            }
            if (this.openRelatedMails.length == 0){
                this.unfoldFlag = false
            }
            store.commit('setStateObj', {unfoldFlag: this.unfoldFlag})
        }
    },
    created () {
        this.relatedMails = this.requestRelatedMails
        let self = this
        eventHub.$on('deleteRelatedMail', function (selectedId) {
            self.deleteRelatedMail(selectedId)
        })
    },
    methods: {
        // UsersOverviewコンポーネント内と共通。mixinにする
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        openDeleteConfirmDialog (type, id, callBack) {
            this.confirmType = type
            this.deleteConfirmCallback = callBack
            this.deleteConfirmDialog = true
            this.selectedId = id
        },
        deleteRelatedMail (request_mail_id) {
            this.loading = true
            axios.post('/api/requests/request_related_mails/delete',{
                request_id: this.requestId,
                request_mail_id: request_mail_id
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        eventHub.$emit('open-notify-modal', { message: '削除しました。' });
                        this.getRequestRelatedMails()
                    } else if (res.data.result == 'error') {
                        eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        updateRelatedMail (request_mail_id, index) {
            axios.post('/api/requests/request_related_mails/update',{
                request_id: this.requestId,
                request_mail_id: request_mail_id,
                is_open_to_client: this.relatedMails[index].pivot.is_open_to_client,
                is_open_to_work: this.relatedMails[index].pivot.is_open_to_work
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        eventHub.$emit('open-notify-modal', { message: '更新しました。' });
                        this.getRequestRelatedMails()
                    } else if (res.data.result == 'warning') {
                        eventHub.$emit('open-notify-modal', { message: '更新に失敗しました。' });
                    } else if (res.data.result == 'error') {
                        eventHub.$emit('open-notify-modal', { message: '更新に失敗しました。' });
                    }
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: '更新に失敗しました。' });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        getRequestRelatedMails () {
            axios.post('/api/requests/request_related_mails',{
                request_id: this.requestId,
                request_work_id: this.requestWorkId
            })
                .then((res) => {
                    console.log('依頼関連メールAPIデータ')
                    console.log(res.data)
                    if (res.data.related_mails == 0){
                        this.$emit('update-related-mails', res.data.related_mails)
                    } else {
                        this.relatedMails = res.data.related_mails
                    }

                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        togglePanel (id) {
            const arr = this.openRelatedMails
            if (arr.indexOf(id) !== -1){
                const newArr = arr.filter(n => n !== id)
                this.openRelatedMails = newArr
            } else {
                this.openRelatedMails.push(id)
            }
            store.commit('setStateObj', {openRelatedMails: this.openRelatedMails})
        },
        openAllRelatedMails () {
            const arr = []
            if (this.relatedMails.length > 0 && this.unfoldFlag == false) {
                this.relatedMails.forEach(function (relatedMail) {
                    arr.push(relatedMail.id)
                })
                this.unfoldFlag = true
            } else {
                this.unfoldFlag = false
            }
            this.openRelatedMails = arr
            store.commit('setStateObj', {openRelatedMails: this.openRelatedMails})
        },
        mailAttachmentDownload (attachment) {
            if (attachment) {
                let uri = '/utilities/download_file?file_path='
                let file_name = attachment.name
                let file_path = attachment.file_path
                uri = uri + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
                window.location.href = uri
            }
        },
        async confirmChangeOpenFlg (request_mail_id, index, isOpenToClient) {
            let confirmText = isOpenToClient ? Vue.i18n.translate('requests.detail.client.confirm_to_close') : Vue.i18n.translate('requests.detail.client.confirm_to_open')
            if (await(this.$refs.confirm.show(confirmText))) {
                this.relatedMails[index].pivot.is_open_to_client = isOpenToClient ? _const.FLG.INACTIVE : _const.FLG.ACTIVE;
                this.updateRelatedMail(request_mail_id, index)
            }
        },
        async confirmChangeWorkOpen (request_mail_id, index) {
            const isOpen = this.relatedMails[index].pivot.is_open_to_work
            const confirmText = isOpen ? this.$t('requests.detail.work.confirm_to_close') : this.$t('requests.detail.work.confirm_to_open')
            if (await(this.$refs.confirm.show(confirmText))) {
                this.relatedMails[index].pivot.is_open_to_work = isOpen ? _const.FLG.INACTIVE : _const.FLG.ACTIVE;
                this.updateRelatedMail(request_mail_id, index)
            }
        },
    }
}
</script>

<style scoped>
.related-mail-panel-header{
    display: flex;
    align-items: center;
    cursor: pointer;
}
.related-mail-title, .related-mail-files{
    display: flex;
    align-items: center;
}
.related-mail-panel-header .related-mail-subject, .related-mail-from{
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#related-mail-content-wrap {
    overflow-x: hidden;
    overflow-y: auto;
    max-height: 615px;
}
#attachment-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 80px;
    cursor: pointer;
}
</style>
