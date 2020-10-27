<template>
    <v-card
        class="card_t"
        id="create"
>
    <template>
        <v-form class="pa-3 v-form"
            v-model="valid"
            lazy-validation
        >
            <a
                v-show="!title_email_flag"
                @click="btnClick"
                style="position: absolute;top: -25px;left: 0;text-decoration: underline;font-size: 12px;margin-left: 25px;"
            >
                {{emailShow ? 'クリックして宛先を非表示':'クリックして宛先を再表示'}}
            </a>
            <a
                    v-show="madeAG"
                    style="position: absolute;top: -25px;right: 0;text-decoration: underline;font-size: 12px;margin-right: 25px;"
            >
                マニュアルを参照する
            </a>
            <div v-show="emailShow || title_email_computed_flag">
                <v-combobox
                        v-model="select"
                        :items="items"
                        label="Mail to"
                        multiple
                        small-chips
                        ref="combo"
                        @focus="addText"
                        deletable-chips
                        :disabled="mailToDisabled"
                        v-show="mailToDisplay"
                ></v-combobox>
                <v-combobox
                        v-model="select_cc"
                        :items="items_cc"
                        label="CC"
                        multiple
                        small-chips
                        ref="combo_t"
                        @focus="addText_t"
                        class="mt-0 pt-0"
                        deletable-chips
                        :disabled="mailCcDisabled"
                        v-show="mailCcDisplay"
                ></v-combobox>
            </div>
            <v-text-field
                    v-model="email"
                    label="Subject"
                    required
            ></v-text-field>
            <div>
                <editor-quil :axiosFormData="axiosFormData" class="mb-3" :heightChange="hei" ref="editor" :flag_editor="flag_bus_other"></editor-quil>
                <file-upload :disabledStatus="disabledStatus" @clearFile="clearFile" @clickDownload="clickDownload" ref="upload" :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types"></file-upload>
            </div>
            <div class="row_new ma-0 mt-3 bottom12_button">
                <div class="btn-icon">
                    <un-know class="line-height-40" @unKnowEvent="unKnowEvent" :unKnowShow="unKnowShow" ref="un_know"></un-know>
                    <checkbox-dialog :checkboxListMade="!madeAG" v-show="checkboxShow" class="checkbox-style" :ajaxPath="ajaxPath" :axiosFormData="axiosFormData" ref="checkboxDialog"></checkbox-dialog>
                    <date-buss-t ref="dateBuss" @okHour="handerTimeH" style="display: inline-block"></date-buss-t>
                </div>
            </div>
            <div class="mt-3">
                <div>
                    <v-btn
                            color="success"
                            class="ma-0 mr-3"
                            style="width: 100px"
                            @click="changeRec"
                            :disabled="mailMustFlag"
                    >
                        確認
                    </v-btn>
                    <v-btn
                            color="#949394"
                            class="ma-0 mr-3"
                            dark
                            style="width: 100px"
                            @click="backCpdf"
                    >
                        戻る
                    </v-btn>
                    <v-btn
                            color="warning"
                            class="ma-0 mr-3"
                            style="width: 100px"
                            v-show="saveShow"
                            :disabled="disabledFlag"
                            @click="save(true)"
                    >
                        一時保存
                    </v-btn>
                </div>
            </div>
        </v-form>
    </template>
    <alert-dialog ref="alert"></alert-dialog>
    <confirm-dialog ref="confirm"></confirm-dialog>
    <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
</v-card>
</template>

