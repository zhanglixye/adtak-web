<template>
    <!-- 確認モーダル -->
    <div id="except-confirm-modal-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline"></span>
                    </v-card-title>
                    <v-card-text>
                        {{ message }}
                        {{ $t('requests.detail._modal.suffix') }}
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="grey" dark @click="modal = false">{{ $t('common.button.cancel') }}</v-btn>
                        <v-btn color="primary" @click="submit()">{{ $t('common.button.ok') }}</v-btn>
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
        message: '',
        method: '',
        selected1: '',
        selected2: '',
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-except-confirm-modal', function(data) {
            self.modal = true
            self.message = data.message
        })
        eventHub.$on('close-except-confirm-modal', function() {
            self.modal = false
        })
    },
    methods: {
        submit: function() {
            eventHub.$emit('exceptRequest');
        }
    }
}
</script>
