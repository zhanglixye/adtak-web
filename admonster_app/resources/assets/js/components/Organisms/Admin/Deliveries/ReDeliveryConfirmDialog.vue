<template>
    <v-dialog v-model="dialog" persistent :width="width" :max-width="maxWidth">
        <v-card>
            <v-card-text class="text-xs-center" v-html="message"></v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn dark color="grey" @click.native="cancel">{{ $t('common.button.stop') }}</v-btn>
                <v-btn color="primary" @click.native="ok">{{ $t('common.button.redelivery') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        width: {type:[String, Number], required : false, default:''},
        maxWidth: {type:[String, Number], required : false, default:600}
    },
    data: () => ({
        dialog: false,
        message: null,
        resolve: null,
        reject: null,
    }),
    methods: {
        show (message) {
            this.dialog = true
            this.message = message
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        ok () {
            this.resolve(true)
            this.dialog = false
        },
        cancel () {
            this.resolve(false)
            this.dialog = false
        }
    },
}
</script>
