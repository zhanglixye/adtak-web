<template>
    <v-layout row justify-center>
        <v-dialog v-model="dialog" persistent max-width="500">

            <v-card>
                <v-card-title class="headline grey lighten-2">{{ $t('list.update_password') }}</v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.name')" required v-model="updateUserData.name" disabled></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.email')" v-model="updateUserData.email" disabled></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.password')" required v-model="password" append-icon="autorenew" @click:append="createRandomPassword"></v-text-field>
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
        password: '',

    }),
    props:{
        updateUserData: { type: Object, require: true },
    },
    methods: {
        updatePassword() {
            this.$emit('update-password', this.updateUserData.id,this.password)
        },
        show() {
            this.dialog = true
        },
        close() {
            this.dialog = false
        },
        createRandomPassword(){
            var pasArr = [
                'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                '0','1','2','3','4','5','6','7','8','9',
                '_','-','$','%','&','@','+','!'
            ]
            var password = ''
            var pasArrLen = 8
            for (var i=0; i<pasArrLen; i++){
                var x = Math.floor(Math.random()*pasArrLen);
                password += pasArr[x];
            }
            this.password = password

        }
    },
    computed: {
        validateFlg () {
            if (this.password != '' ){
                return false
            } else {
                return true
            }
        }
    }
}
</script>

<style scoped>

</style>