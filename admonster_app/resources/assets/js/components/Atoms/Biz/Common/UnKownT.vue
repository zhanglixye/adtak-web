<template>
    <div class="d-inline-block" id="un_kown">
        <a v-show="unKnowShow" class="mr-3 text-center" @click.stop="dialog = true">不明あり</a>
        <v-dialog
                v-model="dialog"
                max-width="500"
                persistent
        >
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col col-10 pa-0">
                                <div style="font-size: 16px">「不明あり」で処理します</div>
                            </div>
                            <div class="col col-2 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pt-5 pb-0 known">
                    <v-text-field
                            v-model="firstName"
                            label="担当者"
                            disabled
                    ></v-text-field>
<!--                    <div>担当者へのコメント</div>-->
                    <v-textarea
                            v-model="lastName"
                            label="担当者へのコメント"
                    ></v-textarea>
                </v-card-text>

                <v-card-actions class="justify-center">
                    <v-btn
                            color="#949394"
                            dark
                            @click="dialog = false"
                            style="width: 100px;"
                    >
                        キャンセル
                    </v-btn>
                    <v-btn
                            color="success"
                            @click="unKnowHandle"
                            :disabled="lastNameStatus"
                            style="margin-left: 15px;width: 100px"
                    >
                        OK
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <confirm-dialog ref="confirm"/>
    </div>
</template>

<script>
import ConfirmDialog from '../../Dialogs/ConfirmDialog';
export default {
    components: {
        ConfirmDialog
    },
    props:{
        unKnowShow:Boolean,
    },
    data(){
        return {
            dialog: false,
            lastName:'',
            lastNameStatus:true
        }
    },
    computed: {
        firstName(){
            return document.getElementById('login-user-name').value
        }
    },
    watch:{
        lastName(value){
            if (value.length > 0){
                this.lastNameStatus = false;
            } else {
                this.lastNameStatus = true;
            }
        }
    },
    methods:{
        async unKnowHandle(){
            this.dialog = false;
            if (await(this.$refs.confirm.show('不明ありで処理します'))) {
                this.$emit('unKnowEvent');
            } else {
                console.log('撤销了不明处理操作');
            }
        }
    }
}
</script>

<style scoped lang="scss">
    .d-inline-block a{
        color: rgb(77, 182, 172);
        text-decoration: underline;
    }
    .v-card__actions{
        padding: 18px;
    }
    /*new add vuetify 降级*/
    hr{
        margin: 0;
    }
    /*v-row -> .row v-col -> .col */
    .row {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        -webkit-box-flex: 1;
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        margin-right: -12px;
        margin-left: -12px;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
</style>
