<template>
    <v-app>
        <app-menu :drawer="sideMenuDrawer" :reference-mode="isReference"></app-menu>
        <app-header :title="businessName" :subtitle="stepName" :reference-mode="isReference"></app-header>
        <v-content :style="contentStyle">
            <v-container fluid grid-list-md>
                <v-layout row wrap>

                    <v-flex xs12>
                        <page-header back-button :alerts="alerts" :reference-mode="isReference"></page-header>
                    </v-flex>

                    <v-flex shrink :class="showRequestContent ? 'xs12 md6' : ''">
                        <!-- 依頼内容コンポーネント -->
                        <request-contents
                            v-model="showRequestContent"
                            height="auto"
                            max-height="640px"
                            min-height="640px"
                            :request-work-id="requestWorkId"
                            :request="requestInfo"
                            :selectedContent="selectContent"
                            :labelData="labelData"
                            :candidates="candidates"
                            :displayInit="isRequestContentsDisplayInit"
                            is-additional
                        >
                        </request-contents>
                    </v-flex>

                    <v-flex grow :class="showRequestContent ? 'xs12 md6' : ''">
                        <!-- 各種作業コンポーネント -->
                        <component
                            ref="bizWork"
                            :is="bizWorkView"
                            :initData="initData"
                            :edit="edit"
                            :loadingDisplay="loadingDisplay"
                            :backBeforePage="backBeforePage"
                        >
                        </component>
                    </v-flex>

                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer :reference-mode="isReference"></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../Organisms/Layouts/PageHeader'
import ProgressCircular from '../../Atoms/Progress/ProgressCircular'
import RequestContents from '../../Organisms/Common/RequestContents'

export default {
    components: {
        PageHeader,
        ProgressCircular,
        RequestContents,
    },
    props: {
        businessId: { type: Number, require: true },
        stepId: { type: Number, require: true },
        requestWorkId: { type: Number, require: true },
        taskId: { type: Number, require: true },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        bizWorkView: '',
        loading: false,
        requestContentDrawer: true,
        initData: null,
        showRequestContent: false,
        sideMenuDrawer: false,
        taskResultContent: {
            results: {
                type: _const.TASK_RESULT_TYPE.DONE,
            },
        },
        alerts: {
            alertMessage: {
                model: false,
                dismissible: true,
                color: 'warning',
                icon: 'priority_high',
                message: null,
            },
        },
        // 検討中...
        // contact: {},  // 作業ごとに異なる
        // checkList: {},  // 作業ごとに異なる
        // workTimes: {  // 工数
        //     startedAt: null,
        //     finishedAt: null,
        //     workTime: null,
        // },
    }),
    computed: {
        businessCode () {
            // 業務コード（Bからはじまる6桁のコード）
            return 'B' + ('00000' + this.businessId).slice(-5)
        },
        stepCode () {
            // 作業コード（Sからはじまる6桁のコード）
            return 'S' + ('00000' + this.stepId).slice(-5)
        },
        edit () {
            // 作業画面の編集可否
            const editableStatusList = [_const.TASK_STATUS.NONE, _const.TASK_STATUS.ON]
            return this.taskInfo ? editableStatusList.includes(this.taskInfo['status']) && this.taskInfo['is_active'] === _const.FLG.ACTIVE : false
        },
        taskInfo () {
            return this.initData ? this.initData['task_info'] : null
        },
        requestInfo () {
            return this.initData ? this.initData['request_info'] : null
        },
        businessName () {
            return this.initData ? this.initData['business_name'] : null
        },
        stepName () {
            return this.initData ? this.initData['step_name'] : null
        },
        labelData () {
            return this.initData ? this.initData['label_data'] : null
        },
        candidates () {
            return this.initData ? this.initData['candidates'] : null
        },
        selectContent () {
            // RequestContent内の条件分岐に合わせて記載
            if (this.requestInfo === null) return ''
            if (Object.prototype.hasOwnProperty.call(this.requestInfo, 'request_mail')) return 'mail'
            if (Object.prototype.hasOwnProperty.call(this.requestInfo, 'request_file')) return 'file'
            return ''
        },
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
    watch: {},
    created () {
        this.init()
    },
    methods: {
        async init () {
            try {
                this.loadingDisplay(true)
                // 作業固有のコンポーネントが存在すれば呼び出す
                if (Vue.options.components[this.stepCode]) {
                    const res = await axios.get(
                        '/api/biz/' + this.businessCode.toLowerCase() + '/' + this.stepCode.toLowerCase()
                        + '/' + this.requestWorkId + '/' + this.taskId + '/create'
                    )
                    this.initData = res.data
                    this.initData.isReference = this.isReference
                    this.bizWorkView = this.stepCode
                } else {
                    // TODO: 汎用画面コンポーネント
                    // this.bizWorkView = 'biz-general-view'
                }

                const message = this.taskInfo ? this.taskInfo['message'] : ''
                if (message) {
                    this.alerts['alertMessage'].message = message
                    this.alerts['alertMessage'].model = true
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingDisplay(false)
            }
        },
        loadingDisplay (bool) {
            this.loading = bool
        },
        backBeforePage () {
            if (window.history.length > 1) {
                window.history.back()
            } else {
                window.location.href = '/tasks'
            }
        }
    },
}
</script>

<style scoped>
</style>
