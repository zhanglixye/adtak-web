<template>
    <v-dialog v-model="dialog" max-width="600">
        <v-card>
            <v-card-title>
                <v-icon class="mr-3">mail</v-icon>
                <span>{{ $t('common.mail.info') }}</span>
            </v-card-title>
            <v-card-text>
                <mail-view
                    :mail="mail"
                ></mail-view>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn dark color="grey" @click.native="close">{{ $t('common.button.close') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import MailView from '../../Molecules/Requests/MailView'

export default {
    components: {
        MailView,
    },
    data: () => ({
        dialog: false,
        mail: {},
    }),
    methods: {
        show (mail) {
            this.mail = mail
            this.dialog = true
        },
        close () {
            this.dialog = false
            this.mail = {}
        },
    },
}
</script>
