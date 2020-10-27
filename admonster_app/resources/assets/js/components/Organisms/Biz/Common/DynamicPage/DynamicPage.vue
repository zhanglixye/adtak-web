<template>
    <v-card class="card_t">
        <template>
            <v-form
                class="v-form pa-3"
                v-model="valid"
                ref="form"
                style="height: 100%;"
            >
                <div v-for="(item,index) in createData" :key="index">
                    <div class="flex_space">
                        <span>{{item.label}}</span>
                        <a v-for="(items,index) in item.urls" :key="index" @click="showPdfLink(items)" v-show="item.urls !== null">{{items.label}}</a>
                    </div>
                    <tree :layout="item.layout" :status-bool="statusBool" :list="item.components" :ajaxPath="ajaxPath" :axios="axiosFormData" :loadingDisplay="loadingDisplay"></tree>
                </div>
                <div class="mt-3">
                    <div>
                        <un-kown v-if="!statusBool" class="line-height-40" @unKnowEvent="unKnowEvent" :unKnowShow="unKnowShow" ref="un_know"></un-kown>
                        <v-btn
                                color="warning"
                                class="ma-0 mr-3"
                                style="width: 100px"
                                @click="save(true)"
                                v-show="!statusBool"
                        >
                            保留
                        </v-btn>
                        <v-btn
                                color="#949394"
                                dark
                                style="width: 100px"
                                @click="back()"
                                class="mr-3"
                                v-show="statusBool"
                        >
                            戻る
                        </v-btn>
                        <v-btn
                                color="warning"
                                class="ma-0 mr-3"
                                style="width: 100px"
                                @click="deal()"
                                v-show="approvalStatus"
                        >
                            {{!statusBool?dealText:dealFinishText}}
                        </v-btn>
                        <date-buss-t :status-bool="statusBool" ref="dateBuss" style="display: inline-block"></date-buss-t>
                    </div>
                </div>
            </v-form>
        </template>
        <div class="unKnowDeal" v-show="unKnowStatus">{{unKnowData}}</div>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
    </v-card>
</template>

