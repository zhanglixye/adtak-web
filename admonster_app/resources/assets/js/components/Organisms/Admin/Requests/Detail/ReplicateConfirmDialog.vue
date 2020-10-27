<template>
    <!-- 確認モーダル -->
    <div id="replicate-confirm-modal-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline"></span>
                    </v-card-title>
                    <v-card-text>
                        {{ message }}
                    </v-card-text>
                    <v-card-text>
                        {{ $t('requests.detail._modal.description') }}
                        <span class="caption red--text ml-1">{{ $t('requests.detail._modal.description_notes') }}</span>
                        <v-checkbox
                            v-model="replicationFlags.related_mail_replication_flag"
                            :label="$t('requests.detail._modal.related_mail')"
                            color="primary"
                            hide-details
                        ></v-checkbox>
                        <v-checkbox
                            v-model="replicationFlags.additional_info_replication_flag"
                            :label="$t('requests.detail._modal.additional_info') + $t('requests.detail._modal.additional_info_notes')"
                            color="primary"
                            hide-details
                        ></v-checkbox>
                    </v-card-text>
                    <v-card-text>
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
        replicationFlags: {
            related_mail_replication_flag: false,
            additional_info_replication_flag: false,
        },
    }),
    created: function () {
        let self = this;
        eventHub.$on('open-replicate-confirm-modal', function(data) {
            self.modal = true
            self.message = data.message
        })
        eventHub.$on('close-replicate-confirm-modal', function() {
            self.modal = false
        })
    },
    methods: {
        submit: function() {
            eventHub.$emit('replicateRequest', this.replicationFlags);
        }
    }
}
</script>
