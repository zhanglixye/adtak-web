<template>
    <v-dialog v-model="dialog" persistent :width="width">
        <v-card>
            <v-card-text v-html="message" style="text-align: center;"></v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn color="primary" @click.native="ok">{{ $t('common.button.ok') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        width: {type: [Number, String], required: false, default: 300}
    },
    data: () => ({
        dialog: false,
        message: null,
        okFunc: null,
    }),
    methods: {
        show (message, okFunc) {
            this.dialog = true
            this.message = message
            this.okFunc = okFunc
        },
        ok () {
            if (typeof this.okFunc === 'function') this.okFunc()
            this.dialog = false
        },
    }
}
</script>
