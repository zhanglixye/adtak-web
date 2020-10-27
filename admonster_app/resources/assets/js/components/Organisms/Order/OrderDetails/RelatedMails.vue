<template>
    <div id="related-mails">
        <div class="related-mail-title mb-2">
            <span
                color="transparent"
            >
                <span>{{ relatedMails.length }} {{ $t('list.case') }}</span>
            </span>
            <v-spacer></v-spacer>
            <v-tooltip top v-if="!isReadonlyMode && orderDetailId !== 0">
                <v-btn
                    icon
                    small
                    color="primary"
                    slot="activator"
                    @click="openCreateFormDialog()"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <span>{{ $t('order.order_details.show.information_component_management.related_mail.import') }}</span>
            </v-tooltip>
            <v-btn
                icon
                small
                color="primary"
                slot="activator"
                @click="openAllRelatedMails()"
            >
                <v-icon v-if="unfoldFlag">unfold_less</v-icon>
                <v-icon v-else>unfold_more</v-icon>
            </v-btn>
        </div>
        <create-mail-alias-dialog
            :create-form-dialog.sync="createFormDialog"
            :order-detail-id="orderDetailId"
            @to-read-mode="toReadMode"
        >
        </create-mail-alias-dialog>

        <div id="related-mail-list-wrap">
            <v-card>
                <v-card-text class="pt-2 pb-2">
                    <div v-for="(item, index) in relatedMails" :key="item.id" class="add-info-list pa-1">
                        <template>
                            <div
                                class="related-mail-panel-header ma-0"
                                @click="togglePanel(item.id)"
                            >
                                <div>
                                    <span><v-icon>mail_outline</v-icon></span>
                                </div>
                                <template v-if="openRelatedMail(item.id)">
                                    <div class="related-mail-from ml-3" style="min-width: 120px; max-width: 120px">
                                        <span class="font-weight-bold body-1">{{ from(item) }}</span>
                                    </div>
                                    <div class="related-mail-subject ml-3">
                                        <span class="ml-1 body-1 font-weight-bold">{{ item.subject }}</span>
                                        <span class="caption">
                                            <span class="ml-1 mr-1">-</span>
                                            <div class="d-inline-block" v-html="item.body"></div>
                                        </span>
                                    </div>
                                    <v-spacer></v-spacer>

                                    <!-- ボタン -->
                                    <div
                                        v-if="!isReadonlyMode && ((userId === item.pivot.created_user_id) || (userId === item.pivot.updated_user_id))"
                                        class="content-detail"
                                    >
                                        <v-tooltip top>
                                            <v-btn
                                                slot="activator"
                                                icon
                                                small
                                                @click.stop="openDeleteConfirmDialog(item.pivot.id, 'deleteRelatedMail')"
                                            >
                                                <v-icon>delete_outline</v-icon>
                                            </v-btn>
                                            <span>{{ $t('common.button.delete') }}</span>
                                        </v-tooltip>
                                    </div>
                                    <!-- / ボタン -->

                                    <div class="related-mail-time ml-1" style="min-width:100px">
                                        <span class="caption">{{ item.created_at | formatDateYmdHm(false, true) }}</span>
                                    </div>
                                    <v-btn icon small>
                                        <v-icon>keyboard_arrow_down</v-icon>
                                    </v-btn>
                                </template>
                                <template v-else>
                                    <v-spacer></v-spacer>
                                    <v-btn icon small>
                                        <v-icon>keyboard_arrow_up</v-icon>
                                    </v-btn>
                                </template>
                            </div>

                            <!-- 添付ファイル -->
                            <div v-show="(openRelatedMails.indexOf(item.id) === -1) ? true : false">
                                <ul class="related-mail-files">
                                    <li v-for="(attachment, i) in item.attachments" :key="i">
                                        <div>
                                            <v-tooltip top v-if="i === 0">
                                                <v-btn slot="activator" icon small @click="togglePanel(item.id)">
                                                    <v-icon>attachment</v-icon>
                                                </v-btn>
                                                <span>{{ $t('order.order_details.show.information_component_management.related_mail.attachments') }}</span>
                                            </v-tooltip>
                                            <v-tooltip top v-if="i <= 2">
                                                <v-chip
                                                    slot="activator"
                                                    color="primary"
                                                    small
                                                    outline
                                                    @click="mailAttachmentDownload(attachment)"
                                                >
                                                    <span id="attachment-name">{{ attachment.name }}</span></v-chip>
                                                <span>{{ attachment.name }}</span>
                                            </v-tooltip>
                                            <v-tooltip top v-if="i === 3">
                                                <v-chip
                                                    slot="activator"
                                                    color="primary"
                                                    small
                                                    text-color="white"
                                                    @click="togglePanel(item.id)"
                                                >
                                                    <span style="cursor: pointer;">
                                                        +{{ item.attachments.length - 3 }}
                                                    </span>
                                                </v-chip>
                                                <span>{{ $t('order.order_details.show.information_component_management.related_mail.other_attachment_number', { count: item.attachments.length - 3 }) }}</span>
                                            </v-tooltip>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <!-- / 添付ファイル -->
                            <v-flex xs12 v-if="(openRelatedMails.indexOf(item.id) === -1) ? false : true">
                                <div id="related-mail-content-wrap">
                                    <related-mail :mail="item"></related-mail>
                                </div>
                            </v-flex>
                        </template>

                        <v-divider color="#BDBDBD" v-if="index + 1 < relatedMails.length" class="ma-0"></v-divider>
                    </div>
                </v-card-text>
            </v-card>
        </div>
        <delete-confirm-dialog
            :dialog.sync="deleteConfirmDialog"
            :selected-id="selectedId"
            :callback="deleteConfirmCallback"
            @to-read-mode="toReadMode"
        ></delete-confirm-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <notify-modal></notify-modal>
    </div>
