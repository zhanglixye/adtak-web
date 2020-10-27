<template>
    <div id="attachment-list">
            <v-dialog v-model="showSelf" persistent :width="attachments.length > 9 ? '750px' : '730px'">
                <v-card>
                    <v-card-title class="pb-2">
                        <span class="mr-auto" :style="{'font-size': '16px'}">{{ $t('common.mail.attachment_list.title') }}</span>
                        <v-btn v-if="isMaxSelected" color="primary" @click="selectedClear">{{ $t('common.button.clear') }}</v-btn>
                        <v-btn v-else color="primary" @click="selectedAll">{{ $t('common.mail.attachment_list.select_all') }}</v-btn>
                        <v-btn :disabled="selectedList.length === 0" color="primary" @click="download">{{ $t('common.button.download') }}</v-btn>
                        <v-btn
                            small
                            icon
                            @click="hide"
                        >
                            <v-icon>clear</v-icon>
                        </v-btn>
                    </v-card-title>
                    <v-divider class="my-0"></v-divider>
                    <v-card-text id="list" class="text-xs-center">
                        <!-- この中にファイルの一覧を入れる -->
                        <v-layout wrap row justify-start>
                            <template v-for="(attachment, i) in showAttachments">
                                <v-flex class="flex-none" ma-3 :key="i">
                                    <v-card hover class="border-all" width="200px" flat ripple
                                        @click.stop.exact="filePreview(i)"
                                        @click.stop.shift="filePreview(i, 'window')"
                                        @click.stop.ctrl="filePreview(i, 'tab')"
                                    >
                                        <v-card-title class="py-1">
                                            <v-icon>mdi-file-document</v-icon>
                                            <v-checkbox
                                                class="mt-0 pt-0 ml-auto flex-none"
                                                :ripple="false"
                                                hide-details
                                                v-model="selectedList"
                                                :value="attachment.id"
                                                @click.stop=""
                                            ></v-checkbox>
                                        </v-card-title>
                                        <v-divider class="my-0"></v-divider>
                                        <v-tooltip top>
                                            <template slot="activator">
                                                <v-card-text class="text-xs-left">
                                                    <div class="text-ellipsis">{{attachment.name}}</div>
                                                    <span class="grey--text">{{attachment.size}}</span>
                                                </v-card-text>
                                            </template>
                                            <span>{{attachment.name}}</span>
                                        </v-tooltip>
                                    </v-card>
                                </v-flex>
                            </template>
                        </v-layout>
                        <progress-circular v-if="loading"></progress-circular>
                    </v-card-text>
                </v-card>
                <progress-circular v-if="filePreviewLoading || fileDlLoading"></progress-circular>
            </v-dialog>
            <alert-dialog ref="alert"></alert-dialog>
            <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
    </div>
</template>

<script>
import ProgressCircular from '../../Atoms/Progress/ProgressCircular'
import FilePreviewDialog from '../../Atoms/Dialogs/FilePreviewDialog'
import AlertDialog from '../../Atoms/Dialogs/AlertDialog'

