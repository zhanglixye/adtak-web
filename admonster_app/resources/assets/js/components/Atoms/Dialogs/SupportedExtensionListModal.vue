<template>
    <v-dialog v-model="dialog" persistent width="300">
        <v-card>
            <v-card-title class="pb-2">
                <span>{{ $t('approvals.file_preview.support_extension_list') }}</span>
            </v-card-title>
            <v-divider class="my-0"></v-divider>
            <v-card-text  style="text-align: left;">
                <span v-for="(supportedExtension, index) in supportedExtensions" :key="index" >
                    <span v-if="!checkEndChar(index)">
                        {{ supportedExtension + '、' }}
                    </span>
                    <span v-else>
                        {{ supportedExtension }}
                    </span>
                </span>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn color="primary" @click.native="close">{{ $t('common.button.close') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    data: () => ({
        dialog: false,
        message: null,
        supportedExtensions: []
    }),
    methods: {
        checkEndChar (index) {
            return this.supportedExtensions.length -1 === index
        },
        show (supportedExtensions) {
            this.supportedExtensions = supportedExtensions
            this.dialog = true
        },
        close () {
            this.dialog = false
        },
    }
}
</script>
