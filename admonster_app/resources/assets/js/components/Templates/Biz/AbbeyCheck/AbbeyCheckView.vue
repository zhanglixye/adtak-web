
<template>
    <v-app id="abbey-check">
        <app-menu :drawer="drawer" :reference-mode="isReference"></app-menu>
        <app-header :title="$t('biz.abbey_check.abbey_check.title')" :reference-mode="isReference"></app-header>
        <v-content id="data-content" :style="contentStyle">
            <confirm-modal></confirm-modal>
            <irregular-modal></irregular-modal>
            <notify-modal></notify-modal>
            <v-container fluid wrap>
                <v-layout row wrap>
                    <v-flex xs12>
                        <page-header back-button :alerts="alert" :reference-mode="isReference">
                            <template slot="rightHeader">
                                <a style="float:right; padding-right: 12px;" href="https://abbey.yahoo.co.jp/babbey/index.html" target="_blank">{{ $t('biz.abbey_check.abbey_check.tool_url') }}<v-icon color="primary" style="font-size:17px;">launch</v-icon></a>
                            </template>
                        </page-header>
                    </v-flex>
                    <v-flex shrink ref="requestContent" :class="showRequestContent ? 'xs12 md6' : ''">
                        <request-contents
                            v-model="showRequestContent"
                            :request-work-id="requestWorkId"
                            selectedContent="mail"
                            height="auto"
                            max-height="639px"
                            min-height="639px"
                            :displayInit="isRequestContentsDisplayInit"
                            is-additional
                        ></request-contents>
                    </v-flex>
                    <v-flex grow :class="[this.showRequestContent ? 'xs12 md6': '']" v-if="resData" :style="{maxWidth : stepMaxWidth}">
                        <steps :res-data.sync="resData" v-model="loading" @getData="getData" ref="steps"></steps>
                    </v-flex>
                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer :reference-mode="isReference"></app-footer>
    </v-app>
</template>

<script>
import PageHeader from './../../../Organisms/Layouts/PageHeader'
import ConfirmModal from '../../../Organisms/Biz/AbbeyCheck/AbbeyCheck/ConfirmModal'
import IrregularModal from '../../../Organisms/Biz/AbbeyCheck/AbbeyCheck/IrregularModal'
import RequestContents from '../../../Organisms/Common/RequestContents'
import Steps from '../../../Organisms/Biz/AbbeyCheck/AbbeyCheck/Steps'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import NotifyModal from '../../../Atoms/Modal/NotifyModal'


const eventHub = new Vue()

export default {
    components: {
        PageHeader,
        ConfirmModal,
        IrregularModal,
        RequestContents,
        Steps,
        ProgressCircular,
        NotifyModal,
    },
    props: {
        eventHub: eventHub,
        requestWorkId: {
            type: Number,
            require: true
        },
        taskId: {
            type: Number,
            require: true
        },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        showRequestContent: false,
        drawer: false,
        loading: false,
        resData: null,
        alert: {
            alertMessage: {
                model: false,
                dismissible: true,
                color: 'warning',
                icon: 'priority_high',
                message: '',
            },
        },
        stepMaxWidth: '100%'// 初期のshowRequestContentがtrueのため
    }),
    watch: {
        showRequestContent: function () {
            this.$nextTick(function () {
                // メールiconの下にstep要素が改行されて表示されるのを防ぐ（原因:動画要素）
                if (this.showRequestContent) {
                    this.stepMaxWidth = '100%'
                } else {
                    this.stepMaxWidth = `calc(100% - ${this.$refs.requestContent.offsetWidth}px)`
                }
            })
        }
    },
    created () {
        window.getApp = this;
        this.getData()
    },
    computed: {
        contentStyle () {
            return this.isReference ? {padding: 0} : {}
        },
        isRequestContentsDisplayInit () {
            return this.isReference ? false : true
        },
        isReference () {
            return this.referenceMode ? this.referenceMode : false
        },
    },
    methods:{
        getData: function() {
            this.loading = true

            // ブラウザバックの際に、キャッシュしている内容とDBの結果の食い違いを発生させないため
            const date = new Date()
            const time = date.getTime()

            axios.get('/api/biz/abbey_check/abbey_check/' + this.requestWorkId + '/' + this.taskId + '/create?time='+time)
                .then((res) => {
                    // 初回ロード時
                    this.resData = res.data
                    this.loading = false

                    if (this.resData.message != null) {
                        this.alert['alertMessage'].message = this.resData.message
                        this.alert['alertMessage'].model = true
                    }
                })
                .catch((err) => {
                    this.loading = false
                    console.log(err)
                });
        },
    }
}
</script>

<style>
#abbey-check,
#abbey-check .v-list__tile__title,
#abbey-check .v-input .v-input__control .v-input__slot input,
#abbey-check .v-input .v-input__control .v-input__slot label,
#abbey-check .v-btn .v-btn__content, #abbey-check .v-btn, #abbey-check label.btn {
    font-size: 12px;
}

#abbey-check .row {
    margin-left: 0px;
    margin-right: 0px;
}
</style>
