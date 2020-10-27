<template>
    <div :class="[layout == 1?'flex_column':'flex_row']">
        <div v-for="(item,index) in list" :key="index" style="padding:0">
            <v-layout row justify-center>
                <v-flex xs10>
                    <div v-if="item.type == 0">
                        <span>{{item.label}}</span>
                        <a v-for="(items,index) in item.urls" :key="index" @click="showPdfLink(items)" v-show="item.urls !== null" class="ml-3">{{items.label}}</a>
                    </div>
                    <component
                            :disabled="statusBool"
                            :allow_file_types="allow_file_types"
                            @clearFile="clearFile"
                            @clickDownload="clickDownload"
                            :emit_message="file_upload_emit_message"
                            :items="item.components"
                            item-text="label"
                            item-value="value"
                            v-model="item.value"
                            :label="item.label"
                            v-if="diffComponentShow(item.type) !== null"
                            :is="diffComponentShow(item.type)"
                            :disabled-status="statusBool"
                            ref="trees"
                            class="ml-5"
                    >

                    </component>
                </v-flex>
            </v-layout>
            <tree :layout="item.layout" :status-bool="statusBool" v-if="scopes[index]" :list="item.components" :ajaxPath="ajaxPath" :axios="axios" :loadingDisplay="loadingDisplay"></tree>
        </div>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
