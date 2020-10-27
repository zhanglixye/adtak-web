<template>
    <div :style="{height:height}">
        <v-card style="height: 100%">
            <v-form class="pa-3 ma-0 flex_column" style="height: 100%">
                <div class="part_one" v-show="new_pdf">
                    <div>
                        『★交通費(AP)メール』シートをPDFにして、UPしてください。（ファイルのリネーム不要）なお、改ページを確認し1ページに収まるようにPDF化してください。
                        <a class="ml-3" @click="showPdfLink('AP')">確認方法</a>
                    </div>
                    <div class="mt-3">
                        <file-upload :diff="diff" :apFileName="apFileName" @clearFile="clearFile" ref="file1" @clickDownload="clickDownload" :emit_message="file_upload_emit_message1" :allow_file_types="allow_file_types"></file-upload>
                    </div>
                </div>
                <div class="part_two" v-show="!new_pdf">
                    <div>
                        『★交通費(常駐)メール』シートをPDFにして、UPしてください。（ファイルのリネーム不要）なお、改ページを確認し1ページに収まるようにPDF化してください。
                        <a class="ml-3" @click="showPdfLink('CH')">確認方法</a>
                    </div>
                    <div class="mt-3">
                        <file-upload :diff="diff" :apFileName="apFileNameDeal" @clearFile="clearFile2" ref="file2" @clickDownload="clickDownload2" :emit_message="file_upload_emit_message2" :allow_file_types="allow_file_types"></file-upload>
                    </div>
                </div>
                <div class="mt-3" style="width: 100%;">
                    <v-btn
                            color="success"
                            dark
                            class="ma-0 mr-3"
                            style="width: 100px"
                            @click="backEmail"
                    >
                        次へ
                    </v-btn>
                    <v-btn
                            color="#949394"
                            class="ma-0 mr-3"
                            dark
                            style="width: 100px"
                            @click="backOutLay"
                    >
                        戻る
                    </v-btn>
                </div>
            </v-form>
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
    </div>
</template>

