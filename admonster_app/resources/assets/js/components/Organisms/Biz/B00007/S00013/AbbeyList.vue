<template>
    <v-card class="height_100">
        <!--项目标题输入区-->
        <div class="project-title-box pa-6">
            <h1 class="project-title">メールから以下の項目を入力してください</h1>
            <div>
                <span class="project">クライアント名：</span>
                <input class="project-input col-6" type="text" v-model="client_name" :disabled="accomplish">
            </div>
        </div>
        <!--素材内容区-->
        <abbey-list-table
            @getMsg="setMsg"
            ref="abbeyListTable"
            :initData="initData"
            :loadingDisplay="loadingDisplay"
            :projectListMore="business_flag_show"
            :formData="formData"
            :accomplish="accomplish"
            @errInformPopup="errInformPopup"
        ></abbey-list-table>
        <!--共通底部-->
        <div class="ma-0 justify-space-between pa-lr-6">
            <div v-show="!accomplish">
                <v-btn @click="disposeData" color="success" class="mr-3" style="width: 100px">処理する</v-btn>
                <v-btn @click="retainData" color="warning" class="mr-3" style="width: 100px">保留</v-btn>
                <UnKown
                    ref="unKown"
                    :unKnowShow="true"
                    :initData="initData"
                    :formData="formData"
                    :loadingDisplay="loadingDisplay"
                ></UnKown>
            </div>
            <div v-show="accomplish"></div>
            <date-buss-t v-show="!accomplish" ref="dateBuss"></date-buss-t>
            <div v-show="accomplish">
                <v-card-text style="padding: 0;">
                    <span style="line-height: 33px">
                        作業時間  <span style="text-decoration: underline;font-size: 35px;color: #333333">{{this.initData.task_result_info.work_time==null?'NaN':parseFloat(this.initData.task_result_info.work_time * 60).toFixed(0)}}</span>  M
                    </span>
                </v-card-text>
            </div>
        </div>
        <!--页面弹窗-->
        <popup-inform ref="popupInform" :message="message"></popup-inform>
        <popup-err-inform ref="popupErrInform" :message="message"></popup-err-inform>
        <confirm-dialog ref="confirmDialog"></confirm-dialog>
    </v-card>
</template>

