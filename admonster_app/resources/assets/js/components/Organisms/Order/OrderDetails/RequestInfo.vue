<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.request_info.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            hide-edit-button
            hide-other-button
            @previous="previous"
            @next="next"
            @shrink-right="shrinkRight"
            @shrink-left="shrinkLeft"
            @grow="grow"
        >
        </common-header>
        <div
            :style="{
                'height': contentHeight,
                'overflow': 'auto',
            }"
        >
            <!-- [臨時対応] ADPORTER_PF-378: 依頼作成処理 -->
            <div class="request-info-title pa-2">
                <span
                    color="transparent"
                >
                    <span>{{ requests.length }} {{ $t('list.case') }}</span>
                </span>
                <v-spacer></v-spacer>
                <v-tooltip top v-if="isEditableMode && orderDetailId !== 0">
                    <v-btn
                        icon
                        small
                        color="primary"
                        slot="activator"
                        @click="openBusinessSelectionDialog()"
                    >
                        <v-icon>add</v-icon>
                    </v-btn>
                    <span>{{ $t('common.button.request_creation') }}</span>
                </v-tooltip>
            </div>
            <request-list class="ma-2" :order-detail-id="orderDetailId"></request-list>
            <!-- [臨時対応] ADPORTER_PF-378: 依頼作成処理 -->
        </div>
        <business-selection-dialog ref="businessSelectionDialog"></business-selection-dialog>
        <request-creation-dialog ref="requestCreationDialog"></request-creation-dialog>
    </div>
</template>

<script>
// Components
import RequestList from './RequestList'
import RequestCreationDialog from './RequestCreationDialog'
import BusinessSelectionDialog from './BusinessSelectionDialog'
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'
import requestInfoStore from '../../../../stores/Order/OrderDetails/Show/request-info'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        RequestList,
        RequestCreationDialog,
        BusinessSelectionDialog,
        CommonHeader,
    },
    props: {
    },
    data() {
        return {
            displayLangCode: 'ja', // 各案件固有情報を多言語対応するか分からないので、日本語に固定
        }
    },
    computed: {
        labelData() {
            return store.state.processingData.labelData
        },
        orderDetailId() {
            return store.state.processingData.orderDetailId
        },
        columns() {
            return store.state.processingData.orderDetailData['columns']
        },
        subject() {
            return store.state.processingData.subjectData
        },
        requests () {
            return requestInfoStore.state.requests
        }
    },
    methods: {
        openBusinessSelectionDialog: async function() {
            let businessId = await this.$refs.businessSelectionDialog.show()
            if (businessId) this.openRequestCreationDialog(businessId)
        },
        openRequestCreationDialog: function(businessId) {
            // NOTE: 今時点ではメール送付を行わないためTo,Ccは未設定とする（画面にも表示しない）
            let settings = {
                // to: [],
                // cc: [],
                subject: this.subject,  // 案件明細の件名
                body: this.formatColumnsForDisplay(this.columns),
            }
            // NOTE: 最終的には業務単位にデフォルトフォーマット設定をDB管理できるようにする
            if ([12, 14].indexOf(businessId) !== -1) {
                let filteringColumns = ['サイト名','リリース日','Q','トリガー','納品期限','予想メニュー数','媒体資料URL','媒体資料ファイル名','チェックリストURL','AG媒体資料紐づけ']
                let columnsText = this.formatColumnsForDisplay(this.columns, filteringColumns)
                settings.body = 'ご担当者様\n\nお疲れ様です。\n下記、AGの登録と媒体資料の紐づけをお願いします。\n\n' + columnsText
            } else if ([13, 15].indexOf(businessId) !== -1) {
                let filteringColumns = ['サイト名','リリース日','Q','トリガー','納品期限','予想メニュー数','媒体資料URL','媒体資料ファイル名','チェックリストURL','Xone媒体資料紐づけ']
                let columnsText = this.formatColumnsForDisplay(this.columns, filteringColumns)
                settings.body = 'ご担当者様\n\nお疲れ様です。\n下記、Xone紐づけをお願いします。\n\n' + columnsText
            }
            this.$refs.requestCreationDialog.show(settings, this.orderDetailId, businessId)
        },
        formatColumnsForDisplay: function(columns, filteringColumns = []) {
            const columnTexts = []
            for (const column of Object.values(columns)) {
                // NOTE: 絞り込み条件あり && 項目名が合致しない場合はスキップ
                /*
                 * NOTE: 作業画面への文字列埋め込みに対応する為、
                 *       AG登録業務では表頭文字列を変更しないよう依頼しているのでこの方法が使えるが
                 *       今後どう対応していくかは別途検討が必要
                 */
                if (filteringColumns.length > 0 && filteringColumns.indexOf(this.getDisplayName(column.label_id)) === -1) {
                    continue
                }
                columnTexts.push(`${this.getDisplayName(column.label_id)} : ${column.value}`)
            }
            return columnTexts.join('\n')
        },
        getDisplayName(labelId) {
            return this.labelData[this.displayLangCode][labelId]
        },
    }
}
</script>

<style scoped>
.request-info-title {
    display: flex;
    align-items: center;
}
</style>
