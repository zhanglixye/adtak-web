<template>
    <div id="file-preview" :style="{'height': '100%', 'overflow': 'auto'}">
        <div class="screen-back-color" :style="{'width': backgroundColorWidth}">
            <v-icon
                v-if="!isWide"
                @click="showAdjustment"
                :style="{'font-size': '35px', 'background-color': 'black', 'position': 'fixed', 'right': '0px'}"
                large
                color="white"
            >settings</v-icon>
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
                        <div v-if="!isPreviewUrlIssued(index)" :style="{'height': '100%', 'width': '100%'}">
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
        <alert-dialog ref="alert" width="350px" :style="{'z-index': '3000'}"></alert-dialog>
        </div>
        <adjustment-file-preview-dialog ref="adjustment" :width.sync="filePreviewWidth"></adjustment-file-preview-dialog>
    </div>
</template>

<script>
import filePreviewMixin from '../../../../mixins/filePreviewMixin'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import AdjustmentFilePreviewDialog from '../../../Atoms/Dialogs/AdjustmentFilePreviewDialog'

const maxAddFileCount = 10

export default {
    mixins: [filePreviewMixin],
    components:{
        ProgressCircular,
        AlertDialog,
        AdjustmentFilePreviewDialog,
    },
    props: {
        inputs: { type: Object, required: true },
    },
    data: () => ({
        isEmbed: true,
        previewUrls: [],
        files: [],
        candidates: [],
        count: 1,
        deleteCount: 0,
        filePreviewWidth_ : 600,
        widnowInnerWidth: window.innerWidth,
        timer: null,
    }),
    computed: {
        previewFiles () {
            return this.files.slice(0, this.count * maxAddFileCount)
        },
        key () {
            return 'filePreviewWidth'
        },
        storageKey () {
            return 'eachStepStates'
        },
        defaultFilePreviewWidth () {
            return 600
        },
        isWide () {
            return this.inputs.is_wide
        },
        filePreviewWidthStyle () {
            return this.isWide ? '100%' : `${this.filePreviewWidth}px`
        },
        filePreviewWidth: {
            set (val) {
                this.filePreviewWidth_ = val

                const stepId = this.inputs['step_id']
                if (!stepId) return
                
                const string = localStorage.getItem(this.inputs['local_storage_key'])
                let obj = JSON.parse(string)
                if (!obj) obj = {}
                
                if (!(this.storageKey in obj)) obj[this.storageKey] = {}
                    

                const eachStepStates = obj[this.storageKey]
                if (!(stepId in eachStepStates)) eachStepStates[stepId] = {}

                const stepStatus = eachStepStates[stepId]
                stepStatus[this.key] = val

                localStorage.setItem(this.inputs['local_storage_key'], JSON.stringify(obj))
            },
            get () {
                return this.filePreviewWidth_
            }
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
    created () {
        this.filePreviewWidth_ = this.getWidth(this.defaultFilePreviewWidth)
        const file_previews = this.inputs['file_preview']
        let files = JSON.parse(file_previews)
        const candidates = files.pop()
        this.show(files, candidates)
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
            // 読み込んでいない場合は再度読み込みを行っている
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
        show:async function (files, candidates) {
            this.candidates = candidates
            this.files = files
            try {
                for (const [index, file] of this.files.entries()) {
                    await this.generatePreviewUrls(file)
                    this.setInterval(index, 'preview' + index)
                }
                window.addEventListener('resize', this.handleResize)
            } catch (e) {
                this.$refs.alert.show(
                    this.$t('common.message.internal_error'),
                    function () {
                        window.close()
                    }
                )
            }
        },
        generatePreviewUrls:async function () {
            try {
                for (const file of this.files) {
                    const url = await this.getFilePreviewUrl(file.file_path, this.isEmbed)
                    this.previewUrls.push(url)
                }
            } catch (e) {
                if (e.statusCode === 408) {
                    // タイムアウト処理
                    this.previewUrls.push('')
                } else {
                    throw e
                }

            }
        },
        getWidth: function (defaultWidth) {
            const stepId = this.inputs['step_id']
            if (!stepId) return defaultWidth
            
            const string = localStorage.getItem(this.inputs['local_storage_key'])
            const obj = JSON.parse(string)
            if (!obj) return defaultWidth
            if (!(this.storageKey in obj)) return defaultWidth

            const eachStepStates = obj[this.storageKey]
            if (!eachStepStates) return defaultWidth
            if (!(stepId in eachStepStates)) return defaultWidth

            const stepStatus = eachStepStates[stepId]
            const key = 'filePreviewWidth'
            if (!(key in stepStatus)) return defaultWidth

            return stepStatus[key]
        },
        showAdjustment: function () {
            this.$refs.adjustment.show()
        }
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
.screen-back-color {
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
#file-preview {
    height: 100%;
    overflow: auto;
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
