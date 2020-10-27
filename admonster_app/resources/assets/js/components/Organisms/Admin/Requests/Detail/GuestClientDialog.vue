<template>
    <v-dialog v-model="guestClientDialog" scrollable persistent width="700">
        <v-card>
            <v-card-title class="headline" primary-title>{{ $t('requests.detail._modal.guest_client.title') }}</v-card-title>
            <v-divider class="ma-0"></v-divider>
            <v-card-actions class="pa-3">
                <a :href="'/client/requests/' + requestId" target="_blank" class="font-weight-bold">
                    <span>{{ $t('requests.detail._modal.guest_client.link_to_client_page') }}</span>
                    <span class="ml-1"><v-icon small>open_in_new</v-icon></span>
                </a>
            </v-card-actions>
            <v-card-actions class="pa-3">
                <span></span>
                <v-combobox
                    v-model="selected"
                    :items="targetGuestClients"
                    :item-text="itemTextForSelect"
                    item-value="email"
                    :label="$t('requests.detail._modal.guest_client.form.email.label')"
                    :placeholder="$t('requests.detail._modal.guest_client.form.email.placeholder')"
                    prepend-icon="people_alt"
                    dense
                    multiple
                    small-chips
                    deletable-chips
                ></v-combobox>
            </v-card-actions>
            <v-divider class="ma-0"></v-divider>
            <v-card-title class="title">{{ $t('requests.detail._modal.guest_client.list.title') }}</v-card-title>
            <v-card-text style="max-height: 300px;">
                <template>
                    <!-- 提供履歴 -->
                    <v-data-table
                        :headers="headers"
                        :items="issuedGuestClients"
                        hide-actions
                        class="elevation-1"
                        style="max-height:200px;"
                    >
                        <template slot="headers" slot-scope="props">
                            <tr id="request_sort_key">
                                <th
                                    v-for="header in props.headers"
                                    :key="header.text"
                                    :class="['column sortable', 'text-xs-' + header.align]"
                                    :style="{ 'min-width': header.width + 'px' }"
                                >
                                    {{ header.text }}
                                    <v-icon small>arrow_drop_up</v-icon>
                                </th>
                            </tr>
                        </template>
                        <template slot="items" slot-scope="props">
                            <tr>
                                <td class="text-xs-left">
                                    {{ props.item['email'] }}
                                    <span v-if="props.item['type']"><v-chip small>{{ clientType(props.item['type']) }}</v-chip></span>
                                </td>
                                <td class="text-xs-right">
                                    {{ props.item['updated_at'] | formatDateYmdHm(true) }}
                                </td>
                                <td class="text-xs-center">
                                    <v-tooltip top v-if="isHuman(props.item['updated_user_id'])">
                                        <v-avatar slot="activator" size="32px" class="ma-1">
                                            <img :src="user_image_path(props.item['updated_user_id'])">
                                        </v-avatar>
                                        <span>{{ user_name(props.item['updated_user_id']) }}</span>
                                    </v-tooltip>
                                    <v-tooltip top v-else>
                                        <v-icon slot="activator" size="32px">android</v-icon>
                                        <span>自動</span>
                                    </v-tooltip>
                                </td>
                            </tr>
                        </template>
                        <template slot="no-data">
                            <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                        </template>
                    </v-data-table>
                    <!-- / 提供履歴 -->
                </template>
            </v-card-text>
            <v-divider class="ma-0"></v-divider>
            <v-card-actions class="justify-center">
                <v-btn @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click="save()" :disabled="selected.length < 1">{{ $t('common.button.send') }}</v-btn>
            </v-card-actions>
        </v-card>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <progress-circular v-if="loading"></progress-circular>
    </v-dialog>
</template>

<script>
import requestDetailMixin from '../../../../../mixins/Admin/requestDetailMixin'

import ProgressCircular from '../../../../Atoms/Progress/ProgressCircular'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'

export default {
    mixins: [requestDetailMixin],
    props: {
        requestId: { type: Number, required: true },
        requestMail: { type: Object },
        guestClientDialog: { type: Boolean, required: true },
        candidates: { type: Array, required: true }
    },
    data: () => ({
        dialog: false,
        confirm: false,
        //loading
        loading: false,

        selected: [],
        targetGuestClients: [],
        issuedGuestClients: [],
    }),
    components:{
        ProgressCircular,
        ConfirmDialog
    },
    created () {
        this.getIssuedStatus()
    },
    mounted () {
    },
    computed: {
        headers: function () {
            return [
                { text: Vue.i18n.translate('requests.detail._modal.guest_client.list.headers.mail_address'), value: 'email', align: 'center', width: '100px' ,sortable: false },
                { text: Vue.i18n.translate('requests.detail._modal.guest_client.list.headers.provision_date'), value: 'updated_at', align: 'center', width: '100px', sortable: false },
                { text: Vue.i18n.translate('requests.detail._modal.guest_client.list.headers.provider'), value: 'updated_user_id', align: 'center', width: '', sortable: false },
            ]
        }
    },
    methods: {
        // UsersOverviewコンポーネント内と共通。mixinにする
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
        getIssuedStatus () {
            this.loading = true
            axios.post('/api/requests/get_guest_client_account_issue_status',{
                request_id: this.requestId,
                request_mail_id: this.requestMail ? this.requestMail.id : '',
            })
                .then((res) => {
                    this.targetGuestClients = res.data.target_guest_clients
                    this.issuedGuestClients = res.data.issued_guest_clients
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.read_failed') })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        async save () {
            if (await(this.$refs.confirm.show('選択したメールアドレス宛にクライアントページへのアクセス用URLを送信します'))) {
                this.issueGuestClientAccount()
            }
        },
        issueGuestClientAccount () {
            if (this.selected.length < 1) {
                return false
            }
            this.loading = true
            axios.post('/api/requests/issue_guest_client_account',{
                request_id: this.requestId,
                request_mail_id: this.requestMail ? this.requestMail.id : '',
                targets: this.selected
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        eventHub.$emit('open-notify-modal', { message: 'メールを送信しました' })
                        this.$emit('update:guestClientDialog', false)
                    } else {
                        eventHub.$emit('open-notify-modal', { message: 'メールの送信に失敗しました' })
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: 'メールの送信に失敗しました' })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        cancel () {
            this.$emit('update:guestClientDialog', false)
        },
        clientType (type) {
            if (type === _const.CLIENT_ISSUE_TARGET_TYPE.MAIL_FROM) {
                return '依頼メールの送信者'
            } else if (type === _const.CLIENT_ISSUE_TARGET_TYPE.MAIL_CC) {
                return '依頼メールの cc '
            } else if (type === _const.CLIENT_ISSUE_TARGET_TYPE.MAIL_BCC) {
                return '依頼メールの bcc '
            } else {
                return ''
            }
        },
        itemTextForSelect (item) {
            const type = this.clientType(item.type)
            let text = item.email
            if (type) {
                text = '【' + type + '】' + text
            }
            return text
        }
    }
}
</script>
