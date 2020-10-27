<template>
    <div style="position: relative;" class="c00800 mt-2">
        <label :class="['v-label', dark ? 'theme--dark' : 'theme--light']">{{label}}</label>
        <div @click.stop="">
            <template v-for="(file, index) in value">
                <div :key="index">
                    <template>
                        <div :class="[dark ? 'text-color-white' : '', 'text-xs-left']">
                            <!-- 表示ファイルがプレビューに対応している -->
                            <span
                                class="file-info"
                                v-if="isPreviewableFileExtension(file.name)"
                                @click.stop.exact="filePreview(index)"
                                @click.stop.shift="filePreview(index, 'window')"
                                @click.stop.ctrl="filePreview(index, 'tab')"
                                style="cursor:pointer;"
                            >
                                <v-tooltip top>
                                    <span slot="activator" class="pr-2">
                                        <v-icon medium>
                                            mdi-file-eye
                                        </v-icon>
                                    </span>
                                    <span>{{ $t('list.tooltip.preview') }}</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <span slot="activator">
                                        {{ file.name + '(' + convertToVisibleForm(file.size) + '：' }}{{ file.created_at | parseUtcStringToUserTimezoneDate }}{{ ')' }}
                                    </span>
                                    <span>
                                        {{ file.name + '(' + convertToVisibleForm(file.size) + '：' }}{{ file.created_at | parseUtcStringToUserTimezoneDate }}{{ ')' }}
                                    </span>
                                </v-tooltip>
                            </span>
                            <!-- 表示ファイルがプレビューに対応していない -->
                            <span
                                class="file-info"
                                v-else
                            >
                                <v-tooltip top>
                                    <span slot="activator">
                                        {{ file.name + '(' + convertToVisibleForm(file.size) + '：' }}{{ file.created_at | parseUtcStringToUserTimezoneDate }}{{ ')' }}
                                    </span>
                                    <span>
                                        {{ file.name + '(' + convertToVisibleForm(file.size) + '：' }}{{ file.created_at | parseUtcStringToUserTimezoneDate }}{{ ')' }}
                                    </span>
                                </v-tooltip>
                            </span>
                        </div>
                    </template>
                </div>
            </template>
            <div :class="[dark ? 'text-color-white' : '', 'text-xs-left', 'text-cut', 'notes-font']">
                <span>
                    <v-tooltip top>
                        <span slot="activator">{{ '※' + $t('approvals.file_preview.compare_same_file_name') }}</span>
                        <span>{{ $t('approvals.file_preview.compare_same_file_name') }}</span>
                    </v-tooltip>
                </span>
            </div>
            <div :class="[dark ? 'text-color-white' : '', 'text-xs-left', 'text-cut', 'notes-font']">
                <span>
                    <v-tooltip top>
                        <span slot="activator" @click.stop="openSupportedExtensionListModal()" style="cursor:pointer;">{{ '※' + $t('approvals.file_preview.here_supported_list') }}</span>
                        <span>{{ $t('approvals.file_preview.here_supported_list') }}</span>
                    </v-tooltip>
                </span>
            </div>
        </div>
        <file-preview-dialog ref="filePreviewDialog" :width.sync="filePreviewDialogWidth" :stepId="stepId" :localStorageKey="localStorageKey"></file-preview-dialog>
    </div>
</template>

<script>
import FilePreviewDialog from '../../Atoms/Dialogs/FilePreviewDialog'

export default {
    components:{
        FilePreviewDialog,
    },
    props: {
        candidates: { type: Array, required: false, default: () => [] },
        value: {type: Array, required: false, default: () => []},
        comparisonContents: { type: Array, required: false, default: () => [] },
        label: {type: String, required: false, default: ''},
        dark: {type: Boolean, required: false, default: false},
        filePreviewWidth: {type: Number, required: false, defualt: 600},
        stepId: {type: Number, required: true},
        localStorageKey: {type: String, required: true},
    },
    data: () => ({
        supportedExtensions: ['.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.jpg', '.jpeg', '.png', '.gif', '.mp4', '.txt']
    }),
    computed: {
        filePreviewDialogWidth: {
            set (val) {
                this.$emit('update:filePreviewWidth', Number(val))
            },
            get () {
                return this.filePreviewWidth
            }
        }
    },
    methods: {
        isPreviewableFileExtension: function (fileName) {
            const pos = fileName.lastIndexOf('.')
            const extension = fileName.slice(pos + 1)
            return this.supportedExtensions.indexOf('.' + extension) !== -1
        },
        openSupportedExtensionListModal: function () {
            this.$refs.filePreviewDialog.showSupportedExtensionListModal()
        },
        filePreview:async function (index, type = '') {
            const previewComparisonContent = this.comparisonContents.filter(comparisonContent => this.value[index].name == comparisonContent.name)
            this.$refs.filePreviewDialog.show(previewComparisonContent, this.candidates, type)
        },
        convertToVisibleForm: function (size, decimalPlaces = 1) {
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
    }
}
</script>

<style scoped>
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

.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

.text-color-white {
    color: white !important;
}

.c00800 {
    white-space: normal;
}
/* Tooltipのspanには効かない */
.c00800 .file-info {
    font-size: 14px;
    display: flex;
    align-items: center;
    padding: 10px 0;
}
.notes-font {
    font-size: 10px;
    color: #9e999a;
}
</style>
