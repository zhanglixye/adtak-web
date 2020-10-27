<template>
    <v-dialog v-model="dialog" persistent width="800">
        <v-card>
            <v-card-title class="pb-0">
                <span class="headline">
                    {{ $t('order.order_details.dialog.request_creation.title') }}
                </span>
            </v-card-title>
            <v-card-text class="pb-0">
                <!-- <v-form v-model="valid" ref="to">
                    <v-combobox
                        v-model="selectTo"
                        :items="[]"
                        label="To"
                        multiple
                        small-chips
                        ref="combo"
                        deletable-chips
                        :search-input.sync="to"
                        :rules="[rules.required]"
                        class="address"
                    >
                        <template slot="append">
                            <div style="display:none"></div>
                        </template>
                    </v-combobox>
                </v-form>
                <v-combobox
                    v-model="selectCc"
                    :items="[]"
                    label="Cc"
                    multiple
                    small-chips
                    ref="combo_t"
                    deletable-chips
                    :search-input.sync="cc"
                    class="address"
                >
                    <template slot="append">
                        <div style="display:none"></div>
                    </template>
                </v-combobox> -->
                <v-text-field
                    v-model="subject"
                    :label="$t('order.order_details.dialog.request_creation.mail_subject')"
                ></v-text-field>
                <div>
                    <editor-quil
                        class="mb-3"
                        :heightAdd="height"
                        ref="editor"
                    ></editor-quil>
                    <file-upload
                        @clearFile="clearFile"
                        ref="upload"
                        :emit_message="fileUploadEmitMessage"
                        :uploadFiles="uploadFiles"
                        @clickDownload="clickDownload"
                    ></file-upload>
                </div>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click="create()">{{ $t('common.button.create') }}</v-btn>
            </v-card-actions>
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
        <progress-circular v-if="loading"></progress-circular>
    </v-dialog>
</template>

