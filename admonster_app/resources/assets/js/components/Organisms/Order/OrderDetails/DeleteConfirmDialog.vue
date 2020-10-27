<template>
    <v-dialog v-model="dialog" persistent width="500">
        <v-card>
            <v-card-title class="headline" primary-title>{{ $t('requests.confirm') }}</v-card-title>
            <v-card-text>
                {{ $t('order.order_details.show.information_component_management.related_mail.delete_dialog.confirm') }}
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat color="primary" @click="save()">{{ $t('common.button.ok') }}</v-btn>
                <v-btn flat color="primary" @click="close()">{{ $t('common.button.cancel') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        dialog: { type: Boolean, required: true },
        selectedId: { type: Number, required: false, default: null },
        callback: { type: String, required: true }
    },
    methods: {
        save () {
            this.$emit('update:dialog', false)
            eventHub.$emit(this.callback, this.selectedId)
        },
        close () {
            this.$emit('update:dialog', false)
            this.$emit('to-read-mode')
        }
    }
}
</script>
