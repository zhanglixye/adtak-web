<template>
    <div>
        <div
            :class="[{ '-drag': isDrag == 'new', '-undrag': isDrag != 'new' }]"
            @dragover.prevent="checkDrag($event, 'new', true)"
            @dragleave.prevent="checkDrag($event, 'new', false)"
            @drop.prevent="onDrop"
        >
            <i aria-hidden="true" class="fa fa-plus"></i>
            <div class="drop">
                <div class="pl-3 pr-3 pt-2 pb-2" style="display: flex;">
                    <div class="flex-ai-fs-0">
                        <label
                            :for="emit_message + 'upload_check_files'"
                            class="file-upload mb-0"
                            style="font-weight: normal"
                        >
                            <span v-show="!disabledStatus">
                                <v-icon style="color: #1976d2">mdi-link-variant</v-icon>
                                {{ $t('file_upload.pick_file') }}
                            </span>
                            <input
                                type="file"
                                class="drop__input"
                                style="display:none;"
                                :id="emit_message + 'upload_check_files'"
                                name="upload_check_files"
                                app
                                @change="onDrop"
                                multiple
                            />
                        </label>
                    </div>
                    <div class="flex-center-ai">
                        <template v-for="(item, index) in uploadFiles">
                            <div class="flex-center-ai" :key="`local-${index}`">
                                <a class="file-name mr-1 ml-2" @click="download">{{ item.file_name }}</a>
                                <i
                                    @click="clearFile(index)"
                                    aria-hidden="true"
                                    class="v-icon material-icons theme--light"
                                    style="cursor: pointer"
                                >cancel</i>
                            </div>
                        </template>
                        <template v-for="(item, index) in uploadAdditionalFiles">
                            <div class="flex-center-ai" :key="`additional-${index}`">
                                <a class="file-name mr-1 ml-2" :href="getFileDownloadUri(item.file_path, item.name)">{{ item.name }}</a>
                                <i
                                    @click="clearAdditionalFile(index)"
                                    aria-hidden="true"
                                    class="v-icon material-icons theme--light"
                                    style="cursor: pointer"
                                >cancel</i>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="isMaxSizeCheck" class="pa-2 text-right">{{ $t('file_upload.notice_upload_max_size', {size: textUploadMaxSize}) }}</div>
    </div>
</template>

<script>
import fileUploadMixin from '.././../../../mixins/fileUploadMixin'

