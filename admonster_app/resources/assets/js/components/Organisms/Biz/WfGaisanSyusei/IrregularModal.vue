<template>
   <!-- イレギュラー処理モーダル -->
    <div id="irregular-modal-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline"></span>
                    </v-card-title>
                    <v-card-text>{{ message }}</v-card-text>
                    <v-card-text>
                        <v-text-field
                            v-model="comment"
                            v-bind:placeholder="$t('biz.wf_gaisan_syusei.common._modal.irregular.placeholder.comment')"
                          ></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="grey" dark @click="modal = false">{{ $t('common.button.cancel') }}</v-btn>
                        <v-btn color="amber darken-3" :dark="valid" :disabled="!valid" @click="submit()">{{ $t('common.button.save') }}</v-btn>
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
        valid: false,
        modal: false,
        message: '',
        comment: ''
    }),
    created: function () {
        console.log('きたよん')
        let self = this;
        eventHub.$on('open-irregular-modal', function(data) {
            self.modal = true
            self.message = data.message
            console.log(data)
        })
        eventHub.$on('close-modal', function() {
            self.modal = false
        })
    },
    methods: {
        submit: function() {
            let data = {
                comment: this.comment
            }
            eventHub.$emit('submitFromIrregularModal', data)
        }
    },
    watch: {
        comment: {
            handler (val) {
                if (val) {
                    this.valid = true
                } else {
                    this.valid = false
                }
            }
        }
    },
}
</script>
