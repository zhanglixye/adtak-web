<template>
    <div id="request-content" :style="{height: height}">
        <div v-for="(content, i) in contents" :key="i">
            <v-tooltip top v-show="!isShow">
                <v-btn
                    small
                    fab
                    slot="activator"
                    color="primary"
                    @click="showContent(content.key)"
                >
                    <v-icon>{{ content.icon }}</v-icon>
                </v-btn>
                <span>{{ content.title }}</span>
            </v-tooltip>
        </div>
        <div v-if="isShow" class="elevation-1">
            <v-list dense ref="header">
                <v-list-tile>
                    <v-list-tile-avatar>
                        <v-icon>{{ content.icon }}</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-content>
                        <v-list-tile-title>
                            <span>{{ content.title }}</span>
                            <div class="request-index-area d-inline-block">
                                <span class="item-label">
                                    <span v-if="isRequestDetail">{{ $t('requests.id') }}</span>
                                    <a v-else class="text-underline">
                                        <v-tooltip top>
                                            <span
                                                slot="activator"
                                                @click.stop.exact="showReferenceView(requestDetailUri)"
                                                @click.stop.shift="showReferenceView(requestDetailUri, 'window')"
                                                @click.stop.ctrl="showReferenceView(requestDetailUri, 'tab')"
                                            >{{ $t('requests.id') }}</span>
                                            <span>{{ $t('common.request_content.information.request_detail_confirm') }}<v-icon dark small>open_in_new</v-icon></span>
                                        </v-tooltip>
                                    </a>
                                </span>
                                <span class="select-all">{{ requestCode + requestWork.request_id }}</span>
                                <div v-if="requestWorkCode" class="d-inline-block">
                                    &#047;
                                    <span class="item-label">{{ $t('request_works.id') }}</span>
                                    <span class="select-all">{{ requestWorkCode }}</span>
                                </div>
                            </div>
                            <div v-if="existAppendices" class="d-inline-block">
                                <v-btn
                                    class="additional-info-bt blinking px-1"
                                    color="red darken-2"
                                    flat
                                    round
                                    @click="moveToAdditional()"
                                >
                                    <v-icon class="mr-1" dark left>error</v-icon>{{ $t('requests.detail.additional_info.notice_link') }}
                                </v-btn>
                            </div>
                        </v-list-tile-title>
                    </v-list-tile-content>
                    <v-list-tile-action v-if="!hideCloseIcon">
                        <v-btn icon @click="hideContent()">
                            <v-icon>chevron_left</v-icon>
                        </v-btn>
                    </v-list-tile-action>
                </v-list-tile>
            </v-list>
            <v-divider class="my-0" ref="divider"></v-divider>
            <div id="request-content-main">
                <before-delivery
                    v-if="beforeWork && beforeWorkTemplate"
                    :before-work="beforeWork"
                    :mail="mail"
                    :request-file="request.request_file"
                    :label-data="labelData"
                    :max-height="contentMaxHeight"
                    :min-height="contentMinHeight"
                    :height="contentHeight"
                ></before-delivery>
                <mail-view
                    :max-height="contentMaxHeight"
                    :min-height="contentMinHeight"
                    :height="contentHeight"
                    :mail="mail"
                    v-else-if="'mail' === selectedKey"
                ></mail-view>
                <request-file
                    :request-file="request.request_file"
                    v-else-if="'file' === selectedKey"
                    :label-data="labelData"
                    :candidates="candidates"
                    :max-height="contentMaxHeight"
                    :min-height="contentMinHeight"
                    :height="contentHeight"
                ></request-file>
            </div>
        </div>
        <appendices-index
            v-if="isShow && isAdditional && requestId"
            v-show="existAppendices"
            :request-id="requestId"
            :candidates="candidates"
            :exist-appendices.sync="existAppendices"
            :add-search-params="appendicesParams"
            :use-store="false"
            class="mt-2"
            height="auto"
            ref="appendicesIndex"
        ></appendices-index>

        <!-- <v-menu open-on-hover right offset-y v-if="workDetailLinks.length > 0 && !isShow">
            <template slot="activator">
                <v-btn
                    color="accent"
                    small
                    fab
                >
                    <v-icon>mdi-information-variant</v-icon>
                </v-btn>
            </template>
            <v-list dense>
                <v-list-tile
                    v-for="(item, index) in workDetailLinks"
                    :key="index"
                    :href="item.href"
                >
                    <v-list-tile-action>
                        <v-icon>{{ item.icon }}</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-content>
                        <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list>
        </v-menu> -->
        <reference-view-dialog ref="referenceViewDialog" content-class="fill-height"></reference-view-dialog>
    </div>
