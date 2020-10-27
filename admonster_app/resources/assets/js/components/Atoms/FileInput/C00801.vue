<template>
    <div class="c00801 mt-2">
        <label :class="['v-label', dark ? 'theme--dark' : 'theme--light']">{{ label }}</label>
        <div class="pt-2">
            <div
                v-if="hasData && isThumbnail"
                style="cursor:pointer;"
                @click.stop.exact="filePreview()"
                @click.stop.shift="filePreview('window')"
                @click.stop.ctrl="filePreview('tab')"
            >
                <v-tooltip top>
                    <!-- ローカル開発の際にはサムネイル画像がないので、動画ファイルの場合に何も表示されない -->
                    <v-img
                        :src="url"
                        :height="imageLoading ? 0 : contentHeight"
                        :width="imageLoading ? 0 : contentWidth"
                        contain
                        slot="activator"
                    ></v-img>
                    <span >{{ fileName }}</span>
                </v-tooltip>
                <v-progress-circular
                    v-if="imageLoading"
                    indeterminate
                    color="grey"
                    :style="{height: contentHeight}"
                ></v-progress-circular>
            </div>
            <div
                v-else-if="hasData && !isThumbnail"
                class="text-break"
                style="cursor:pointer;"
                @click.stop.exact="filePreview()"
                @click.stop.shift="filePreview('window')"
                @click.stop.ctrl="filePreview('tab')"
            >
                <v-tooltip top>
                    <div
                        slot="activator"
                        :style="{height: contentHeight, width: contentWidth, border: '1px solid lightgray'}"
                    >
                        <v-icon :dark="dark" :size="contentIconSize" :color="dark ? '' : 'grey lighten-1'" :style="{'margin-top': iconTopMargin}">insert_drive_file</v-icon>
                    </div>
                    <span>{{ fileName }}</span>
                </v-tooltip>
            </div>
            <div
                v-else
                :class="['text-break', dark ? 'text-color-white' : '']"
                :style="{height: contentHeight, width: contentWidth, position: 'relative'}"
            >
                <span class="position-absolute">{{ $t('item_configs.c00801.unset') }}</span>
            </div>
            <div :class="[dark ? 'text-color-white' : '', 'text-xs-left', 'text-cut', 'notes-font']">
                <span>
                    {{ `${$t('item_configs.c00801.open_the_file_preview')}${$t('item_configs.c00801.parenthesis.open')}`}}
                    <span
                        class="under"
                        @click.stop="openSupportedExtensionListModal()"
                        style="cursor:pointer; "
                    >
                        {{ $t('item_configs.c00801.supported_extension') }}
                    </span>
                    {{ $t('item_configs.c00801.parenthesis.close')}}
                </span>
            </div>
        </div>
        <file-preview-dialog ref="filePreviewDialog" :width.sync="filePreviewDialogWidth"></file-preview-dialog>
    </div>
</template>

<script>
import FilePreviewDialog from '../../Atoms/Dialogs/FilePreviewDialog'

export default {
    components:{
        FilePreviewDialog,
    },
    props: {
        value: { type: Object, required: false, default: () => {} },
        label: { type: String, required: false, default: '' },
        dark: { type: Boolean, required: false, default: false },
        candidates: { type: Array, required: false, default: () => [] },
        comparisonContents: { type: Array, required: false, default: () => [] },
        filePreviewWidth: {type: Number, required: false, defualt: 600},
        height: {type: [Number, String], required: false, default: 150},
        iconSize: {type: [Number, String], required: false, default: 40},
        width: {type: [Number, String], required: false, default: '100%'},
    },
    computed: {
        contentHeight () {
            return Number.isInteger(this.height) ? `${this.height}px` : this.height
        },
        contentWidth () {
            return Number.isInteger(this.width) ? `${this.width}px` : this.width
        },
        contentIconSize () {
            return Number.isInteger(this.iconSize) ? `${this.iconSize}px` : this.iconSize
        },
        iconTopMargin () {
            return `calc((${this.contentHeight} - ${this.contentIconSize}) / 2)`
        },
        isThumbnail () {
            /* s3を使い前提の条件
            * ローカルの際にはサムネイルはないので、この条件はあっていない。
            */
            return RegExp('video/*').test(this.mimeType) || RegExp('image/*').test(this.mimeType)
        },
        filePath () {
            return this.value ? this.value['file_path'] : ''
        },
        mimeType () {
            return this.value ? this.value['mime_type'] : ''
        },
        url () {
            return this.thumbnail ? this.thumbnail['url'] : ''
        },
        originalUrl () {
            return this.original ? this.original['url'] : ''
        },
        filePreviewDialogWidth: {
            set (val) {
                this.$emit('update:filePreviewWidth', Number(val))
            },
            get () {
                return this.filePreviewWidth
            }
        },
        fileName () {
            return this.value ? this.value['name'] : ''
        }
    },
    data: () => ({
        thumbnail: null,
        original: null,
        imageLoading: false,
        hasData: true,
    }),
    mounted () {
        if (!this.value) {
            this.hasData = false
            return
        }
        this.init()
    },
    methods: {
        filePreview:async function (type = '') {
            const previewComparisonContent = this.comparisonContents.filter(comparisonContent =>  comparisonContent !== null)
            this.$refs.filePreviewDialog.show(previewComparisonContent, this.candidates, type)
        },
        async init () {
            try {
                this.imageLoading = true
                const res = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail', {
                    params: { file_path: this.filePath }
                })
                this.thumbnail = res.data
            } catch (e) {
                console.log(e)
            } finally {
                this.imageLoading = false
            }
        },
        openSupportedExtensionListModal: function () {
            this.$refs.filePreviewDialog.showSupportedExtensionListModal()
        },
    }
}
</script>

<style scoped>
.c00801 {
    position: relative;
}
.v-label {
    height: 20px;
    line-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    top: -14px;
    left: 0px;
    right: auto;
    position: absolute;
    -webkit-transform-origin: top left;
    transform-origin: top left;
    white-space: nowrap;
    pointer-events: none;
    font-size: calc(12px * 0.75);
}

.text-break {
    white-space: pre-wrap;
    word-break: break-all;
}

.notes-font {
    font-size: 10px;
    color: #9e999a;
}

.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

.text-color-white {
    color: white !important;
}

.under {
  text-decoration: underline;
}

.position-absolute{
  position: absolute;  /*要素を浮かす*/
  /*relativeを指定した親要素を支点とした位置をすべて0に指定↓*/
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto; /*上下左右中央に*/
  width:100%;
  height: 1em;/* フォントサイズ */
}
</style>