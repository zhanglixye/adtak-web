<template>
    <v-app>
        <app-menu :drawer="sideMenuDrawer"></app-menu>
        <app-header
            :title="$t('order.orders.setting.title')"
            :subtitle="$t('order.orders.setting.item.subtitle')"
        ></app-header>
        <v-content>
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <v-flex xs12>
                        <page-header back-button></page-header>
                    </v-flex>
                    <v-flex xs12>
                        <settings></settings>
                    </v-flex>
                    <v-flex xs12>
                        <div
                            class="text-xs-center"
                        >
                            <v-btn
                                :disabled="isDisabled"
                                color="primary"
                                @click="save"
                            >{{ $t('common.button.save') }}</v-btn>
                        </div>
                    </v-flex>
                    <v-flex xs12>
                        <page-footer back-button></page-footer>
                    </v-flex>
                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <alert-dialog ref="alert"></alert-dialog>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import Settings from '../../../Organisms/Order/Orders/Detail/Item/Settings'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import store from '../../../../stores/Order/Orders/Detail/Item/store'

export default {
    components: {
        PageHeader,
        ProgressCircular,
        Settings,
        ConfirmDialog,
        AlertDialog,
        PageFooter,
    },
    props: {
        inputs: { type: Object, required: true },
    },
    data: () => ({
        loading: false,
        sideMenuDrawer: false,
    }),
    computed: {
        isDisabled: function() {
            return !store.state.processingData.valid
        },
        orderId: function() {
            return this.inputs.order_id
        },
        order: function() {
            return store.state.order
        }
    },
    created() {
        this.init()
    },
    methods: {
        init: async function() {
            this.loading = true
            try {
                await store.dispatch('tryLoad', this.orderId)
            } catch (error) {
                await this.$refs.alert.show(error, () => {window.history.back()})
            }
            this.loading = false
        },
        loadingDisplay: function(bool) {
            this.loading = bool
        },
        save: async function() {
            try {
                let message = ''
                if (this.order.is_active === _const.FLG.INACTIVE) message += `${this.$t('order.orders.setting.message.is_order_inactive')}<br>`
                message += this.$t('order.orders.setting.message.save')
                if (!await this.$refs.confirm.show(message)) return
                await store.dispatch('trySave')
                await this.$refs.alert.show(this.$t('common.message.saved'), () => {window.history.back()})
            } catch (error) {
                await this.$refs.alert.show(error, () => {window.history.back()})
            }
        },
    },
}
</script>