<script>
import Tree from './Tree';
import UnKown from '../../../../Atoms/Biz/Common/Mails/UnKown';
import DateBussT from '../../../../Atoms/Biz/Common/DateBussT';
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog';
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    name: 'CommonPage',
    components: {FilePreviewDialog, ConfirmDialog, AlertDialog, DateBussT, UnKown, Tree},
    props:{
        edit: { type: Boolean, require: false, default: false },
        axiosFormData:{type:FormData,require:true},
        loadingDisplay: { type: Function, require: true },
    },
    computed:{
        createData(){
            return JSON.parse(this.axiosFormData.get('task_result_content'));
        }
    },
    created(){
        this.defaultGetWorkTime();
    },
    mounted(){

    },
    data:()=>{
        return {
            valid:false,
            unKnowShow:true,
            //ajaxPath
            ajaxPath:'/api/biz/b00018/s00024/',
            //是否有review判断
            statusBool:false,
            dealText:'处理する',
            dealFinishText:'送信',
            //ajaxCommonPath
            ajaxCommonPath:'/api/biz/common/mail/',
            //判断是否是不明状态
            unKnowStatus:false,
            //判断是承认状态
            approvalStatus:true,
            unKnowData:'',
        }
    },
    methods:{
        //不明处理
        unKnowEvent(){
            this.save('unknow');
        },
        showPdfLink(pathObject){
            console.log(pathObject);
            const file = {
                name: pathObject.label,
                file_path: pathObject.url
            }
            let type = '';
            this.$refs.filePreviewDialog.show([file], [], type);
        },
        //保存处理
        async save(flag){
            try {
                this.loadingDisplay(true);
                let outInputData = {
                    components:this.createData,
                    unknown_comment:this.$refs.un_know.$data.lastName,
                    work_time:{
                        started_at:this.$refs.dateBuss.$data.saveFromData===''?null:this.convertToTime(this.$refs.dateBuss.$data.saveFromData),
                        finished_at:this.$refs.dateBuss.$data.saveToData===''?null:this.convertToTime(this.$refs.dateBuss.$data.saveToData),
                        total:this.$refs.dateBuss.$data.iHourValue,
                    }
                };
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('task_result_content',JSON.stringify(outInputData));
                let sendString = 'tmpSave';
                if (flag != true){
                    sendString = 'process';
                }
                if (flag == 'unknow'){
                    sendString = 'unknown';
                }
                //发送请求
                const res = await axios.post('/api/biz/common/dynamic_page/'+ sendString,formData);
                if (res.data.result == 'success'){
                    //重新调用work_time接口
                    const workTimeResultN = await axios.post(this.ajaxCommonPath+'getWorktime',this.axiosFormData)
                    if (workTimeResultN.data.result == 'success'){
                        let time_work_new =  workTimeResultN.data.data;
                        let fromDate = time_work_new.started_at==null?'':time_work_new.started_at.replace(/-/g,'/').substring(0,time_work_new.started_at.length-3);
                        let toDate = time_work_new.finished_at == null?'':time_work_new.finished_at.replace(/-/g,'/').substring(0,time_work_new.finished_at.length-3);
                        let hour = time_work_new.work_time == null?0:time_work_new.work_time;
                        this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime = this.convertToLocalTime(fromDate);
                        this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime = this.convertToLocalTime(toDate);
                        this.$refs.dateBuss.$data.mailHourFlag = true;
                        this.$refs.dateBuss.$data.iHour = hour;
                        this.$refs.dateBuss.$data.name = hour;
                        this.$refs.dateBuss.$data.saveFromData = this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime;
                        this.$refs.dateBuss.$data.saveToData = this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime;
                    }
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'));
                    if (sendString == 'process' || flag == 'unknow') {
                        window.location.href = '/tasks';
                    }
                } else {
                    this.$refs.alert.show(res.data.err_message);
                }
            } catch (e) {
                this.loadingDisplay(false);
                console.log(e);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false)
            }
        },
        //处理する
        deal(){
            try {
                //if (!this.statusBool){
                this.save();
                // } else {
                //     this.statusBool = true;
                // }
            } catch (e) {
                console.log(e);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            }
        },
        //back task一览
        back(){
            //this.statusBool = false;
            window.location.href = '/tasks';
        },
        //设置保存默认值数据回显
        saveInitValue(content){
            //不明
            if (content.unknown_comment != undefined){
                this.$refs.un_know.$data.lastName = content.unknown_comment;
            }
        },
        convertToLocalTime: function (utcDate, outPutFormat='YYYY/MM/DD HH:mm') {
            if (utcDate == ''){
                return '';
            }
            return  moment.utc(utcDate).local().format(outPutFormat)
        },
        convertToTime: function(date){
            if (date == ''){
                return null;
            }
            return moment(date).utc().format('YYYY/MM/DD HH:mm:ss');
        },
        //默认调用
        async defaultGetWorkTime(){
            //默认work_time赋值
            const workTimeResult = await axios.post(this.ajaxCommonPath+'getWorktime',this.axiosFormData)
            if (workTimeResult.data.result == 'success'){
                let time_work_new =  workTimeResult.data.data;
                let fromDate = time_work_new.started_at==null?'':time_work_new.started_at.replace(/-/g,'/').substring(0,time_work_new.started_at.length-3);
                let toDate = time_work_new.finished_at == null?'':time_work_new.finished_at.replace(/-/g,'/').substring(0,time_work_new.finished_at.length-3);
                let hour = time_work_new.work_time == null?0:time_work_new.work_time;
                this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime = this.convertToLocalTime(fromDate);
                this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime = this.convertToLocalTime(toDate);
                this.$refs.dateBuss.$data.mailHourFlag = true;
                this.$refs.dateBuss.$data.iHour = hour;
                this.$refs.dateBuss.$data.name = hour;
                this.$refs.dateBuss.$data.saveFromData = this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime;
                this.$refs.dateBuss.$data.saveToData = this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime;
            }
        }
    }
}
</script>

<style scoped lang="scss">
    .card_t {
        height: 100%;
        display: flex;
        .v-form {
            flex-grow: 1;
        }
    }
    a{
        font-size: 12px;
        color: #1976d2;
        text-decoration: underline;
    }
    .flex_space{
        display: flex;
        justify-content: space-between;
    }
    .unKnowDeal{
        color: red;
        position: absolute;
        width: 500px;
        height: 200px;
        top: 10%;
        left: 10%;
        cursor: pointer;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>