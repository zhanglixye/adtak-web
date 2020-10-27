<template>
    <!-- 確認モーダル -->
    <div id="confirm-modal-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline"></span>
                    </v-card-title>
                        <v-card-text v-html="message"></v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="grey" dark @click="modal = false">{{ $t('common.button.cancel') }}</v-btn>
                        <v-btn color="amber darken-3" dark @click="submit()">{{ $t('common.button.save') }}</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>
    </div>
    <!-- / 確認モーダル -->
</template>

<script>
export default {
    props: {
    },
    data: () => ({
        modal: false,
        message: ''
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-modal', function(data) {
            self.modal = true
            self.message = data.message
        })
        eventHub.$on('close-modal', function() {
            self.modal = false
        })
    },
    methods: {
        submit: function(data = null) {
            eventHub.$emit('submit', data);
        }
    }
}
</script>
