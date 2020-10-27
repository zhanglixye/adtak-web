<template>
    <v-card
        class="card_t business_t_recognize" :style="{height: height}"
    >
        <template>
            <v-form class="pa-3 ma-0" style="width: 100%">
                <v-text-field
                        v-model="mailToRead"
                        label="Mail to"
                        disabled
                ></v-text-field>
                <v-text-field
                        v-model="mailCcRead"
                        label="CC"
                        disabled
                ></v-text-field>
                <v-text-field
                        v-model="subjectRead"
                        label="Subject"
                        disabled
                ></v-text-field>
                <v-card-text class="flex-grow-1 flex_column pt-0 pb-0">
                    <div v-show="unKnowFlag" v-html="data == null?body:data.body" class="default_text text--primary flex-grow-1" style="overflow: auto">

                    </div>
                    <div v-html="unKnowText" id="un-know-text" v-show="!unKnowFlag" class="default_text text--primary flex-grow-1 un-know-text">

                    </div>
                </v-card-text>
                <v-card-title class="pa-0 mt-2">
                    <v-spacer>
                        <div class="row_overwrite no-gutters">
                            <div class="col-12 pa-0">
                                <div class="title">ファイル添付</div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-layout class="ma-2">
                    <v-flex style="display: flex">
                        <div class="pr-3" v-for="(item,i) in selectFiles" :key="i">
                              <span class="mr-3">
                                <v-icon large  style="cursor: pointer">description</v-icon>
                              </span>
<!--                            <a @click="download(item.file_seq_no,item.name)">{{item.name}}</a>-->
                                <a
                                    @click.stop.ctrl="pdfPreviewCommon(item.file_path,item.file_seq_no,item.name,'tab')"
                                    @click.stop.exact="pdfPreviewCommon(item.file_path,item.file_seq_no,item.name)"
                                    @click.stop.shift="pdfPreviewCommon(item.file_path,item.file_seq_no,item.name,'window')"
                                >{{item.name}}</a>
                        </div>
                    </v-flex>
                </v-layout>
                <v-card-title class="pa-0">
                    <v-spacer>
                        <div class="row_overwrite no-gutters">
                            <div class="col-12 pa-0">
                                <div class="title">作業内容に問題が無いか確認してください</div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <!-- <v-divider></v-divider>-->
                <v-card-text>
                    <div v-for="(item_parent,i) in checkList" :key="i" :class="[checkboxListMade?'check_dia_border':'check_dia_border_none',checkbox_title_border,(item_parent.pos == 1)?'ml-au30':'']">
                        <div v-show="!checkboxListMade">{{item_parent.content}}</div>
                        <v-flex :class="[!checkboxListMade?'flex-wrap':'']">
                            <component disabled :is="diffComponentShow(item)" v-model="item.value" v-for="(item,o) in item_parent.items" :key="o" class="fw" :label="item.content"></component>
                        </v-flex>
                        <div class="ming" v-show="checkboxListMade">{{item_parent.content}}</div>
                        <v-radio-group v-if="!checkboxListMade && (item_parent.group_component_type == 1)" row v-model="item_parent.value" disabled>
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
                <v-card-title class="pa-0">
                    <v-spacer>
                        <div class="row_overwrite no-gutters">
                            <div class="col-12 pa-0">
                                <div class="title">作業時間登録</div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <div class="row_overwrite ma-0">
                    <div class="subheader">
                        開始時間:{{data==null?startTime:data.beginDateTime}}
                    </div>
                    <div class="subheader">
                        終了時間:{{data==null?startTime:data.endTime}}
                    </div>
                </div>
                <v-card-text>
                    <span style="line-height: 33px">
                        作業時間  <span style="font-size: 12px;cursor: default;">{{data==null?parseFloat(isHour * 60).toFixed(0):parseFloat(data.iHour * 60).toFixed(0)}}</span>  M
                    </span>
                </v-card-text>
                <div v-if="!isReference" class="row_overwrite ma-0">
                    <v-btn
                            color="#949394"
                            dark
                            style="width: 100px"
                            @click="changeEmail"
                            class="mr-4"
                    >
                        戻る
                    </v-btn>
                    <v-btn
                            color="success"
                            style="width: 100px"
                            @click="saveMail"
                            v-show="typeZero"
                    >
                        送信
                    </v-btn>
                </div>
            </v-form>
        </template>
        <alert-dialog ref="alert"></alert-dialog>
        <progress-circular v-if="filePreviewLoading"></progress-circular>
        <notify-modal></notify-modal>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
    </v-card>
</template>

