<template>
    <v-app>
        <app-menu :drawer="false"></app-menu>
        <app-header
            :title="$t('order.order_details.show.title')"
            :subtitle="order_detail_id === 0 ? $t('order.order_details.show.create') : $t('order.order_details.show.edit')">
        </app-header>
        <v-content id="list">
            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <!-- Main -->
                    <v-flex xs12 md12>
                        <page-header back-button></page-header>
                    </v-flex>
                    <v-flex xs6>
                        <div class="text-truncate headline">
                            {{ $t('order.order_details.show.subject') }}:
                            <v-tooltip top>
                                <span slot="activator">
                                    {{ subject }}
                                </span>
                                <span>{{ subject }}</span>
                            </v-tooltip>
                        </div>
                    </v-flex>
                    <v-flex xs6 class="text-right">
                        <v-tooltip top class="my-auto">
                            <v-btn v-if="isAdmin" flat icon color="primary" slot="activator" @click="() => {}">
                                <v-icon>settings</v-icon>
                            </v-btn>
                            <span>{{ $t('common.button.setting') }}</span>
                        </v-tooltip>
                    </v-flex>
                    <v-flex xs12>
                        <information-component-management :order-detail-id="order_detail_id"></information-component-management>
                    </v-flex>
                </v-layout>
                <page-footer class="mt-3" back-button></page-footer>
                <progress-circular v-if="loading"></progress-circular>
            </v-container>
            <alert-dialog ref="alert"></alert-dialog>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import store from '../../../../stores/Order/OrderDetails/Show/store'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import InformationComponentManagement from '../../../Organisms/Order/OrderDetails/InformationComponentManagement'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'

export default {
    components: {
        PageHeader,
        PageFooter,
        ProgressCircular,
        InformationComponentManagement,
        AlertDialog,
    },
    props: {
        order_id: { type: Number, required: true },
        order_detail_id: { type: Number, required: true },
    },
    data: () => ({
        displayLangCode: 'ja', // 各案件固有情報を多言語対応するか分からないので、日本語に固定
    }),
    computed: {
        subject() {
            return store.state.processingData.initSubject
        },
        loading() {
            return store.state.processingData.loading
        },
        isAdmin() {
            return store.state.processingData.isAdmin
        },
    },
    created() {
        store.commit('setOrderId', this.order_id)
        store.commit('setOrderDetailId', this.order_detail_id)
        this.init()
    },
    methods: {
        init: async function() {
            try {
                await store.dispatch('getInitData', { orderId: this.order_id, orderDetailId: this.order_detail_id })
            } catch (error) {
                if (error === 'no_admin_permission') {
                    await this.$refs.alert.show(this.$t('common.message.no_admin_permission'), () => { window.history.back() })
                } else if (error === 'no_permission') {
                    await this.$refs.alert.show(this.$t('common.message.no_permission'), () => { window.history.back() })
                } else {
                    await this.$refs.alert.show(this.$t('common.message.internal_error'), () => { window.history.back() })
                }
            }
        },
    }
}
</script>

<style>
.display-item .v-text-field {
    padding-left: 10px;
    padding-top: 0px;
}
.each-item-content {
    padding-left: 15px;
}
.info-icon-size {
    width: 24px;
    font-size: 24px;
}
</style>
