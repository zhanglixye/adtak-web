<template>
    <div class="d-inline-block">
        <v-dialog
                v-model="dialog"
                max-width="700"
                persistent
        >
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col col-11 pa-0">
                                <div style="font-size: 16px">{{title?unKnowText:unKnowText_AP}}</div>
                            </div>
                            <div class="col col-1 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pt-3 pb-0">
                    <div class="flex_column check_dia_bo pl-3 pr-3">
                        <v-checkbox v-model="unKnowCheckboxBoolean1" label="明細合計と合計金額が不一致"></v-checkbox>
                        <v-checkbox v-model="unKnowCheckboxBoolean2" class="mt-0" label="社員番号 記入なし"></v-checkbox>
                        <v-checkbox v-model="unKnowCheckboxBoolean3" class="mt-0" label="フリガナ 記入なし"></v-checkbox>
                        <v-checkbox v-model="unKnowCheckboxBoolean4" class="mt-0" label="氏名 記入なし"></v-checkbox>
                        <v-checkbox v-model="unKnowCheckboxBoolean5" class="mt-0" label="その他"></v-checkbox>
                        <v-textarea
                                clearable
                                clear-icon="cancel"
                                label="コメント"
                                v-model="unKnowTextareaValue1"
                        ></v-textarea>
                        <div class="ming">不備</div>
                    </div>
                </v-card-text>
<!--                <v-card-text class="pt-3 pb-0">-->
<!--                    <div class="flex_column check_dia_bo pl-3 pr-3">-->
<!--                        <v-checkbox v-model="unKnowCheckboxBoolean6" label="不明理由"></v-checkbox>-->
<!--                        <v-textarea-->
<!--                                clearable-->
<!--                                clear-icon="cancel"-->
<!--                                label="コメント"-->
<!--                                v-model="unKnowTextareaValue2"-->
<!--                        ></v-textarea>-->
<!--                        <div class="ming">不明</div>-->
<!--                    </div>-->
<!--                </v-card-text>-->
                <v-card-actions class="justify-center">
                    <v-btn
                            color="#949394"
                            dark
                            @click="dialog = false"
                            style="margin-left: 15px;width: 100px"
                    >
                        キャンセル
                    </v-btn>

                    <v-btn
                            color="#4db6ac"
                            dark
                            @click="loginConfirm"
                            style="width: 100px;margin-left: 16px"
                            :disabled="disabledFlag"
                    >
                        登録
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    props:{
        title:{
            type:Boolean,
            default:false,
        }
    },
    data(){
        return {
            dialog: false,
            firstName:'高田浩敏',
            unKnowText:'『★交通費(常駐)メール』シート　不備・不明理由を選択して下さい。（複数可）',
            unKnowText_AP:'『★交通費(AP)メール』シート　不備・不明理由を選択して下さい。（複数可）',
            unKnowCheckboxBoolean1:true,
            unKnowCheckboxBoolean2:false,
            unKnowCheckboxBoolean3:false,
            unKnowCheckboxBoolean4:false,
            unKnowCheckboxBoolean5:false,
            //unKnowCheckboxBoolean6:false,
            unKnowTextareaValue1:'',
            //unKnowTextareaValue2:''
            disabledFlag:false
        }
    },
    watch:{
        unKnowCheckboxBoolean1(){
            if (this.checkUnKnow()){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        },
        unKnowCheckboxBoolean2(){
            if (this.checkUnKnow()){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        },
        unKnowCheckboxBoolean3(){
            if (this.checkUnKnow()){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        },
        unKnowCheckboxBoolean4(){
            if (this.checkUnKnow()){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        },
        unKnowCheckboxBoolean5(){
            if (this.checkUnKnow()){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        },
        unKnowTextareaValue1(){
            if (this.checkUnKnow()){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        }
    },
    methods:{
        async loginConfirm(){
            this.dialog = false;
            let outLayComponent = this.$parent.$parent.$parent;
            if (await(outLayComponent.$refs.confirm.show('不明ありで処理します'))) {
                this.$emit('unkownEvent');
            } else {
                console.log('撤销');
            }
        },
        //不明 disabled判断
        checkUnKnow(){
            if (!this.unKnowCheckboxBoolean1 && !this.unKnowCheckboxBoolean2 && !this.unKnowCheckboxBoolean3 && !this.unKnowCheckboxBoolean4 && !this.unKnowCheckboxBoolean5 && (!(this.unKnowTextareaValue1 != '') || this.unKnowTextareaValue1===null)){
                return true;
            } else {
                return false;
            }
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
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    .theme--dark.v-btn.v-btn--disabled:not(.v-btn--icon):not(.v-btn--flat):not(.v-btn--outline) {
        background-color: rgba(0,0,0,.12)!important;
    }
</style>