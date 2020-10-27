<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.create_request.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            hide-edit-button
            hide-other-button
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
            class="text-center pa-2"
        >
            <div>
                <span class="display-1">
                    準備中
                </span>
            </div>
            <div>
                <v-btn
                    color="grey"
                    dark
                    @click="close"
                >{{ $t('common.button.close') }}</v-btn>
            </div>
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
    methods: {
        close: function() {
            this.toReadMode()
            this.closeCreateRequest()
        },
    }
}
</script>

<style scoped>
</style>