<script>
import EditorQuil from '../../../Molecules/Biz/Common/Mails/EditorQuil';
import UnKnow from '../../../Atoms/Biz/Common/Mails/UnKown';
import CheckboxDialog from '../../../Atoms/Biz/Common/Mails/CheckboxDialog'
import $ from 'jquery';
import FileUpload from '../../../Atoms/Biz/Common/FileUpload';
import DateBussT from '../../../Atoms/Biz/Common/DateBussT';
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog';
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog';
import FilePreviewDialog from '../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    components:{
        ConfirmDialog,
        AlertDialog,
        DateBussT,
        FileUpload,
        EditorQuil,
        UnKnow,
        CheckboxDialog,
        FilePreviewDialog
    },
    props: {
        flag_business: Boolean,
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
        axiosFormData:{type:FormData,require:true},
        defaultEmailStatus:{type:Boolean,default: false},
        title_email_flag:{type:Boolean,require:false,default:false},
        //AG业务标识
        madeAG:{type:Boolean,require:false,default:false},
    },
    data:()=>{
        return {
            valid : true,
            name: 'kaiwait-dev002@adpro-inc.co.jp',
            email:'',
            select: [],
            select_cc:[],
            items: [],
            items_cc: [],
            rateArr:[],
            rateArrCc:[],
            emailShow:false,
            hei:124,
            flag_bus_other:false,
            //Customer supply component
            // ファイルアップロード用
            file_upload_emit_message: 'email-sale-file',
            allow_file_types: [],
            //文件集合
            uploadFiles:[],
            //不明
            fileImportDialog:false,
            //ajaxPath
            ajaxPath:'/api/biz/common/mail/',
            saveShow:true,
            disabledFlag:false,
            signTemplate:false,
            mailTemplate:false,
            //判断文件是否改动
            fileChangeFlag:false,
            //判断是够保证存过数据
            saveStateType:false,
            reviewDisplay:true,
            unKnowShow:true,
            mailMustFlag:false,
            disabledStatus:false,
            mailToDisabled:false,
            mailCcDisabled:false,
            mailToDisplay:true,
            mailCcDisplay:true,
            checkboxShow:true,
            //file 是否必填 6
            numBinaryFileAttachment_must:false,
            //file 是否必填 5
            numBinaryFileAttachment_not_must:false,
            //判断是否是完了状态
            finishAg:false
        }
    },
    computed:{
        title_email_computed_flag:function () {
            return this.title_email_flag==true?this.btnClick():false;
        }
    },
    created(){
        const self = this;
        // ファイルアップロード用
        eventHub.$on(this.file_upload_emit_message, function(data){
            //初始化设置uploadFiles为空数组
            self.uploadFiles = [];
            // check_filesに追加
            for (const file of data.file_list){
                const result = {
                    err_description: '',
                    file_name: file.name,
                    file_size: file.size,
                    file_data: file.data,
                    file_path: '',
                    type: file.type,
                };
                self.uploadFiles.push(result);
                self.fileChangeFlag = true;
            }
            // ローカルに一時保存
            self.moveToTemporary(self.uploadFiles)
        })
        //点击文件如果有stp_no 请求获取base64 否则本地获取base64
        //默认调用mail_template 与 sign_template
        self.mailSignTemplate();
    },
    watch:{
        defaultEmailStatus(bool){
            if (bool){
                //需要判断是够是回显状态
                this.$nextTick(()=>{
                    const _self = this;
                    _self.setInitValue();
                });
            }
        },
    },
    mounted(){
        this.flag_bus_other = !this.flag_business;

    },
    methods:{
        //不明处理
        unKnowEvent(){
            this.save('unknow');
        },
        //增加百分比格外功能 -- 暂未实现
        addText(){
            setTimeout( ()=>{
                let oRef = this.$refs.combo.$refs.menu.$refs.content.querySelectorAll('a');
                $(oRef).each((index,event)=>{
                    if ($(event).find('.addRate').length > 0){
                        return;
                    }
                    $(event).append('<div class=\'addRate\' style=\'font-weight: bold;font-size: small\'>'+this.rateArr[index]+'%</div>');
                })
            },800)
        },
        //增加百分比格外功能 -- 暂未实现
        addText_t(){
            setTimeout( ()=>{
                let oRef = this.$refs.combo_t.$refs.menu.$refs.content.querySelectorAll('a');
                $(oRef).each((index,event)=>{
                    if ($(event).find('.addRate').length > 0){
                        return;
                    }
                    $(event).append('<div class=\'addRate\' style=\'font-weight: bold;font-size: small\'>'+this.rateArrCc[index]+'%</div>');
                })
            },800)
        },
        //测试  监听子组件的值
        handerTimeH(){
            //window.console.log(msg);
        },
        //点击クリックして宛先を非表示切换效果
        btnClick(){
            this.emailShow = !this.emailShow;
            if (!this.emailShow){
                this.hei = 124;
            } else {
                this.hei = 0;
            }
            return true;
        },
        //跳转到承认页面
        changeRec(checkboxDataReview){
            //确认判断
            let boolSave = this.saveClickFlag();
            boolSave.then((result) => {
                //判断是否完了状态
                if (result !== undefined){
                    if (!result){
                        return;
                    }
                }
                //reviewDisplay true 承认 false 直接提交
                if (this.reviewDisplay){
                    //获取模块数据
                    this.$nextTick(()=>{
                        this.getEmail(checkboxDataReview);
                    })
                    this.$emit('changeRec');
                } else {
                    this.save();
                }
            }).catch(e =>{
                console.log(e);
            })
        },
        // Customer provided
        moveToTemporary: async function (uploadFile) {
            // 画像データをblobURL -> base64
            // const file = this.uploadFile
            const files = uploadFile;
            //暂时看不懂用处
            const convert = this.convertToBase64;
            //await convert(files);
            //支持多文件上传
            await [].forEach.call(files, convert);
            //delete this.uploadFile.type;
        },
        //To base64
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {
                // base64データが入っている場合は処理しない
                //added kaiwait 暂时三个文件会有错误
                //for (const file of files){
                if ('data' == file.file_data.substring(0, 4)) resolve(file)
                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob';
                xhr.onload = () => {
                    var reader = new window.FileReader()
                    reader.readAsDataURL(xhr.response)
                    reader.onloadend = () => {
                        // メモリから削除
                        URL.revokeObjectURL(file.file_data)
                        file.file_data = reader.result
                        resolve(file)
                    }
                    reader.onerror = (e) => reject(e)
                }
                xhr.onerror = (e) => reject(e)
                xhr.open('GET', file.file_data)
                xhr.send()
                // }
            })
        },
        //settingDefault 请求 回显数据不用调用
        async setInitValue(){
            try {
                this.loadingDisplay(true);
                const res = await axios.post(this.ajaxPath+'getCommonMailSettingByTaskId?task_id='+this.axiosFormData.get('task_id')+'&request_work_id='+this.axiosFormData.get('request_work_id'),this.axiosFormData)
                if (res.data.result == 'success') {
                    // 完了アラート
                    // this.$refs.alert.show(
                    //     Vue.i18n.translate('approvals.alert.order_work.text')
                    // )
                    //setting默认值
                    let resData = res.data.data;
                    let objInitSetting = [
                        {'mailTo':Number(resData.mail_to).toString(2)},
                        {'cc':Number(resData.cc).toString(2)},
                        {'subject':Number(resData.subject).toString(2)},
                        {'body':Number(resData.body).toString(2)},
                        {'mail_template':Number(resData.mail_template).toString(2)},
                        {'sign_template':Number(resData.sign_template).toString(2)},
                        {'file_attachment':Number(resData.file_attachment).toString(2)},
                        {'check_list_button':Number(resData.check_list_button).toString(2)},
                        {'review':Number(resData.review).toString(2)},
                        {'use_time':Number(resData.use_time).toString(2)},
                        {'unknown':Number(resData.unknown).toString(2)},
                        {'save_button':Number(resData.save_button).toString(2)}
                    ];
                    this.setInitMailToValue(objInitSetting);
                    //重新调用work_time接口
                    const workTimeResultN = await axios.post(this.ajaxPath+'getWorktime',this.axiosFormData)
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
                        this.$refs.dateBuss.$data.mailHourFlag = true;
                        this.$refs.dateBuss.$data.iHour = hour;
                        this.$refs.dateBuss.$data.name = hour;
                        this.$refs.dateBuss.$data.saveFromData = this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime;
                        this.$refs.dateBuss.$data.saveToData = this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime;
                    }
                } else {
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('common.message.internal_error')
                    )

                }
            } catch (error) {
                console.error(error);
            } finally {
                this.loadingDisplay(false);
            }
        },
        //默认setting设置值 没有回显数据调用
        async setInitMailToValue(objInitSetting){
            this.mailMustFlag = true;
            //popup
            let numBinaryPopup = objInitSetting[0]['mailTo'].charAt(objInitSetting[0]['mailTo'].length - 5);
            if (numBinaryPopup == ''){
                this.items = [];
            }
            //判断第三位的值 default
            //mailTo
            let numBinary = objInitSetting[0]['mailTo'].charAt(objInitSetting[0]['mailTo'].length - 3);
            if (numBinary == '1'){
                const mailToResult = await axios.post(this.ajaxPath+'getDefaultMailTo',this.axiosFormData)
                if (mailToResult.data.result == 'success') {
                    if (mailToResult.data.data.mailTo instanceof Array){
                        this.select = mailToResult.data.data.mailTo;
                    } else {
                        let arr = [];
                        //console.log(mailToResult.data.data.mailTo);
                        if (mailToResult.data.data.mailTo == null){
                            this.select = [];
                        } else {
                            arr.push(mailToResult.data.data.mailTo);
                            this.select = arr;
                        }
                    }
                } else {
                    return false;
                }
            }
            //disabled
            let numBinaryDisabled = objInitSetting[0]['mailTo'].charAt(objInitSetting[0]['mailTo'].length - 2);
            if (numBinaryDisabled == '0') {
                this.mailToDisabled = true;
            } else {
                this.mailToDisabled = false;
            }
            //display
            let numBinaryDisplay = objInitSetting[0]['mailTo'].charAt(objInitSetting[0]['mailTo'].length - 1);
            if (numBinaryDisplay == '0') {
                this.mailToDisplay = false;
            } else {
                this.mailToDisplay = true;
            }
            let numBinaryCcPopup = objInitSetting[1]['cc'].charAt(objInitSetting[1]['cc'].length - 5);
            if (numBinaryCcPopup == ''){
                this.items_cc = [];
            }
            //cc
            let numBinaryCc = objInitSetting[1]['cc'].charAt(objInitSetting[1]['cc'].length - 3);
            if (numBinaryCc == '1'){
                const mailCcResult = await axios.post(this.ajaxPath+'getDefaultMailCc',this.axiosFormData)
                if (mailCcResult.data.result == 'success') {
                    if (mailCcResult.data.data.mailCc instanceof Array){
                        this.select_cc = mailCcResult.data.data.mailCc;
                    } else {
                        let arrCc = [];
                        if (mailCcResult.data.data.mailCc == null){
                            this.select_cc = [];
                        } else {
                            arrCc.push(mailCcResult.data.data.mailCc);
                            this.select_cc = arrCc;
                        }
                    }
                } else {
                    return false;
                }
            }
            //disabled
            let numBinaryCcDisabled = objInitSetting[1]['cc'].charAt(objInitSetting[1]['cc'].length - 2);
            if (numBinaryCcDisabled == '0'){
                this.mailCcDisabled = true;
            } else {
                this.mailCcDisabled = false;
            }
            //display
            let numBinaryCcDisplay = objInitSetting[1]['cc'].charAt(objInitSetting[1]['cc'].length - 1);
            if (numBinaryCcDisplay == '0') {
                this.mailCcDisplay = false;
            } else {
                this.mailCcDisplay = true;
            }
            //subject
            let numBinarySubject = objInitSetting[2]['subject'].charAt(objInitSetting[2]['subject'].length - 3);
            if (numBinarySubject == '1'){
                //新加参数时区
                let axiosFormDataTime = new FormData();
                axiosFormDataTime.append('step_id', this.axiosFormData.get('step_id'));
                axiosFormDataTime.append('request_id', this.axiosFormData.get('request_id'));
                axiosFormDataTime.append('request_work_id', this.axiosFormData.get('request_work_id'));
                axiosFormDataTime.append('task_id', this.axiosFormData.get('task_id'));
                axiosFormDataTime.append('task_started_at', this.axiosFormData.get('task_started_at'));
                axiosFormDataTime.append('task_result_content', this.axiosFormData.get('task_result_content'));
                let time_zone = 0 - new Date().getTimezoneOffset() / 60;
                axiosFormDataTime.append('time_zone', time_zone);
                const subjectResult = await axios.post(this.ajaxPath+'getDefaultSubject',axiosFormDataTime);
                if (subjectResult.data.result == 'success') {
                    this.email = subjectResult.data.data.mailSubject;
                } else {
                    return false;
                }
            }
            if (!this.mailTemplate){
                //mail_template
                let numBinaryMailTemplate = objInitSetting[4]['mail_template'].charAt(objInitSetting[4]['mail_template'].length - 3);
                if (numBinaryMailTemplate == '1'){
                    const mailTemplateResult = await axios.post(this.ajaxPath+'getDefaultBodyTemplates',this.axiosFormData)
                    if (mailTemplateResult.data.result == 'success') {
                        this.$refs.editor.$refs.dialog_tem.$data.defaultTemData = mailTemplateResult.data.data;
                    } else {
                        return false;
                    }
                }
            }
            if (!this.signTemplate){
                //sign_template
                let numBinarySignTemplate = objInitSetting[5]['sign_template'].charAt(objInitSetting[5]['sign_template'].length - 3);
                if (numBinarySignTemplate == '1'){
                    const signTemplateResult = await axios.post(this.ajaxPath+'getDefaultSignTemplates',this.axiosFormData)
                    if (signTemplateResult.data.result == 'success') {
                        this.$refs.editor.$refs.dialog.$data.title = signTemplateResult.data.data==null?'':signTemplateResult.data.data[0]['title'];
                        this.$refs.editor.$refs.dialog.$refs.editor_sign.$data.content = signTemplateResult.data.data == null?'':signTemplateResult.data.data[0]['content'];
                    } else {
                        return false;
                    }
                }
            }
            //body
            let numBinaryBody = objInitSetting[3]['body'].charAt(objInitSetting[3]['body'].length - 3);
            if (numBinaryBody == '1'){
                try {
                    const bodyResult = await axios.post(this.ajaxPath+'getDefaultBody',this.axiosFormData)
                    if (bodyResult.data.result == 'success') {
                        //console.log(bodyResult.data.data);
                        this.$refs.editor.$data.content = bodyResult.data.data.mailBody;
                        //this.$refs.editor.insert(this.$refs.editor.$refs.dialog.$refs.editor_sign.$data.content);
                    } else {
                        //return false;
                        throw 'getDefaultBody interface error'
                    }
                } catch (e) {
                    console.log(e);
                }
            }
            //file_attachment
            let numBinaryFileAttachment = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 3);
            let numBinaryFileAttachment_1 = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 2);
            //是否disabled
            if (numBinaryFileAttachment_1 == 0) {
                this.disabledStatus = true;
            } else {
                this.disabledStatus = false;
            }
            //是否必填第六位
            let numBinaryFileAttachment_must = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 6);
            if (numBinaryFileAttachment_must == 1){
                this.numBinaryFileAttachment_must = true;
            }
            //是否必填第五位
            let numBinaryFileAttachment_not_must = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 5);
            if (numBinaryFileAttachment_not_must == 1){
                this.numBinaryFileAttachment_not_must = true;
            }
            //console.log(numBinaryFileAttachment_not_must);
            if (numBinaryFileAttachment == '1'){
                let fileString = '';
                const fileAttachResult = await axios.post(this.ajaxPath+'getDefaultAttachments',this.axiosFormData)
                if (fileAttachResult.data.result == 'success') {
                    //console.log(fileAttachResult);
                    //文件上传存储
                    this.uploadFiles = [];
                    for (const value of fileAttachResult.data.data){
                        this.uploadFiles.push({
                            'file_data':value.base64_content,
                            'file_name':value.file_name,
                            'file_path':value.file_path,
                            'type':value.mime_type,
                            'file_size':value.file_size,
                            'file_seq_no':value.file_seq_no,
                        })
                        fileString += ('<span>'+ value.file_name + '</span>' + ',');
                        this.$refs.upload.$data.clearFlag = true;
                    }
                    fileString = fileString.substring(0,fileString.length - 1);
                    this.$refs.upload.$data.dragDropFileName = fileString;
                    //将fileChangeFlag状态设置为false
                    this.fileChangeFlag = false;
                } else {
                    return false;
                }
            }
            //check_list_button
            let numBinaryCheckListButton = objInitSetting[7]['check_list_button'].charAt(objInitSetting[7]['check_list_button'].length - 3);
            if (numBinaryCheckListButton == '1') {
                //checkSelectValue
                const checkListValueResult = await axios.post(this.ajaxPath+'getDefaultChecklistValues',this.axiosFormData)
                if (checkListValueResult.data.result == 'success') {
                    let checkSArr = checkListValueResult.data.data.defaultChecklistValues;
                    this.$refs.checkboxDialog.$data.checkboxData = checkSArr;
                } else {
                    return false;
                }
            }
            //check_list_button display
            let numBinaryCheckListButtonDisplay = objInitSetting[7]['check_list_button'].charAt(objInitSetting[7]['check_list_button'].length - 1);
            //console.log(numBinaryCheckListButtonDisplay);
            if (numBinaryCheckListButtonDisplay == '1') {
                this.checkboxShow = true;
            } else {
                this.checkboxShow = false;
            }
            //review
            let numBinaryReview = objInitSetting[8]['review'].charAt(objInitSetting[8]['review'].length - 1);
            if (numBinaryReview == '1') {
                this.reviewDisplay = true;
            } else {
                this.reviewDisplay = false
            }
            //use_time
            let numBinaryUseTime = objInitSetting[9]['use_time'].charAt(objInitSetting[9]['use_time'].length - 3);
            if (numBinaryUseTime == '1') {
                const useTimeResult = await axios.post(this.ajaxPath+'getDefaultUseTime',this.axiosFormData)
                if (useTimeResult.data.result == 'success') {
                    //console.log(useTimeResult.data.data);
                    this.$refs.dateBuss.$data.mailHourFlag = true;
                    this.$refs.dateBuss.$data.iHour = useTimeResult.data.data.useTime.useTimeHour == null?0:useTimeResult.data.data.useTime.useTimeHour;
                    this.$refs.dateBuss.$data.name = useTimeResult.data.data.useTime.useTimeHour == null?0:useTimeResult.data.data.useTime.useTimeHour;
                    this.$refs.dateBuss.$data.fromDate = this.convertToLocalTime(useTimeResult.data.data.useTime.beginDateTime==null?'':useTimeResult.data.data.useTime.beginDateTime.replace(/-/g,'/').substring(0,useTimeResult.data.data.useTime.beginDateTime.length-3));
                    this.$refs.dateBuss.$refs.toDate = this.convertToLocalTime(useTimeResult.data.data.useTime.endTime==null?'':useTimeResult.data.data.useTime.endTime.replace(/-/g,'/').substring(0,useTimeResult.data.data.useTime.endTime.length-3));
                    this.$refs.dateBuss.$data.saveFromData = this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime;
                    this.$refs.dateBuss.$data.saveToData = this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime;
                } else {
                    return false;
                }
            }
            //unknown
            let numBinaryNnKnown = objInitSetting[10]['unknown'].charAt(objInitSetting[10]['unknown'].length - 3);
            if (numBinaryNnKnown == '1'){
                const unKnowResult = await axios.post(this.ajaxPath+'getDefaultUnknown',this.axiosFormData)
                if (unKnowResult.data.result == 'success') {
                    //console.log(unKnowResult);
                    if (unKnowResult.data.data.unknown != null){
                        //console.log(numBinaryNnKnown);
                        this.$refs.un_know.$data.lastName = unKnowResult.data.data.unknown;
                    }
                } else {
                    return false;
                }
            }
            //不明display
            let numBinaryNnKnowno = objInitSetting[10]['unknown'].charAt(objInitSetting[10]['unknown'].length - 1);
            //console.log(numBinaryNnKnowno);
            if (numBinaryNnKnowno == 0) {
                this.unKnowShow = false;
            }
            if (numBinaryNnKnowno == 1){
                this.unKnowShow = true;
            }
            //save_button
            //判断最后一位 显示
            let numBinarySaveButtonShow = objInitSetting[11]['save_button'].charAt(objInitSetting[11]['save_button'].length - 1);
            if (numBinarySaveButtonShow == '1') {
                //dev时 要解开
                //this.saveShow = false;
            }
            //disabled
            if (objInitSetting[11]['save_button'].length == 2){
                let numBinarySaveButton = objInitSetting[11]['save_button'].charAt(objInitSetting[11]['save_button'].length - 2);
                if (numBinarySaveButton == '1') {
                    //this.disabledFlag = true;
                }
            }
            //防止请求期间按钮可点击
            this.mailMustFlag = false;
        },
        //设置保存默认值数据回显
        saveInitValue(content){
            //保存后调用默认二进制接口
            this.saveDefaultBinary();
            //return;
            //一时保存不调用调用默认请求
            this.select = content.C00300_28;
            this.select_cc = content.C00300_29;
            this.email = content.C00100_30;
            this.$refs.editor.$data.content = content.C00900_31;
            this.$refs.checkboxDialog.$data.checkboxValue = content.G00000_33;
            if (content.uploadFiles !== undefined){
                //file
                let fileString = '';
                this.uploadFiles = [];
                for (const value of content.uploadFiles){
                    this.uploadFiles.push({
                        'file_name':value.file_name,
                        'file_path':value.file_path,
                        //暂时不设置
                        'file_seq_no':value.file_seq_no,
                    })
                    fileString += ('<span>'+ value.file_name + '</span>' + ',');
                    this.$refs.upload.$data.clearFlag = true;
                }
                fileString = fileString.substring(0,fileString.length - 1);
                this.$refs.upload.$data.dragDropFileName = fileString;
            }
            //不明
            this.$refs.un_know.$data.lastName = content.C00200_34;
        },
        //mailTemplate signTemplate checkboxList 默认请求接口赋值
        async mailSignTemplate(){
            try {
                this.loadingDisplay(true);
                //mailTemplate
                let mailTemplateResult = await axios.post(this.ajaxPath+'getBodyTemplates',this.axiosFormData)
                if (mailTemplateResult.data.result == 'success') {
                    if (mailTemplateResult.data.data.length != 0){
                        this.$refs.editor.$refs.dialog_tem.$data.defaultTemData = mailTemplateResult.data.data;
                        this.mailTemplate = true;
                    }
                } else {
                    return false;
                }
                //sign_template
                const signTemplateResult = await axios.post(this.ajaxPath+'getSignTemplates',this.axiosFormData)
                if (signTemplateResult.data.result == 'success') {
                    if (signTemplateResult.data.data != null){
                        this.$refs.editor.$refs.dialog.$data.title = signTemplateResult.data.data['title'];
                        this.$refs.editor.$refs.dialog.$refs.editor_sign.$data.content = signTemplateResult.data.data['content'];
                        //console.log(this.$refs.editor);
                        //this.$refs.editor.insert(signTemplateResult.data.data['content']);
                        this.signTemplate = true;
                    }
                } else {
                    return false;
                }
                //checkList interface
                const checkListButtonResult = await axios.post(this.ajaxPath+'getChecklistItems',this.axiosFormData)
                let testArray = checkListButtonResult.data.data;
                //console.log(testArray);
                if (checkListButtonResult.data.result == 'success') {
                    //this.$refs.checkboxDialog.$data.checkboxData = checkListButtonResult.data.data;
                    this.$refs.checkboxDialog.$data.checkboxData = testArray;
                } else {
                    return false;
                }
                //search mail
                const mailToResult = await axios.post(this.ajaxPath+'searchMailToFrequencyList',this.axiosFormData);
                if (mailToResult.data.result == 'success'){
                    let array = [];
                    let rateArr = [];
                    for (const value of mailToResult.data.data) {
                        array.push(value.mail_account);
                        rateArr.push(value.frequency)
                    }
                    this.items = array;
                    this.rateArr = rateArr;
                }
                //search mailCc
                const mailToCcResult = await axios.post(this.ajaxPath+'searchMailCcFrequencyList',this.axiosFormData);
                if (mailToCcResult.data.result == 'success'){
                    let array = [];
                    let rateArr = [];
                    for (const value of mailToCcResult.data.data) {
                        array.push(value.mail_account);
                        rateArr.push(value.frequency)
                    }
                    this.items_cc = array;
                    this.rateArrCc = rateArr;
                }
                //默认work_time赋值
                const workTimeResult = await axios.post(this.ajaxPath+'getWorktime',this.axiosFormData)
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
                //file type forbid
                const file_type_http = await axios.post(this.ajaxPath+'getCommonMailAttachmentTypeByTaskId',this.axiosFormData)
                if (file_type_http.data.result == 'success'){
                    if (file_type_http.data.data.length == 0){
                        this.allow_file_types = null;
                    } else {
                        this.allow_file_types = file_type_http.data.data;
                    }
                }
            } catch (e) {
                console.log(e)
            } finally {
                this.loadingDisplay(false);
            }
        },
        //一时保存数据
        async save(flag){
            try {
                this.loadingDisplay(true)
                let saveData = this.getSaveData();
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                //console.log(saveData);
                formData.append('task_result_content',JSON.stringify(saveData));
                let sendString = 'tmpSaveMail';
                if (!this.reviewDisplay && (flag != true)){
                    sendString = 'saveMail';
                }
                if (flag == 'unknow') {
                    sendString = 'saveMail';
                }
                console.log(sendString);
                //发送请求
                const res = await axios.post('/api/biz/common/mail/'+ sendString,formData)
                if (res.data.result === 'success') {
                    //重新调用work_time接口
                    const workTimeResultN = await axios.post(this.ajaxPath+'getWorktime',this.axiosFormData)
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
                    //一时保存后将file_path重新赋值
                    this.uploadFiles = res.data.data.uploadFiles;
                    //将fileChangeFlag状态设置为false
                    this.fileChangeFlag = false;
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                    if (sendString == 'saveMail' || flag == 'unknow') {
                        window.location.href = '/tasks';
                    }
                } else {
                    this.$refs.alert.show(res.data.err_message)
                    //失败返回false
                    this.reviewDisplay = true;
                }
                //console.log(res);
            } catch (e) {
                this.loadingDisplay(false)
                console.log(e);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false)
            }
        },
        //获取email数据 传递给review组件
        async getEmail(checkboxDateReview){
            let getEmailData = {};
            getEmailData.select = this.select;
            getEmailData.selectCc = this.select_cc;
            getEmailData.subject = this.email;
            getEmailData.body = this.$refs.editor.$data.content;
            getEmailData.uploadFiles = this.uploadFiles;
            getEmailData.unKnowFirstName=this.$refs.un_know.firstName;
            getEmailData.unKnowLastName=this.$refs.un_know.$data.lastName;
            //getEmailData.iHour = this.$refs.dateBuss.iHour;
            //getEmailData.beginDateTime = this.$refs.dateBuss.$data.fromDate._i === undefined?null:this.$refs.dateBuss.$data.fromDate._i+':00';
            //getEmailData.endTime = this.$refs.dateBuss.$data.toDate._i === undefined?null:this.$refs.dateBuss.$data.toDate._i+':00';
            let rec_date = this.$parent.$refs.readRecognize.$data.typeZero
            if (!rec_date){
                try {
                    this.loadingDisplay(true);
                    let httpHour = await axios.post(this.ajaxPath+'getWorktime',this.axiosFormData);
                    if (httpHour.data.result == 'success'){
                        getEmailData.iHour = httpHour.data.data.work_time;
                        getEmailData.beginDateTime = httpHour.data.data.started_at==null?'':this.convertToLocalTime(httpHour.data.data.started_at);
                        getEmailData.endTime = httpHour.data.data.finished_at==null?'':this.convertToLocalTime(httpHour.data.data.finished_at);
                    }
                } catch (e) {
                    console.log(e);
                } finally {
                    this.loadingDisplay(false);
                }
            } else {
                getEmailData.iHour = this.$refs.dateBuss.iHourValue;
                //getEmailData.beginDateTime = ((this.$refs.dateBuss.$data.fromDate._i === undefined) || (this.$refs.dateBuss.$data.fromDate._i === 'Invalid date'))?null:this.$refs.dateBuss.$data.fromDate._i+':00';
                //getEmailData.endTime = ((this.$refs.dateBuss.$data.toDate._i === undefined) || (this.$refs.dateBuss.$data.toDate._i === 'Invalid date'))?null:this.$refs.dateBuss.$data.toDate._i+':00';
                getEmailData.beginDateTime = this.$refs.dateBuss.$data.saveFromData===''?null:(this.$refs.dateBuss.$data.saveFromData+':00');
                getEmailData.endTime = this.$refs.dateBuss.$data.saveToData===''?null:(this.$refs.dateBuss.$data.saveToData+':00');
            }
            getEmailData.checkboxDialog = this.$refs.checkboxDialog.$data.checkboxData;
            //console.log(checkboxDateReview);
            if (checkboxDateReview != undefined){
                if (checkboxDateReview[0] != undefined){
                    getEmailData.checkboxDialog = checkboxDateReview;
                }
            }
            //getEmailData.checkboxListValue = this.$refs.checkboxDialog.$data.checkedArray;
            getEmailData.isReference = this.initData.isReference
            this.$emit('emailRecData',getEmailData);
        },
        //获取save数据调用
        getSaveData(){
            let G00000_27 = {};
            G00000_27.C00300_28=this.select;
            G00000_27.C00300_29=this.select_cc;
            G00000_27.C00100_30=this.email;
            G00000_27.C00900_31=this.$refs.editor.$data.content;
            let checkArray = this.objDeepCopy(this.$refs.checkboxDialog.$data.checkboxData);
            if (this.madeAG){
                if (checkArray.length != 0){
                    if (checkArray[1].value == 0){
                        checkArray[1]['items'][0]['value'] = true;
                        checkArray[1]['items'][1]['value'] = false;
                    } else if (checkArray[1].value == 1){
                        checkArray[1]['items'][0]['value'] = false;
                        checkArray[1]['items'][1]['value'] = true;
                    } else {
                        checkArray[1]['items'][0]['value'] = null;
                        checkArray[1]['items'][1]['value'] = null;
                    }
                }
            }
            G00000_27.G00000_33 = checkArray;
            G00000_27.C00200_34 = this.$refs.un_know.$data.lastName;
            G00000_27.G00000_35 = {
                //'C00700_36':((this.$refs.dateBuss.$data.fromDate._i === undefined) || (this.$refs.dateBuss.$data.fromDate._i === 'Invalid date'))?null:this.convertToTime(this.$refs.dateBuss.$data.fromDate._i),
                //'C00700_37':((this.$refs.dateBuss.$data.toDate._i === undefined) || (this.$refs.dateBuss.$data.toDate._i === 'Invalid date'))?null:this.convertToTime(this.$refs.dateBuss.$data.toDate._i),
                'C00700_36':this.$refs.dateBuss.$data.saveFromData===''?null:this.convertToTime(this.$refs.dateBuss.$data.saveFromData),
                'C00700_37':this.$refs.dateBuss.$data.saveToData===''?null:this.convertToTime(this.$refs.dateBuss.$data.saveToData),
                'C00100_38':this.$refs.dateBuss.$data.iHourValue,
            }
            //file
            let fileUp = [];
            if (this.uploadFiles === undefined) {
                this.uploadFiles = [];
            }
            for (let file of this.uploadFiles){
                let resultNew = {
                    'file_name':file.file_name
                };
                if (this.fileChangeFlag){
                    resultNew.file_data=file.file_data;
                } else {
                    resultNew.file_seq_no = file.file_seq_no;
                }
                fileUp.push(resultNew);
            }
            //console.log(fileUp);
            G00000_27.uploadFiles=fileUp;
            return G00000_27;
        },
        //下载文件
        async downLoadPdf(baseData, firstFileName, lastFileName) {
            let fileName = firstFileName + '.' + lastFileName;
            //没有file_sep 发请求
            if (baseData === undefined){
                for (const i of this.uploadFiles){
                    if (i.file_name == fileName && i.file_seq_no !== undefined){
                        let formData = new FormData();
                        let obj = {
                            'file_seq_no':i.file_seq_no
                        }
                        formData.append('step_id',this.axiosFormData.get('step_id'));
                        formData.append('request_id',this.axiosFormData.get('request_id'));
                        formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                        formData.append('task_id',this.axiosFormData.get('task_id'));
                        formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                        formData.append('file_seq_no',obj.file_seq_no);
                        formData.append('task_result_content',this.axiosFormData.get('task_result_content'));
                        let res = await axios.post(this.ajaxPath+'downloadFile',formData)
                        if (res.data.result == 'success'){
                            baseData = res.data.data.file_data
                            //console.log(baseData)
                        } else {
                            this.$refs.alert.show(res.data.err_message);
                        }
                    }
                }
            }
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
        //点击file下载文件
        clickDownload(data){
            let self = this;
            let base64 = '';
            let fileNewName = '';
            let file_path = '';
            let type = '';
            for (const v of self.uploadFiles){
                if (v.file_name == data){
                    base64 = v.file_data;
                    fileNewName = v.file_name;
                    file_path = v.file_path;
                    type = v.type;
                }
            }
            if (file_path == '') {
                self.downLoadPdf(base64,self.splitFileName(fileNewName),self.getFileType(fileNewName));
                return
            }
            const file = {
                name: fileNewName,
                file_path: file_path,
            }
            self.$refs.filePreviewDialog.show([file], [], type)
        },
        //返回pdf页面
        backCpdf(){
            this.$emit('backCpdf');
        },
        //checkbox数组判断
        checkTrue(checkBoxArr){
            let j = 0;
            for (const i of checkBoxArr){
                if (i){
                    j ++;
                }
                if (j == checkBoxArr.length){
                    return true;
                }
            }
            return false;
        },
        clearFile(){
            this.uploadFiles = [];
        },
        //确认保存判断
        async saveClickFlag(){
            try {
                this.loadingDisplay(true);
                let saveData = this.getSaveData();
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                //console.log(saveData);
                formData.append('task_result_content',JSON.stringify(saveData));
                //file 必填验证
                if (this.numBinaryFileAttachment_must){
                    if (this.uploadFiles.length == 0){
                        this.$refs.alert.show(Vue.i18n.translate('添付ファイルの入力は必須です'));
                        return false;
                    }
                } else {
                    if (this.numBinaryFileAttachment_not_must){
                        if (this.uploadFiles.length == 0) {
                            const file_must_flag = await this.fileNotMust();
                            if (!file_must_flag) {
                                return false;
                            }
                        }
                    }
                }
                //if (this.checkboxShow) {
                if (!this.finishAg) {
                    const res = await axios.post('/api/biz/common/mail/inputValidation',formData);
                    if (res.data.result == 'success'){
                        return true;
                    } else {
                        let error_text = res.data.err_message;
                        this.$refs.alert.show(Vue.i18n.translate(error_text));
                        return false;
                    }
                }
                //}else {
                //    return true;
                //}
            } catch (e) {
                this.loadingDisplay(false);
                console.log(e);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false);
            }
        },
        //file not must
        async fileNotMust(){
            this.$parent.$parent.$parent.$parent.loading = false;
            if (await(this.$refs.confirm.show('添付ファイル無しで処理を続行しますか'))) {
                return true;
            } else {
                return false;
            }
        },
        //保存时 调用默认二进制接口
        async saveDefaultBinary(){
            try {
                this.loadingDisplay(true);
                const res = await axios.post(this.ajaxPath+'getCommonMailSettingByTaskId?task_id='+this.axiosFormData.get('task_id')+'&request_work_id='+this.axiosFormData.get('request_work_id'),this.axiosFormData);
                let resData = res.data.data;
                let objInitSetting = [
                    {'mailTo':Number(resData.mail_to).toString(2)},
                    {'cc':Number(resData.cc).toString(2)},
                    {'subject':Number(resData.subject).toString(2)},
                    {'body':Number(resData.body).toString(2)},
                    {'mail_template':Number(resData.mail_template).toString(2)},
                    {'sign_template':Number(resData.sign_template).toString(2)},
                    {'file_attachment':Number(resData.file_attachment).toString(2)},
                    {'check_list_button':Number(resData.check_list_button).toString(2)},
                    {'review':Number(resData.review).toString(2)},
                    {'use_time':Number(resData.use_time).toString(2)},
                    {'unknown':Number(resData.unknown).toString(2)},
                    {'save_button':Number(resData.save_button).toString(2)}
                ];
                    //不明display
                let numBinaryNnKnowno = objInitSetting[10]['unknown'].charAt(objInitSetting[10]['unknown'].length - 1);
                if (numBinaryNnKnowno == 0) {
                    this.unKnowShow = false;
                }
                if (numBinaryNnKnowno == 1){
                    this.unKnowShow = true;
                }
                let numBinaryFileAttachment_1 = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 2);
                //是否disabled
                if (numBinaryFileAttachment_1 == 0) {
                    this.disabledStatus = true;
                } else {
                    this.disabledStatus = false;
                }
                //是否必填第六位
                let numBinaryFileAttachment_must = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 6);
                if (numBinaryFileAttachment_must == 1){
                    this.numBinaryFileAttachment_must = true;
                }
                //是否必填第五位
                let numBinaryFileAttachment_not_must = objInitSetting[6]['file_attachment'].charAt(objInitSetting[6]['file_attachment'].length - 5);
                if (numBinaryFileAttachment_not_must == 1){
                    this.numBinaryFileAttachment_not_must = true;
                }
                //check_list_button display
                let numBinaryCheckListButtonDisplay1 = objInitSetting[7]['check_list_button'].charAt(objInitSetting[7]['check_list_button'].length - 1);
                if (numBinaryCheckListButtonDisplay1 == '1') {
                    this.checkboxShow = true;
                } else {
                    this.checkboxShow = false;
                }
            } catch (e) {
                console.log(e);
            } finally {
                this.loadingDisplay(false);
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
        //数组深拷贝
        objDeepCopy: function (source) {
            var sourceCopy = source instanceof Array ? [] : {};
            for (var item in source) {
                if (source[item] === null){
                    sourceCopy[item] = null;
                } else {
                    sourceCopy[item] = typeof source[item] === 'object' ? this.objDeepCopy(source[item]) : source[item];
                }
            }
            return sourceCopy;
        }
    }
}
</script>

<style scoped lang="scss">
    .card_t{
    height: 100%;
    display: flex;
    .v-form{
        flex-grow: 1;
    }
    .font_color{
        color:#1976d2;
    }
}
    .hei{
        height: auto;
        margin-bottom: 10px;
    }
    .solo_sign{
        position: absolute;
        left: 0;
        bottom: -40px;
    }
    /* row class重写*/
    .row,.row_new{
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
    /*a标签颜色overwrite*/
    .application a {
        color: #1976d2;
    }
    .ab_right_16{
        position: absolute;
        right: 32px;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    .bottom12_button{
        width: 100%;
    }
    .heightAuto490{
        height: 490px;
        overflow: auto;
    }
    .heightAuto374{
        height: 374px;
        overflow: auto;
    }
    .btn-icon{
        display: flex;
    }
    .line-height-40{
        line-height: 40px;
    }
    .checkbox-style{
        margin-right: 20px !important;
    }
    .flex-center{
        display: flex;
        justify-content: center;
    }
</style>