<script>
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
import NotifyModal from '../../../../Atoms/Dialogs/NotifyModal';
import ProgressCircular from '../../../../Atoms/Progress/ProgressCircular';
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    components: {AlertDialog,NotifyModal,ProgressCircular,FilePreviewDialog},
    props:{
        data:Object,
        height: { type: String, required: false, default: ''},// auto cssの単位付き数値 空白
        axiosFormData:{type:FormData,require:true},
        checkboxListMade:{type:Boolean,require: false,default:true}
    },
    data:()=>{
        return {
            select: [],
            selectCc: [],
            email:'',
            selectFiles:[],
            body:'',
            checkList:[],
            startTime:'',
            endTime:'',
            isHour:0,
            ajaxPath:'/api/biz/common/mail/',
            typeZero:true,
            filePreviewLoading: false,
            unKnowFlag:true,
            unKnowText:'',
            //class name
            checkbox_title_border:'check_dia_bo',
            //完了 不要
            radioArray:[0,1],
        }
    },
    computed:{
        mailToRead(){
            if (this.data==null){
                return this.select
            } else {
                return this.data.select;
            }
        },
        mailCcRead(){
            if (this.data == null){
                return this.selectCc;
            } else {
                return this.data.selectCc;
            }
        },
        subjectRead(){
            if (this.data == null){
                return this.email;
            } else {
                return this.data.subject;
            }
        },
        isReference () {
            return this.data && this.data.isReference ? this.data.isReference : false
        },
    },
    watch:{
        data(value){
            //确认页面赋值
            //默认初始化清空
            this.selectFiles=[];
            this.checkList=[];
            for (let v of value.uploadFiles) {
                this.selectFiles.push({name:v.file_name,file_seq_no:v.file_seq_no,file_path:v.file_path});
            }
            this.checkList = value.checkboxDialog;
        }
    },
    methods:{
        changeEmail:function () {
            if (!this.typeZero){
                //调转到top
                window.location.href = '/tasks';
                return;
            }
            //跳转到email页面
            this.$emit('changeEmail');
        },
        //保存数据
        saveMail(){
            this.$emit('emailSend');
        },
        //下载文件
        async download(file_seq_no,name){
            //判断是否是主动选择文件
            if (file_seq_no === undefined){
                this.data.uploadFiles.forEach((value)=>{
                    let fileName = value.file_name;
                    if (fileName == name){
                        let baseData = value.file_data;
                        this.downLoadPdf(baseData,this.splitFileName(name),this.getFileType(name));
                    }
                });
            } else {
                let baseData;
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('file_seq_no',file_seq_no);
                formData.append('task_result_content',this.axiosFormData.get('task_result_content'));
                let res = await axios.post(this.ajaxPath+'downloadFile',formData)
                if (res.data.result == 'success'){
                    baseData = res.data.data.file_data
                } else {
                    this.$refs.alert.show(res.data.err_message);
                }
                this.downLoadPdf(baseData,this.splitFileName(name),this.getFileType(name));
            }
        },
        downLoadPdf(baseData, firstFileName, lastFileName) {
            let fileName = firstFileName + '.' + lastFileName;
            let bytes = atob(baseData.substring(baseData.indexOf(',') + 1)); //去掉url的头，并转换为byte
            //处理异常,将ascii码小于0的转换为大于0
            let content = new ArrayBuffer(bytes.length);
            let ia = new Uint8Array(content);
            for (let i = 0; i < bytes.length; i++) {
                ia[i] = bytes.charCodeAt(i);
            }
            let blob;
            if (lastFileName == 'pdf') {
                blob = new Blob([content], {
                    'type': 'application/pdf'
                });
            } else if (lastFileName == 'zip') {
                blob = new Blob([content], {
                    'type': 'application/zip'
                });
            } else if (lastFileName == 'txt'){
                blob = new Blob([ia], {
                    'type': 'text/plain,charset=shift-jis'
                });
            } else if (lastFileName == 'log'){
                blob = new Blob([ia], {
                    'type': 'text/plain'
                });
            } else if (lastFileName == 'xls'){
                blob = new Blob([content], {
                    'type': 'application/vnd.ms-excel'
                });
            } else {
                blob = new Blob([content], {
                    'type': 'application/excel'
                });
            }

            if (window.navigator.msSaveBlob) {
                window.navigator.msSaveBlob(blob, fileName);
                // msSaveOrOpenBlobの場合はファイルを保存せずに開ける
                window.navigator.msSaveOrOpenBlob(blob, fileName);
            } else {
                let itemA = document.createElement('a');
                itemA.href = window.URL.createObjectURL(blob);
                itemA.download = fileName;
                itemA.click();
            }
        },
        //获取文件类型
        getFileType(filePath){
            let startIndex = filePath.lastIndexOf('.');
            if (startIndex != -1)
                return filePath.substring(startIndex+1, filePath.length).toLowerCase();
            else return '';
        },
        //获取文件名
        splitFileName(text){
            let pattern = /\.{1}[a-z]{1,}$/;
            if (pattern.exec(text) !== null) {
                return (text.slice(0, pattern.exec(text).index));
            } else {
                return text;
            }
        },
        //pdf 预览共同处理函数
        pdfPreviewCommon(file_path,file_seq_no,file_name,type){
            if (file_path == '') {
                this.download(file_seq_no,file_name)
                return
            }
            const file = {
                name: file_name,
                file_path: file_path,
            }
            this.$refs.filePreviewDialog.show([file], [], type)
        },
        diffComponentShow(item){
            if (item.component_type == 0){
                return 'v-checkbox';
            } else if (item.component_type == 1) {
                return 'v-text-field';
            }
        },
    },

}
</script>

<style scoped lang="scss">
    *:not(i){
        font-size: 12px !important;
    }
    .v-application .title{
        font-size: 14px !important;
    }
    .card_t{
        height: 100%;
        display: flex;
    }
    .htu{
        padding-left: 50px;
        margin-top: 5px;
    }
    .check_dia_bo{
        border: 1px solid rgba(0,0,0,0.12);
        position: relative;
        .ming{
            background-color: white;
            position: absolute;
            left: 30px;
            top: -12px;
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
    .subheader{
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        height: 48px;
        font-weight: 400;
        padding: 0 16px 0 16px;
    }
    hr{
        margin: 0;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    #un-know-text.un-know-text{
        height: 300px;
        overflow: auto;
        font-weight: bold !important;
        color: red !important;
    }
    .flex-wrap{
        display: flex;
        flex-wrap: wrap;
    }
    .fw{
        font-weight: bold;
        padding-left: 50px;padding-right: 80px
    }
    .ml-au30{
        margin-left: 30px !important;
    }
    .flex-wrap .fw{
        padding-left: 40px;
        padding-right: 40px;
        flex: unset;
    }
    .flex-wrap .fw:nth-child(6){
        padding-left: 30px;
    }
</style>