export default {
    components: {
        AlertDialog,
        ProgressCircular,
        FilePreviewDialog,
    },
    props: {
        attachments: {type: Array, required: true},
    },
    data: () => ({
        showSelf: false,
        loading: false,
        selectedList: [],
        filePreviewLoading: false,
        fileDlLoading: false
    }),
    computed: {
        isMaxSelected: function () {
            return this.selectedList.length === this.attachments.length
        },
        showAttachments: function () {
            const attachments = JSON.parse(JSON.stringify(this.attachments))
            for (const attachment of attachments) {
                attachment.size = this.convertToVisibleForm(attachment.size)
            }
            return attachments
        }
    },
    methods: {
        show: function (requestMailId) {
            if (requestMailId) {
                // 依頼メールIDの指定があれば
                this.setAttachments(requestMailId)
            }
            this.selectedClear()
            this.showSelf = true
        },
        hide: function () {
            this.showSelf = false
            this.selectedClear()
        },
        selectedAll: function () {
            this.selectedList = this.attachments.map(attachment => attachment.id)
        },
        selectedClear: function () {
            this.selectedList = []
        },
        setAttachments: function (requestMailId) {
            this.loading = true
            axios.post('/api/requests/getRequestMailDetail', {'request_mail_id': requestMailId})
                .then((res) => {
                    // 添付ファイルモーダルに反映
                    this.$emit('update:attachments', this.getMailAttachments(res.data.request_mail))
                }).catch((error) => {
                    console.log(error)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }).finally(() => {
                    this.loading = false
                })
        },
        getMailAttachments (requestMail) {
            let array = []
            if (requestMail && requestMail.mail_attachments.length > 0) {
                requestMail.mail_attachments.forEach(function (item) {
                    const fileName = item.name
                    const filePath = item.file_path
                    const id = item.id
                    const fileSize = item.file_size
                    let uri = '/utilities/download_file?file_path='
                    uri += encodeURIComponent(filePath) + '&file_name=' + encodeURIComponent(fileName)
                    array.push({
                        name: fileName,
                        path: filePath,
                        downloadUri: uri,
                        id: id,
                        size: fileSize
                    })
                })
            }
            return array
        },
        download: async function () {

            if (this.selectedList.length > 1) {
                this.fileDlLoading = true
                const sendSelectedList = this.attachments.flatMap(attachment => {
                    if (!this.selectedList.includes(attachment.id)) return []
                    const item = {}
                    item['name'] = attachment.name
                    item['file_path'] = attachment.path
                    return item
                })
                const zipFileName = 'attachments'
                try {
                    const formData = new FormData()
                    formData.append('selectedList', JSON.stringify(sendSelectedList))
                    formData.append('zipFileName', zipFileName)
                    const res = await axios.post('/api/utilities/createZipFile', formData)
                    // TODO エラークラスを作成する
                    if (res.data.status !== 200) {
                        throw Object.assign(new Error(res.data.message), {name: res.data.error, statusCode: res.data.status})
                    }

                    const a = document.createElement('a')
                    // 別タブに切り替えないようにする
                    a.download = `${zipFileName}.zip` // ダウンロードを失敗した時に出るファイル名
                    a.target = '_blank'
                    // download uri
                    let uri = '/api/utilities/downloadFromLocal?file_path='
                    uri += encodeURIComponent(res.data.file_path) + '&file_name=' + encodeURIComponent(res.data.file_name)
                    a.href = uri
                    document.body.appendChild(a)
                    a.click()
                    document.body.removeChild(a)
                } catch (error) {
                    console.log(error)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                } finally {
                    this.fileDlLoading = false
                }
            } else {
                for (const attachment of this.attachments) {
                    // 選択されていない物は処理しない
                    if (!this.selectedList.includes(attachment.id)) continue
                    const a = document.createElement('a')
                    // 別タブに切り替えないようにする
                    a.download = attachment.name // ダウンロードを失敗した時に出るファイル名
                    a.target   = '_blank'
                    a.href = attachment.downloadUri
                    document.body.appendChild(a)
                    a.click()
                    document.body.removeChild(a)
                }
            }

            this.hide()
        },
        convertToVisibleForm : function (size, decimalPlaces = 1) {
            // 最小単位はKB 例 5byte -> 1KB
            const kb = 1000
            const mb = Math.pow(kb, 2)
            let target = kb
            let unit = 'KB'

            // 最適なファイルサイズを計算
            if (size >= mb) {
                target = mb
                unit = 'MB'
            }

            // 不正な引数
            if (!Number.isInteger(decimalPlaces)) throw `Decimal places is Invalid number. value: ${decimalPlaces}`

            // 指定した桁分、ずらす為の数
            const smaller = Math.pow(10, decimalPlaces)

            // 最小単位以下は１ 最小単位以外は指定した少数単位を四捨五入
            const val =  size < kb ?  1 : Math.round((size / target) * smaller) / smaller
            // カンマ区切りの数字 + 単位
            return val.toLocaleString() + unit
        },
        filePreview: async function (index, type = '') {
            const file = this.showAttachments[index]
            file.file_path = file.path
            this.$refs.filePreviewDialog.show([file], [], type)
        }
    }
}
</script>

<style scoped>
.border-all {
    border:1px solid rgba(0, 0, 0, .12);
}

.flex-none {
    flex: none;
}

.text-ellipsis {
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

#list {
    position: relative;
    height: 440px;
    overflow-y: auto;
}

/* チェックボタン周辺でクリックしても反応しないマージン分を削る */
#list >>> .v-input--selection-controls__input {
    margin-right: 0;
}

#list >>> input[type="checkbox"] {
    margin: 0;
}
/* /チェックボタン周辺でクリックしても反応しないマージン分を削る */
</style>
