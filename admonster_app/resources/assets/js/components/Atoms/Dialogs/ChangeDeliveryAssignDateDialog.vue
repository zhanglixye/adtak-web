<template>
    <v-dialog v-model="dialog" persistent :width="width">
        <v-card>
            <v-card-title
                class="change-dialog-title"
                :style="{ backgroundColor: primaryColor, color: 'white' }"
            >
                <v-icon class="mr-3">settings</v-icon>
                <span class="mr-auto" :style="{'font-size': '16px'}">{{ $t('list.tooltip.change_delivery_date') }}</span>
                <v-btn small icon @click="cancel()">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text id="list" class="text-xs-center">
                <date-time-picker
                    v-model="assignDate"
                    :disabled="assignCancel"
                ></date-time-picker>
                <v-checkbox
                    v-model="assignCancel"
                    class="justify-center"
                    color="primary"
                    :label="$t('deliveries.edit.setting.assign_date_cancel')"
                    hide-details
                ></v-checkbox>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn dark color="grey" @click.native="cancel">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click.native="save">{{ $t('common.button.save') }}</v-btn>
            </v-card-actions>
            <progress-circular v-if="loading"></progress-circular>
        </v-card>
    </v-dialog>
</template>

<script>
import DateTimePicker from '../Pickers/DateTimePicker'
import ProgressCircular from '../../Atoms/Progress/ProgressCircular'

export default {
    components:{
        DateTimePicker,
        ProgressCircular,
    },
    props: {
        width: { type: [Number, String], required: false, default: 300 },
    },
    data: () => ({
        dialog: false,
        loading: false,
        delivery: {},
        assignDate: '',
        assignCancel: false,
    }),
    computed: {
        primaryColor () {
            return this.$vuetify.theme.primary
        },
    },
    methods: {
        show (delivery, assignDate) {
            this.delivery = delivery
            this.assignDate = assignDate
            this.assignCancel = false
            this.dialog = true
        },
        cancel () {
            this.dialog = false
        },
        save () {
            this.loading = true
            let params = new FormData()
            params.append('delivery_id', this.delivery.delivery_id)
            params.append('request_work_id', this.delivery.request_work_id)
            params.append('updated_at', this.delivery.updated_at)
            if (!this.assignCancel) {
                params.append('assign_delivery_at', this.assignDate)
            }
            axios.post('/api/deliveries/changeDeliveryAssignDate', params)
                .then((res) => {
                    if (res.data.status != 200) {
                        throw Object.assign(new Error(res.data.message), { name: res.data.error, statusCode: res.data.status })
                    }
                    this.dialog = false
                    this.$emit('reload')
                }).catch((e) => {
                    console.log(e)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.updated_by_others') })
                }).finally(() => {
                    this.loading = false
                })
        },
    },
}
</script>

<style scoped>
.v-card__title {
    padding: 0 0 0 16px;
}
.change-dialog-title .v-icon {
    color: #fff;
    caret-color: #fff;
}
</style>
