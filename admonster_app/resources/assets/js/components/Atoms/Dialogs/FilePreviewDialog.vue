<template>
    <div id="file-preview-Dialog">
        <v-dialog v-model="showSelf" fullscreen persistent>
            <div class="dialog-back-color" :style="{'width': backgroundColorWidth}">
                <v-icon
                    v-if="!isWide"
                    @click="showAdjustment"
                    :style="{'font-size': '35px', 'background-color': 'black', 'position': 'fixed', 'right': '45px'}"
                    large
                    color="white"
                >settings</v-icon>
                <v-icon
                    @click="hide"
                    :style="{'font-size': '35px', 'background-color': 'black', 'position': 'fixed', 'right': '0px'}"
                    large
                    color="white"
                >clear</v-icon>
                <div
                    v-for="(file, index) in previewFiles"
                    :key="index"
                    :id="['file-preview' + index]"
                    class="file-content"
                >
                    <table :style="{ width: filePreviewWidthStyle }">
                        <tr class="table-head">
                            <div
                                v-if="file.user_id && candidates.length"
                                :style="{'min-width': '35%', 'line-height': '48px', 'text-align': 'left'}"
                            >
                                <v-avatar slot="activator" size="26px" class="ma-1">
                                    <img :src="userImagePath(file.user_id)">
                                </v-avatar>
                                <span>{{ userName(file.user_id) }}</span>
                            </div>
                            <div class="text-cut" :style="{'width': '65%', 'line-height': '48px'}">
                                <v-tooltip v-if="file.name" top>
                                    <span style="margin: auto" slot="activator">{{ file.name }}</span>
                                    <span >{{ file.name }}</span>
                                </v-tooltip>
                            </div>
                            <div v-if="!isWide" :style="{'position': 'absolute', 'right': '0', 'line-height': '48px'}">
                                <v-btn
                                    icon
                                    @click="deleteFile(index)"
                                    :style="{'margin-top': '0px'}"
                                >
                                    <v-icon :style="{'font-size': '25px'}" large color="black" >clear</v-icon>
                                </v-btn>
                            </div>
                        </tr>
                        <tr :style="{'height': '100%', 'background-color': '#d1d1d1'}">
                            <div v-if="!isPreviewUrlIssued(index)" :style="{'height': '100%', display: 'flex', justifyContent: 'center'}">
                                <iframe
                                    :id="['preview' + index]"
                                    v-if="isReadPreviewUrl(index)"
                                    :src="previewUrls[index]" :style="{'height': '100%', 'width': '100%'}"
                                    :frameborder="0"
                                    data-loaded=false
                                    @load="setLoaded('preview' + index)"
                                >
                                </iframe>
                                <div v-else :style="{'height': '100%', 'width': '100%', 'top': '0', 'bottom': '0'}">
                                    <div class="retry-wrap">
                                        <v-icon :style="{'font-size': '30px'}" large color="black" >error_outline</v-icon><br>
                                        {{ $t('approvals.file_preview.read_error') }}<br>
                                        <v-btn
                                            text
                                            @click="update(index)"
                                        >
                                            {{ $t('common.button.retry') }}
                                        </v-btn>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <progress-circular v-if="true"></progress-circular>
                            </div>
                        </tr>
                    </table>
                </div>
                <div v-if="canPreview()">
                    <v-tooltip top class="next-icon-wrap">
                        <span slot="activator" class="next-icon-wrap">
                            <v-btn :style="{'background-color': 'black', 'width': '50px', 'height': '50px'}" fab>
                                <v-icon
                                    @click="nextFiles()"
                                    x-large
                                    color="white"
                                >trending_flat</v-icon>
                            </v-btn>
                        </span>
                        <span>{{ $t('approvals.file_preview.next') + getNumberOfAdditionalFiles() + $t('approvals.file_preview.show_count') }}</span>
                    </v-tooltip>
                </div>
            </div>
        </v-dialog>
        <alert-dialog ref="alert" width="350px"></alert-dialog>
        <adjustment-file-preview-dialog ref="adjustment" :width.sync="filePreviewWidth"></adjustment-file-preview-dialog>
        <supported-extension-list-modal ref="supportedExtensionListModal"></supported-extension-list-modal>
    </div>
