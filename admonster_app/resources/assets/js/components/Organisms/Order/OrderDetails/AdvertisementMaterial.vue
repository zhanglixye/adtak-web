<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.advertisement_material.title')"
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
            class="text-center"
        >
            <span class="display-1">
                準備中
            </span>
        </div>
    </div>
</template>

<script>
// Components
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        CommonHeader,
    },
}
</script>

<style scoped>
</style>
