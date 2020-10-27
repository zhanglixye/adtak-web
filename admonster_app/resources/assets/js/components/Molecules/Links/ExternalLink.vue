<template>
    <v-dialog class="external-link" v-model="dialog" max-width="350">
        <span :class="[dark ? 'text-color-white' : '']" slot="activator">
            {{ uri }}<v-icon small :dark="dark">open_in_new</v-icon>
        </span>
        <v-card>
            <v-card-text>
                {{ $t('common.message.display_in_external_link') }}<br>
                {{ $t('common.message.please_selected_ok') }}
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat color="primary" @click="dialog = false">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn flat color="primary" @click="openWindow()">{{ $t('common.button.ok') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        uri: { type: String, require: true },
        dark: { type: Boolean, required: false, default: false},
    },
    data: () => ({
        dialog: false,
    }),
    methods: {
        openWindow () {
            // TODO: 別ウインドウ表示に関するオプションは要検討
            window.open(this.uri)

            this.dialog = false
        }
    }
}
</script>

<style scoped>
.text-color-white {
    color: white !important;
}
.external-link {
    font-size: 12px;
}
</style>