</template>

<script>
import filePreviewMixin from '../../../mixins/filePreviewMixin'
import ProgressCircular from '../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../Atoms/Dialogs/AlertDialog'
import AdjustmentFilePreviewDialog from './AdjustmentFilePreviewDialog'
import SupportedExtensionListModal from '../../Atoms/Dialogs/SupportedExtensionListModal'

const maxAddFileCount = 10

export default {
    mixins: [filePreviewMixin],
    components:{
        ProgressCircular,
        AlertDialog,
        AdjustmentFilePreviewDialog,
        SupportedExtensionListModal,
    },
    props: {
        width: {type: Number, required: false, default: 600},
        stepId: {type: Number, required: false, default: 0},
        localStorageKey: {type: String, required: false, default: ''},
        isWide: {type: Boolean, required: false, default: false},
    },
    data: () => ({
        isEmbed: true,
        previewUrls: [],
        showSelf: false,
        files: [],
        candidates: [],
        count: 1,
        comparisonUniqueStr: '',
        deleteCount: 0,
        widnowInnerWidth: window.innerWidth,
        timer: null,
        supportedExtensions: ['.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.jpg', '.jpeg', '.png', '.gif', '.mp4', '.txt']
    }),
    computed: {
        previewFiles () {
            return this.files.slice(0, this.count * maxAddFileCount)
        },
        filePreviewWidth: {
            set (val) {
                this.$emit('update:width', Number(val))
            },
            get () {
                return this.width
            }
        },
        filePreviewWidthStyle () {
            return this.isWide ? '100%' : `${this.filePreviewWidth}px`
        },
        backgroundColorWidth () {
            let backgroundWidth = (this.previewFiles.length - this.deleteCount) * (this.filePreviewWidth + 50) + 100
            if (backgroundWidth <= this.widnowInnerWidth) {
                return '100%'
            } else {
                return backgroundWidth += 'px'
            }
        },
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.handleResize)
    },
    methods: {
        handleResize () {
            // resizeのたびに発火
            if (this.timer !== null) {
                clearTimeout(this.timer);
            }

            // 毎回処理させないようにする
            const self = this
            this.timer = setTimeout(function() {
                self.widnowInnerWidth = window.innerWidth
            }, 200);
        },
        getNumberOfAdditionalFiles () {
            if (this.files.length - this.previewFiles.length < maxAddFileCount) {
                return this.files.length - this.previewFiles.length
            }
            return maxAddFileCount
        },
        nextFiles: function () {
            this.count += 1
        },
        update:async function (index) {
            try {
                this.previewUrls.splice(index, 1, null)
                const url = await this.getFilePreviewUrl(this.files[index].file_path, this.isEmbed)
                this.previewUrls.splice(index, 1, url)
            } catch (e) {
                if (e.statusCode === 408) {
                    // タイムアウト処理
                    this.previewUrls.push('')
                } else {
                    this.hide()
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                }
            }
        },
        deleteFile (index) {
            this.deleteCount += 1
            const element = document.getElementById('file-preview' + index)
            element.parentNode.removeChild(element)
        },
        isPreviewUrlIssued (index) {
            return this.previewUrls[index] === null || this.previewUrls[index] === undefined
        },
        isReadPreviewUrl (index) {
            if (document.getElementById('preview' + index) !== null) {
                if (document.getElementById('preview' + index).contentDocument !== null) {
                    document.getElementById('preview' + index).src = this.previewUrls[index]
                }
            }
            return this.previewUrls[index] !== ''
        },
        canPreview () {
            return this.count * maxAddFileCount < this.files.length
        },
        operator (userId) {
            const operator = this.candidates.filter(user => userId == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        userName (userId) {
            return this.operator(userId).name
        },
        userImagePath (userId) {
            return this.operator(userId).user_image_path
        },
        show:async function (files, candidates = [], type = '') {
            this.files = this.isPreviewableFileExtension(files)
            if (this.files.length > 0) {
                this.candidates = candidates
                // 表示方法に指定がある場合
                if (type) {
                    this.filePreviewOpen(type)
                    return
                }
                this.showSelf = true
                window.addEventListener('resize', this.handleResize)
                const uniqueStr = this.comparisonUniqueStr = new Date().getTime().toString(16) + Math.floor(1000*Math.random()).toString(16)
                try {
                    for (const [index, file] of this.files.entries()) {
                        if (this.showSelf) {
                            await this.generatePreviewUrls(file, uniqueStr)
                            this.setInterval(index, 'preview' + index)
                        }
                    }
                } catch (e) {
                    const self = this
                    this.$refs.alert.show(
                        this.$t('common.message.internal_error'),
                        function () {
                            self.hide()
                        }
                    )
                }
            }
        },
        generatePreviewUrls:async function (file, uniqueStr) {
            try {
                const url = await this.getFilePreviewUrl(file.file_path, this.isEmbed)
                if (uniqueStr === this.comparisonUniqueStr) this.previewUrls.push(url)
            } catch (e) {
                if (e.statusCode === 408) {
                    // タイムアウト処理
                    this.previewUrls.push('')
                } else {
                    throw e
                }

            }
        },
        filePreviewOpen:async function (type) {
            if (type) {
                const feature = type == 'window' ? 'width=1000, height=700, resizable=1, top=0, left=200' : ''
                let previewComparisonContent = this.files
                previewComparisonContent.push(this.candidates)
                const req = await axios.post('/approvals/filePreview', {
                    previewComparisonContent: JSON.stringify(previewComparisonContent),
                    localStorageKey: this.localStorageKey,
                    stepId: this.stepId,
                    isWide: this.isWide,
                })
                window.open('', '_blank', feature).document.write(req.data)
            }
        },
        hide: function () {
            this.previewUrls = []
            this.files = []
            this.count = 1
            this.comparisonUniqueStr = ''
            this.deleteCount = 0
            this.showSelf = false
            window.removeEventListener('resize', this.handleResize)
        },
        showAdjustment: function () {
            this.$refs.adjustment.show()
        },
        isPreviewableFileExtension (files) {
            let res = []
            for (const file of files) {
                const pos = file.file_path.lastIndexOf('.')
                const extension = file.file_path.slice(pos + 1)
                if (this.supportedExtensions.indexOf('.' + extension) !== -1) {
                    res.push(file)
                } else {
                    this.download(file)
                }
            }
            return res
        },
        download: async function (file) {
            const element = document.createElement('a')
            element.target   = '_blank'
            element.download = file.name
            element.href = `/utilities/download_file?file_path=${file.file_path}&file_name=${file.name}`
            document.body.appendChild(element)
            element.click()
            document.body.removeChild(element)
        },
        showSupportedExtensionListModal () {
            this.$refs.supportedExtensionListModal.show(this.supportedExtensions)
        },
    }
}
</script>
<style scoped>
.color {
    background-color:#4DB6AC;
    color:white;
    height:48px;
    padding: 0 24px;
}
.table-head {
    background-color: #ffffff;
    color: black;
    height:48px;
    padding: 0 24px;
    display: flex;
}
.dialog-back-color {
    height:100%;
    background-color:rgba( 33, 33, 33, 0.66 );
    display: flex;
}
.preview-content {
    display: flex;
}
.next-icon-wrap{
    left: 15px;
    top: 50%;
    text-align: center;
    display: block;
}
.retry-wrap{
    position:absolute;
    top: 50%;
    margin-top: -25px;
    width: 100%;
    text-align: center;
    display: block;
}
.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
.file-content {
    height: 100%;
    padding: 40px 40px 10px 0;
}
.file-content:first-of-type {
    padding-left: 42px;
}
.file-content table {
    height: 100%;
    position: relative;
    table-layout: fixed;
}
</style>
