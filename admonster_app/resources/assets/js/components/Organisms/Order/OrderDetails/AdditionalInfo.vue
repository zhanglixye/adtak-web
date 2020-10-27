<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.additional_info.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            hide-edit-button
            hide-other-button
            @previous="previous"
            @next="next"
            @shrink-right="shrinkRight"
            @shrink-left="shrinkLeft"
            @grow="grow"
        >
        </common-header>
        <div
            :style="{
                'height': contentHeight,
                'overflow': 'auto',
            }"
            class="text-center"
        >
            <!-- 補足情報登録 -->
            <v-card v-if="isEdit">
                <v-card-title class="headline" primary-title>{{ $t('request_logs.types.' + editType) }}</v-card-title>
                <v-card-text>
                    <v-textarea
                        v-model="inputAdditional.content"
                        outline
                        hide-details
                        label="テキストを入力..."
                    ></v-textarea>
                    <div v-if="uploadFiles.length > 0" class="text-xs-left">
                        <div class="font-weight-bold mt-3">{{ $t('file_upload.uploading_files') }}</div>
                        <template v-for="(file, index) in uploadFiles">
                            <v-chip
                                :key="index"
                                color="light-blue lighten-2"
                                close small outline
                                @input="removeUploadingFile(index)"
                            >{{ file.file_name }}</v-chip>
                        </template>
                    </div>
                    <!-- ファイルアップロード -->
                    <div class="mt-1">
                        <file-upload :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types"></file-upload>
                        <div class="caption text-xs-right">
                            <span>※ {{ $t('file_upload.allowed_extensions') }}</span>&ensp;
                            <span v-for="(item, index) in allow_file_type_names" :key="item">
                                <span>{{ item }}</span>
                                <span v-show="(index + 1 < allow_file_type_names.length)" class="ml-1 mr-1">|</span>
                            </span>
                        </div>
                    </div><!-- / ファイルアップロード -->
                </v-card-text>
                <!-- クライアントページへの表示設定
                <v-card-text>
                    <v-switch
                        v-model="inputAdditional.is_open_to_client"
                        :label="$t('requests.detail.client.switch_label') + ': ' + textForIsOpenToClientSwitcher"
                        color="primary"
                        class="justify-center"
                    ></v-switch>
                </v-card-text>
                -->
                <!-- 処理ボタン -->
                <v-card-actions class="justify-center">
                    <v-btn @click="cancel">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn color="primary" @click="save">{{ $t('common.button.save') }}</v-btn>
                </v-card-actions><!-- / 処理ボタン -->
            </v-card><!-- / 補足情報登録 -->
            <!-- 補足情報リスト -->
            <div v-else>
                <div class="additional-info-title pa-2">
                    <span
                        color="transparent"
                    >
                        <span>{{ additionalInfos.length }} {{ $t('list.case') }}</span>
                    </span>
                    <v-spacer></v-spacer>
                    <div>
                        <v-tooltip top>
                            <v-btn
                                v-show="isEditableMode && orderDetailId !== 0"
                                slot="activator"
                                color="primary"
                                icon small
                                @click="showAdditionalInput()"
                            >
                                <v-icon>add</v-icon>
                            </v-btn>
                            <span>{{ $t('requests.detail.additional_info.add') }}</span>
                        </v-tooltip>
                    </div>
                </div>

                <v-card v-if="additionalInfos.length > 0" class="ma-2">
                    <v-card-text class="pt-1 pb-1 justify-start">
                        <div id="add-info-list-wrap">
                            <div v-for="(item, index) in additionalInfos" :key="item.id" class="add-info-list pa-1">
                                <div
                                    class="add-info-panel-header text-left"
                                    @click="togglePanel(item.id)"
                                >
                                    <!-- ユーザーアイコン -->
                                    <v-avatar size="32px" class="ma-1">
                                        <img v-if="isHuman(item.updated_user_id)" :src="user_image_path(item.updated_user_id)">
                                        <v-icon v-else slot="activator">android</v-icon>
                                    </v-avatar><!-- / ユーザーアイコン -->
                                    <div class="header-summary ml-2">
                                        <span class="caption font-weight-bold">{{ user_name(item.updated_user_id) }}</span>
                                        <span v-if="isUpdate(item)" class="creation-info ml-1">
                                            ({{ $t('requests.detail.created_user') }} : {{ user_name(item.created_user_id) }}<span class="mx-1">|</span>{{ $t('requests.detail.created_at') }} : {{ item.created_at | formatDateYmdHm(false, true) }})
                                        </span>
                                        <div>
                                            <span class="caption">{{ item.updated_at | formatDateYmdHm(false, true) }}</span>
                                            <span v-show="!isOpenToggle(item.id)" class="ml-3 body-1" v-html="item.content"></span>
                                        </div>
                                    </div>
                                    <v-spacer></v-spacer>
                                    <div class="content-detail">
                                        <template v-if="isOpenToggle(item.id)">
                                            <!-- クライアントページボタン
                                            <v-tooltip top>
                                                <v-btn
                                                    slot="activator"
                                                    icon small
                                                    @click.stop="changeOpenToClient(item)"
                                                >
                                                    <v-icon v-if="item.is_open_to_client">mdi-eye-outline</v-icon>
                                                    <v-icon v-else>mdi-eye-off-outline</v-icon>
                                                </v-btn>
                                                <span v-if="item.is_open_to_client">{{ $t('requests.detail.client.is_status_open') }}</span>
                                                <span v-else>{{ $t('requests.detail.client.is_status_closed') }}</span>
                                            </v-tooltip>
                                            -->
                                            <template v-if="(userId == item.created_user_id || userId == item.updated_user_id)">
                                                <!-- 編集ボタン -->
                                                <v-tooltip top>
                                                    <v-btn
                                                        v-show="isEditableMode"
                                                        slot="activator"
                                                        icon small
                                                        @click.stop="showAdditionalInput(item.id)"
                                                    >
                                                        <v-icon>edit</v-icon>
                                                    </v-btn>
                                                    <span>{{ $t('common.button.edit') }}</span>
                                                </v-tooltip><!-- / 編集ボタン -->
                                                <!-- 削除ボタン -->
                                                <v-tooltip top>
                                                    <v-btn
                                                        v-show="isEditableMode"
                                                        slot="activator"
                                                        icon small
                                                        @click.stop="openDeleteConfirmDialog('deleteWithAttachments', item.id, 'deleteAdditionalInfo')"
                                                    >
                                                        <v-icon>delete_outline</v-icon>
                                                    </v-btn>
                                                    <span>{{ $t('common.button.delete') }}</span>
                                                </v-tooltip><!-- / 削除ボタン -->
                                            </template>
                                        </template>
                                    </div>
                                    <!-- 添付ファイルダウンロード用リスト -->
                                    <v-menu
                                        origin="center center"
                                        transition="scale-transition"
                                        bottom open-on-hover
                                    >
                                        <v-btn
                                            slot="activator"
                                            v-show="!isOpenToggle(item.id) && attachments(item.order_additional_attachments).length > 0"
                                            icon
                                            @click.prevent=""
                                        >
                                            <v-icon>attachment</v-icon>
                                        </v-btn>
                                        <v-list>
                                            <v-list-tile v-for="(file, i) in attachments(item.order_additional_attachments)" :key="i" :href="file.download_uri">
                                                <v-list-tile-title>{{ file.file_name }}<v-icon color="primary" class="ml-3">cloud_download</v-icon></v-list-tile-title>
                                            </v-list-tile>
                                        </v-list>
                                    </v-menu><!-- / 添付ファイルダウンロード用リスト -->
                                    <v-btn icon>
                                        <v-icon v-if="isOpenToggle(item.id)">keyboard_arrow_up</v-icon>
                                        <v-icon v-else>keyboard_arrow_down</v-icon>
                                    </v-btn>
                                </div>
                                <!-- 内容（展開時） -->
                                <div v-show="isOpenToggle(item.id)" class="pa-2 pl-5 text-left">
                                    <div v-html="item.content"></div>
                                    <!-- 添付ファイル -->
                                    <ul class="mt-2 mb-0 pl-0">
                                        <li v-for="(attachment, i) in attachments(item.order_additional_attachments)" :key="i">
                                            <div style="display:flex;align-items:flex-end;">
                                                <v-icon>attachment</v-icon>
                                                <a class="ml-1" :href="attachment.download_uri">{{ attachment.file_name }}</a>
                                                <v-tooltip v-if="(userId == item.created_user_id || userId == item.updated_user_id)" top>
                                                    <v-icon
                                                        v-show="isEditableMode"
                                                        slot="activator"
                                                        class="ml-2"
                                                        style="cursor:pointer;"
                                                        small
                                                        @click.stop="openDeleteConfirmDialog('delete', attachment.file_id, 'deleteAdditionalAttachment')"
                                                    >clear</v-icon>
                                                    <span>{{ $t('common.button.delete') }}</span>
                                                </v-tooltip>
                                            </div>
                                            <template v-if="getFileExtensionType(attachment.download_uri) == 'image'">
                                                <v-img :src="attachment.download_uri" width="300px"></v-img>
                                            </template>
                                            <template v-else-if="getFileExtensionType(attachment.download_uri) == 'video'">
                                                <video
                                                    controls
                                                    width="300px"
                                                    :src="attachment.download_uri"
                                                ></video>
                                            </template>
                                        </li>
                                    </ul><!-- / 添付ファイル -->
                                </div><!-- / 内容（展開時） -->
                                <v-divider v-if="index+1 < additionalInfos.length" class="ma-0"></v-divider>
                            </div>
                        </div>
                    </v-card-text>
                </v-card><!-- / 補足情報リスト -->
            </div>
        </div>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <delete-confirm-dialog :dialog.sync="deleteConfirmDialog" :type="confirmType" :selected-id="selectedId" :callback="deleteConfirmCallback"></delete-confirm-dialog>
    </div>
