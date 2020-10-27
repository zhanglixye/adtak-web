<template>
    <!-- お知らせモーダル -->
    <div id="abbey-notify-modal-block" v-if="modal">
        <v-container row justify-center>
            <v-dialog v-model="modal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline"></span>
                    </v-card-title>
                    <v-card-text>{{message}}</v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn auto color="primary" @click="click">{{ $t('common.button.close') }}</v-btn>
                        <v-spacer></v-spacer>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
    </div>
    <!-- /お知らせモーダル -->
</template>

<script>
export default {
    components: {
    },
    data: () => ({
        next_process_message: '',
        modal: false,
        message: '',
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-notify-modal', function(data) {
            self.modal = true
            self.message = data.message
            self.next_process_message = data.emitMessage
        })

        eventHub.$on('close-notify-modal', function() {
            self.modal = false
        })
    },
    methods: {
        click: function() {
            this.modal = false
            console.log(this.next_process_message)
            if (this.next_process_message != undefined && this.next_process_message != '') {
                eventHub.$emit(this.next_process_message)
            }
        }
    }
}
</script>
