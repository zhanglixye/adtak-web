<template>
    <div class="d-inline-block">
        <a class="mr-3" @click.stop="dialog = true">不明あり</a>
        <v-dialog
                v-model="dialog"
                max-width="600"
                persistent
        >
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col col-10 pa-0">
                                <div style="font-size: 16px">{{unKnowText}}</div>
                            </div>
                            <div class="col col-2 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pt-3 pb-0">
                    <div class="flex_column check_dia_bo pl-3 pr-3">
                        <v-textarea
                                clearable
                                clear-icon="cancel"
                                label="コメント"
                                v-model="text1"
                        ></v-textarea>
                        <div class="ming">不備な点を記載ください</div>
                    </div>
                </v-card-text>
                <v-card-text class="pt-3 pb-0">
                    <div class="flex_column check_dia_bo pl-3 pr-3">
                        <v-textarea
                                clearable
                                clear-icon="cancel"
                                label="コメント"
                                v-model="text2"
                        ></v-textarea>
                        <div class="ming">不明な点を記載ください</div>
                    </div>
                </v-card-text>
                <v-card-actions class="justify-center">
                    <v-btn
                            color="#949394"
                            dark
                            @click="clearText"
                            style="margin-left: 15px;width: 100px"
                    >
                        キャンセル
                    </v-btn>

                    <v-btn
                            color="#4db6ac"
                            dark
                            @click="loginConfirm"
                            style="width: 100px;margin-left: 16px"
                    >
                        登録
                    </v-btn>
                    <div class="ab_right_16">
                        <date-buss-t ref="dateBuss" @okHour="handerTimeH"></date-buss-t>
                    </div>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import DateBussT from '../Common/DateBussT';
export default {
    components: {DateBussT},
    data:()=>({
        dialog: false,
        firstName:'高田浩敏',
        unKnowText:'不備・不明理由を選択して下さい。（複数可）',
        text1:'',
        text2:'',
    }),
    methods:{
        async loginConfirm(){
            this.dialog = false;
            let outLayComponent = this.$parent.$parent.$parent;
            if (await(outLayComponent.$refs.confirm.show('不明ありで処理します'))) {
                this.$emit('unkownEvent');
            } else {
                this.text1 = '';
                this.text2 = '';
                this.$refs.dateBuss.$data.name = 0;
                this.$refs.dateBuss.$data.iHour = 0;
            }
        },
        //测试  监听子组件的值
        handerTimeH(msg){
            window.console.log(msg);
        },
        clearText(){
            this.dialog = false;
            this.text1='';
            this.text2='';
            this.$refs.dateBuss.$data.name = 0;
            this.$refs.dateBuss.$data.iHour = 0;
        }
    }
}
</script>

<style scoped lang="scss">
    .v-card__actions{
        padding: 18px;
    }
    .check_dia_bo{
        border: 1px solid rgba(0,0,0,0.12);
        position: relative;
        .ming{
            background-color: white;
            position: absolute;
            left: 30px;
            top: -10px;
            color: #aaaaaa;
            font-size: 18px
        }
    }
    .d-inline-block a{
        color: rgb(77, 182, 172);
        text-decoration: underline;
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
    .ab_right_16{
        position: absolute;
        right: 16px;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
</style>