import VRadioExtend from './VRadioExtend';
import VDatePickerExtend from './VDatePickerExtend';
import FileUpload from '../../../../Atoms/Biz/Common/FileUpload';
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
import AExtend from './AExtend';
export default {
    name: 'Tree',
    components: {AExtend, AlertDialog, FileUpload, VDatePickerExtend, VRadioExtend, FilePreviewDialog},
    props:{
        list:Array,
        ajaxPath:String,
        axios:{type:FormData,require:true},
        loadingDisplay: { type: Function, require: true },
        statusBool:{type:Boolean,require:false,default:false},
        layout:{type:Number,require:false},
    },
    data(){
        return {
            scopes:[],
            file_upload_emit_message: 'email-sale-file',
            //储存文件的数组
            uploadFiles:[],
            //允许文件类型
            allow_file_types: [

            ],
            //判断文件是否改动
            fileChangeFlag:false,
        }
    },
    computed:{

    },
    created() {
        this.scope();
        /**
             * 文件drop事件监听
             * @type {default}
             */
        const self = this;
        //防止多次监听
        eventHub.$off(this.file_upload_emit_message);
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
            self.moveToTemporary(self.uploadFiles);
        })
        //请求getTaskResultFileInfoById接口
        if (this.list[0].type == 800){
            this.getFileInformation();
        }
        //allow_file_types 赋值
        this.allow_file_types = this.list[0].validate_option.attachment_type;
    },
    methods:{
        /**
             * 递归组件方法
             */
        scope() {
            this.list.forEach((item, index) => {
                //this.scopesDefault[index] = false
                if (item.type == 0) {
                    this.scopes[index] = true
                } else {
                    this.scopes[index] = false
                }
            })
        },
        /**
             * 判断不同组件的显示
             */
        diffComponentShow(type){
            let str;
            if (type == 100){
                str = 'v-text-field';
            } else if (type == 300){
                str = 'v-radio-extend';
            } else if (type == 500){
                str = 'v-select';
            } else if (type == 400) {
                str  = 'v-checkbox';
            } else if (type == 700){
                str = 'v-date-picker-extend';
            } else if (type == 800){
                str = 'file-upload';
            } else if (type == 1000){
                str = 'a-extend';
            } else {
                str = null;
            }
            return str;
        },
        /**
             * 清空uploadFiles数组
             */
        clearFile(){
            this.uploadFiles = [];
            this.list[0].value = [];
            eventHub.$emit('upload-file-tree',[]);
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
                        formData.append('step_id',this.axios.get('step_id'));
                        formData.append('request_id',this.axios.get('request_id'));
                        formData.append('request_work_id',this.axios.get('request_work_id'));
                        formData.append('task_id',this.axios.get('task_id'));
                        formData.append('task_started_at',this.axios.get('task_started_at'));
                        formData.append('file_seq_no',obj.file_seq_no);
                        formData.append('task_result_content',this.axios.get('task_result_content'));
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
        // Customer provided
        moveToTemporary: async function (uploadFile) {
            this.loadingDisplay(true);
            // 画像データをblobURL -> base64
            // const file = this.uploadFile
            const files = uploadFile;
            const convert = this.convertToBase64;
            console.log('promise all start')
            await Promise.all(files.map(async upload_file => upload_file = await convert(upload_file)))
            console.log('promise all end')
            for (let i = 0; i <  uploadFile.length; i++){
                delete uploadFile[i].type
            }
            //await convert(files[0]);
            //支持多文件上传
            //await [].forEach.call(files,convert);
            //调用uploadFileToS3接口
            let formData = new FormData();
            formData.append('step_id',this.axios.get('step_id'));
            formData.append('request_id',this.axios.get('request_id'));
            formData.append('request_work_id',this.axios.get('request_work_id'));
            formData.append('task_id',this.axios.get('task_id'));
            formData.append('task_started_at',this.axios.get('task_started_at'));
            formData.append('task_result_file_content',JSON.stringify(uploadFile));
            axios.post('/api/biz/common/dynamic_page/uploadFileToS3',formData)
                .then((res) => {
                    if (res.data.result == 'success'){
                        //获取seq-no
                        let seq_arr = [];
                        let file_seq_array = res.data.data;
                        file_seq_array.forEach((item)=>{
                            seq_arr.push(item.seq_no);
                        })
                        if (this.list[0].type == 800){
                            this.list[0].value = seq_arr;
                        }
                        //赋值uploadFiles
                        this.uploadFiles = [];
                        for (const value of file_seq_array){
                            this.uploadFiles.push({
                                'file_name':value.file_name,
                                'file_path':value.file_path,
                                //暂时不设置
                                'file_seq_no':value.seq_no,
                            })
                        }
                    }
                })
                .catch(error =>{
                    console.log(error)
                })
                .finally(()=>{
                    this.loadingDisplay(false);
                })
        },
        //To base64
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {
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
            })
        },
        //请求getTaskResultFileInfoById
        async getFileInformation() {
            try {
                this.loadingDisplay(true);
                let formData = new FormData();
                let obj = {
                    task_result_id:this.axios.get('id'),
                    seq_no_array:this.list[0].value,
                };
                formData.append('step_id',this.axios.get('step_id'));
                formData.append('request_id',this.axios.get('request_id'));
                formData.append('request_work_id',this.axios.get('request_work_id'));
                formData.append('task_id',this.axios.get('task_id'));
                formData.append('task_started_at',this.axios.get('task_started_at'));
                formData.append('task_result_file_content',JSON.stringify(obj));
                const res = await axios.post('/api/biz/common/dynamic_page/getTaskResultFileInfoById',formData)
                if (res.data.result == 'success'){
                    let contentFile = res.data.data;
                    //file
                    let fileString = '';
                    this.uploadFiles = [];
                    for (const value of contentFile){
                        this.uploadFiles.push({
                            'file_name':value.file_name,
                            'file_path':value.file_path,
                            //暂时不设置
                            'file_seq_no':value.seq_no,
                        })
                        fileString += ('<span>'+ value.file_name + '</span>' + ',');
                        this.$refs.trees[0].$data.clearFlag = true;
                    }
                    fileString = fileString.substring(0,fileString.length - 1);
                    this.$refs.trees[0].$data.dragDropFileName = fileString;
                }
            } catch (e) {
                this.loadingDisplay(false);
                console.log(e);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false);
            }
        }
    }
}
</script>

<style scoped>
    .fw{
        font-weight: bold;
        padding-left: 50px;padding-right: 80px
    }
    .flex_column{
        display: flex;
        flex-direction: column;
    }
    .flex_row{
        display: flex;
        justify-content: space-around;
    }
</style>