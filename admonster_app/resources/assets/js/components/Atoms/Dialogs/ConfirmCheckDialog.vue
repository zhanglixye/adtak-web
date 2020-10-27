<template>
    <v-dialog v-model="dialog" persistent max-width="600">
        <v-card>
            <v-card-text class="text-xs-center">
            <v-checkbox
                v-for="(message, index) in messages" :key="index"
                v-model="checklist[index]"
                :label="message"
                hide-details
            ></v-checkbox>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn dark color="grey" @click.native="cancel">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click.native="ok" :disabled="isRemaining">{{ $t('common.button.ok') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    data: () => ({
        dialog: false,
        messages: [],
        checklist: [],
        resolve: null,
        reject: null,
    }),
    computed: {
        isRemaining () {
            return this.checklist.filter(check => check == false).length ? true : false
        },
    },
    methods: {
        show (messages) {
            this.dialog = true
            this.messages = messages
            this.checklist = messages.map(() => false)
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
