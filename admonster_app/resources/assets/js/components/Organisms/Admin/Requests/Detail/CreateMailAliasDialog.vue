<template>
    <div>
        <v-dialog v-model="createFormDialog" persistent width="600">
            <v-card id="createFormDialog">
                <v-card-title class="headline">{{ $t('requests.detail.related_mails.create_dialog.title') }}</v-card-title>
                <v-card-text>
                    <div
                        v-for="(label, key) in $t('requests.detail.related_mails.create_dialog.steps')"
                        :key="key"
                    >{{ label }}</div>
                    <div><span class="caption">{{ $t('requests.detail.related_mails.create_dialog.annotation') }}</span></div>
                </v-card-text>
                <v-card-actions class="justify-center pa-5">
                    <div v-if="aliasCreateFlag">
                        <div><span class="body-2 mb-1">{{ alias_mail_address }}</span></div>
                        <div><span class="red--text darken-4">{{ $t('requests.detail.related_mails.create_dialog.completed.expiration_date') }}</span></div>
                        <div><span class="red--text darken-4">{{ captionRelatedToClient }}</span></div>
                        <div><span class="red--text darken-4">{{ captionRelatedToWork }}</span></div>
                    </div>
                    <div v-else>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    v-model="customFrom"
                                    hide-details
                                    :placeholder="$t('requests.detail.related_mails.create_dialog.custom_from')"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-switch :label="textForIsOpenToClientSwitcher" v-model="isOpenToClient" color="primary" class="d-inline-flex"></v-switch>
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-switch :label="textForIsOpenToWorkSwitcher" v-model="isOpenToWork" color="primary" class="d-inline-flex"></v-switch>
                            </v-flex>
                            <v-flex xs12 class="justify-center" style="display: flex;">
                                <v-btn large color="orange" @click="create_aliases()">{{ $t('requests.detail.related_mails.create_dialog.create_btn') }}</v-btn>
                            </v-flex>
                        </v-layout>
                    </div>
                </v-card-actions>
                <v-card-actions class="justify-center">
                    <v-btn @click="close()">{{ $t('common.button.close') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <center-progress-circular v-show="loading"></center-progress-circular>
    </div>
</template>

<script>
import CenterProgressCircular from '../../../../Atoms/Progress/CenterProgressCircular'
export default {
    name: 'CreateMailAliasDialog',
    components: {
        CenterProgressCircular
    },
    props: {
        createFormDialog: { type: Boolean, required: true },
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number }
    },
    data: ()=> ({
        alias_mail_address: '',
        loading: false,
        aliasCreateFlag: false,
        isOpenToClient: false,
        isOpenToClientAfterCreate: '',
        isOpenToWork: false,
        customFrom: '',
    }),
    watch: {
        createFormDialog () {
            if (this.alias_mail_address != '' && this.createFormDialog == false){
                this.aliasCreateFlag = true
            }
            else if (this.createFormDialog == true) {
                this.alias_mail_address = ''
                this.aliasCreateFlag = false
            }
            else if (this.alias_mail_address == '' && this.createFormDialog == false){
                this.aliasCreateFlag = false
            }
        }
    },
    computed: {
        textForIsOpenToClientSwitcher () {
            const label = this.$t('requests.detail.related_mails.create_dialog.settings.client_display')
            return label + (this.isOpenToClient ? 'on' : 'off')
        },
        captionRelatedToClient () {
            const label = this.$t('requests.detail.related_mails.create_dialog.completed.client_display')
            return label + this.$t('requests.detail.related_mails.create_dialog.switcher.' + (this.isOpenToClientAfterCreate ? 'on' : 'off'))
        },
        textForIsOpenToWorkSwitcher () {
            const label = this.$t('requests.detail.related_mails.create_dialog.settings.task_display')
            return label + (this.isOpenToWork ? 'on' : 'off')
        },
        captionRelatedToWork () {
            const label = this.$t('requests.detail.related_mails.create_dialog.completed.task_display')
            return label + this.$t('requests.detail.related_mails.create_dialog.switcher.' + (this.isOpenToWork ? 'on' : 'off'))
        }
    },
    methods: {
        close () {
            this.isOpenToWork = false
            this.customFrom = ''
            this.$emit('update:createFormDialog', false)
        },
        create_aliases () {
            this.loading = true
            axios.post('/api/requests/alias_mail_address', {
                request_id: this.requestId,
                request_work_id: this.requestWorkId,
                is_open_to_client: this.isOpenToClient,
                is_open_to_work: this.isOpenToWork,
                from: this.customFrom,
            })
                .then((res) => {
                    this.aliasCreateFlag = true
                    this.alias_mail_address = res.data.alias
                    this.isOpenToClientAfterCreate = res.data.is_open_to_client

                    // 初期化
                    this.isOpenToClient = false
                })
                .catch((err) => {
                    // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') });
                })
                .finally(() => {
                    this.loading = false
                });
        }
    }
}
</script>
<style>
.v-input label {
    margin-bottom: 0;
}
</style>
