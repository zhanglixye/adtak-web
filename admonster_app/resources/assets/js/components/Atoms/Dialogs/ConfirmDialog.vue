<template>
    <v-dialog v-model="dialog" persistent max-width="600">
        <v-card>
            <v-card-text class="text-xs-center" v-html="message"></v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn dark color="grey" @click.native="cancel">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click.native="ok">{{ okButtonName }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    data: () => ({
        dialog: false,
        message: null,
        okButtonName: null,
        resolve: null,
        reject: null,
    }),
    methods: {
        show (message, okButtonName = this.$t('common.button.ok')) {
            this.dialog = true
            this.message = message
            this.okButtonName = okButtonName
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
