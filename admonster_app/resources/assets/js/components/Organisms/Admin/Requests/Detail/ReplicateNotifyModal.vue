<template>
    <!-- お知らせモーダル -->
    <div id="notify-modal-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="400px">
                <v-card>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn small icon @click="modal = false"><v-icon>close</v-icon></v-btn>
                    </v-card-actions>
                    <v-card-text class="text-xs-center">
                        <div class="mb-3">{{ message }}</div>
                        <div class="mb-1">
                            <a :href="url">{{ $t('requests.detail._modal.link_to_new') }}</a>
                        </div>
                        <div>
                            <a href="/management/requests">{{ $t('requests.detail._modal.link_to_index') }}</a>
                        </div>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn auto color="primary" @click="close">{{ $t('common.button.close') }}</v-btn>
                        <v-spacer></v-spacer>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>
    </div>
    <!-- /お知らせモーダル -->
</template>

<script>
export default {
    components: {
    },
    data: () => ({
        url: '',
        modal: false,
        message: '',
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-replicate-notify-modal', function(data) {
            self.modal = true
            self.message = data.message
            self.url = data.url
        })

        eventHub.$on('close-replicate-notify-modal', function() {
            self.modal = false
        })
    },
    methods: {
        close: function() {
            this.modal = false
        },
    }
}
</script>
