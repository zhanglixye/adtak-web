<template>
    <div id="mail">
        <div
            v-if="mail"
            class="mail-body"
            :style="{ height : bodyHeight, maxHeight: maxBodyHeight, minHeight: maxBodyHeight }"
        >
            <div>
                <div class="subheading">
                    <div>{{ mail.subject }}</div>
                    <div class="text-xs-right caption">{{ mail.date | formatDateYmdHm(true) }}</div>
                </div>
                <div class="grey--text">
                    <div>From:&nbsp;{{ mail.from }}</div>
                    <div>To:&nbsp;{{ mail.to }}</div>
                    <div>Cc:&nbsp;{{ mail.cc }}</div>
                </div>
            </div>
            <div>
                <span v-html="mail.body"></span>
            </div>
        </div>
        <div class="mail-attachment overflow-hidden" v-if="mailAttachments.length > 0" :style="{height : attachmentHeight}">
            <v-divider class="my-0"></v-divider>
            <!-- サイズ計測用 -->
            <resize-observer class="pa-0" @notify="handleResize"></resize-observer>
            <v-layout row fill-height class="overflow-hidden" style="padding: 0px;">
                <template v-for="(attachment, i) in showableAttachmentList">
                    <v-flex
                        @click.stop="download(attachment)"
                        class="border-right filter my-auto flex-padding"
                        :style="{'max-width': `${attachmentWidth}px`, 'cursor': 'pointer', 'min-width': `${attachmentWidth}px`, 'height': '100%'}"
                        :key="i"
                    >
                        <div>
                            <v-icon>mdi-file-document</v-icon>
                            <span
                                class="text-nowrap overflow-hidden d-inline-block text-overflow-ellipsis grey--text"
                                :style="{'height': '1.5em', 'width': 'calc(100% - 30px)'}"
                            >
                                {{ attachment.name }}
                            </span>
                        </div>
                    </v-flex>
                </template>
                <!-- 未表示がある場合 -->
                <template v-if="showableAttachmentList.length < mailAttachments.length">
                    <div class="align-self-center" :style="{'width': `${hiddenIconWidth}px`}">
                        <v-badge
                            color="green"
                            right
                            overlap
                        >
                            <template slot="badge">
                                <span>
                                    {{mailAttachments.length - showableAttachmentList.length}}
                                </span>
                            </template>
                            <v-avatar size="32px">
                                <v-icon >more_horiz</v-icon>
                            </v-avatar>
                        </v-badge>
                    </div>
                </template>
                <v-tooltip top class="ml-auto mr-2 flex-none" style="margin-top: 8px;">
                    <v-icon large @click.stop="showAttachmentList" slot="activator">mdi-cloud-download-outline</v-icon>
                    <span>{{ $t('common.mail.file_list') }}</span>
                </v-tooltip>
            </v-layout>
        </div>
        <mail-attachments-modal ref="attachmentListModal" :attachments="mailAttachments"></mail-attachments-modal>
    </div>
</template>

<script>
import MailAttachmentsModal from './MailAttachmentsModal'
import { ResizeObserver } from 'vue-resize'

export default {
    components: {
        MailAttachmentsModal,
        ResizeObserver
    },
    props: {
        mail: { type: Object, require: true },
        height: { type: String, required: false, default: ''},// 数字（cssの単位）
        maxHeight: { type: String, required: false, default: ''},// 数字（cssの単位）
        minHeight: { type: String, required: false, default: ''},
    },
    data: () =>({
        displayCount: 0,
        attachmentWidth: 200,
        hiddenIconWidth: 50,
        attachmentHeight: '50px',
    }),
    computed: {
        bodyHeight: function () {
            // calcで計算できないのでそのまま返す
            if (this.height === 'auto') return 'auto'
            if (this.height === '') return ''

            // 添付ファイルを考慮した高さを返す
            const attachmentLength = this.mailAttachments.length
            return attachmentLength > 0 ? `calc(${this.height} - ${this.attachmentHeight})` : `calc(${this.height})`// 上記以外
        },
        maxBodyHeight: function () {
            if (this.maxHeight === '') return ''
            return this.mailAttachments.length > 0 ? `calc(${this.maxHeight} - ${this.attachmentHeight})` : `calc(${this.maxHeight})`
        },
        minBodyHeight: function () {
            if (this.minHeight === '') return ''
            return this.mailAttachments.length > 0 ? `calc(${this.minHeight} - ${this.attachmentHeight})` : `calc(${this.minHeight})`
        },
        mailAttachments: function() {
            let array = [];
            if (this.mail && this.mail.attachments) {
                this.mail.attachments.forEach(function (item) {
                    const fileName = item.name
                    const filePath = item.file_path
                    const id = item.id
                    const fileSize = item.file_size
                    let uri = '/utilities/download_file?file_path='
                    uri = uri + encodeURIComponent(filePath) + '&file_name=' + encodeURIComponent(fileName)
                    array.push({
                        name: fileName,
                        path: filePath,
                        downloadUri: uri,
                        id: id,
                        size: fileSize
                    });
                });
            }
            return array;
        },
        showableAttachmentList: function () {
            return this.mailAttachments.slice(0, this.displayCount)
        }
    },
    mounted() {
        this.handleResize()
    },
    methods: {
        download: function (attachment) {
            const a = document.createElement('a')
            // 別タブに切り替えないようにする
            a.download = attachment.name // ダウンロードを失敗した時に出るファイル名
            a.target   = '_blank'
            a.href = attachment.downloadUri
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
        },
        showAttachmentList: function () {
            this.$refs.attachmentListModal.show()
        },
        handleResize: function() {
            let displayCount = Math.floor(this.$el.clientWidth / this.attachmentWidth)
            const widthSizeToUse = displayCount * this.attachmentWidth + this.hiddenIconWidth // 使用する幅
            if (widthSizeToUse > this.$el.clientWidth) displayCount -= 1 // コンポーネントの幅が足りない場合は、表示数を減らす
            this.displayCount = displayCount
        },
    },
}
</script>

<style scoped>
#mail {
    font-size: 12px;
    background-color: #fff;
}
.mail-body > div,
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
    /* padding: 16px; */
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
</style>
