<template>
    <div class="row ma-0 checkboxDialog" justify="center">
        <v-dialog v-model="dialog" scrollable width="650px" persistent>
            <template slot="activator">
                <v-tooltip bottom>
                    <template slot="activator">
                        <v-btn v-if="createRowNumberNodes == 0" color="#333333" flat fab small dark class="ma-0">
                            <svg width="20px" height="20px">
                                <path fill-rule="evenodd"  fill="rgb(25, 118, 210)"
                                      d="M11.000,19.000 L11.000,17.000 L20.000,17.000 L20.000,19.000 L11.000,19.000 ZM11.000,12.000 L20.000,12.000 L20.000,14.000 L11.000,14.000 L11.000,12.000 ZM11.000,6.000 L20.000,6.000 L20.000,8.000 L11.000,8.000 L11.000,6.000 ZM11.000,1.000 L20.000,1.000 L20.000,3.000 L11.000,3.000 L11.000,1.000 ZM0.000,11.000 L9.000,11.000 L9.000,20.000 L0.000,20.000 L0.000,11.000 ZM2.000,18.000 L7.000,18.000 L7.000,13.000 L2.000,13.000 L2.000,18.000 ZM0.000,0.000 L9.000,0.000 L9.000,9.000 L0.000,9.000 L0.000,0.000 ZM2.000,7.000 L7.000,7.000 L7.000,2.000 L2.000,2.000 L2.000,7.000 Z"/>
                            </svg>
                        </v-btn>
                    </template>
                    <span>チェックリスト</span>
                </v-tooltip>
                <v-tooltip bottom>
                    <template slot="activator">
                        <v-btn v-if="createRowNumberNodes == 1" color="#4db6ac" flat fab small dark class="ma-0">
                            <svg width="20px" height="20px">
                                <path fill-rule="evenodd"  fill="rgb(77, 182, 172)"
                                      d="M11.000,19.000 L11.000,17.000 L20.000,17.000 L20.000,19.000 L11.000,19.000 ZM11.000,12.000 L20.000,12.000 L20.000,14.000 L11.000,14.000 L11.000,12.000 ZM11.000,6.000 L20.000,6.000 L20.000,8.000 L11.000,8.000 L11.000,6.000 ZM11.000,1.000 L20.000,1.000 L20.000,3.000 L11.000,3.000 L11.000,1.000 ZM0.000,11.000 L9.000,11.000 L9.000,20.000 L0.000,20.000 L0.000,11.000 ZM2.000,18.000 L7.000,18.000 L7.000,13.000 L2.000,13.000 L2.000,18.000 ZM0.000,0.000 L9.000,0.000 L9.000,9.000 L0.000,9.000 L0.000,0.000 ZM1.000,8.000 L8.000,8.000 L8.000,1.000 L1.000,1.000 L1.000,8.000 ZM2.000,2.000 L7.000,2.000 L7.000,7.000 L2.000,7.000 L2.000,2.000 Z"/>
                            </svg>
                        </v-btn>
                    </template>
                    <span>チェックリスト</span>
                </v-tooltip>
                <v-tooltip bottom>
                    <template slot="activator">
                        <v-btn v-if="createRowNumberNodes == 2" color="#1976D2" flat fab small dark class="ma-0">
                            <svg
                                    width="20px" height="20px">
                                <path fill-rule="evenodd"  fill="rgb(153, 153, 153)"
                                      d="M11.000,19.000 L11.000,17.000 L20.000,17.000 L20.000,19.000 L11.000,19.000 ZM11.000,12.000 L20.000,12.000 L20.000,14.000 L11.000,14.000 L11.000,12.000 ZM11.000,6.000 L20.000,6.000 L20.000,8.000 L11.000,8.000 L11.000,6.000 ZM11.000,1.000 L20.000,1.000 L20.000,3.000 L11.000,3.000 L11.000,1.000 ZM0.000,11.000 L9.000,11.000 L9.000,20.000 L0.000,20.000 L0.000,11.000 ZM0.000,0.000 L9.000,0.000 L9.000,9.000 L0.000,9.000 L0.000,0.000 Z"/>
                            </svg>
                        </v-btn>
                    </template>
                    <span>チェックリスト</span>
                </v-tooltip>
            </template>
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col-10 pa-0">
                                <div style="font-size: 16px">作業内容に問題が無いか確認してください</div>
                            </div>
                            <div class="col-2 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <div v-for="(item_parent,i) in checkboxData" :key="i" :class="[checkboxListMade?'check_dia_border':'check_dia_border_none',checkbox_title_border,(item_parent.pos == 1)?'ml-au30':'']">
                        <div v-show="!checkboxListMade">{{item_parent.content}}</div>
                        <v-flex :class="[!checkboxListMade?'flex-wrap':'']">
                            <component :rules="!checkboxListMade&&(item.component_type == 1)?rules:[]" v-on:change="dealTextChange" :is="diffComponentShow(item)" v-model="item.value" v-for="(item,o) in item_parent.items" :key="o" class="fw" :label="item.content" :disabled="item.disabled"></component>
                        </v-flex>
                        <div class="ming" v-show="checkboxListMade">{{item_parent.content}}</div>
                        <v-radio-group v-if="!checkboxListMade && (item_parent.group_component_type == 1)" row v-model="item_parent.value" @change="handleRadio">
                            <v-radio
                                    class="mr-10 fw"
                                    v-for="(item,o) in item_parent.items"
                                    :key="o"
                                    :label="item.content"
                                    :value="radioArray[o]"
                            >
                            </v-radio>
                        </v-radio-group>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-btn
                            color="success"
                            @click="dialog = false"
                            style="margin:0 auto;width: 100px"
                    >
                        確定
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
export default {
    props:{
        axiosFormData:{type:FormData,require:true},
        ajaxPath:String,
        checkboxListMade:{type:Boolean,require: false,default:true}
    },
    data(){
        return {
            dialog: false,
            //源数据
            checkboxData:[],
            //保存过的数据回显，重新赋值checkedArray 提交数据[1,2,3,4]
            checkboxValue:[],
            //commit array
            commitArray:[],
            //checkbox选择数量flag
            createRowNumberNodes:0,
            //new demand
            //是否开启半角验证
            match : true,
            //class name
            mt4:'mt-4',
            checkbox_title_border:'check_dia_bo',
            //完了 不要
            radioArray:[0,1],
        }
    },
    computed:{
        //半角验证
        rules(){
            const rules = [];
            if (this.match) {
                const rule =
                    // eslint-disable-next-line no-control-regex
                    v => (/^(-?\d+)(\.\d+)?$/g.test(v) || !v) || '半角文字を入力してください';
                rules.push(rule)
            }
            return rules;
        },
    },
    watch:{
        checkboxData(){
            //默认icon赋值
            this.dealRowNumberNodes();
            if (this.checkboxValue.length > 0){
                this.checkboxData = this.checkboxValue;
            }
        },
    },
    methods:{
        dealTextChange(value){
            if (typeof value == 'string') {
                eventHub.$emit('checkbox-text',{
                    'message': value
                })
            }
            this.dealRowNumberNodes();
        },
        dealRowNumberNodes(){
            //AG业务判断
            if (this.checkboxListMade){
                let arrTrue = [];
                let arrFalse = [];
                let sum = 0;
                this.checkboxData.forEach( (item) => {
                    item.items.forEach((i)=>{
                        sum ++;
                        if (typeof i.value == 'string'){
                            if (i.value != ''){
                                arrTrue.push(i.value);
                            } else {
                                arrFalse.push(i.value);
                            }
                        }
                        if (typeof i.value == 'boolean'){
                            if (i.value){
                                arrTrue.push(i.value);
                            } else {
                                arrFalse.push(i.value);
                            }
                        }
                        if (i.value == null){
                            arrFalse.push(i.value);
                        }
                    })
                })
                if (sum == arrTrue.length){
                    this.createRowNumberNodes = 2;
                } else if (sum == arrFalse.length){
                    this.createRowNumberNodes = 0;
                } else {
                    this.createRowNumberNodes = 1;
                }
            } else {
                this.iconFlagAg();
            }
        },
        diffComponentShow(item){
            if (item.component_type == 0){
                return 'v-checkbox';
            } else if (item.component_type == 1) {
                return 'v-text-field';
            }
        },
        //不要时 status  disabled
        handleRadio(value){
            if (value === 1){
                this.checkboxData.forEach((item)=>{
                    if (item.order_num === 3 || item.order_num === 4) {
                        item.items.forEach(i=>{
                            i.disabled = true;
                            i.value=false;
                        })
                    }
                })
            } else {
                this.checkboxData.forEach((item)=>{
                    if (item.order_num === 3 || item.order_num === 4) {
                        item.items.forEach(i=>{
                            i.disabled = false;
                        })
                    }
                })
            }
            this.iconFlagAg();
        },
        //AG icon status 判断
        iconFlagAg(){
            //为[]的判断
            if (this.checkboxData.length == 0){
                this.createRowNumberNodes = 0;
                return;
            }
            let sumAg = 0;
            if (this.checkboxData[0]['items'][0].value !== null && this.checkboxData[0]['items'][0].value != ''){
                sumAg ++;
            }
            if (this.checkboxData[1].value == 0){
                //完了
                try {
                    this.checkboxData[2]['items'].forEach(i =>{
                        if (i.value){
                            sumAg ++;
                            throw new Error('exit');
                        }
                    })
                } catch (e) {
                    //console.log(e)
                }
                if (this.checkboxData[3]['items'][0].value){
                    sumAg ++;
                }
            } else if (this.checkboxData[1].value == 1) {
                //不要
                sumAg +=2;
            }
            if (this.checkboxData[4]['items'][0].value){
                sumAg ++;
            }
            if (sumAg == 4) {
                this.createRowNumberNodes = 2;
            } else if (sumAg == 0){
                this.createRowNumberNodes = 0;
            } else {
                this.createRowNumberNodes = 1;
            }
        }
    }
}
</script>