<script>
import DateBussT from './DateBussT';
import AbbeyListTable from './AbbeyListTable';
import PopupInform from './popupInform'
import PopupErrInform from './popupErrInform'
import UnKown from './UnKown'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'
export default {
    name: 'AbbeyList',
    components:{
        PopupErrInform,
        PopupInform,
        UnKown,
        DateBussT,
        AbbeyListTable,
        ConfirmDialog
    },
    props:{
        business_flag_show:Boolean,
        initData: { type: Object, require: true },
        loadingDisplay: { type: Function, require: true },
        formData: { type: FormData, require: true },
        accomplish:Boolean
    },
    data(){
        return {
            message:'', //通知弹窗的消息
            client_name:''
        }
    },
    computed:{

    },
    created(){
        this.getWorkingHours();
    },
    mounted(){
        this.client_name = JSON.parse(this.initData.task_result_info.content).client_name;
        this.setWorkingHours();
    },
    methods:{
        //设置弹窗消息
        informPopup:function (msg) {
            this.message = msg;
            this.$refs.popupInform.dialog = true;
        },
        errInformPopup:function (msg) {
            this.message = msg;
            this.$refs.popupErrInform.dialog = true;
        },
        //向父级组件传递，子级组件传递过来的值（中转站），作用【列表模块】与【内容详细模块的切换】
        setMsg:function (data) {
            this.$emit('setInfoMsg',data);
        },
        //向子级组件传递，父级组件传递过来的值
        getMsg:function () {
            this.$refs.abbeyListTable.loadResultThumbnail()
        },
        //保留接口调用
        async retainData () {
            try {
                this.loadingDisplay(true);
                var _this = this;
                var url = '/api/biz/b00007/s00013/saveWork';
                var parameter = this.formData;
                parameter.set('task_result_content',JSON.stringify(this.$parent.getAllComponentData()));
                await axios.post(url,parameter).then((res)=>{
                    if (res.data.result == 'success'){
                        _this.informPopup('保存しました。');
                        _this.getWorkingHours();
                    } else {
                        _this.errInformPopup(res.data.err_message);
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false);
                this.dialog = false;
            }
        },
        //处理接口调用
        async disposeData () {
            try {
                //获取压缩包数组
                var zip_arr = this.$refs.abbeyListTable.$refs.popupZip.projectList;
                //声明处理函数的控制变量
                var UnZip_State;
                //循环查询压缩包数组的解压缩状态，如果有没加压的压缩包就结束循环，并给 UnZip_State 赋值 false
                var _this = this;
                var url = '/api/biz/b00007/s00013/commitWork';
                var parameter = this.formData;
                parameter.set('task_result_content',JSON.stringify(this.$parent.getAllComponentData()));
                for (var i = 0; i < zip_arr.length; i++) {
                    if (zip_arr[i].unzip_flag === false) {
                        UnZip_State = false;
                        break;
                    } else {
                        UnZip_State = true;
                    }
                }
                if (UnZip_State === false){
                    if (await (this.$refs.confirmDialog.show('未解凍ファイルはあります。続行しませんか', 'はい'))) {
                        this.loadingDisplay(true);
                        await axios.post(url,parameter).then((res)=>{
                            if (res.data.result == 'success'){
                                window.onbeforeunload = null;
                                window.location.href = '/tasks';
                            } else {
                                this.loadingDisplay(false);
                                _this.errInformPopup(res.data.err_message);
                            }
                        })
                    } else {
                        console.log('取消了处理操作');
                    }
                } else {
                    this.loadingDisplay(true);
                    await axios.post(url,parameter).then((res)=>{
                        if (res.data.result == 'success'){
                            window.onbeforeunload = null;
                            window.location.href = '/tasks';
                        } else {
                            this.loadingDisplay(false);
                            _this.errInformPopup(res.data.err_message);
                        }
                    })
                }
            } catch (err) {
                console.log(err);
            } finally {
                this.dialog = false;
            }
        },
        //设置工作时间
        setWorkingHours:function () {
            this.$refs.dateBuss.fromDate = this.initData.task_result_info.started_at==null?'':this.initData.task_result_info.started_at.replace(/-/g,'/').substring(0,this.initData.task_result_info.started_at.length-3);
            this.$refs.dateBuss.toDate = this.initData.task_result_info.finished_at==null?'':this.initData.task_result_info.finished_at.replace(/-/g,'/').substring(0,this.initData.task_result_info.finished_at.length-3);
        },
        //后台返回后 转换时区
        convertToLocalTime: function (utcDate, outPutFormat='YYYY/MM/DD HH:mm') {
            return  moment.utc(utcDate).local().format(outPutFormat)
        },
        //获取后台作业时间,重新调用work_time接口
        async getWorkingHours () {
            let url = '/api/biz/common/mail/getWorktime';
            const workTimeResultN = await axios.post(url,this.formData);
            if (workTimeResultN.data.result == 'success'){
                let time_work_new =  workTimeResultN.data.data;
                let fromDate = time_work_new.started_at==null?'':time_work_new.started_at.replace(/-/g,'/').substring(0,time_work_new.started_at.length-3);
                let toDate = time_work_new.finished_at == null?'':time_work_new.finished_at.replace(/-/g,'/').substring(0,time_work_new.finished_at.length-3);
                let hour = time_work_new.work_time == null?0:time_work_new.work_time;
                this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime = this.convertToLocalTime(fromDate);
                if (toDate == '' || toDate == null){
                    this.$refs.dateBuss.$refs.dateTimeTo.$data.textDateValue = '';
                    this.$refs.dateBuss.$refs.dateTimeTo.$data.textTimeValue = '';
                    this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime = '';
                } else {
                    this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime = this.convertToLocalTime(toDate);
                }
                this.$refs.dateBuss.$data.iHour = hour;
                this.$refs.dateBuss.$data.name = hour;
            }
        }
    }
}
</script>

<style scoped>
    .pa-6{
        padding: 24px;
    }
    .v-btn{
        margin: 0;
    }
    .col-6{
        max-width: 50%;
        min-width: 50%;
    }
    .pa-lr-6{
        padding-left:24px;
        padding-right: 24px;
    }
    .height_100{
        height: 100%;
    }
    .justify-space-between{
        display: flex;
        padding-bottom: 20px;
    }
    .project-title-box{
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    .project-title-box .project-title{
        font-size: 18px;
        font-weight: bold;
        color: #555555;
        margin-top: 0;
        margin-bottom: 20px;
    }
    .project-title-box .project{
        font-size: 14px;
        color: #555555;
        display: inline-block;
        height: 30px;
        line-height: 30px;
    }
    .project-input{
        font-size: 14px;
        outline: none;
        color: #333333;
        height: 30px;
        line-height: 30px;
        padding: 0 10px;
        margin-left: 6px;
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    button.warning {
        background-color: #fb8c00!important;
        border-color: #fb8c00!important;
    }
</style>
