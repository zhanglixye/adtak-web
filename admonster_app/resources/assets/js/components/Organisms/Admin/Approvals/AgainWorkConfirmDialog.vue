<template>
    <v-dialog v-model="dialog" persistent max-width="600">
        <v-card>
            <v-card-text class="text-xs-center" v-html="message"></v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn dark color="grey" @click.native="close">{{ $t('common.button.close') }}</v-btn>
                <v-btn color="primary" @click.native="ok">{{ $t('common.button.again_work') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
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
        close () {
            this.resolve(false)
            this.dialog = false
        }
    },
}
</script>
