<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.related_mail.title')"
            :height="headerHeight"
            :mode="mode"
            :hide-other-button="hideOtherButton"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            hide-edit-button
            @previous="previous"
            @next="next"
            @shrink-right="shrinkRight"
            @shrink-left="shrinkLeft"
            @grow="grow"
            @open-create-mail="openCreateMail"
            @open-create-request="openCreateRequest"
        >
        </common-header>
        <div
            :style="{
                'height': contentHeight,
                'overflow': 'auto',
            }"
            class="pa-2"
        >
            <related-mails
                :order-detail-id="orderDetailId"
                :is-readonly-mode="isReadonlyMode"
                @to-edit-mode="toEditMode"
                @to-read-mode="toReadMode"
            ></related-mails>
        </div>
    </div>
</template>

<script>
// Components
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'
import RelatedMails from './RelatedMails'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

// Stores
import orderInfoStore from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        CommonHeader,
        RelatedMails,
    },
    computed: {
        orderDetailId() {
            return orderInfoStore.state.processingData.orderDetailId
        },
        hideOtherButton() {
            return this.orderDetailId === 0
        },
    }
}
</script>

<style scoped>
</style>
