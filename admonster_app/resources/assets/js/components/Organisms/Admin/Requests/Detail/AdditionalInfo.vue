<template>
    <div id="additional-info">
        <!-- タイトル部分 -->
        <div v-if="additionalInfos.length > 0">
            <span
                color="transparent"
                depressed
            >
                <span class="subheading font-weight-bold">{{ $t('requests.detail.additional_info.title') }}</span>
                <span>（{{ additionalInfos.length }} {{ $t('list.case') }}）</span>
            </span>
            <v-tooltip top>
                <v-btn
                    icon
                    small
                    color="primary"
                    slot="activator"
                    @click="openCreateFormDialog"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <span>{{ $t('requests.detail.additional_info.add') }}</span>
            </v-tooltip>
        </div>
        <div v-else>
            <a class="subheading font-weight-bold text-underline" @click="openCreateFormDialog">{{ $t('requests.detail.additional_info.link_text_add') }}</a>
        </div>
        <!-- / タイトル部分 -->

        <div id="add-info-list-wrap" v-if="additionalInfos.length > 0">
            <v-card>
                <v-card-text class="pt-1 pb-1">
                    <div v-for="(item, index) in additionalInfos" :key="item.id" class="add-info-list pa-1">

                        <template>
                            <div class="add-info-panel-header"
                                @click="togglePanel(item.id)"
                            >
                                <div>
                                    <template v-if="isHuman(item.updated_user_id)">
                                        <v-avatar slot="activator" size="32px" class="ma-1">
                                            <img :src="user_image_path(item.updated_user_id)">
                                        </v-avatar>
                                    </template>
                                    <template v-else>
                                        <v-avatar slot="activator" size="32px" class="ma-1">
                                            <v-icon slot="activator">android</v-icon>
                                        </v-avatar>
                                    </template>
                                </div>
                                <div class="header-summary ml-2">
                                    <span class="caption font-weight-bold">{{ user_name(item.updated_user_id) }}</span>
                                    <span class="creation-info ml-1" v-if="isUpdate(item)">
                                        <span>(</span>{{ $t('requests.detail.created_user') }} : {{user_name(item.created_user_id) }}<span class="ml-1 mr-1">|</span>{{ $t('requests.detail.created_at') }} : {{ item.created_at | formatDateYmdHm(false, true) }}<span>)</span>
                                    </span>
                                    <br>
                                    <span>
                                        <span class="caption">{{ item.updated_at | formatDateYmdHm(false, true) }}</span>
                                        <span v-show="!openedAddInfo(item.id)" class="ml-3 body-1" v-html="item.content"></span>
                                    </span>
                                </div>
                                <v-spacer></v-spacer>

                                <!-- 編集・削除ボタン -->
                                <div class="content-detail">
                                    <template v-if="openedAddInfo(item.id)">
                                        <v-tooltip top>
                                            <v-btn
                                                slot="activator"
                                                icon
                                                small
                                                flat
                                                color="primary"
                                                @click.stop="confirmChangeWorkOpen(item.id, index)"
                                            >
                                                <v-icon v-if="item.is_open_to_work">mdi-eye-outline</v-icon>
                                                <v-icon v-else>mdi-eye-off-outline</v-icon>
                                            </v-btn>
                                            <span v-if="item.is_open_to_work">{{ $t('requests.detail.work.is_status_open') }}</span>
                                            <span v-else>{{ $t('requests.detail.work.is_status_closed') }}</span>
                                        </v-tooltip>
                                        <v-tooltip top>
                                            <v-btn
                                                slot="activator"
                                                icon
                                                small
                                                @click.stop="confirmChangeOpenFlg(item.id, index, additionalInfos[index].is_open_to_client)"
                                            >
                                                <v-icon v-if="item.is_open_to_client">mdi-eye-outline</v-icon>
                                                <v-icon v-else>mdi-eye-off-outline</v-icon>
                                            </v-btn>
                                            <span v-if="item.is_open_to_client">{{ $t('requests.detail.client.is_status_open') }}</span>
                                            <span v-else>{{ $t('requests.detail.client.is_status_closed') }}</span>
                                        </v-tooltip>
                                    </template>
                                    <template v-if="(userId == item.created_user_id || userId == item.updated_user_id) && openedAddInfo(item.id)">
                                        <v-tooltip top>
                                            <v-btn slot="activator" icon small @click.stop="edit(item.id)">
                                                <v-icon>edit</v-icon>
                                            </v-btn>
                                            <span>{{ $t('common.button.edit') }}</span>
                                        </v-tooltip>
                                        <v-tooltip top>
                                            <v-btn slot="activator" icon small @click.stop="openDeleteConfirmDialog('deleteWithAttachments', item.id, 'deleteAdditionalInfo')"
                                            >
                                                <v-icon>delete_outline</v-icon>
                                            </v-btn>
                                            <span>{{ $t('common.button.delete') }}</span>
                                        </v-tooltip>
                                    </template>
                                </div>
                                <!-- / 編集・削除ボタン -->

                                <!-- 添付ファイルダウンロード用リスト -->
                                <v-menu
                                    bottom
                                    origin="center center"
                                    transition="scale-transition"
                                    open-on-hover>
                                    <template slot="activator">
                                        <v-btn icon v-show="(!openedAddInfo(item.id) && attachments(item.request_additional_info_attachments).length > 0)" @click.prevent="">
                                            <v-icon>attachment</v-icon>
                                        </v-btn>
                                    </template>
                                    <v-list>
                                        <v-list-tile v-for="(file, i) in attachments(item.request_additional_info_attachments)" :key="i" :href="file.download_uri">
                                            <v-list-tile-title>{{ file.file_name }}<v-icon color="primary" class="ml-3">cloud_download</v-icon></v-list-tile-title>
                                        </v-list-tile>
                                    </v-list>
                                </v-menu>
                                <!-- / 添付ファイルダウンロード用リスト -->

                                <v-btn icon>
                                    <v-icon v-if="openedAddInfo(item.id)">keyboard_arrow_up</v-icon>
                                    <v-icon v-else>keyboard_arrow_down</v-icon>
                                </v-btn>
                            </div>
                        </template>

                        <!-- 内容（展開時） -->
                        <div v-show="openedAddInfo(item.id)" style="padding:8px 8px 8px 42px;">
                            <div v-if="isEditId != item.id">
                                <div v-html="item.content"></div>
                                <!-- 添付ファイル -->
                                <template>
                                    <ul class="mt-2 mb-0 pl-0">
                                        <li v-for="(attachment, i) in attachments(item.request_additional_info_attachments)" :key="i">
                                            <div style="display:flex;align-items:flex-end;">
                                                <v-icon>attachment</v-icon>
                                                <a :href="attachment.download_uri" class="ml-1">
                                                {{ attachment.file_name }}
                                                </a>
                                                <v-tooltip top v-if="(userId == item.created_user_id || userId == item.updated_user_id) && openedAddInfo(item.id)">
                                                    <v-icon
                                                        slot="activator"
                                                        small
                                                        class="ml-2"
                                                        style="cursor:pointer;"
                                                        @click.stop="openDeleteConfirmDialog('delete', attachment.file_id, 'deleteAdditionalInfoAttachment')"
                                                    >clear</v-icon>
                                                    <span>{{ $t('common.button.delete') }}</span>
                                                </v-tooltip>
                                            </div>
                                            <template v-if="getFileExtensionType(attachment.download_uri) == 'image'">
                                                <v-img :src="attachment.download_uri" width="300px"></v-img>
                                            </template>
                                            <template v-else-if="getFileExtensionType(attachment.file_path) == 'video'">
                                                <video
                                                    controls
                                                    width="300px"
                                                    :src="attachment.download_uri"
                                                ></video>
                                            </template>
                                        </li>
                                    </ul>
                                </template>
                                <!-- / 添付ファイル -->
                            </div>
                            <div v-else>
                                <v-textarea
                                    v-model="additionalInfos[index].content"
                                    outline
                                    hide-details
                                    class="mb-1"
                                ></v-textarea>
                                <div v-if="uploadFiles.length > 0">
                                    <div class="font-weight-bold mt-3">{{ $t('file_upload.uploading_files') }}</div>
                                    <span>
                                        <span v-for="(file, index) in uploadFiles" :key="index">
                                            <span>
                                                <v-chip
                                                    close
                                                    color="light-blue lighten-2"
                                                    small
                                                    outline
                                                    @input="removeUploadingFile(index)"
                                                >{{ file.file_name }}</v-chip>
                                            </span>
                                        </span>
                                    </span>
                                </div>

                                <!-- ファイルアップロード -->
                                <file-upload :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types"></file-upload>
                                <div class="caption text-xs-right mb-1">
                                    <span>※ {{ $t('file_upload.allowed_extensions') }}</span>&ensp;
                                    <span v-for="(item, index) in allow_file_type_names" :key="index">
                                        <span>{{ item }}</span>
                                        <span v-show="(index + 1 < allow_file_type_names.length)" class="ml-1 mr-1">|</span>
                                    </span>
                                </div>
                                <!-- / ファイルアップロード -->

                                <!-- 処理ボタン -->
                                <div class="text-xs-center">
                                    <v-btn @click="isEditId = null">{{ $t('common.button.cancel') }}</v-btn>
                                    <v-btn @click="updateAdditionalInfo(item.id, index)" color="primary">{{ $t('common.button.save') }}</v-btn>
                                </div>
                                <!-- / 処理ボタン -->

                            </div>
                        </div>
                        <!-- / 内容（展開時） -->
                        <v-divider v-if="index + 1 < additionalInfos.length" class="ma-0"></v-divider>
                    </div>
                </v-card-text>
            </v-card>
        </div>

        <additional-info-form-dialog
            :request-id="requestId"
            :request-work-id="requestWorkId"
            :create-form-dialog.sync="createFormDialog"
            @callGetAdditionalInfos="getAdditionalInfos"
        ></additional-info-form-dialog>

        <delete-confirm-dialog :dialog.sync="deleteConfirmDialog" :type="confirmType" :selected-id="selectedId" :callback="deleteConfirmCallback"></delete-confirm-dialog>

        <confirm-dialog ref="confirm"></confirm-dialog>
        <notify-modal></notify-modal>
    </div>
