<template>
    <div
        id="custom-status-setting"
    >
        <div>
            {{ $t('order.orders.setting.custom_status.title') }}
            <v-btn flat icon color="primary" @click="openCustomStatusModal()">
                <v-icon>mdi-plus-circle</v-icon>
            </v-btn>
        </div>
        <div
            class="elevation-1"
        >
            <draggable :list="customStatuses" :options="{ animation: 300, handle: '.handle' }">
                <li
                    class="list-group-item"
                    style="overflow: auto;"
                    v-for="customStatus in customStatuses"
                    :key="customStatus.forKey"
                >
                    <span style="float: left;" class="handle">
                        <v-icon>mdi-menu</v-icon>
                    </span>
                    <v-tooltip top>
                        <span
                            @click="openCustomStatusModal(customStatus.forKey)"
                            slot="activator"
                            class="status-item"
                            :style="{ 'text-decoration': 'underline', color: '#4DB6AC', cursor: 'pointer' }"
                            >{{ customStatus.customStatusName }}</span>
                        <span>{{ customStatus.customStatusName }}</span>
                    </v-tooltip>
                    <v-btn
                        style="float: right; color: rgba(0, 0, 0, .54);"
                        small
                        icon
                        @click="deleteCustomStatus(customStatus.forKey, customStatus.isUseCustomStatus)"
                    >
                        <v-icon>close</v-icon>
                    </v-btn>
                </li>
            </draggable>
        </div>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <custom-status-dialog ref="customStatus"></custom-status-dialog>
    </div>
</template>

<script>
import CustomStatusDialog from './CustomStatusDialog'
import store from '../../../../../stores/Order/Orders/Detail/store'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'

export default {
    components: {
        CustomStatusDialog,
        ConfirmDialog,
    },
    props: {
        orderId: { type: String, required: true },
    },
    computed: {
        customStatuses: function() {
            return store.state.processingData.customStatuses
        },
        deleteCustomStatusIds: function() {
            return store.state.processingData.deleteCustomStatusIds
        },
    },
    methods: {
        deleteCustomStatus: async function(forKey, isUseCustomStatus) {
            const messages = []
            if (isUseCustomStatus) messages.push(this.$t('order.orders.setting.custom_status.message.status_used_in_order_detail'))
            messages.push(this.$t('order.orders.setting.custom_status.message.delete'))
            const message = messages.join('<br>')
            if (!(await this.$refs.confirm.show(message))) return
            if (isUseCustomStatus) {
                if (!await this.$refs.confirm.show(`<h4>${this.$t('order.orders.setting.custom_status.message.last_confirmation')}</h4>
                    <div style="color: red;">${this.$t('order.orders.setting.custom_status.message.delete_last_confirmation')}</div>`,
                this.$t('common.button.delete'))) return
            }

            const deleteCustomStatusId = this.customStatuses.find(item => item.forKey === forKey)['customStatusId']
            if (deleteCustomStatusId !== null) store.commit('setDeleteCustomStatusIds', deleteCustomStatusId) // deleteCustomStatusId !== nullはDB登録済み

            store.commit('setCustomStatuses', this.customStatuses.filter(item => item.forKey !== forKey))
        },
        openCustomStatusModal: function(forKey = null) {
            this.$refs.customStatus.open(forKey)
        },
    },
}
</script>

<style scoped>
.sortable-ghost {
    background-color: #dcdcdc;
}
.handle {
    color: rgba(0, 0, 0, 0.54);
    padding: 10px;
    cursor: move;
}
.status-item {
    float: left;
    max-width: 650px;
    padding: 10px;
    margin-right: 10px;
}
</style>