</template>

<script>
import MailView from '../../Molecules/Requests/MailView'
import RequestFile from '../../Molecules/Requests/RequestFile'
import BeforeDelivery from '../../Molecules/Requests/BeforeDelivery'
import ReferenceViewDialog from '../../Atoms/Dialogs/ReferenceViewDialog'
import AppendicesIndex from '../Client/Requests/Detail/Appendices/Index'

export default {
    components: {
        MailView,
        RequestFile,
        BeforeDelivery,
        ReferenceViewDialog,
        AppendicesIndex,
    },
    props: {
        requestWorkId: { type: Number, required: true },
        request: { type: Object, required: false, default: () => ({}) },  // TODO: 最終的には削除
        candidates: { type: Array, required: false, default: () => [] },  // TODO: 最終的には削除
        value: { type: Boolean, required: false, default: null },
        selectedContent: { type: String, required: false, default: '' },
        displayInit: { type: Boolean, required: false, default: false },
        labelData: { type: Object, required: false, default: () => ({}) },  // TODO: 最終的には削除
        hideCloseIcon: { type: Boolean, required: false, default: false },
        height: { type: String, required: false, default: '' },// auto cssの単位付き数値 空白
        maxHeight: { type: String, required: false, default: '' },// cssの単位付き数値
        minHeight: { type: String, required: false, default: '' },
        countSummary: { type: Array, required: false, default: () => [] },
        parentComponent: { type: String, required: false, default: '' },
        isAdditional: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        selectedKey: null,
        show: false,
        nonContentHeight: 0,
        initData: null,
        existAppendices: false,
        appendicesParams: {
            types: [_const.APPENDICES_TYPE.RELATED_MAIL, _const.APPENDICES_TYPE.ADDITIONAL],
            open_page: 'work',
            rows_per_page: 3,
        }
    }),
    computed: {
        requestWork () {
            return this.initData ? this.initData['request_work'] : null
        },
        requestMail () {
            return this.initData ? this.initData['request_mail'] : null
        },
        requestFile () {
            return this.initData ? this.initData['request_file'] : null
        },
        beforeWork () {
            return this.initData ? this.initData['before_work'] : null
        },
        sendMail () {
            return this.beforeWork ? this.beforeWork['send_mail'] : null
        },
        mail () {
            // prior beforeWork
            return this.sendMail ? this.sendMail : this.requestMail
        },
        beforeWorkTemplate () {
            return this.beforeWork.before_work_template
        },
        requestId () {
            return this.requestWork ? this.requestWork.request_id : ''
        },
        contentHeight () {
            if (this.height === 'auto') {
                return this.height
            } else if (this.height !== '') {
                return `${this.height} - ${this.nonContentHeight}px`
            } else {
                return ''
            }
        },
        contentMaxHeight () {
            if (this.maxHeight !== '') {
                return `${this.maxHeight} - ${this.nonContentHeight}px`
            } else {
                return ''
            }
        },
        contentMinHeight () {
            return this.minHeight !== '' ? `${this.minHeight} - ${this.nonContentHeight}px` : this.minHeight
        },
        contents () {
            if (this.request === undefined || this.request === null) return []

            let contents = []
            if (this.mail) {
                contents.push({ key: 'mail', icon: 'email', title: Vue.i18n.translate('common.request_content.mail') })
            }

            if (this.request.request_file) {
                contents.push({ key: 'file', icon: 'insert_drive_file', title: Vue.i18n.translate('common.request_content.file') })
            }
            return contents
        },
        content () {
            const content = this.contents.find(content => this.selectedKey === content.key)
            if (content === undefined) return { key: '', icon: '', title: '' }
            return content
        },
        // workDetailLinks () {
        //     let workDetailLinks = []
        //     if (this.request.is_business_admin === true) {
        //         workDetailLinks.push({ title: Vue.i18n.translate('common.request_content.information.request_detail_confirm'), icon: 'mdi-file-document-box-multiple', href: '/requests/' + this.request['id'] })
        //         workDetailLinks.push({ title: Vue.i18n.translate('common.request_content.information.work_detail_confirm'), icon: 'mdi-file-document-box', href: '/request_works/' + this.request['request_work_id'] })
        //     }
        //     return workDetailLinks
        // },
        isShow: {
            set (val) {
                this.show = val
                this.$emit('input', val)
                this.$nextTick(function () {
                    // ヘッダーサイズを取得
                    if (this.show) {
                        this.calculationNonContentHeight()
                    }
                })
            },
            get () {
                return this.show
            }
        },
        requestCode() {
            return _const.MASTER_ID_PREFIX.REQUEST_ID
        },
        requestWorkCode () {
            const prefix = _const.MASTER_ID_PREFIX
            const requestWorkCode = this.requestCode + this.requestWork.request_id + prefix.SEPARATOR + prefix.REQUEST_WORKS_ID
            const requestWorks = this.countSummary.filter(item => item.request_work_ids)
            if (requestWorks.length == 0) return requestWorkCode + this.requestWorkId
            return requestWorkCode + requestWorks.map(item => item.request_work_ids.split(',').join(',' + requestWorkCode)).join(',' + requestWorkCode)
        },
        requestDetailUri () {
            return '/management/requests/' + this.requestWork.request_id
        },
        isRequestDetail () {
            return this.parentComponent == 'RequestDetailView'
        },
    },
    watch: {
        selectedContent () {
            this.selectedKey = this.selectedContent
            this.$nextTick(function () {
                if (this.isShow) this.calculationNonContentHeight()
            })
        },
        height () {
            if (this.isShow) this.calculationNonContentHeight()
        },
        maxHeight () {
            if (this.isShow) this.calculationNonContentHeight()
        }
    },
    mounted() {
        if (this.isShow) this.calculationNonContentHeight()
    },
    methods:{
        async init () {
            try {
                const res = await axios.get('/api/utilities/getWorkRequestInfo', {
                    params: {
                        requestWorkId: this.requestWorkId,
                    }
                })
                this.initData = res.data
                this.isShow = this.displayInit
            } catch (error) {
                console.log({error})
            } finally {
                // pass
            }
        },
        showContent (key) {
            this.selectedKey = key
            this.isShow = true
        },
        hideContent () {
            this.isShow = false
        },
        calculationNonContentHeight: function() {
            // 指定された高さからheaderのサイズを引く
            if (this.$refs.header && this.$refs.divider) {
                let total = 0
                total += this.$refs.divider.$el.offsetHeight
                total += this.$refs.header.$el.offsetHeight
                this.nonContentHeight = total
            }
        },
        showReferenceView (uri, type) {
            this.$refs.referenceViewDialog.show([uri], type)
        },
        moveToAdditional () {
            const target = this.$refs.appendicesIndex.$el.offsetTop
            this.scrollToPosition(target)
        },
        scrollToPosition (target) {
            const diff = target - window.pageYOffset
            if (diff != 0) {
                let step = 25 //移動幅
                step = (diff < 0) ? -step : step //移動方向を設定

                const timer = setInterval(() => {
                    const before = window.pageYOffset //移動前の位置
                    // 移動幅より差分が大きければ移動（追い越し防止）
                    if (diff > step) {
                        scrollBy(0, step)
                    }
                    const next = window.pageYOffset + step //次回移動する位置
                    // スクロール終了判定
                    if ((diff > 0 && next >= target) || (diff < 0 && next <= target) || window.pageYOffset == before) {
                        // 移動の最終調整
                        scrollBy(0, target - window.pageYOffset)
                        clearInterval(timer)
                    }
                }, 10)
            }
        },
    },
    created () {
        this.init()
        this.selectedKey = this.selectedContent
    }
}
</script>
<style scoped>
.select-all {
    user-select: all;
    -webkit-user-select: all;
    -moz-user-select: all;
}
.request-index-area:before {
    content: '\28';
}
.request-index-area:after {
    content: '\29';
}
.request-index-area .item-label:after {
    content: ':';
}
.additional-info-bt {
    height: auto;
    margin: 0;
    padding: 0;
}
.blinking {
    -webkit-animation:blink 0.5s ease-in-out infinite alternate;
    -moz-animation:blink 0.5s ease-in-out infinite alternate;
    animation:blink 0.5s ease-in-out infinite alternate;
}
@-webkit-keyframes blink {
    0% {opacity:0;}
    100% {opacity:1;}
}
@-moz-keyframes blink {
    0% {opacity:0;}
    100% {opacity:1;}
}
@keyframes blink {
    0% {opacity:0;}
    100% {opacity:1;}
}
</style>