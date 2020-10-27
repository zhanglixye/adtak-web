<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.order_detail_info.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            :hide-other-button="hideOtherButton"
            @previous="previous"
            @next="next"
            @shrink-right="shrinkRight"
            @shrink-left="shrinkLeft"
            @grow="grow"
            @to-edit-mode="toEditMode"
            @open-create-mail="openCreateMail"
            @open-create-request="openCreateRequest"
        >
        </common-header>
        <div
            :style="{
                'height': contentHeight,
                'overflow': 'auto',
                'padding-left': '16px',
                'padding-bottom': '16px',
                'padding-right': '16px',
                'white-space': 'nowrap',
            }"
        >
            <div style="display: flex; margin-bottom: 10px;">
                <span style="padding-right: 15px; padding-top: 16px;">
                    {{ $t('order.order_details.show.information_component_management.order_detail_info.subject') }}
                </span>
                <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                <div style="width: 100%;">
                    <v-form v-model="valid" ref="form" @submit.prevent>
                        <v-text-field
                            v-model="subject"
                            label
                            style="padding-top: 5px ;padding-right: 60px;"
                            counter="256"
                            :rules="[rules.check, rules.required]"
                            :disabled="!isEditMode"
                        ></v-text-field>
                    </v-form>
                </div>
            </div>
            <div>
                <div class="my-2" style="max-width: 665px;">
                    <span>{{ $t('order.order_details.show.information_component_management.order_detail_info.item') }}</span>
                </div>
                <template
                    v-for="column in columns"
                >
                    <div
                        :key="column.label_id"
                        class="each-item-content"
                        style="display: flex;"
                    >
                        <span
                            style="width: 650px; display: flex; padding-top: 15px; padding-bottom: 15px;"
                            v-if="column.item_type === dateItemType"
                        >
                            <v-tooltip top>
                                <v-icon
                                    class="info-icon-size"
                                    style="margin-top: 4px;"
                                    slot="activator"
                                >mdi-file-excel</v-icon>
                                <span>{{ getDisplayName(column.label_id) }}</span>
                            </v-tooltip>
                            <date-picker-without-buttons
                                class="display-item"
                                style="width: 100%; padding-top: 5px;"
                                :label="getDisplayName(column.label_id)"
                                placeholder="yyyy/mm/dd"
                                :dateValue="column.value"
                                :isActive="isEditMode"
                                @change="column.value = $event"
                            ></date-picker-without-buttons>
                        </span>
                        <span v-if="column.item_type === numItemType" style="width: 100%;">
                            <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                            <v-form v-model="displayItemValid" @submit.prevent>
                                <v-text-field
                                    hint="999,999,999 ,999 ~ -999,999,999,999"
                                    :rules="[rules.numCheck]"
                                    v-model="column.value"
                                    :label="getDisplayName(column.label_id)"
                                    :disabled="!isEditMode"
                                    style="max-width: 650px; padding-top: 15px;"
                                >
                                    <template slot="prepend">
                                        <v-tooltip top>
                                            <v-icon class="info-icon-size" slot="activator">mdi-file-excel</v-icon>
                                            <span>{{ getDisplayName(column.label_id) }}</span>
                                        </v-tooltip>
                                    </template>
                                </v-text-field>
                            </v-form>
                        </span>
                        <span v-if="column.item_type !== numItemType && column.item_type !== dateItemType" style="width: 100%;">
                            <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                            <v-form v-model="displayItemValid" @submit.prevent>
                                <v-textarea
                                    :rules="[rules.textMaxCheck]"
                                    v-model="column.value"
                                    :label="getDisplayName(column.label_id)"
                                    style="max-width: 650px; padding-top: 10px;"
                                    counter="2000"
                                    rows="1"
                                    auto-grow
                                    :disabled="!isEditMode"
                                >
                                    <template slot="prepend">
                                        <v-tooltip top>
                                            <v-icon class="info-icon-size" slot="activator">mdi-file-excel</v-icon>
                                            <span>{{ getDisplayName(column.label_id) }}</span>
                                        </v-tooltip>
                                    </template>
                                </v-textarea>
                            </v-form>
                        </span>
                    </div>
                </template>
            </div>
            <div>
                <div class="my-2">
                    {{ $t('order.order_details.show.information_component_management.order_detail_info.custom_status') }}
                </div>
                <template v-for="customStatus in customStatuses">
                    <div :key="customStatus.customStatusId" class="each-item-content">
                        <v-select
                            v-model="customStatus.selectAttributeId"
                            :label="customStatus.customStatusName"
                            :items="customStatus.attributes"
                            style="max-width: 650px;"
                            dense
                            :disabled="!isEditMode"
                        >
                            <template slot="prepend">
                                <v-tooltip top>
                                    <v-icon class="info-icon-size" slot="activator">mdi-wrench</v-icon>
                                    <span>{{ customStatus.customStatusName }}</span>
                                </v-tooltip>
                            </template>
                        </v-select>
                    </div>
                </template>
            </div>
            <div style="text-align: center;">
                <v-btn
                    v-if="isEditMode"
                    color="grey"
                    dark
                    @click="chancel"
                >{{ $t('common.button.cancel') }}</v-btn>
                <v-btn
                    color="primary"
                    v-if="isEditMode"
                    :disabled="disabledButton"
                    @click="save()"
                >{{ $t('common.button.save') }}</v-btn>
            </div>
        </div>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