export default {
    mixins: [fileUploadMixin],
    props: {
        upFileName: { type: String, required: false, default: undefined },
        disabledStatus: { type: Boolean, required: false, default: false },
        uploadFiles: { type: Array, required: false, default: () => [] },
        uploadAdditionalFiles: { type: Array, required: false, default: () => [] },
        isMaxSizeCheck: { type: Boolean, required: false, default: false },
    },
    data() {
        return {
            selectedNumber: 0,
            // アップロードファイルサイズの上限（KB）
            uploadFileTotalMaxSize: 7000000,
        }
    },
    computed:{
        textUploadMaxSize() {
            return Math.floor((this.uploadFileTotalMaxSize / Math.pow(1000, 2)) * 100) / 100 + 'MB'
        },
    },
    methods: {
        // アップロードファイルサイズチェック
        validateFileSize(files) {
            const currentUploadFileSize = Array.from(this.uploadFiles).reduce((sum, file) => sum + file.file_size, 0)
            const currentAdditionalUploadFileSize = Array.from(this.uploadAdditionalFiles).reduce((sum, file) => {
                if (file.size !== null) return sum + file.size
                return sum
            }, 0)
            const fileSizeSum = currentUploadFileSize + currentAdditionalUploadFileSize + Array.from(files).reduce((sum, file) => {
                if (file.size !== null) return sum + file.size
                return sum
            }, 0)
            if (this.isMaxSizeCheck && fileSizeSum > this.uploadFileTotalMaxSize) {
                eventHub.$emit('open-notify-modal', {
                    message: this.$t('file_upload.message.over_max_file_size', {size: this.textUploadMaxSize})
                })
                return false
            }
            return true
        },
        //inputタグとドラッグ&ドロップから呼ばれる
        onDrop(event) {
            this.selectedNumber = 0
            this.isDrag = null
            const files = event.target.files ? event.target.files : event.dataTransfer.files

            // アップロードファイル数のチェック
            if (this.maxFileCnt && files.length > this.maxFileCnt) {
                eventHub.$emit('open-notify-modal', {
                    message: this.$t('file_upload.message.over_max_file_cnt') + this.maxFileCnt,
                })
                return false
            }

            // アップロードファイルサイズチェック
            if (!this.validateFileSize(files)) {
                return false
            }

            // 特定ファイルのチェック
            const specificFileTypeCheck = file => {
                // ブラックリストフィルタリング
                if (this.forbid_file_types != undefined) {
                    for (const type of this.forbid_file_types) {
                        if (file.type == type) return false
                    }
                    return true
                }

                // ホワイトリストフィルタリング
                if (this.allow_file_types != undefined) {
                    // 許可されたファイル
                    for (const type of this.allow_file_types) {
                        if (file.type == type) return true
                    }
                    return false
                }

                // 指定無し
                return true
            }
            const specificFiles = []
            let isOneOrMoreForbidFiles = false
            for (const file of files) {
                const isAllow = specificFileTypeCheck(file)
                if (isAllow) {
                    specificFiles.push(file)
                } else {
                    isOneOrMoreForbidFiles = true
                }
            }

            if (isOneOrMoreForbidFiles) {
                eventHub.$emit('open-notify-modal', {
                    message: this.$t('file_upload.message.error'),
                })
            }

            if (specificFiles.length === 0) return

            const fileList = []

            for (const file of specificFiles) {
                const fileAttribute = {
                    id: this.totalId,
                    name: file.name,
                    size: file.size,
                    data: URL.createObjectURL(file),
                    lastModified: file.lastModified,
                    type: file.type,
                }
                this.totalId += 1
                let fileAttrName
                if (this.upFileName == undefined) {
                    fileAttrName = fileAttribute['name']
                } else {
                    const name = fileAttribute['name']
                    if (this.selectedNumber == 0) {
                        fileAttrName = this.upFileName + '.' + this.getFileType(name)
                    } else {
                        let numberFileLine = '_' + (this.selectedNumber < 10 ? '0' + this.selectedNumber : this.selectedNumber)
                        fileAttrName = this.upFileName + numberFileLine + '.' + this.getFileType(name)
                    }
                    fileAttribute['name'] = fileAttrName
                }
                this.selectedNumber += 1
                fileList.push(fileAttribute)
            }
            eventHub.$emit(this.emit_message, { file_list: fileList })
        },
        download(event) {
            this.$emit('clickDownload', event.target.innerText)
        },
        clearFile(index) {
            if (this.disabledStatus) return
            $('.drop__input').val('')
            this.$emit('clearFile', index)
        },
        getFileType(filePath) {
            let startIndex = filePath.lastIndexOf('.')
            if (startIndex != -1) return filePath.substring(startIndex + 1, filePath.length).toLowerCase()
            else return ''
        },
        clearAdditionalFile(index) {
            if (this.disabledStatus) return
            this.$emit('clearAdditionalFile', index)
        },
        getFileDownloadUri(file_path, file_name) {
            return '/utilities/download_file?file_path=' + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
        },
    },
}
</script>

<style scoped lang="scss">
.-undrag {
    border: dashed;
    border-color: gray;
}

.-drag {
    border: dashed;
    border-color: red;
}
.flex-center-ai {
    display: flex;
    justify-content: center;
    align-items: center;
}
.flex-ai-fs-0 {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}
label.file-upload {
    cursor: pointer;
    color: #1976d2;
}
</style>