<script>
import FileUpload from '../../../../Atoms/Biz/Common/FileUpload';
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    components: {AlertDialog, FileUpload,FilePreviewDialog},
    props:{
        heightExpand:Number,
        axiosFormData:{type:FormData,require:true},
        loadingDisplay: { type: Function, require: true },
        new_pdf:Boolean,
        apFileName:String,
        //S00011 business diff
        diff:{type:Boolean,require:false,default:false}
    },
    data:()=>{
        return {
            test:11,
            asd:null,
            // ファイルアップロード用
            file_upload_emit_message1: 'pdf-up-file1',
            file_upload_emit_message2: 'pdf-up-file2',
            allow_file_types: [
                'application/pdf'
            ],
            uploadFiles:[],
            uploadFiles2:[],
            //判断文件是否改动
            fileChangeFlag:false,
            fileChangeFlag2:false,
            ajaxPath:'/api/biz/common/mail/',
        }
    },
    computed:{
        height() {
            return '100%'
        },
        apFileNameDeal(){
            return '【常駐先】_'+this.apFileName;
        }
    },
    created(){
        let self = this;
        //file 1
        eventHub.$on(this.file_upload_emit_message1, function(data){
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
        //file2
        eventHub.$on(this.file_upload_emit_message2, function(data){
            //初始化设置uploadFiles为空数组
            self.uploadFiles2 = [];
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
                self.uploadFiles2.push(result);
                self.fileChangeFlag2 = true;
            }
            // ローカルに一時保存
            self.moveToTemporary(self.uploadFiles2)
        })
    },
    methods:{
        //跳转到outLay组件
        backOutLay(){
            this.$emit('backOutLay');
        },
        //跳转到Email组件
        backEmail(){
            if (this.new_pdf){
                if (this.uploadFiles == undefined){
                    this.uploadFiles = [];
                }
                if (this.uploadFiles.length == 0 ){
                    this.$refs.alert.show('ファイルのアップは必須です');
                    return;
                }
                this.uploadFiles2 = [];
            } else {
                if (this.uploadFiles2 == undefined){
                    this.uploadFiles2 = [];
                }
                if (this.uploadFiles2.length == 0 ){
                    this.$refs.alert.show('ファイルのアップは必須です');
                    return;
                }
                this.uploadFiles = [];
            }
            // if (this.pdf_show==false?(this.pdf_show || this.new_pdf == true?false:true):(this.pdf_show || (this.new_pdf==false?true:this.new_pdf))){
            //     if (this.uploadFiles2.length == 0 ){
            //         this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            //         return;
            //     }
            // }else {
            //     this.uploadFiles2 = [];
            // }
            //保存pdf
            this.savePdfData();
        },
        async savePdfData(){
            try {
                this.loadingDisplay(true);
                let C00800_24 = {};
                let fileUp = [];
                let fileUp2 = [];
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
                for (let file of this.uploadFiles2){
                    let resultNew = {
                        'file_name':file.file_name
                    };
                    if (this.fileChangeFlag2){
                        resultNew.file_data=file.file_data;
                    } else {
                        resultNew.file_seq_no = file.file_seq_no;
                    }
                    fileUp2.push(resultNew);
                }
                C00800_24.C00800_25_uploadFiles=fileUp;
                C00800_24.C00800_26_uploadFiles=fileUp2;
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('task_result_content',JSON.stringify(C00800_24));
                //console.log(savePeData);
                let res = await axios.post('/api/biz/b00006/s00011/uploadPdf',formData);
                //console.log(res);
                //console.log(res.data.result);
                if (res.data.result == 'success') {
                    //console.log(this.uploadFiles);
                    this.uploadFiles = res.data.data.C00800_25_uploadFiles;
                    this.uploadFiles2 = res.data.data.C00800_26_uploadFiles;
                    //console.log(this.uploadFiles);
                    //this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                    this.$emit('backEmail');
                } else {
                    this.$refs.alert.show(res.data.err_message)
                    return ;
                }
            } catch (e) {
                console.log(e)
                this.loadingDisplay(false);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false);
            }
        },
        saveInitValue(content){
            //console.log(content);
            //file1
            if (content.C00800_25_uploadFiles !== undefined && content.C00800_25_uploadFiles.length != 0){
                //file
                let fileString = '';
                this.uploadFiles = [];
                for (const value of content.C00800_25_uploadFiles){
                    this.uploadFiles.push({
                        'file_name':value.file_name,
                        'file_path':value.file_path,
                        //暂时不设置
                        'file_seq_no':value.file_seq_no,
                    })
                    fileString += ('<span>'+ value.file_name + '</span>' + ',');
                    this.$refs.file1.$data.clearFlag = true;
                }
                fileString = fileString.substring(0,fileString.length - 1);
                this.$refs.file1.$data.dragDropFileName = fileString;
            }
            //file2
            if (content.C00800_26_uploadFiles !== undefined && content.C00800_26_uploadFiles.length != 0){
                //file
                let fileString2 = '';
                this.uploadFiles2 = [];
                for (const value of content.C00800_26_uploadFiles){
                    this.uploadFiles2.push({
                        'file_name':value.file_name,
                        'file_path':value.file_path,
                        //暂时不设置
                        'file_seq_no':value.file_seq_no,
                    })
                    fileString2 += ('<span>'+ value.file_name + '</span>' + ',');
                    this.$refs.file2.$data.clearFlag = true;
                }
                fileString2 = fileString2.substring(0,fileString2.length - 1);
                this.$refs.file2.$data.dragDropFileName = fileString2;
                // if (content.C00800_26 === undefined){
                //     this.$emit('update:pdf_show', false);
                // }
            }
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
        //下载文件
        async downLoadPdf(baseData, firstFileName, lastFileName,flag) {
            let fileName = firstFileName + '.' + lastFileName;
            //没有file_sep 发请求
            if (baseData === undefined){
                if (flag == 1){
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
                            } else {
                                this.$refs.alert.show(res.data.err_message);
                            }
                        }
                    }
                }
                if (flag == 2){
                    for (const i of this.uploadFiles2){
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
                            } else {
                                this.$refs.alert.show(res.data.err_message);
                            }
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
        clickDownload(data){
            let self = this;
            let base64 = '';
            let fileNewName='';
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
                self.downLoadPdf(base64,self.splitFileName(fileNewName),self.getFileType(fileNewName),1);
                return
            }
            const file = {
                name: fileNewName,
                file_path: file_path,
            }
            self.$refs.filePreviewDialog.show([file], [], type)
        },
        clickDownload2(data){
            let self = this;
            let base64 = '';
            let fileNewName='';
            let file_path = '';
            let type = '';
            for (const v of self.uploadFiles2){
                if (v.file_name == data){
                    base64 = v.file_data;
                    fileNewName = v.file_name;
                    file_path = v.file_path;
                    type = v.type;
                }
            }
            if (file_path == '') {
                self.downLoadPdf(base64,self.splitFileName(fileNewName),self.getFileType(fileNewName),2);
                return
            }
            const file = {
                name: fileNewName,
                file_path: file_path,
            }
            self.$refs.filePreviewDialog.show([file], [], type)
        },
        clearFile(){
            this.uploadFiles = [];
        },
        clearFile2(){
            this.uploadFiles2 = [];
        },
        showPdfLink(flag){
            try {
                let work_id = this.axiosFormData.get('request_work_id');
                let task_id = this.axiosFormData.get('task_id');
                if (flag == 'AP'){
                    if (this.diff){
                        try {
                            let type = '';
                            const file = {
                                name: 'PDF添付時マニュアル.pdf',
                                file_path: 'manuals/B00006/PDF添付時マニュアル.pdf',
                            }
                            this.$refs.filePreviewDialog.show([file], [], type);
                        } catch (e) {
                            console.log(e);
                        }
                    }
                }
                if (flag == 'CH'){
                    let itemA = document.createElement('a');
                    itemA.href = this.ajaxPath+work_id+'/'+task_id+'/downloadPdf?file_name=経費申請マニュアル07.pdf&file_path=b00006/pdf/7.pdf';
                    itemA.click();
                }
            } catch (e) {
                console.log(e);
            }
        }
    }
}
</script>

<style scoped lang="scss">
    @import "../../../../../../sass/biz/b00006.scss";
    .part_one,.part_two{
        flex: 1;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    a{
        font-size: 12px;
        color: #1976d2;
        text-decoration: underline;
    }
</style>