</template>

<script>
import store from '../../../../../stores/Admin/Requests/Detail/store'
import requestDetailMixin from '../../../../../mixins/Admin/requestDetailMixin'

import AdditionalInfoFormDialog from './AdditionalInfoFormDialog'
import DeleteConfirmDialog from './DeleteConfirmDialog'
import FileUpload from '../../../../Atoms/Upload/FileUpload'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'
import NotifyModal from '../../../../Atoms/Dialogs/NotifyModal'

export default {
    name: 'additionalInfos',
    mixins: [requestDetailMixin],
    props: {
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number },
        candidates: { type: Array, required: true }
    },
    data: () => ({
        deleteConfirmDialog: false,
        confirmType: '',
        deleteConfirmCallback: '',
        selectedId: null,

        dialog: false,
        //loading
        loading: false,
        additionalInfos: [],
        createFormDialog: false,
        isEditId: null,

        openedAddInfoIds: store.state.openedAddInfoIds,
        // サムネイル判定用拡張子
        supportedExtensions: {
            image: ['jpg', 'jpeg', 'png', 'gif'],
            video: ['mp4']
        },

        // ファイルアップロード用
        file_upload_emit_message: 'append-files-for-update-request-add-info',

        // 仮
        uploadFiles: []
    }),
    components:{
        // ProgressCircular
        DeleteConfirmDialog,
        AdditionalInfoFormDialog,
        FileUpload,
        ConfirmDialog,
        NotifyModal
    },
    created () {
        let self = this
        this.getAdditionalInfos()

        eventHub.$on('updateAdditionalInfo', function(selectedId) {
            self.updateAdditionalInfo(selectedId)
        })

        eventHub.$on('deleteAdditionalInfo', function(selectedId) {
            self.deleteAdditionalInfo(selectedId)
        })

        eventHub.$on('deleteAdditionalInfoAttachment', function(selectedId) {
            self.deleteAdditionalInfoAttachment(selectedId)
        })

        // ファイルアップロード用
        eventHub.$on(this.file_upload_emit_message, function(data){
            // check_filesに追加
            for (const file of data.file_list){
                const result = {
                    err_description: '',
                    file_name: file.name,
                    file_size: file.size,
                    file_data: file.data,
                    file_path: '',
                    type: file.type,
                }
                self.uploadFiles.push(result)
            }
        })
    },
    mounted () {
    },
    computed: {
        scrollOptions() {
            return {
                offset: - document.getElementById('header').clientHeight
            }
        },
        openedAddInfo () {
            return function (id) {
                if (this.openedAddInfoIds.indexOf(id) != -1) {
                    return true
                } else {
                    return false
                }
            }
        },
        attachments () {
            return function (attachments) {
                let array = [];
                if (attachments.length > 0) {
                    attachments.forEach(item => {
                        let uri = '/utilities/download_file?file_path='
                        let file_name = item.name
                        let file_path = item.file_path
                        uri = uri + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
                        array.push({
                            file_id: item.id,
                            file_name: file_name,
                            file_path: file_path,
                            download_uri: uri,
                        });
                    });
                }
                return array
            }
        },
        getFileExtensionType () {
            return function (file) {
                if (!file) return
                const extension = file.slice(file.lastIndexOf('.') + 1)
                return Object.keys(this.supportedExtensions).filter(type => (this.supportedExtensions[type].some(support => support == extension))).shift()
            }
        },
        userId () {
            return document.getElementById('login-user-id').value
        },
    },
    methods: {
        // UsersOverviewコンポーネント内と共通。mixinにする
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
        togglePanel(id) {
            const arr = this.openedAddInfoIds
            if (arr.indexOf(id) !== -1) {
                const newArr = arr.filter(n => n !== id)
                this.openedAddInfoIds = newArr
            } else {
                this.openedAddInfoIds.push(id)
            }
            store.commit('setStateObj', { openedAddInfoIds: this.openedAddInfoIds })
        },
        getAdditionalInfos () {
            this.loading = true
            axios.post('/api/request_additional_infos',{
                request_id: this.requestId,
                request_work_id: this.requestWorkId
            })
                .then((res) => {
                    this.additionalInfos = res.data.request_additional_infos
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: 'データの取得に失敗しました。' });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        openCreateFormDialog () {
            this.uploadFiles = []
            this.createFormDialog = true
        },
        edit (id) {
            // 他が編集中の場合、一時アップロード中のファイルをリセット
            // TODO : 一時アップロードファイルを最初からアップロード対象のrequest_additional_infoと紐づける
            if (this.isEditId != id) {
                this.uploadFiles = []
            }
            this.isEditId = id
        },
        updateAdditionalInfo:async function(request_additional_info_id, index) {
            this.loading = true

            // 画像データをblobURL -> base64
            const files = this.uploadFiles
            const convert = this.convertToBase64
            await Promise.all(files.map(async upload_file => upload_file = await convert(upload_file)))
            for (let i = 0; i <  this.uploadFiles.length; i++){
                delete this.uploadFiles[i].type
            }

            axios.post('/api/request_additional_infos/update',{
                request_id: this.requestId,
                request_work_id: this.requestWorkId,
                content: this.additionalInfos[index].content,
                is_open_to_client: this.additionalInfos[index].is_open_to_client,
                is_open_to_work: this.additionalInfos[index].is_open_to_work,
                request_additional_info_id: request_additional_info_id,
                request_additional_info_attachments: this.uploadFiles
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        eventHub.$emit('open-notify-modal', { message: '更新しました。' });
                        this.isEditId = null
                        this.getAdditionalInfos()
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
        deleteAdditionalInfo (request_additional_info_id) {
            this.loading = true
            axios.post('/api/request_additional_infos/delete',{
                request_id: this.requestId,
                request_work_id: this.requestWorkId,
                request_additional_info_id: request_additional_info_id
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                    // alert('削除しました。')
                        eventHub.$emit('open-notify-modal', { message: '削除しました。' });
                        this.getAdditionalInfos()
                    } else if (res.data.result == 'warning') {
                    // alert('削除に失敗しました。')
                        eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                    } else if (res.data.result == 'error') {
                    // alert('削除に失敗しました。')
                        eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                    }
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    // alert('削除に失敗しました。')
                    eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        deleteAdditionalInfoAttachment (request_additional_info_attachment_id) {
            this.loading = true
            axios.post('/api/request_additional_infos/delete_attachment',{
                request_id: this.requestId,
                request_work_id: this.requestWorkId,
                request_additional_info_attachment_id: request_additional_info_attachment_id
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                    // alert('削除しました。')
                        eventHub.$emit('open-notify-modal', { message: '削除しました' });
                        this.getAdditionalInfos()
                    } else if (res.data.result == 'warning') {
                    // alert('削除に失敗しました。')
                        eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                    } else if (res.data.result == 'error') {
                    // alert('削除に失敗しました。')
                        eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                    }
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: '削除に失敗しました。' });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        openDeleteConfirmDialog (type, id, callBack) {
            this.confirmType = type
            this.deleteConfirmCallback = callBack
            this.deleteConfirmDialog = true
            this.selectedId = id
        },
        removeUploadingFile (index) {
            this.uploadFiles.splice(index, 1)
        },
        isUpdate (item) {

            if (item.created_user_id != item.updated_user_id){
                return true;
            } else if (item.created_at != item.updated_at) {
                return true;
            } else {
                return false;
            }
        },
        async confirmChangeOpenFlg (id, index, isOpenToClient) {
            let confirmText = isOpenToClient ? Vue.i18n.translate('requests.detail.client.confirm_to_close') : Vue.i18n.translate('requests.detail.client.confirm_to_open')
            if (await(this.$refs.confirm.show(confirmText))) {
                this.additionalInfos[index].is_open_to_client = isOpenToClient ? _const.FLG.INACTIVE : _const.FLG.ACTIVE
                this.updateAdditionalInfo(id, index)
            }
        },
        async confirmChangeWorkOpen (id, index) {
            const isOpen = this.additionalInfos[index].is_open_to_work
            const confirmText = isOpen ? Vue.i18n.translate('requests.detail.work.confirm_to_close') : Vue.i18n.translate('requests.detail.work.confirm_to_open')
            if (await(this.$refs.confirm.show(confirmText))) {
                this.additionalInfos[index].is_open_to_work = isOpen ? _const.FLG.INACTIVE : _const.FLG.ACTIVE;
                this.updateAdditionalInfo(id, index)
            }
        }
    }
}
</script>
<style scoped>
.add-info-panel-header {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.add-info-panel-header .header-summary {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.add-info-panel-header .header-summary .creation-info {
    font-size: 10px;
}
.add-info-panel-header .content-detail {
    display:flex;
    align-items:center;
}
</style>
