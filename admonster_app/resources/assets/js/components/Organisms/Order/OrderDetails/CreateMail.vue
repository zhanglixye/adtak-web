<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.create_mail.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            hide-edit-button
            hide-other-button
            @shrink-right="shrinkRight"
            @shrink-left="shrinkLeft"
            @grow="grow"
        >
        </common-header>
        <div
            :style="{
                'height': contentHeight,
                'overflow': 'auto',
                'background-color': 'white',
            }"
            class="pa-2"
        >
            <v-form v-model="valid" ref="to">
                <v-combobox
                    v-model="selectTo"
                    :items="[]"
                    label="To"
                    multiple
                    small-chips
                    deletable-chips
                    :search-input.sync="to"
                    :rules="[rules.required]"
                    class="address"
                    @contextmenu.prevent="showContextMenu($event, 'to')"
                >
                    <!-- △を非表示 -->
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
                deletable-chips
                :search-input.sync="cc"
                class="address"
                @contextmenu.prevent="showContextMenu($event, 'cc')"
            >
                <!-- △を非表示 -->
                <template slot="append">
                    <div style="display:none"></div>
                </template>
            </v-combobox>
            <v-text-field v-model="subject" label="Subject"></v-text-field>
            <div>
                <div @contextmenu.prevent="showContextMenu($event, 'body')">
                    <editor-quil
                        class="mb-3"
                        :heightAdd="124"
                        ref="editor"
                    ></editor-quil>
                </div>
                <div @contextmenu.prevent="showContextMenu($event, 'file')">
                    <file-upload
                        @clearFile="clearFile"
                        @clearAdditionalFile="clearAdditionalFile"
                        ref="upload"
                        :emit_message="fileUploadEmitMessage"
                        :uploadFiles="uploadFiles"
                        :uploadAdditionalFiles="uploadAdditionalFiles"
                        :isMaxSizeCheck="true"
                        @clickDownload="clickDownload"
                    ></file-upload>
                </div>
                <vue-context ref="vueContent">
                    <li v-if="rightClickItem === 'file'">
                        <a @click.prevent="showOrderAdditionalInfoSelection">{{ $t('order.order_details.show.information_component_management.additional_info.title') }}</a>
                    </li>
                    <li v-else>
                        <a @click.prevent="showOrderDetailColumnSelection">{{ $t('order.order_details.show.information_component_management.order_detail_info.title') }}</a>
                    </li>
                </vue-context>
            </div>
            <div class="pa-2 text-right">
                <v-btn color="primary" @click="sendMail()" :disabled="!valid">{{ $t('common.button.send') }}</v-btn>
                <v-tooltip top>
                    <v-btn
                        flat
                        icon
                        slot="activator"
                        color="grey"
                        @click="close"
                    >
                        <v-icon>mdi-delete-outline</v-icon>
                    </v-btn>
                    <span>{{ $t('order.order_details.show.information_component_management.create_mail.discard') }}</span>
                </v-tooltip>
            </div>
            <alert-dialog ref="alert"></alert-dialog>
            <order-additional-info-selection-dialog ref="orderAdditionalInfoSelectionDialog"></order-additional-info-selection-dialog>
            <order-detail-column-selection-dialog ref="orderDetailColumnSelectionDialog"></order-detail-column-selection-dialog>
            <progress-circular v-if="loading"></progress-circular>
        </div>
    </div>
</template>