<script>
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import EditorQuil from './EditorQuil'
import FileUpload from './FileUpload'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        AlertDialog,
        EditorQuil,
        FileUpload,
        ProgressCircular,
    },
    data() {
        return {
            loading: false,
            height: 124,
            subject: '',
            fileUploadEmitMessage: 'email-sale-file',
            // selectTo: [],
            // selectCc: [],
            items: [],
            uploadFiles: [],
            dialog: false,
            // valid: false,
            // to: null,
            // cc: null,
            // rules: {
            //     required: v => v.length !== 0 || (this.to !== null && this.to !== '') || Vue.i18n.translate('order.order_details.show.mail_created_dialog.to_not_input'),
            // },
            orderDetailId: null,
            businessId: null,
        }
    },
    // watch: {
    //     to() {
    //         this.$refs.to.validate()
    //     },
    // },
    created() {
        const self = this
        // ファイルアップロード用
        eventHub.$on(this.fileUploadEmitMessage, function(data) {
            for (const file of data.file_list) {
                self.uploadFiles.push({
                    err_description: '',
                    file_name: file.name,
                    file_size: file.size,
                    file_data: file.data,
                    file_path: '',
                    type: file.type,
                })
            }
            // ローカルに一時保存
            self.moveToTemporary(self.uploadFiles)
        })
    },
    // mounted() {
    //     this.$refs.to.validate()
    // },
    methods: {
        getFileType(filePath) {
            let startIndex = filePath.lastIndexOf('.')
            if (startIndex != -1) return filePath.substring(startIndex + 1, filePath.length).toLowerCase()
            else return ''
        },
        splitFileName(text) {
            const pattern = /\.{1}[a-z]{1,}$/
            if (pattern.exec(text) !== null) {
                return text.slice(0, pattern.exec(text).index)
            } else {
                return text
            }
        },
        clickDownload(data) {
            let base64 = ''
            let fileNewName = ''
            for (const v of this.uploadFiles) {
                if (v.file_name == data) {
                    base64 = v.file_data
                    fileNewName = v.file_name
                }
            }
            this.downLoadFile(base64, this.splitFileName(fileNewName), this.getFileType(fileNewName))
        },
        async downLoadFile(baseData, firstFileName, lastFileName) {
            const fileName = firstFileName + '.' + lastFileName
            const bytes = atob(baseData.substring(baseData.indexOf(',') + 1))
            const content = new ArrayBuffer(bytes.length)
            const ia = new Uint8Array(content)
            for (let i = 0; i < bytes.length; i++) {
                ia[i] = bytes.charCodeAt(i)
            }
            let blob
            if (lastFileName == 'pdf') {
                blob = new Blob([content], {
                    type: 'application/pdf',
                })
            } else if (lastFileName == 'zip') {
                blob = new Blob([content], {
                    type: 'application/zip',
                })
            } else if (lastFileName == 'txt') {
                blob = new Blob([ia], {
                    type: 'text/plain,charset=shift-jis',
                })
            } else if (lastFileName == 'log') {
                blob = new Blob([ia], {
                    type: 'text/plain',
                })
            } else if (lastFileName == 'xls') {
                blob = new Blob([content], {
                    type: 'application/vnd.ms-excel',
                })
            } else {
                blob = new Blob([content], {
                    type: 'application/excel',
                })
            }

            if (window.navigator.msSaveBlob) {
                window.navigator.msSaveBlob(blob, fileName)
                // msSaveOrOpenBlobの場合はファイルを保存せずに開ける
                window.navigator.msSaveOrOpenBlob(blob, fileName)
            } else {
                let itemA = document.createElement('a')
                itemA.href = window.URL.createObjectURL(blob)
                itemA.download = fileName
                itemA.click()
            }
        },
        moveToTemporary: async function(uploadFile) {
            const files = uploadFile
            const convert = this.convertToBase64
            await [].forEach.call(files, convert)
        },
        convertToBase64: function(file) {
            return new Promise((resolve, reject) => {
                if ('data' == file.file_data.substring(0, 4)) resolve(file)
                let xhr = new XMLHttpRequest()
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
                    reader.onerror = e => reject(e)
                }
                xhr.onerror = e => reject(e)
                xhr.open('GET', file.file_data)
                xhr.send()
            })
        },
        clearFile(index) {
            this.uploadFiles.splice(index, 1)
        },
        show(mailInfo = { to: [], cc: [], subject: '', body: '' }, orderDetailId = null, businessId = null) {
            // 初期データをセット
            // if ('to' in mailInfo) this.selectTo = mailInfo.to
            // if ('cc' in mailInfo) this.selectCc = mailInfo.cc
            if ('subject' in mailInfo) this.subject = mailInfo.subject
            if ('body' in mailInfo) this.$refs.editor.insertText(mailInfo.body)

            this.orderDetailId = orderDetailId
            this.businessId = businessId

            this.dialog = true
        },
        cancel() {
            // 値をリセット
            // this.selectTo = []
            // this.selectCc = []
            this.subject = ''
            this.$refs.editor.$data.content = ''
            this.uploadFiles = []
            this.$refs.upload.$data.dragDropFileName = ''
            this.$refs.upload.$data.clearFlag = false
            this.dialog = false
        },
        async create() {
            this.loading = true
            // if (this.to !== null) this.selectTo.push(this.to)
            // if (this.cc !== null) this.selectCc.push(this.cc)
            try {
                const formData = new FormData()
                formData.append('order_detail_id', this.orderDetailId)
                formData.append('business_id', this.businessId)
                // formData.append('to', this.selectTo)
                // formData.append('cc', this.selectCc)
                formData.append('mail_subject', this.subject)
                formData.append('mail_body', this.$refs.editor.$data.content)
                formData.append('mail_attachments', JSON.stringify(this.uploadFiles))
                const res = await axios.post('/api/order/order_details/create_requests', formData)
                if (res.data.status !== 200) throw res.data.message

                // 値をリセット
                // this.selectTo = []
                // this.selectCc = []
                this.subject = ''
                this.$refs.editor.$data.content = ''
                this.uploadFiles = []
                this.$refs.upload.$data.dragDropFileName = ''
                this.$refs.upload.$data.clearFlag = false

                this.$refs.alert.show(
                    this.$t('order.order_details.dialog.request_creation.success'),
                    () => window.location.reload()
                )
                this.dialog = false
            } catch (error) {
                console.log({error})
                if (error === 'no_admin_permission') {
                    this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                } else {
                    this.$refs.alert.show(
                        this.$t('order.order_details.dialog.request_creation.failure')
                    )
                }
            }
            this.loading = false
        },
    },
}
</script>

<style scoped>
.address >>> .v-select__selections {
    width: 100%;
}
.address >>> .v-chip {
    max-width: 98%;
    overflow: hidden;
}
</style>
