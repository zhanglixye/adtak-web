<template>
    <div id="mail">
        <div class="mail-body"
            :style="{ height : bodyHeight, maxHeight: maxBodyHeight }"
        >
            <div>
                <div class="subheading">
                    <div>{{ requestMail.subject }}</div>
                    <div class="text-xs-right caption">{{ requestMail.recieved_at | formatDateYmdHm(true) }}</div>
                </div>
                <div class="grey--text">
                    <div>From:&nbsp;{{ from }}</div>
                    <div>To:&nbsp;{{ requestMail.to }}</div>
                    <div>Cc:&nbsp;{{ requestMail.cc }}</div>
                </div>
            </div>
            <div>
                <span v-html="requestMail.original_body"></span>
            </div>
        </div>
            <div class="mail-attachment overflow-hidden" v-if="mailAttachments.length > 0" :style="{height : attachmentHeight}">
            <v-divider class="my-0"></v-divider>
            <!-- サイズ計測用 -->
            <resize-observer class="pa-0" @notify="handleResize"></resize-observer>
            <!-- カーソルを合わせた時のみ見える -->
            <span class="position-absolute w-100 h-100" @mouseover="filter=true" @mouseleave="filter=false">
                <transition name="filter">
                    <span v-if="filter" class="position-absolute w-100 h-100 filter d-flex align-center">
                        <v-tooltip top class="ml-auto mr-2 flex-none">
                            <v-icon large color="white" @click.stop="showAttachmentList" slot="activator">mdi-cloud-download-outline</v-icon>
                            <span>{{ $t('common.mail.file_list') }}</span>
                        </v-tooltip>
                    </span>
                </transition>
            </span>
            <!-- /カーソルを合わせた時のみ見える -->
            <v-layout row fill-height class="overflow-hidden">
                <template v-for="(attachment, i) in showableAttachmentList">
                    <v-flex ref class="border-right my-auto px-3" :style="{'max-width': `${attachmentWidth}px`, 'min-width': `${attachmentWidth}px`, 'height': '80%'}" :key="i">
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
            </v-layout>
        </div>
        <request-mail-attachments-modal ref="attachmentListModal" :attachments="mailAttachments"></request-mail-attachments-modal>
    </div>
</template>

<script>
import RequestMailAttachmentsModal from './RequestMailAttachmentsModal'
import { ResizeObserver } from 'vue-resize'

export default {
    components: {
        RequestMailAttachmentsModal,
        ResizeObserver
    },
    props: {
        requestMail: { type: Object, require: true },
        height: { type: String, required: false, default: ''},// 数字（cssの単位）
        maxHeight: { type: String, required: false, default: ''}// 数字（cssの単位）
    },
    data: () =>({
        filter: false,
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
            const attachmentLength = this.requestMail.mail_attachments.length
            return attachmentLength > 0 ? `calc(${this.height} - ${this.attachmentHeight})` : `calc(${this.height})`// 上記以外
        },
        maxBodyHeight: function () {
            if (this.maxHeight === '') return ''
            return this.requestMail.mail_attachments.length > 0 ? `calc(${this.maxHeight} - ${this.attachmentHeight})` : `calc(${this.maxHeight})`
        },
        mailAttachments: function() {
            let array = [];
            if (this.requestMail && this.requestMail.mail_attachments.length > 0) {
                this.requestMail.mail_attachments.forEach(function (item) {
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
        },
        from () {
            const from = this.requestMail.pivot.from
            return from ? from : this.requestMail.from.split('&lt;')[0]
        },
    },
    mounted() {
        this.handleResize()
    },
    methods: {
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

.filter {
    background-color: black;
    z-index: 2;
    opacity: 0.3;
}

.position-absolute {
    position: absolute;
}

.h-100 {
    height: 100% !important;
}

.w-100 {
    width: 100% !important;
}

.flex-none {
    flex: none !important;
}

.filter-enter-active, .filter-leave-active {
    transition: opacity .3s;
}

.filter-enter, .filter-leave-to {
    opacity: 0;
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