<script>
// Components
import VueContext from 'vue-context'
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import EditorQuil from './EditorQuil'
import FileUpload from './FileUpload'
import OrderAdditionalInfoSelectionDialog from './OrderAdditionalInfoSelectionDialog'
import OrderDetailColumnSelectionDialog from './OrderDetailColumnSelectionDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        VueContext,
        CommonHeader,
        AlertDialog,
        EditorQuil,
        FileUpload,
        OrderAdditionalInfoSelectionDialog,
        OrderDetailColumnSelectionDialog,
        ProgressCircular,
    },
    data() {
        return {
            loading: false,
            subject: '',
            fileUploadEmitMessage: 'set-mail-attachment-file',
            selectTo: [],
            selectCc: [],
            items: [],
            uploadFiles: [],
            uploadAdditionalFiles: [],
            valid: false,
            to: null,
            cc: null,
            rules: {
                required: v => v.length !== 0 || (this.to !== null && this.to !== '') || this.$t('order.order_details.show.information_component_management.create_mail.to_not_input'),
            },
            rightClickItem: '',
            cursorIndex: null,
        }
    },
    computed: {
        orderDetailId() {
            return store.state.processingData.orderDetailId
        },
    },
    watch: {
        to() {
            this.$refs.to.validate()
        },
    },
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
        eventHub.$on('set-column-value', function(data) {
            switch (self.rightClickItem) {
            case 'to':
                data.columns.forEach(column  => self.selectTo.push(column.value))
                break
            case 'cc':
                data.columns.forEach(column  => self.selectCc.push(column.value))
                break
            case 'body':
                self.$refs.editor.insertText(
                    data.columns.reduce((accumulator, column) => accumulator += `${column.label_value} : ${column.value}\n`, ''),
                    data.cursorIndex
                )
                break
            }
        })
        eventHub.$on('set-additional-file', function(data) {
            if (self.$refs.upload.validateFileSize(data.additionalInfos)) {
                data.additionalInfos.forEach(info => self.uploadAdditionalFiles.push(info))
            }
        })
    },
    mounted() {
        this.$refs.to.validate()
    },
    methods: {
        close: function() {
            this.allClear()
            this.toReadMode()
            this.closeCreateMail()
        },
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
        clearAdditionalFile(index) {
            this.uploadAdditionalFiles.splice(index, 1)
        },
        set(mailInfo = { to: [], cc: [], subject: '', body: '', bodyType: undefined}) {
            // 初期データをセット
            if ('to' in mailInfo) this.selectTo = mailInfo.to
            if ('subject' in mailInfo) this.subject = mailInfo.subject
            if ('cc' in mailInfo) this.selectCc = mailInfo.cc
            if ('body' in mailInfo) {
                this.$refs.editor.$data.content = ''
                const bodyType = mailInfo['bodyType']
                if (bodyType === _const.CONTENT_TYPE.TEXT) this.$refs.editor.insertText(mailInfo.body)
                if (bodyType === _const.CONTENT_TYPE.HTML) this.$refs.editor.insertHtml(mailInfo.body)
            }
        },
        allClear() {
            // 値をリセット
            this.selectTo = []
            this.selectCc = []
            this.subject = ''
            this.$refs.editor.$data.content = ''
            this.uploadFiles = []
            this.uploadAdditionalFiles = []
            this.$refs.upload.$data.dragDropFileName = ''
            this.$refs.upload.$data.clearFlag = false
        },
        async sendMail() {
            this.loading = true
            if (this.to !== null) this.selectTo.push(this.to)
            if (this.cc !== null) this.selectCc.push(this.cc)
            try {
                const formData = new FormData()
                formData.append('to', this.selectTo)
                formData.append('cc', this.selectCc)
                formData.append('subject', this.subject)
                formData.append('orderDetailId', this.orderDetailId)
                formData.append('body', this.$refs.editor.$data.content)
                formData.append('attachments', JSON.stringify(this.uploadFiles))
                formData.append('additional_attachments', JSON.stringify(this.uploadAdditionalFiles))
                const res = await axios.post('/api/order/order_details/create_send_mail', formData)
                if (res.data.status !== 200) throw res.data.message

                this.$refs.alert.show(
                    this.$t('order.order_details.show.information_component_management.create_mail.send_mail'),
                    () => this.close()
                )
            } catch (error) {
                console.log('error: ', error)
                if (error === 'no_admin_permission') {
                    this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                } else {
                    this.$refs.alert.show(this.$t('order.order_details.show.information_component_management.create_mail.no_send_mail'))
                }
            }
            this.loading = false
        },
        showContextMenu(event, itemName) {
            this.rightClickItem = itemName
            this.$refs.vueContent.open(event)
            const cursor = this.$refs.editor.getSelection()
            this.cursorIndex = cursor ? cursor.index : null
        },
        showOrderDetailColumnSelection() {
            this.$refs.orderDetailColumnSelectionDialog.show(this.cursorIndex)
        },
        showOrderAdditionalInfoSelection() {
            this.$refs.orderAdditionalInfoSelectionDialog.show()
        },
    }
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