<style scoped lang="scss">
    .v-card__actions{
        padding: 18px !important;
    }
    .htu{
        padding-left: 50px;
        margin-top: 5px;
    }
    .fw{
        font-weight: bold;
        padding-left: 50px;padding-right: 50px
    }
    .flex-wrap .fw{
        padding-left: 20px;
        padding-right: 20px;
        flex: unset;
    }
    .flex-wrap .fw:nth-child(2){
        //padding-left: 0;
    }
    .flex-wrap .fw:nth-child(5){
        //padding-left: 55px;
    }
    .flex-wrap .fw:nth-child(6){
        padding-left: 12px;
    }
    .check_dia_bo{
        position: relative;
        padding:20px 0;
        .ming{
            background-color: white;
            position: absolute;
            left: 30px;
            top: -15px;
            color: #aaaaaa;
            font-size: 18px
        }
    }
    .check_dia_border{
        border: 1px solid rgba(0,0,0,0.12);
        margin-top: 24px;
    }
    .check_dia_border_none{
        border: none;
        padding: 0 !important;
    }
    .ml-au30{
        margin-left: 30px !important;
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
    .checkboxDialog{
        display: flex;
        justify-content: center;
        width: 100px;
    }
    .flex-wrap{
        display: flex;
        flex-wrap: wrap;
    }
</style>