<template>
    <v-dialog v-model="fileImportDialog" persistent width="600">
        <v-card id="fileImportDialog">
            <v-card-title class="headline">
                {{ $t('imported_files.import_dialog.header') }}
            </v-card-title>
            <!-- アップロードファイル -->
            <v-card-actions>
                <v-container grid-list-md>
                    <template v-if="Object.keys(this.uploadFile).length > 0">
                        <div style="display:flex; align-items:center;" class="pa-3 grey lighten-3">
                            <span class="mr-2"><v-icon small>attachment</v-icon></span>
                            <span style="width: 90%; word-break: break-all;">{{ uploadFile.file_name }}</span>
                            <v-spacer></v-spacer>
                            <v-tooltip top>
                                <v-btn slot="activator" icon small>
                                    <v-icon @click="deleteTmpFile(uploadFile.tmpFileInfo)">
                                        delete_outline
                                    </v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.delete') }}</span>
                            </v-tooltip>
                        </div>
                    </template>
                    <template v-else>
                        <file-upload
                            :disabled="disabledFile"
                            :emit_message="file_upload_emit_message"
                            :allow_file_types="allow_file_types"
                            :max-file-cnt="max_file_cnt"
                        ></file-upload>
                    </template>
                </v-container>
            </v-card-actions>
            <!-- / アップロードファイル -->
            <!-- <v-card-actions>
                <v-container grid-list-md>
                    スプレッドシートで取り込む場合はURLを入力してください
                    <v-text-field
                        v-model="fileUrl"
                        single-line
                        outline
                        :disabled="disabledText"
                    ></v-text-field>
                </v-container>
            </v-card-actions> -->
            <!-- ボタン -->
            <v-card-actions class="justify-center">
                <v-btn @click="close()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn
                    @click="openImportMethodSelectionDialog()"
                    color="primary"
                    :disabled="disabledButtons"
                >
                    {{ $t('common.button.next') }}
                </v-btn>
            </v-card-actions>
            <!-- / ボタン -->
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
        <progress-circular v-if="loading"></progress-circular>
    </v-dialog>
</template>

<script>
import store from '../../../../stores/Admin/ImportedFiles/store'
import FileUpload from '../../../Atoms/Upload/FileUpload'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        FileUpload,
        AlertDialog,
        ProgressCircular
    },
    props: {
        fileImportDialog: { type: Boolean }
    },
    data: () => ({
        eventHub: eventHub,
        loading: false,
        fileUrl: '',

        file_upload_emit_message: 'file-upload',
        allow_file_types: [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        uploadFile: store.state.uploadFile,
        max_file_cnt: 1,
    }),
    created () {
        const self = this

        // ファイルアップロード用
        eventHub.$on(this.file_upload_emit_message, function (data) {
            const file = data.file_list[0]
            const result = {
                err_description: '',
                file_name: file.name,
                file_size: file.size,
                file_data: file.data,
                file_path: '',
                type: file.type,
            }
            self.uploadFile = result

            // ローカルに一時保存
            self.moveToTemporary(self.uploadFile)
        })
    },
    computed: {
        disabledText () {
            return 0 !== Object.keys(this.uploadFile).length
        },
        disabledButtons () {
            return !(this.disabledText || this.disabled)
        },
        disabledFile () {
            return this.fileUrl !== ''
        }
    },
    methods: {
        openImportMethodSelectionDialog () {
            this.$emit('update:importMethodSelectionDialog', true)
            this.$emit('update:isFileImport', false)
            this.$emit('update:fileImportDialog', false)
        },
        close () {
            if (Object.keys(this.uploadFile).length > 0) {
                // 一時ファイルを削除
                this.deleteTmpFile(this.uploadFile.tmpFileInfo)
            }
            this.$emit('update:fileImportDialog', false)
        },
        deleteTmpFile (file_path_info) {
            // 一時ファイルを削除
            axios.post('/api/imported_files/tmp_file_delete', {
                tmp_file_info: file_path_info
            })
                .then((res) => {
                    console.log(res)
                    store.commit('resetUploadFile')
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('imported_files.error_messages.delete_file') })
                })
                .finally(() => {
                    this.loading = false
                    this.uploadFile = {}
                })
        },
        moveToTemporary: async function (uploadFile) {
            this.loading = true
            // 画像データをblobURL -> base64
            // const file = this.uploadFile
            const file = uploadFile
            const convert = this.convertToBase64
            await convert(file)
            delete this.uploadFile.type
            axios.post('/api/imported_files/tmp_upload',{
                upload_file: this.uploadFile
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        this.uploadFile.tmpFileInfo = res.data.tmp_file_info
                        store.commit('setUploadFile', this.uploadFile)
                    } else {
                        eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('file_upload.message.upload_faild') })
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('file_upload.message.upload_faild') })
                })
                .finally(() => {
                    this.loading = false
                })
        },
        convertToBase64: function(file) {
            return new Promise((resolve, reject) => {
                // base64データが入っている場合は処理しない
                if ('data' == file.file_data.substring(0, 4)) resolve(file)
                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob'
                xhr.onload = () => {
                    var reader = new window.FileReader()
                    reader.readAsDataURL(xhr.response)
                    reader.onloadend = () => {
                        // メモリから削除
                        URL.revokeObjectURL(file.file_data)
                        file.file_data = reader.result
                        resolve(file)
                    }
                    reader.onerror = (e) => reject(e)
                }
                xhr.onerror = (e) => reject(e)
                xhr.open('GET', file.file_data)
                xhr.send()
            })
        }
    }
}
</script>
