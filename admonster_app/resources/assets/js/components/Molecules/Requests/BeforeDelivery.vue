<template>
    <div id="mail">
        <div
            class="mail-body"
            :style="{ height: bodyHeight, maxHeight: maxBodyHeight, minHeight: minBodyHeight }"
        >
            <!-- template header -->
            <div v-html="beforeWorkTemplate.header" class="px-3 pt-3 mb-3"></div>
            <!-- template body -->
            <div class="pa-2">
                <!-- メール情報 -->
                <template v-if="mail">
                    <v-chip label small>{{ $t('common.mail.info') }}</v-chip>
                    <div class="pa-2 pb-3">
                        <div>From&#058;&nbsp;{{ mail.from }}</div>
                        <div>To&#058;&nbsp;{{ mail.to }}</div>
                        <div>Cc&#058;&nbsp;{{ mail.cc }}</div>
                        <div>{{ $t('common.datetime.date') }}&#058;&nbsp;{{ mail.date | formatDateYmdHm }}</div>
                        <div>{{ $t('requests.request_name') }}&#058;&nbsp;{{ mail.subject }}</div>
                    </div>
                </template>
                <!-- ファイル情報 -->
                <template v-else-if="requestFile">
                    <v-chip label small>{{ $t('common.file.file_info') }}</v-chip>
                    <div class="pa-2 pb-3">
                        <div>{{ $t('common.file.file_info_details.name') }}&#058;&nbsp;{{ requestFile.name }}</div>
                        <div>{{ $t('common.file.file_info_details.request_line_number') }}&#058;&nbsp;{{ requestFile.row_no }}</div>
                        <div>{{ $t('common.file.file_info_details.capture_datetime') }}&#058;&nbsp;{{ requestFile.created_at | formatDateYmdHm }}</div>
                        <div>{{ $t('common.file.file_info_details.capture_person') }}&#058;&nbsp;
                            <v-tooltip top>
                                <v-avatar slot="activator" size="26px" class="ma-1">
                                    <img :src="user_image_path(requestFile.created_user_id)">
                                </v-avatar>
                                <span>{{ user_name(requestFile.created_user_id) }}</span>
                            </v-tooltip>
                        </div>
                    </div>
                </template>
                <!-- 納品データ -->
                <v-chip label small>{{ $t('deliveries.info_data') }}</v-chip>
                <div class="pa-2">
                    <template v-for="items in beforeWork.item_configs">
                        <template v-for="(item, key) in items">
                            <v-layout v-if="isItemDisplay(item)" row wrap :key="key">
                                <v-flex xs4 class="font-weight-bold">
                                    <label-component
                                        :label-id="item.label_id"
                                        :lang-data="labelData"
                                    ></label-component>
                                </v-flex>
                                <v-flex xs8 class="height-center">
                                    <template v-if="isItemTypeFile(item.item_type)">
                                        <v-layout row wrap>
                                            <template v-for="seq in beforeDelivery[item.group][item.key]">
                                                <v-flex md2 xs3 ma-1 :key="deliveryFileInfo(seq, 'file_path')">
                                                    <file-thumbnail
                                                        :file-info="deliveryFileInfo(seq)"
                                                        :max-height="100"
                                                        :max-width="100"
                                                    ></file-thumbnail>
                                                </v-flex>
                                            </template>
                                        </v-layout>
                                    </template>
                                    <template v-else>
                                        <item-type
                                            :item="getItemConfigWithValue(beforeDelivery[item.group][item.key], item)"
                                            :lang-data="labelData"
                                        ></item-type>
                                    </template>
                                </v-flex>
                            </v-layout>
                        </template>
                    </template>
                </div>
            </div>
            <!-- template footer -->
            <div v-html="beforeWorkTemplate.footer" class="px-3 py-2"></div>
        </div>
        <!-- 添付ファイル -->
        <div
            v-if="attachments.length > 0"
            class="mail-attachment overflow-hidden"
            :style="{ height: attachmentHeight }"
        >
            <v-divider class="my-0"></v-divider>
            <!-- サイズ計測用 -->
            <resize-observer class="pa-0" @notify="handleResize"></resize-observer>
            <v-layout row fill-height class="overflow-hidden" style="padding: 0px;">
                <template v-for="(attachment, i) in showableAttachmentList">
                    <v-flex
                        class="border-right filter my-auto flex-padding"
                        :style="{ maxWidth: `${attachmentWidth}px`, cursor: 'pointer', minWidth: `${attachmentWidth}px`, height: '100%' }"
                        :key="i"
                        @click.stop="download(attachment)"
                    >
                        <div>
                            <v-icon>mdi-file-document</v-icon>
                            <span
                                class="text-nowrap overflow-hidden d-inline-block text-overflow-ellipsis grey--text"
                                :style="{ height: '1.5em', width: 'calc(100% - 30px)' }"
                            >
                                {{ attachment.name }}
                            </span>
                        </div>
                    </v-flex>
                </template>
                <!-- 未表示がある場合 -->
                <template v-if="showableAttachmentList.length < attachments.length">
                    <div class="align-self-center" :style="{ width: `${hiddenIconWidth}px` }">
                        <v-badge color="green" right overlap>
                            <template slot="badge">
                                <span>{{ attachments.length - showableAttachmentList.length }}</span>
                            </template>
                            <v-avatar size="32px">
                                <v-icon>more_horiz</v-icon>
                            </v-avatar>
                        </v-badge>
                    </div>
                </template>
                <v-tooltip top class="ml-auto mr-2 flex-none" style="margin-top: 8px;">
                    <v-icon large slot="activator" @click.stop="showAttachmentList">mdi-cloud-download-outline</v-icon>
                    <span>{{ $t('common.mail.file_list') }}</span>
                </v-tooltip>
            </v-layout>
        </div><!-- /添付ファイル -->
        <mail-attachments-modal ref="attachmentListModal" :attachments="attachments"></mail-attachments-modal>
    </div>
