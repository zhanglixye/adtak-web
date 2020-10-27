<template>
    <div>
        <v-dialog v-model="createFormDialog" persistent width="600">
            <v-card id="createFormDialog">
                <v-card-title class="headline">
                    {{ $t('order.order_details.show.information_component_management.related_mail.create_dialog.title') }}
                </v-card-title>
                <v-card-text>
                    <div
                        v-for="(label, key) in $t('order.order_details.show.information_component_management.related_mail.create_dialog.steps')"
                        :key="key"
                    >{{ label }}</div>
                    <div>
                        <span class="caption">
                            {{ $t('order.order_details.show.information_component_management.related_mail.create_dialog.annotation') }}
                        </span>
                    </div>
                </v-card-text>
                <v-card-actions class="justify-center pa-5">
                    <div v-if="aliasCreateFlag">
                        <div><span class="body-2 mb-1">{{ aliasMailAddress }}</span></div>
                        <div>
                            <span class="red--text darken-4">
                                {{ $t('order.order_details.show.information_component_management.related_mail.create_dialog.completed.expiration_date') }}
                            </span>
                        </div>
                    </div>
                    <div v-else>
                        <v-layout row wrap>
                            <v-flex xs12 style="margin-bottom: 16px;">
                                <v-text-field
                                    v-model="customFrom"
                                    hide-details
                                    :placeholder="$t('requests.detail.related_mails.create_dialog.custom_from')"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12 class="justify-center" style="display: flex;">
                                <v-btn
                                    large
                                    color="orange"
                                    @click="createAliases()"
                                >
                                    {{ $t('order.order_details.show.information_component_management.related_mail.create_dialog.create_btn') }}
                                </v-btn>
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
// Components
import CenterProgressCircular from '../../../Atoms/Progress/CenterProgressCircular'

export default {
    name: 'CreateMailAliasDialog',
    components: {
        CenterProgressCircular
    },
    props: {
        createFormDialog: { type: Boolean, required: true },
        orderDetailId: { type: Number, required: true }
    },
    data: ()=> ({
        aliasMailAddress: '',
        loading: false,
        aliasCreateFlag: false,
        customFrom: '',
    }),
    watch: {
        createFormDialog () {
            if (this.aliasMailAddress !== '' && this.createFormDialog === false) {
                this.aliasCreateFlag = true
            }
            else if (this.createFormDialog === true) {
                this.aliasMailAddress = ''
                this.aliasCreateFlag = false
            }
            else if (this.aliasMailAddress === '' && this.createFormDialog === false) {
                this.aliasCreateFlag = false
            }
        }
    },
    methods: {
        close () {
            this.customFrom = ''
            this.$emit('to-read-mode')
            this.$emit('update:createFormDialog', false)
        },
        async createAliases () {
            this.loading = true
            const form = Vue.util.extend({}, { order_detail_id: this.orderDetailId, custom_from: this.customFrom })
            try {
                const res = await axios.post('/api/order/order_details/alias_mail_address', form)
                if (res.data.status !== 200) throw res.data.message
                this.aliasCreateFlag = true
                this.aliasMailAddress = res.data.alias
            } catch (error) {
                console.log(error)
                if (error === 'no_admin_permission') {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.no_admin_permission') })
                } else {
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                }
            }
            this.loading = false
        }
    }
}
</script>
<style>
.v-input label {
    margin-bottom: 0;
}
</style>
