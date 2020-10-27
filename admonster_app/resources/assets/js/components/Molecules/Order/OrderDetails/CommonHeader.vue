<template>
    <div>
        <v-toolbar flat :color="headerColor" dark :height="height">
            <v-tooltip top v-model="tooltip.leftShrinkButton">
                <v-btn v-if="showShrinkButtons" icon @click="shrinkRight" slot="activator">
                    <v-icon>mdi-chevron-double-right</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.shrink_to_right') }}</span>
            </v-tooltip>
            <v-tooltip top v-model="tooltip.leftGrowButton">
                <v-btn v-if="showLeftGrowButton" icon @click="grow" slot="activator">
                    <v-icon>mdi-chevron-double-left</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.grow') }}</span>
            </v-tooltip>
            <v-tooltip top>
                <v-btn v-if="showSwitchingButton" icon @click="previous" slot="activator">
                    <v-icon>chevron_left</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.switch') }}</span>
            </v-tooltip>
            <v-spacer></v-spacer>
            <v-toolbar-title>{{ title }}</v-toolbar-title>
            <v-tooltip
                top
                v-model="tooltip.editButton"
            >
                <v-btn
                    v-if="showEditButton"
                    icon
                    @click="toEditMode"
                    slot="activator"
                >
                    <v-icon>edit</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.edit') }}</span>
            </v-tooltip>
            <v-spacer></v-spacer>
            <v-menu offset-y>
                <v-tooltip
                    top
                    v-model="tooltip.otherButton"
                    slot="activator"
                >
                    <v-btn
                        v-if="showOtherButton"
                        icon
                        slot="activator"
                    >
                        <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                    <span>{{ $t('order.order_details.show.information_component_management.header.other') }}</span>
                </v-tooltip>
                <v-list dense>
                    <v-list-tile
                        v-for="(item, index) in items"
                        :key="index"
                        @click="item.func"
                    >
                        <v-list-tile-avatar>
                            <v-icon>{{ item.icon }}</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>{{ item.title }}</v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-menu>
            <v-tooltip top>
                <v-btn v-if="showSwitchingButton" icon @click="next" slot="activator">
                    <v-icon>chevron_right</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.switch') }}</span>
            </v-tooltip>
            <v-tooltip top v-model="tooltip.rightGrowButton">
                <v-btn v-if="showRightGrowButton" icon @click="grow" slot="activator">
                    <v-icon>mdi-chevron-double-right</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.grow') }}</span>
            </v-tooltip>
            <v-tooltip top v-model="tooltip.rightShrinkButton">
                <v-btn v-if="showShrinkButtons" icon @click="shrinkLeft" slot="activator">
                    <v-icon>mdi-chevron-double-left</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.header.shrink_to_left') }}</span>
            </v-tooltip>
        </v-toolbar>
    </div>
</template>

<script>
export default {
    props: {
        mode: { type: Number, required: true },
        hideLeftGrowButton: { type: Boolean, required: false, default: false },
        hideRightGrowButton: { type: Boolean, required: false, default: false },
        hideShrinkButtons: { type: Boolean, required: false, default: false },
        hideEditButton: { type: Boolean, required: false, default: false },
        hideOtherButton: { type: Boolean, required: false, default: false },
        fullWidth: { type: Boolean, required: false, default: false },
        height: { type: [String, Number], required: false, default: undefined },
        title: { type: String, required: false, default: '' },
    },
    data() {
        return {
            // 共通処理
            tooltip: {
                leftGrowButton: false,
                rightGrowButton: false,
                leftShrinkButton: false,
                rightShrinkButton: false,
                editButton: false,
                otherButton: false,
            },
        }
    },
    computed: {
        // 共通処理
        headerColor() {
            const defaultColor = 'primary'
            return this.isEditMode ? 'light-blue' : defaultColor
        },
        showLeftGrowButton() {
            return (!this.fullWidth && !this.hideLeftGrowButton)
        },
        showRightGrowButton() {
            return (!this.fullWidth && !this.hideRightGrowButton)
        },
        showShrinkButtons() {
            return this.fullWidth
        },
        showSwitchingButton() {
            return !this.isEditMode
        },
        showEditButton() {
            return this.isEditableMode && !this.hideEditButton
        },
        showOtherButton() {
            return this.isEditableMode && !this.hideOtherButton
        },
        isEditableMode() {
            return [_const.DISPLAY_MODE.EDIT_OFF].includes(this.mode)
        },
        isEditMode() {
            return [_const.DISPLAY_MODE.EDIT_ON].includes(this.mode)
        },
        items() {
            return [
                {
                    title: this.$t('order.order_details.show.information_component_management.header.create_request'),
                    func: this.openCreateRequest,
                    icon: undefined,
                },
                {
                    title: this.$t('order.order_details.show.information_component_management.header.create_mail'),
                    func: this.openCreateMail,
                    icon: 'email'
                }
            ]
        },
    },
    methods: {
        previous: function() {
            this.$emit('previous')
        },
        next: function() {
            this.$emit('next')
        },
        grow: function() {
            this.hiddenTooltip()
            this.$emit('grow')
        },
        shrinkRight: function() {
            this.hiddenTooltip()
            this.$emit('shrink-right')
        },
        shrinkLeft: function() {
            this.hiddenTooltip()
            this.$emit('shrink-left')
        },
        hiddenTooltip: function() {
            for (const key in this.tooltip) {
                this.tooltip[key] = false
            }
        },
        toEditMode: async function() {
            await this.$nextTick()
            this.hiddenTooltip()
            this.$emit('to-edit-mode')
        },
        toReadMode: function() {
            this.$emit('to-read-mode')
        },
        openCreateRequest: function() {
            this.$emit('open-create-request')
        },
        openCreateMail: function() {
            this.$emit('open-create-mail')
        },
    }
}
</script>

<style scoped>
</style>