</template>

<script>
import MailAttachmentsModal from './MailAttachmentsModal'
import LabelComponent from '../../Atoms/Labels/Label'
import ItemType from '../../Molecules/ImportedFiles/ItemType'
import FileThumbnail from '../../Atoms/FileInput/FileThumbnail'
import { ResizeObserver } from 'vue-resize'

export default {
    components: {
        MailAttachmentsModal,
        LabelComponent,
        ItemType,
        FileThumbnail,
        ResizeObserver,
    },
    props: {
        beforeWork: { type: Object, require: true },
        mail: { type: Object, require: false, default: () => {} },
        requestFile: { type: Object, required: false, default: () => {} },
        labelData: { type: Object, required: false, default: () => {} },
        height: { type: String, required: false, default: '' },
        maxHeight: { type: String, required: false, default: '' },
        minHeight: { type: String, required: false, default: '' },
    },
    data: () =>({
        displayCount: 0,
        attachmentWidth: 200,
        hiddenIconWidth: 50,
        attachmentHeight: '50px',
    }),
    mounted() {
        this.handleResize()
    },
    computed: {
        bodyHeight () {
            // calcで計算できないのでそのまま返す
            if (this.height === 'auto' || this.height === '') return this.height
            // 添付ファイルを考慮した高さを返す
            const attachmentLength = this.attachments.length
            return attachmentLength > 0 ? `calc(${this.height} - ${this.attachmentHeight})` : `calc(${this.height})`
        },
        maxBodyHeight () {
            if (this.maxHeight === '') return this.maxHeight
            return this.attachments.length > 0 ? `calc(${this.maxHeight} - ${this.attachmentHeight})` : `calc(${this.maxHeight})`
        },
        minBodyHeight () {
            if (this.minHeight === '') return this.minHeight
            return this.attachments.length > 0 ? `calc(${this.minHeight} - ${this.attachmentHeight})` : `calc(${this.minHeight})`
        },
        attachments () {
            let attachments = [];
            // 前工程の納品内容がある場合は前工程の内容を表示する
            if (this.deliveryFiles) {
                let fileSeqNumbers = []
                this.beforeWork['item_configs'][0].forEach(item => {
                    if (this.isItemTypeFile(item.item_type)) {
                        fileSeqNumbers = [...fileSeqNumbers, ...this.beforeDelivery[item.group][item.key]];
                    }
                })
                for (let seqNo of fileSeqNumbers) {
                    const item = this.deliveryFiles[seqNo]
                    const fileName = item.name
                    const filePath = item.file_path
                    const uri = `/utilities/download_file?file_path=${encodeURIComponent(filePath)}&file_name=${encodeURIComponent(fileName)}`
                    attachments.push({
                        id: item.id,
                        name: fileName,
                        path: filePath,
                        downloadUri: uri,
                        size: item.size,
                    });
                }
            // それ以外はメール情報を表示する
            } else if (this.mail && this.mail.attachments) {
                this.mail.attachments.forEach(function (item) {
                    const fileName = item.name
                    const filePath = item.file_path
                    const uri = `/utilities/download_file?file_path=${encodeURIComponent(filePath)}&file_name=${encodeURIComponent(fileName)}`
                    attachments.push({
                        id: item.id,
                        name: fileName,
                        path: filePath,
                        downloadUri: uri,
                        size: item.file_size,
                    });
                });
            }
            return attachments;
        },
        showableAttachmentList () {
            return this.attachments.slice(0, this.displayCount)
        },
        beforeDelivery () {
            return JSON.parse(this.beforeWork.content)
        },
        beforeWorkTemplate () {
            return JSON.parse(this.beforeWork.before_work_template)
        },
        deliveryFiles () {
            return this.beforeWork ? this.beforeWork['files'] : null
        }
    },
    methods: {
        download (attachment) {
            const elem = document.createElement('a')
            // 別タブに切り替えないようにする
            elem.download = attachment.name
            elem.href = attachment.downloadUri
            elem.target   = '_blank'
            document.body.appendChild(elem)
            elem.click()
            document.body.removeChild(elem)
        },
        showAttachmentList () {
            this.$refs.attachmentListModal.show()
        },
        handleResize () {
            let displayCount = Math.floor(this.$el.clientWidth / this.attachmentWidth)
            const widthSizeToUse = displayCount * this.attachmentWidth + this.hiddenIconWidth
            if (widthSizeToUse > this.$el.clientWidth) displayCount -= 1
            this.displayCount = displayCount
        },
        getItemConfigWithValue (value, itemConfig) {
            itemConfig.value = value
            return itemConfig
        },
        isItemDisplay (item) {
            const keys = item.group in this.beforeWorkTemplate.body ? this.beforeWorkTemplate.body[item.group] : []
            return keys.find(key => key === item.key)
        },
        deliveryFileInfo (seqNo, key='') {
            if (!this.beforeWork['files'][seqNo]) {
                console.warn('Not found file: seq_no: ' + seqNo)
                return ''
            }
            if (key) return this.beforeWork['files'][seqNo][key]
            return this.beforeWork['files'][seqNo]
        },
        isItemTypeFile (itemType) {
            return _const.ITEM_CONFIG_TYPE.FILE_INPUT === itemType
        },
        async thumbnailUrl (filePath) {
            const res = await axios.get('/api/utilities/getFileReferenceUrlForThumbnail', {
                params: { 'file_path': filePath }
            })
            if (res.data.status === 200) {
                return res.data.url
            } else {
                console.warn('error: ' + res)
            }
            return ''
        },
    },
}
</script>

<style scoped>
#mail {
    background-color: #fff;
    font-size: 12px;
}
.mail-attachment > div {
    padding: 8px;
}
.mail-body {
    overflow-y: auto;
}
.mail-attachment {
    position: relative;
}
.filter:hover {
    background-color: rgba(0,0,0,0.08);
    transition: .3s ease-in-out;
}
.flex-none {
    flex: none !important;
}
.flex-padding {
    padding-top: 12px!important;
    padding-left: 24px!important;
    padding-right: 24px!important;
    padding-bottom: 4px!important;
}
.v-card__text {
    padding: 8px;
}
.v-btn {
    text-decoration: none;
}
.border-right {
    border: 0 solid rgba(0,0,0,.12);
    border-right-width: thin;
}
.text-overflow-ellipsis {
    text-overflow: ellipsis;
}
.text-nowrap {
    white-space: nowrap;
}
.height-center {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
</style>
