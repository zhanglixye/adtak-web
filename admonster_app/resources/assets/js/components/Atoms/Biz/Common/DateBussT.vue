<template>
    <div>
        <v-card-text class="pa-0">
            <span style="line-height: 33px">
                作業時間  <span v-show="statusBool">{{minuteOutValue}}</span><a v-show="!statusBool" style="text-decoration: underline;font-size: 35px" @click.stop="dialogOpen">{{minuteOutValue}}</a> M
            </span>
        </v-card-text>
        <v-dialog
                v-model="dialog"
                max-width="600"
                persistent
        >
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col-6 pa-0">
                                <div style="font-size: 16px">作業時間登録</div>
                            </div>
                            <div class="col-6 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <div style="padding: 24px 8px 0">
                    <div class="row ma-0">
                        <v-subheader class="hei">
                            <date-time-picker
                                    :value="fromDate"
                                    :dateTypeLabel="'開始時間:' + $t('list.search_condition.from')"
                                    :from =true ref="dateTimeFrom"
                                    @dateTime ="dateTimeFrom"
                            ></date-time-picker>
                        </v-subheader>
                        <v-subheader class="hei">
                            <date-time-picker
                                    :value="toDate"
                                    :dateTypeLabel="'終了時間:' + $t('list.search_condition.to')"
                                    :from =true ref="dateTimeTo"
                                    @dateTime ="dateTimeTo"
                            ></date-time-picker>
                        </v-subheader>
                    </div>
                    <v-card-actions>
                        <v-flex md3 class="ml-2" style="display: flex">
                            <v-text-field
                                    v-model="minute"
                                    style="font-size: 35px;"
                                    class="text-center-input"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md6>
                            <span style="font-size: 16px;line-height: 70px">M ({{name}} H)</span>
                        </v-flex>
                    </v-card-actions>
                </div>
                <v-card-actions style="display: flex;justify-content: center">
                    <v-btn
                            color="warning"
                            class="ma-0 mr-3"
                            dark
                            style="width: 100px"
                            @click="clearTime"
                    >
                        クリア
                    </v-btn>
                    <v-btn
                            color="success"
                            @click="getDateValue"
                            style="width: 100px"
                            class="mr-3"
                    >
                        保存
                    </v-btn>
                    <v-btn
                            color="#949394"
                            dark
                            style="width: 100px"
                            @click="dialog = false"
                    >
                        戻る
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import DateTimePicker from './DateTimePicker';
export default {
    components:{
        DateTimePicker
    },
    props:{
        statusBool:{type:Boolean,require:false,default:false}
    },
    data(){
        return {
            dialog: false,
            iHour:'0',
            iHourValue:'0',
            name:'0',
            fromDate:'',
            toDate:'',
            minute:'0',
            minuteOut:'0',
            minuteOutValue:'0',
            //判断是否是mail作成页面赋值
            mailHourFlag:false,
            saveFromData:'',
            saveToData:'',
        }
    },
    watch:{
        name(value){
            if (isNaN(value)){
                this.name = '0';
            }
            this.minute = parseFloat(value * 60).toFixed(0);
        },
        iHour(value){
            this.minuteOut = parseFloat(value * 60).toFixed(0);
            if (this.mailHourFlag){
                this.minuteOutValue = this.minuteOut;
                this.iHourValue = this.iHour;
            }
        },
        minute(value){
            this.name = parseFloat(value/60).toFixed(2);
            this.iHour = parseFloat(value/60).toFixed(2);
        }
    },
    created(){
        // 重写方法，自定义格式化日期
        Date.prototype.toLocaleString = function() {
            // 补0   例如 2018/7/10 14:7:2  补完后为 2018/07/10 14:07:02
            function addZero(num) {
                if (num < 10)
                    return '0' + num;
                return num;
            }
            // 按自定义拼接格式返回
            return this.getFullYear() + '-' + addZero(this.getMonth() + 1) + '-' + addZero(this.getDate()) + ' ' +
                    addZero(this.getHours()) + ':' + addZero(this.getMinutes());
        }
    },
    methods:{
        getDateValue(){
            this.dialog = false;
            this.iHour = this.name == ''?'0':this.name;
            this.minuteOut = this.minute;
            this.minuteOutValue = this.minuteOut;
            this.iHourValue = this.iHour;
            this.$emit('okHour',this.iHour);
            this.saveFromData = this.convertToLocalTime(this.fromDate._d);
            this.saveToData = this.convertToLocalTime(this.toDate._d);
        },
        dateTimeFrom: function (msg) {
            this.mailHourFlag = false;
            this.fromDate = msg;
            let startTime_inner = this.fromDate._d
            let endTime_inner = this.toDate._d
            if (startTime_inner != 'Invalid Date' && endTime_inner != 'Invalid Date'){
                this.name = parseFloat(String((endTime_inner - startTime_inner) / 1000 / 60 / 60)).toFixed(2);
            }
        },
        dateTimeTo: function (msg) {
            this.mailHourFlag = false;
            this.toDate = msg;
            let startTime_inner = this.fromDate._d
            let endTime_inner = this.toDate._d
            if (startTime_inner != 'Invalid Date' && endTime_inner != 'Invalid Date'){
                this.name = parseFloat(String((endTime_inner - startTime_inner) / 1000 / 60 / 60)).toFixed(2);
            }
        },
        clearTime(){
            this.$refs.dateTimeFrom.$data.textDateValue = '';
            this.$refs.dateTimeFrom.$data.textTimeValue = '';
            this.$refs.dateTimeFrom.$data.dateTime = '';
            this.$refs.dateTimeTo.$data.textDateValue = '';
            this.$refs.dateTimeTo.$data.textTimeValue = '';
            this.$refs.dateTimeTo.$data.dateTime = '';
        },
        showNowDate(string){
            try {
                let rightNow = new Date();
                let date = rightNow.toISOString().slice(0,10).replace(/-/g, '/');
                let getHour = rightNow.getHours();
                let getMin = rightNow.getMinutes();
                let res = date + ' ' + getHour + ':' + getMin;
                if (string == 'from'){
                    this.$refs.dateTimeFrom.$data.dateTime = res;
                } else if (string == 'to') {
                    this.$refs.dateTimeTo.$data.dateTime = res;
                } else {
                    throw 'error date'
                }
            } catch (e) {
                console.log(e);
            }
        },
        convertToLocalTime: function (utcDate, outPutFormat='YYYY/MM/DD HH:mm') {
            if (utcDate == '' || utcDate == undefined){
                return '';
            }
            return  moment.utc(utcDate).local().format(outPutFormat)
        },
        dialogOpen(){
            if (this.saveFromData == ''){
                this.$refs.dateTimeFrom.$data.textDateValue = '';
                this.$refs.dateTimeFrom.$data.textTimeValue = '';
                this.$refs.dateTimeFrom.$data.dateTime = '';
            } else {
                this.$refs.dateTimeFrom.$data.dateTime = this.saveFromData;
            }
            if (this.saveToData == ''){
                this.$refs.dateTimeTo.$data.textDateValue = '';
                this.$refs.dateTimeTo.$data.textTimeValue = '';
                this.$refs.dateTimeTo.$data.dateTime = '';
            } else {
                this.$refs.dateTimeTo.$data.dateTime = this.saveToData;
            }
            this.dialog = true;
        }
    }
}
</script>

<style scoped lang="scss">
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
    a{
        color:#1976d2;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
</style>