</template>

<script>
// Components
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog';
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import DeleteConfirmDialog from '../../../Organisms/Admin/Requests/Detail/DeleteConfirmDialog'
import FileUpload from '../../../Atoms/Upload/FileUpload'
// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'
import requestDetailMixin from '../../../../mixins/Admin/requestDetailMixin'
// Store
import store from '../../../../stores/Order/OrderDetails/Show/store'
import storeAdditionalInfo from '../../../../stores/Order/OrderDetails/Show/additional-info'

export default {
    name: 'orderDetailAdditionalInfo',
    mixins: [
        circleComponentMixin,
        requestDetailMixin,
    ],
    components: {
        CommonHeader,
        AlertDialog,
        ConfirmDialog,
        DeleteConfirmDialog,
        FileUpload,
    },
    data: () => ({
        loading: false,
        isEdit: false,
        editType: _const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_CREATED,
        inputAdditional: {
            id: 0,
            content: '',
            is_open_to_client: false,
        },
        initAdditional: {},
        // ファイルアップロード用
        file_upload_emit_message: 'append-files-for-update-order-add-info',
        // サムネイル判定用拡張子
        supportedExtensions: {
            image: ['jpg', 'jpeg', 'png', 'gif'],
            video: ['mp4'],
        },

        deleteConfirmDialog: false,
        confirmType: '',
        selectedId: null,
        deleteConfirmCallback: '',
        openedTogglePanels: storeAdditionalInfo.state.processingData.openedTogglePanels,
        uploadFiles: []
    }),
    computed: {
        orderDetailData () {
            return store.state.processingData.orderDetailData
        },
        orderId () {
            return this.orderDetailData.order_id
        },
        orderDetailId() {
            return store.state.processingData.orderDetailId
        },
        additionalInfos () {
            return storeAdditionalInfo.state.processingData.additionalInfos
        },
        isOpenToggle () {
            return function (id) {
                return this.openedTogglePanels.indexOf(id) != -1 ? true : false
            }
        },
        userId () {
            return document.getElementById('login-user-id').value
        },
        operator () {
            return function (user_id) {
                let operator = store.state.processingData.candidates.filter(user => user_id == user.id)
                return operator.length > 0 ? operator[0] : []
            }
        },
        user_name () {
            return function (user_id) {
                return this.operator(user_id).name
            }
        },
        user_image_path () {
            return function (user_id) {
                return this.operator(user_id).user_image_path
            }
        },
        attachments () {
            return function (attachments) {
                let files = []
                if (attachments) {
                    attachments.forEach(item => {
                        let file_name = item.name
                        let file_path = item.file_path
                        let uri = '/utilities/download_file?file_path=' + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
                        files.push({
                            file_id: item.id,
                            file_name: file_name,
                            file_path: file_path,
                            download_uri: uri,
                        })
                    })
                }
                return files
            }
        },
        textForIsOpenToClientSwitcher () {
            return this.inputAdditional.is_open_to_client ? 'on' : 'off'
        },
        getFileExtensionType () {
            return function (file) {
                if (!file) return
                const extension = file.slice(file.lastIndexOf('.') + 1)
                return Object.keys(this.supportedExtensions).filter(type => (this.supportedExtensions[type].some(support => support == extension))).shift()
            }
        },
    },
    watch: {
        orderDetailData (data, oldData) {
            if (!Object.keys(oldData).length) {
                if (this.orderDetailId !== 0) {
                    this.getAdditionalInfos()
                }
            }
        },
        deleteConfirmDialog (data) {
            if (!data) {
                this.toReadMode()
            } else {
                this.toEditMode()
            }
        },
    },
    created () {
        let self = this
        Object.assign(self.initAdditional, self.inputAdditional)

        eventHub.$on('updateAdditionalInfo', function(selectedId) {
            self.updateAdditionalInfo(selectedId)
        })
        eventHub.$on('deleteAdditionalInfo', function(selectedId) {
            self.deleteAdditionalInfo(selectedId)
        })
        eventHub.$on('deleteAdditionalAttachment', function(selectedId) {
            self.deleteAdditionalAttachment(selectedId)
        })

        // ファイルアップロード用
        eventHub.$on(this.file_upload_emit_message, function(fileInfo) {
            fileInfo.file_list.forEach(file => {
                self.uploadFiles.push({
                    type: file.type,
                    file_name: file.name,
                    file_size: file.size,
                    file_data: file.data,
                    file_path: '',
                    err_description: '',
                })
            })
        })
    },
    methods: {
        showAdditionalInput(id) {
            this.reset()
            if (id) {
                const additionalInfo = this.additionalInfos.filter(additionalInfo => additionalInfo.id == id)
                if (additionalInfo.length) {
                    Object.assign(this.inputAdditional, additionalInfo[0])
                }
                this.editType = _const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_UPDATED
            }
            this.toEditMode()
            this.isEdit = true
        },
        save () {
            if (this.inputAdditional.id) {
                this.updateAdditionalInfo()
            } else {
                this.storeAdditionalInfo()
            }
        },
        cancel () {
            this.isEdit = false
            this.reset()
            this.toReadMode()
        },
        reset () {
            this.editType = _const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_CREATED
            Object.assign(this.inputAdditional, this.initAdditional)
            this.uploadFiles = []
        },
        togglePanel (id) {
            const openedTogglePanels = this.openedTogglePanels
            if (this.isOpenToggle(id)) {
                this.openedTogglePanels = openedTogglePanels.filter(togglePanel => togglePanel !== id)
            } else {
                this.openedTogglePanels.push(id)
            }
            storeAdditionalInfo.commit('setOpenedTogglePanels', this.openedTogglePanels)
        },
        async getAdditionalInfos () {
            await storeAdditionalInfo.dispatch('getAdditionalInfos', { orderId: this.orderId, orderDetailId: this.orderDetailId })
        },
        async uploadFilesConvertToBase64 () {
            // 画像データをコンバート
            const files = this.uploadFiles
            if (files) {
                await Promise.all(files.map(async upload_file => upload_file = await this.convertToBase64(upload_file)))
                for (let i = 0; i <  this.uploadFiles.length; i++) {
                    delete this.uploadFiles[i].type
                }
            }
        },
        async storeAdditionalInfo () {
            this.loading = true
            try {
                // 画像データをblobURL -> base64
                await this.uploadFilesConvertToBase64()

                const res = await axios.post('/api/order/additional_infos/store', {
                    order_id: this.orderId,
                    order_detail_id: this.orderDetailId,
                    content: this.inputAdditional.content,
                    is_open_to_client: this.inputAdditional.is_open_to_client,
                    order_additional_attachments: this.uploadFiles,
                })
                if (res.data.status !== 200) throw res.data.message

                this.loading = false
                this.$refs.alert.show(this.$t('common.message.saved'))
                this.getAdditionalInfos()
                this.cancel()
            } catch (error) {
                console.log(error)
                this.loading = false
                if (['no_admin_permission'].includes(error)) {
                    this.$refs.alert.show(this.$t('common.message.' + error))
                } else {
                    this.$refs.alert.show(this.$t('common.message.save_failed'))
                }
            }
        },
        async updateAdditionalInfo () {
            this.loading = true
            try {
                // 画像データをblobURL -> base64
                await this.uploadFilesConvertToBase64()

                const res = await axios.post('/api/order/additional_infos/update', {
                    order_id: this.orderId,
                    order_additional_info_id: this.inputAdditional.id,
                    content: this.inputAdditional.content,
                    is_open_to_client: this.inputAdditional.is_open_to_client,
                    order_additional_attachments: this.uploadFiles,
                })
                if (res.data.status !== 200) throw res.data.message

                this.loading = false
                this.$refs.alert.show(this.$t('common.message.updated'))
                this.getAdditionalInfos()
                this.cancel()
            } catch (error) {
                console.log(error)
                this.loading = false
                if (['no_admin_permission'].includes(error)) {
                    this.$refs.alert.show(this.$t('common.message.' + error))
                } else {
                    this.$refs.alert.show(this.$t('common.message.save_failed'))
                }
            }
        },
        async deleteAdditionalInfo (orderAdditionalInfoId) {
            this.loading = true
            try {
                const res = await axios.post('/api/order/additional_infos/delete', {
                    order_id: this.orderId,
                    order_additional_info_id: orderAdditionalInfoId
                })
                if (res.data.status !== 200) throw res.data.message

                this.$refs.alert.show(this.$t('common.message.deleted'))
                this.loading = false
                this.getAdditionalInfos()
            } catch (error) {
                console.log(error)
                this.loading = false
                if (['no_admin_permission'].includes(error)) {
                    this.$refs.alert.show(this.$t('common.message.' + error))
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }
            }
        },
        async deleteAdditionalAttachment (orderAdditionalAttachmentId) {
            this.loading = true
            try {
                const res = await axios.post('/api/order/additional_infos/delete_attachment', {
                    order_id: this.orderId,
                    order_additional_attachment_id: orderAdditionalAttachmentId
                })
                if (res.data.status !== 200) throw res.data.message

                this.$refs.alert.show(this.$t('common.message.deleted'))
                this.loading = false
                this.getAdditionalInfos()
            } catch (error) {
                console.log(error)
                this.loading = false
                if (['no_admin_permission'].includes(error)) {
                    this.$refs.alert.show(this.$t('common.message.' + error))
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }
            }
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
            return item.created_user_id != item.updated_user_id || item.created_at != item.updated_at ? true : false
        },
        async changeOpenToClient (additionalInfo) {
            const isOpenToClient = additionalInfo.is_open_to_client ? _const.FLG.INACTIVE : _const.FLG.ACTIVE
            if (await this.$refs.confirm.show(isOpenToClient ? this.$t('requests.detail.client.confirm_to_open') : this.$t('requests.detail.client.confirm_to_close'))) {
                Object.assign(this.inputAdditional, additionalInfo)
                this.inputAdditional.is_open_to_client = isOpenToClient
                this.updateAdditionalInfo()
            }
        },
    },
}
</script>

<style scoped>
.add-info-panel-header {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.additional-info-title {
    display: flex;
    align-items: center;
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