// Components
import DatePickerWithoutButtons from '../../../Atoms/Pickers/DatePickerWithoutButtons'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        DatePickerWithoutButtons,
        ConfirmDialog,
        AlertDialog,
        CommonHeader,
    },
    data() {
        return {
            rules: {
                numCheck: v => {
                    v = v.toString().replace(/,/g, '')
                    const font = /[^\d-.]/.test(v)
                    if (font) {
                        // 文字
                        return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.contain_text')
                    } else {
                        const minus = /-+/.test(v)
                        if (minus) {
                            const minusCount = v.match(/-/g).length
                            if (minusCount > 1) {
                                // 複数
                                return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.minus_included_multiple')
                            } else {
                                const minusPosition = /^-/.test(v)
                                if (!minusPosition) {
                                    // 位置
                                    return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.minus_position_is_different')
                                }
                            }
                            if (v.length <= 1) return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.not_input_number')
                            return Number(v) >= -999999999999 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.less_than_minimum_value')
                        }
                    }
                    return Number(v) <= 999999999999 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.exceeded_maximum_value')
                },
                check: v => v.length <= 256 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.limit_order_detail_name', { number: 256 }),
                textMaxCheck: v => v.length <= 2000 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.limit_display_name', { number: 2000 }),
                required: v => v !== '' || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.no_order_detail_name')
            },
            valid: false,
            displayItemValid: false,
            displayLangCode: 'ja', // 各案件固有情報を多言語対応するか分からないので、日本語に固定
        }
    },
    computed: {
        orderId() {
            return store.state.processingData.orderId
        },
        orderDetailId() {
            return store.state.processingData.orderDetailId
        },
        orderDetail() {
            return store.state.processingData.orderDetailData
        },
        subject: {
            set(subject) {
                store.commit('setSubjectData', subject)
            },
            get() {
                return store.state.processingData.subjectData
            },
        },
        columns() {
            return store.state.processingData.orderDetailData['columns']
        },
        customStatuses() {
            return store.state.processingData.customStatuses
        },
        numItemType() {
            return _const.ITEM_TYPE.NUM.ID
        },
        dateItemType() {
            return _const.ITEM_TYPE.DATE.ID
        },
        labelData() {
            return store.state.processingData.labelData
        },
        disabledButton() {
            return !this.valid || !this.displayItemValid
        },
        hideOtherButton() {
            return this.orderDetailId === 0
        },
    },
    watch: {
        subject: {
            immediate: true,
            async handler() {
                await this.$nextTick()
                this.$refs.form.validate()
            },
        },
    },
    methods: {
        getDisplayName: function(labelId) {
            return this.labelData[this.displayLangCode][labelId]
        },
        save: async function() {
            let message = ''
            if (this.orderDetailId === 0) {
                if (this.orderDetail.order_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_inactive') + '<br>'
                message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.has_creating_newly')
            } else {
                if (this.orderDetail.order_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_inactive') + '<br>'
                if (this.orderDetail.order_detail_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_details_inactive') + '<br>'
                message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.save')
            }

            if (await this.$refs.confirm.show(message)) {
                try {
                    await store.dispatch('saveOrderDetail', {
                        orderId: this.orderId,
                        orderDetailId: this.orderDetailId,
                    })
                    // ダイアログ表示
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'), () => {
                        if (this.orderDetailId === 0) {// 新規登録
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = '/order/order_details?order_id=' + this.orderId
                            }
                        } else {
                            this.toReadMode()
                        }
                    })
                } catch (error) {
                    console.log('error: ', error)
                    await this.$refs.alert.show(error)
                }
            }
        },
        chancel: function() {
            store.dispatch('reset')
            this.toReadMode()
        },
    }
}
</script>

<style scoped>
</style>
