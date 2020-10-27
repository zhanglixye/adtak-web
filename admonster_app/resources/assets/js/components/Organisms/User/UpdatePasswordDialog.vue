<template>
    <v-layout row justify-center>
        <v-dialog v-model="dialog" persistent max-width="500">

            <v-card>
                <v-card-title class="headline grey lighten-2">{{ $t('list.update_password') }}</v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.full_name')" required v-model="user.name" disabled></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field
                                        :label="$t('user.dialog.old_password')"
                                        v-model="oldPassword"
                                        :append-icon="oldPasswordShowFlg ? 'visibility' : 'visibility_off'"
                                        :type="oldPasswordShowFlg ? 'text' : 'password'"
                                        @click:append="oldPasswordShowFlg = !oldPasswordShowFlg"
                                        :rules="[rules.required, rules.min]"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field
                                        :label="$t('user.dialog.new_password')"
                                        required
                                        v-model="newPassword"
                                        :append-icon="newPasswordShowFlg ? 'visibility' : 'visibility_off'"
                                        :type="newPasswordShowFlg ? 'text' : 'password'"
                                        @click:append="newPasswordShowFlg = !newPasswordShowFlg"
                                        :rules="[rules.required, rules.min]"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field
                                    :label="$t('user.dialog.check_new_password')"
                                    required
                                    v-model="checkNewPassword"
                                    :append-icon="checkPasswordShowFlg ? 'visibility' : 'visibility_off'"
                                    :type="checkPasswordShowFlg ? 'text' : 'password'"
                                    @click:append="checkPasswordShowFlg = !checkPasswordShowFlg"
                                    :rules="[rules.required, rules.min, passwordConfirmationRule]"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" text @click.native="dialog = false">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn color="primary" text @click.native="updatePassword" :disabled="validateFlg">{{ $t('common.button.update') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>

<script>
export default {
    name: 'UpdatePasswordDialog',
    data: () => ({
        dialog: false,
        oldPassword: '',
        newPassword: '',
        checkNewPassword: '',
        oldPasswordShowFlg:false,
        newPasswordShowFlg:false,
        checkPasswordShowFlg:false,
        rules: {
            required: value => !!value || Vue.i18n.translate('user.dialog.error_message.input_required'),
            min: v => v.length >= 8 || Vue.i18n.translate('user.dialog.error_message.digit_Limits')
        }

    }),
    props:{
        user: { type: Object, required: true },
    },
    methods: {
        updatePassword() {
            this.$emit('update-password', this.user.id,this.oldPassword, this.newPassword)
        },
        show() {
            this.dialog = true
            this.oldPassword = ''
            this.newPassword=''
            this.checkNewPassword=''
        },
        close() {
            this.dialog = false
            this.oldPassword = ''
            this.newPassword=''
            this.checkNewPassword=''
        }
    },
    computed: {
        validateFlg () {
            if (this.oldPassword !== '' && this.newPassword.length >= 8 && (this.checkNewPassword === this.newPassword)){
                return false
            } else {
                return true
            }
        },
        passwordConfirmationRule() {
            return () => (this.newPassword === this.checkNewPassword) || this.$t('user.dialog.error_message.compare_error')
        }
    }
}
</script>

<style scoped>

</style>
