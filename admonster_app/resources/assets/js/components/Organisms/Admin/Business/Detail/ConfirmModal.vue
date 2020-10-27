<template>
   <!-- 確認モーダル -->
    <div id="confirm-modal-block" v-if="confirm_modal">
        <v-layout row justify-center>
            <v-dialog v-model="confirm_modal" persistent max-width="300px">
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ $t('businesses.detail.confirm.title') }}</span>
                    </v-card-title>
                    <v-flex text-xs-center>
                        {{ message }}
                        <br>
                        {{ $t('businesses.detail.confirm.confirm') }}
                    </v-flex>
                    <v-card-actions class="btn-center-block">
                        <v-btn dark color="grey" @click="confirm_modal = false">{{ $t('common.button.cancel') }}</v-btn>
                        <v-btn color="primary"  @click="executionProcess()">OK</v-btn>
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
        confirm_modal: false,
        message: '',
        process: ''
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-confirm-modal', function(data) {
            self.confirm_modal = true
            self.message = data.message
            self.process = data.process
        })
        eventHub.$on('close-confirm-modal', function() {
            self.confirm_modal = false
        })
    },
    methods: {
        executionProcess () {
            this.confirm_modal = false;
            eventHub.$emit(this.process);
        }
    }
}
</script>
