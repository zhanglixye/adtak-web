<template>
    <div id="status-summary">
        <v-card>
            <v-card-title class="pt-1 pb-1 subheading grey darken-1 white--text">
            <span>{{ $t('requests.detail.status_summary.header') }}</span>
            </v-card-title>
            <v-divider class="ma-0"></v-divider>
            <v-card-text class="status-summary-inner">
                <v-list v-if="requestLogs.length > 0" two-line>
                    <template v-for="(item, index) in requestLogs">
                        <v-list-tile
                            v-if="displayLogTypes(item.type)"
                            :key="item.id"
                            avatar
                            ripple
                        >
                            <!-- ユーザーアイコン -->
                            <v-list-tile-avatar>
                                <v-tooltip top v-if="isHuman(item.updated_user_id)">
                                    <v-avatar slot="activator" size="32px" class="ma-1">
                                        <img :src="user_image_path(item.created_user_id)">
                                    </v-avatar>
                                    <span>{{ user_name(item.created_user_id) }}</span>
                                </v-tooltip>
                                <v-tooltip top v-else>
                                    <v-icon slot="activator" size="32px">android</v-icon>
                                    <span>{{ $t('requests.auto') }}</span>
                                </v-tooltip>
                            </v-list-tile-avatar>
                            <!-- / ユーザーアイコン -->

                            <!-- ログ種別テキスト -->
                            <v-list-tile-content>
                                <v-list-tile-title>
                                    <span>{{ $t('request_logs.types.' + item.type) }}</span>
                                </v-list-tile-title>
                                <v-list-tile-sub-title class="text--primary caption">
                                    <span>{{ item.step_name }}</span>
                                    <span v-if="item.request_work_code">({{ item.request_work_code }})</span>
                                </v-list-tile-sub-title>
                            </v-list-tile-content>
                            <!-- / ログ種別テキスト -->

                            <!-- 日時 & リンクボタン -->
                            <v-list-tile-action>
                                <v-list-tile-action-text>{{ item.created_at | formatDateYmdHm }}</v-list-tile-action-text>
                                <template v-if="linkedLog(item.type)">
                                    <request-log-link-btn :log="item"></request-log-link-btn>
                                </template>
                                <template v-else>
                                    <v-tooltip top class="mr-1"></v-tooltip>
                                </template>
                            </v-list-tile-action>
                            <!-- / 日時 & リンクボタン -->
                        </v-list-tile>
                        <v-divider
                            v-if="index + 1 < requestLogs.length && displayLogTypes(item.type)"
                            :key="`divider-${item.id}`"
                            class="ma-0"
                        ></v-divider>
                    </template>
                </v-list>
                <div v-else class="ma-3">
                    <span>{{ $t('requests.detail.to_be_checked.no_count') }}</span>
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import requestDetailMixin from '../../../../../mixins/Admin/requestDetailMixin'
import RequestLogLinkBtn from '../../../../Molecules/Admin/Requests/Detail/RequestLogLinkBtn'

export default {
    mixins: [requestDetailMixin],
    components:{
        RequestLogLinkBtn
    },
    props: {
        requestLogs: { type: Array },
        candidates: { type: Array },
        referenceMode: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        // 出力しないログ
        hiddenLogTypes: [
            _const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY,
            _const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT,
            _const.REQUEST_LOG_TYPE.TASK_HOLDED_WITH_UNCLEAR_POINT,
            _const.REQUEST_LOG_TYPE.TASK_HOLDED_WITH_SOME_REASON,
        ],
        hasLinkTypes: [
            _const.REQUEST_LOG_TYPE.ALLOCATION_COMPLETED,
            _const.REQUEST_LOG_TYPE.ALLOCATION_CHANGED,
            _const.REQUEST_LOG_TYPE.ALL_TASKS_COMPLETED,
            _const.REQUEST_LOG_TYPE.APPROVAL_REJECTED,
            _const.REQUEST_LOG_TYPE.STEPS_RETURNED,
            _const.REQUEST_LOG_TYPE.APPROVAL_COMPLETED,
            _const.REQUEST_LOG_TYPE.DELIVERY_COMPLETED,
        ]
    }),
    computed: {
        linkedLog() {
            return function (type) {
                return this.hasLinkTypes.includes(type) && !this.referenceMode
            }
        }
    },
    methods: {
        // UsersOverviewコンポーネント内と共通。mixinにする
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
        openWindow (url) {
            window.open(url,  null, 'width=1000, height=700, resizable=1, top=0,left=200')
        },
        displayLogTypes (logType) {
            return !this.hiddenLogTypes.includes(logType)
        }
    }
}
</script>
<style scoped>
    #status-summary {
        position: relative;
        height: 100%;
    }
    #status-summary .status-summary-inner {
        overflow-x: hidden;
        overflow-y: auto;
    }
    #status-summary > .v-card {
        height: 100%;
    }
</style>
