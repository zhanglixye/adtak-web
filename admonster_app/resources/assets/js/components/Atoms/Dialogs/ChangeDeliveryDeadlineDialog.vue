<template>
    <v-dialog v-model="dialog" persistent :width="width">
        <v-card>
            <v-card-title
                class="change-dialog-title"
                :style="{'background-color': primaryColor, 'color': 'white'}"
            >
                <v-icon class="mr-3">settings</v-icon>
                <span class="mr-auto" :style="{'font-size': '16px'}">{{ $t('list.tooltip.change_delivery_deadline') }}</span>
                <v-btn
                    small
                    icon
                    @click="cancel()"
                >
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text id="list" class="text-xs-center">
                <date-time-picker
                    v-model="deadline"
                >
                </date-time-picker>
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
        ProgressCircular
    },
    props: {
        width: {type: [Number, String], required: false, default: 300},
        apiPath: {type: String, required: true},
    },
    data: () => ({
        dialog: false,
        loading: false,
        id: null,
        deadline: '',
    }),
    computed: {
        primaryColor () {
            return this.$vuetify.theme.primary
        },
    },
    methods: {
        show (deadline, id) {
            this.deadline = deadline
            this.id = id
            this.dialog = true
        },
        cancel () {
            this.dialog = false
        },
        save () {
            this.loading = true
            let params = new FormData()
            params.append('deadline', this.deadline)
            params.append('id', this.id)
            axios.post(this.apiPath + '/changeDeliveryDeadline', params)
                .then((res) =>{
                    if (res.data.status > 200) {
                        throw Object.assign(new Error(res.data.message), {name: res.data.error, statusCode: res.data.status})
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
    }
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