</template>

<script>
import store from '../../../../stores/Order/OrderDetails/Show/related-mail'
import RelatedMail from '../../../Molecules/Mails/RelatedMail'
import DeleteConfirmDialog from './DeleteConfirmDialog'
import CreateMailAliasDialog from './CreateMailAliasDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import orderInfoStore from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    name: 'RelatedMails',
    components: {
        RelatedMail,
        DeleteConfirmDialog,
        CreateMailAliasDialog,
        ConfirmDialog,
        NotifyModal
    },
    props: {
        orderDetailId: { type: Number, required: true },
        isReadonlyMode: { type: Boolean, required: true }
    },
    data: ()=>({
        unfoldFlag: store.state.unfoldFlag,

        deleteConfirmDialog: false,
        deleteConfirmCallback: '',
        selectedId: null,

        openRelatedMails: store.state.openRelatedMails,
        createFormDialog: false,
        relatedMails:[]
    }),
    computed: {
        userId() {
            return Number(document.getElementById('login-user-id').value)
        },
        openRelatedMail() {
            const self = this
            return function (relatedMailId) {
                return self.openRelatedMails.indexOf(relatedMailId) === -1
            }
        },
        from() {
            return function (mail) {
                const from = mail.pivot.from
                return from ? from : mail.from.split('&lt;')[0]
            }
        },
    },
    watch: {
        openRelatedMails() {
            if (this.openRelatedMails.length === this.relatedMails.length) {
                this.unfoldFlag = true
            }
            if (this.openRelatedMails.length === 0) {
                this.unfoldFlag = false
            }
            store.commit('setStateObj', { unfoldFlag: this.unfoldFlag })
        }
    },
    created() {
        if (this.orderDetailId !== 0) this.getRequestRelatedMails()
        const self = this
        eventHub.$on('deleteRelatedMail', function (selectedId) {
            self.deleteRelatedMail(selectedId)
        })
    },
    methods: {
        openDeleteConfirmDialog(id, callBack) {
            this.toEditMode()
            this.deleteConfirmCallback = callBack
            this.deleteConfirmDialog = true
            this.selectedId = id
        },
        async toEditMode() {
            await this.$nextTick()
            this.$emit('to-edit-mode')
        },
        async toReadMode() {
            await this.$nextTick()
            this.$emit('to-read-mode')
        },
        openCreateFormDialog() {
            this.toEditMode()
            this.createFormDialog = true
        },
        async deleteRelatedMail(orderRelatedMailId) {
            orderInfoStore.commit('setLoading', true)
            const form = Vue.util.extend({}, { order_related_mail_id: orderRelatedMailId, started_at: this.startedAt })
            try {
                const res = await axios.post('/api/order/order_details/delete_order_related_mails', form)
                if (res.data.status !== 200) throw res.data.message
                eventHub.$emit('open-notify-modal', { message: this.$t('common.message.deleted') })
                orderInfoStore.commit('setLoading', false)
                await this.getRequestRelatedMails()
            } catch (error) {
                orderInfoStore.commit('setLoading', false)
                console.log(error)
                if (error === 'no_admin_permission') {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.no_admin_permission') })
                } else if (error === 'updated_by_others') {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.updated_by_others') })
                } else {
                    eventHub.$emit('open-notify-modal', { message: this.$t('order.order_details.show.information_component_management.related_mail.failed_to_deletion') })
                }
            }
            this.toReadMode()
        },
        async getRequestRelatedMails() {
            const form = Vue.util.extend({}, { order_detail_id: this.orderDetailId })
            try {
                const res = await axios.post('/api/order/order_details/get_related_mail_list', form)
                if (res.data.status !== 200) throw res.data.message
                this.relatedMails = res.data.related_mails
                this.startedAt = res.data.started_at
            } catch (error) {
                console.log(error)
                eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
            }
        },
        togglePanel(id) {
            const arr = this.openRelatedMails
            if (arr.indexOf(id) !== -1) {
                const newArr = arr.filter(n => n !== id)
                this.openRelatedMails = newArr
            } else {
                this.openRelatedMails.push(id)
            }
            store.commit('setStateObj', { openRelatedMails: this.openRelatedMails })
        },
        openAllRelatedMails() {
            const arr = []
            if (this.relatedMails.length > 0 && this.unfoldFlag === false) {
                this.relatedMails.forEach(function (relatedMail) {
                    arr.push(relatedMail.id)
                })
                this.unfoldFlag = true
            } else {
                this.unfoldFlag = false
            }
            this.openRelatedMails = arr
            store.commit('setStateObj', { openRelatedMails: this.openRelatedMails} )
        },
        mailAttachmentDownload(attachment) {
            if (attachment) {
                const fileName = attachment.name
                const filePath = attachment.file_path
                window.location.href = '/utilities/download_file?file_path=' + encodeURIComponent(filePath) + '&file_name=' + encodeURIComponent(fileName)
            }
        },
    }
}
</script>

<style scoped>
.related-mail-panel-header {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.related-mail-title, .related-mail-files {
    display: flex;
    align-items: center;
}
.related-mail-panel-header .related-mail-subject, .related-mail-from {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#related-mail-content-wrap {
    overflow-x: hidden;
    overflow-y: auto;
    max-height: 615px;
}
#attachment-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 80px;
    cursor: pointer;
}
</style>
