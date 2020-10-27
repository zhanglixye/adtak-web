<template>
    <div :style="{height:height}">
        <v-card class="pa-3" style="height: 100%;">
            <div v-show="!unKnowBoxShow">
                <p class="ma-0 text-right">
                    <a href="#" target="_blank">マニュアルを参照する</a>
                </p>
                <p class="item-title my-0">1、左記チェックリストを確認し、更新・新規登録のメニューに媒体資料のboxURLを登録（紐づけ）してください。</p>
                <p class="item-title mb-3">2、媒体資料の掲載（紐づけ）状況確認。</p>
                <div class="item-info">
                    <p class="info-title">今Q以降の媒体資料が表示されていますか？</p>
                    <v-checkbox
                        v-model="G00000_1.C00400_2"
                        label="はい  ：古い媒体資料は掲載されていません。"
                        hide-details
                        :disabled="disabledStatus"
                        @click.stop.native="G00000_1.C00400_3 = false"
                    ></v-checkbox>
                    <v-checkbox
                        v-model="G00000_1.C00400_3"
                        label="いいえ：古い媒体資料は削除しました。"
                        hide-details
                        :disabled="disabledStatus"
                        @click.stop.native="G00000_1.C00400_2 = false"
                    ></v-checkbox>
                </div>
                <p class="item-title mt-4 mb-3">3、登録（紐づけ）メニュー数を入力してください。</p>
                <div class="item-info">
                    <input class="text-box" type="text" v-model="G00000_1.C00100_4" :disabled="disabledStatus">
                    <span>メニュー</span>
                </div>
                <p class="item-title mt-4 mb-2">※補足情報や資料がある場合は入力・アップロードしてください。</p>
                <textarea class="multi-row-text-box mb-2" v-model="G00000_1.C00100_5" :disabled="disabledStatus"></textarea>
                <file-upload
                    ref="fileUpload"
                    :disabledStatus="disabledStatus"
                    :apFileName="apFileName"
                    @clearFile="clearFile"
                    @clickDownload="clickDownload"
                    :emit_message="file_upload_emit_message"
                    :allow_file_types="allow_file_types"
                />
                <file-preview-dialog ref="filePreviewDialog" :isWide='true'/>
            </div>
            <div v-show="unKnowBoxShow" class="UnKown-content-box">
                <h1>不備/不明アリで処理されました。ご確認ください</h1>
                <textarea v-model="unKownContent" disabled></textarea>
            </div>
            <div class="footer-btn_box">
                <div class="row_new ma-0 mt-2 bottom12_button">
                    <div class="btn-icon">
                        <un-kown-t
                            ref="un_know_t"
                            v-show="!disabledStatus"
                            class="line-height-40"
                            @unKnowEvent="unKnowEvent"
                            :unKnowShow="unKnowShow"
                        />
                        <date-buss-t
                            ref="dateBuss"
                            v-show="!disabledStatus"
                            style="display: inline-block;"
                        />
                        <v-card-text class="pa-0" v-show="disabledStatus">
                            <span style="line-height: 33px">
                                作業時間<a style="text-decoration: underline;font-size: 35px;color: #555555;">{{minuteOutValue}}</a> M
                            </span>
                        </v-card-text>
                    </div>
                </div>
                <div class="mt-1">
                    <v-btn
                        v-show="!disabledStatus"
                        color="success"
                        class="ma-0 mr-3"
                        style="width: 100px"
                        @click="verifyData"
                    >
                        確認
                    </v-btn>
                    <v-btn
                        color="#949394"
                        class="ma-0 mr-3"
                        dark
                        style="width: 100px"
                        @click="backHistory"
                    >
                        戻る
                    </v-btn>
                    <v-btn
                        v-show="!disabledStatus"
                        color="warning"
                        class="ma-0 mr-3"
                        style="width: 100px"
                        @click="retainData"
                    >
                        一時保存
                    </v-btn>
                </div>
            </div>
            <!--页面弹窗-->
            <popup-inform ref="popupInform" :message="popupMessage"/>
            <alert-dialog ref="alert"/>
        </v-card>
    </div>
</template>

<script>
import FileUpload from '../../../../Atoms/Biz/Common/FileUpload';
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
import UnKownT from '../../../../Atoms/Biz/Common/UnKownT';
import DateBussT from '../../../../Atoms/Biz/Common/DateBussT';
import PopupInform from '../../B00007/S00013/popupInform';
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
export default {
    name: 'XoneMediaMaterialLink',
    components: {
        FileUpload,
        FilePreviewDialog,
        UnKownT,
        DateBussT,
        PopupInform,
        AlertDialog
    },
    props: {
        prependFunctionToBackHistory: {
            type: Function, required: false, default: () => {
            }
        },
        initData: {type: Object, require: true},
        edit: {type: Boolean, require: false, default: false},
        loadingDisplay: {type: Function, require: true},
        maxHeight: {type: Number, require: true},
        axiosFormData: {type: FormData, require: true},
        workState: {type: Number, require: true},
    },
    data: () => ({
        G00000_1: {
            C00400_2: false,
            C00400_3: false,
            C00100_4: null,
            C00100_5: null,
            C00800_6: null,
            C00800_6_uploadFiles: [
                {
                    file_name: null,
                    file_path: null,
                    file_seq_no: null
                }
            ],
            G00000_35: {
                C00700_36: null,
                C00700_37: null,
                C00100_38: null
            }
        },
        minuteOutValue:'',
        disabledStatus: false,
        unKnowBoxShow: false,
        apFileName: null,
        uploadFiles: [],
        file_upload_emit_message: 'email-sale-file',
        allow_file_types: [
            'application/pdf',
            'application/x-zip-compressed',
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
        ],
        unKnowShow: true,
        checkboxShow: true,
        ajaxPath: '/api/biz/common/mail/',
        popupMessage: '',
        unKownContent: ''
    }),
    computed: {
        height: function () {
            return (this.maxHeight === 0) ? '100%' : this.maxHeight + 'px';
        }
    },
    created() {
        let self = this;
        eventHub.$on(this.file_upload_emit_message, function (data) {
            //初始化设置uploadFiles为空数组
            self.uploadFiles = [];
            // check_filesに追加
            for (const file of data.file_list) {
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
    },
    mounted() {
        this.pageInitialization();  //页面初始化
    },
    methods: {
        //保存状态下的页面初始化
        pageInitialization: function () {
            // -1 ：未开始  0:完成  1：不明  4：保存过
            if (this.workState == -1){
                let content = JSON.parse(this.initData.task_result_info.content);
                if (content.G00000_1.C00800_6_uploadFiles.length !== 0) {
                    let uploadFiles = [];
                    for (let i = 0; i < content.G00000_1.C00800_6_uploadFiles.length; i++) {
                        uploadFiles.push(
                            {
                                file_name: content.G00000_1.C00800_6_uploadFiles[i].name,
                                file_path: content.G00000_1.C00800_6_uploadFiles[i].file_path,
                                file_seq_no: content.G00000_1.C00800_6_uploadFiles[i].seq_no
                            }
                        )
                    }
                    this.uploadFiles = uploadFiles;
                }
                //文件上传组件数据回显
                if (content.G00000_1.C00800_6_uploadFiles.length !== 0) {
                    //file
                    let fileString = '';
                    this.uploadFiles = [];
                    for (const value of content.G00000_1.C00800_6_uploadFiles) {
                        this.uploadFiles.push({
                            'file_name': value.file_name,
                            'file_path': value.file_path,
                            'file_seq_no': value.file_seq_no,
                        });
                        fileString += ('<span>' + value.file_name + '</span>' + ',');
                        this.$refs.fileUpload.$data.clearFlag = true;
                    }
                    fileString = fileString.substring(0, fileString.length - 1);
                    this.$refs.fileUpload.$data.dragDropFileName = fileString;
                }
            } else if (this.workState == 0) {
                let content = JSON.parse(this.initData.task_result_info.content);
                this.G00000_1.C00400_2 = content.G00000_1.C00400_2;
                this.G00000_1.C00400_3 = content.G00000_1.C00400_3;
                this.G00000_1.C00100_4 = content.G00000_1.C00100_4;
                this.G00000_1.C00100_5 = content.G00000_1.C00100_5;
                this.G00000_1.C00800_6 = content.G00000_1.C00800_6;
                this.G00000_1.C00800_6_uploadFiles = content.G00000_1.C00800_6_uploadFiles;
                //文件上传组件数据回显
                if (content.G00000_1.C00800_6_uploadFiles.length !== 0) {
                    //file
                    let fileString = '';
                    this.uploadFiles = [];
                    for (const value of content.G00000_1.C00800_6_uploadFiles) {
                        this.uploadFiles.push({
                            'file_name': value.file_name,
                            'file_path': value.file_path,
                            'file_seq_no': value.file_seq_no,
                        });
                        fileString += ('<span>' + value.file_name + '</span>' + ',');
                        this.$refs.fileUpload.$data.clearFlag = true;
                    }
                    fileString = fileString.substring(0, fileString.length - 1);
                    this.$refs.fileUpload.$data.dragDropFileName = fileString;
                }
                //获取作业时间
                this.getWorkingHours();
                //启用禁用状态
                this.disabledStatus = true;
                //设置不明按钮不显示
                this.unKnowShow = false;
            } else if (this.workState == 1){
                //获取作业时间
                this.getWorkingHours();
                //启用禁用状态
                this.disabledStatus = true;
                //设置不明按钮不显示
                this.unKnowShow = false;
                //显示不明页面
                this.unKnowBoxShow = true;
                //设置不明内容文本
                this.unKownContent = JSON.parse(this.initData.task_result_info.content).G00000_1.C00200_34;
            } else if (this.workState == 4) {
                let content = JSON.parse(this.initData.task_result_info.content);
                this.G00000_1.C00400_2 = content.G00000_1.C00400_2;
                this.G00000_1.C00400_3 = content.G00000_1.C00400_3;
                this.G00000_1.C00100_4 = content.G00000_1.C00100_4;
                this.G00000_1.C00100_5 = content.G00000_1.C00100_5;
                this.G00000_1.C00800_6 = content.G00000_1.C00800_6;
                this.G00000_1.C00800_6_uploadFiles = content.G00000_1.C00800_6_uploadFiles;
                //文件上传组件数据回显
                if (content.G00000_1.C00800_6_uploadFiles.length !== 0) {
                    //file
                    let fileString = '';
                    this.uploadFiles = [];
                    for (const value of content.G00000_1.C00800_6_uploadFiles) {
                        this.uploadFiles.push({
                            'file_name': value.file_name,
                            'file_path': value.file_path,
                            'file_seq_no': value.file_seq_no,
                        });
                        fileString += ('<span>' + value.file_name + '</span>' + ',');
                        this.$refs.fileUpload.$data.clearFlag = true;
                    }
                    fileString = fileString.substring(0, fileString.length - 1);
                    this.$refs.fileUpload.$data.dragDropFileName = fileString;
                }
                //获取作业时间
                this.getWorkingHours();
            }
        },
        //设置弹窗消息
        informPopup: function (msg) {
            this.popupMessage = msg;
            this.$refs.popupInform.dialog = true;
        },
        //清空文件列表
        clearFile() {
            this.uploadFiles = [];
        },
        //下载文件
        clickDownload(data) {
            let self = this;
            let base64 = '';
            let fileNewName = '';
            let file_path = '';
            let type = '';
            for (const v of self.uploadFiles) {
                if (v.file_name == data) {
                    base64 = v.file_data;
                    fileNewName = v.file_name;
                    file_path = v.file_path;
                    type = v.type;
                }
            }
            if (file_path == '') {
                self.downLoadPdf(base64, self.splitFileName(fileNewName), self.getFileType(fileNewName), 1);
                return
            }
            const file = {
                name: fileNewName,
                file_path: file_path,
            };
            self.$refs.filePreviewDialog.show([file], [], type)
        },
        //获取文件名
        splitFileName(text) {
            let pattern = /\.{1}[a-z]{1,}$/;
            if (pattern.exec(text) !== null) {
                return (text.slice(0, pattern.exec(text).index));
            } else {
                return text;
            }
        },
        //获取文件类型
        getFileType(filePath) {
            let startIndex = filePath.lastIndexOf('.');
            if (startIndex != -1)
                return filePath.substring(startIndex + 1, filePath.length).toLowerCase();
            else return '';
        },
        //下载文件
        async downLoadPdf(baseData, firstFileName, lastFileName) {
            let fileName = firstFileName + '.' + lastFileName;
            //没有file_sep 发请求
            if (baseData === undefined) {
                for (const i of this.uploadFiles) {
                    if (i.file_name == fileName && i.file_seq_no !== undefined) {
                        let formData = new FormData();
                        let obj = {
                            'file_seq_no': i.file_seq_no
                        }
                        formData.append('step_id', this.axiosFormData.get('step_id'));
                        formData.append('request_id', this.axiosFormData.get('request_id'));
                        formData.append('request_work_id', this.axiosFormData.get('request_work_id'));
                        formData.append('task_id', this.axiosFormData.get('task_id'));
                        formData.append('task_started_at', this.axiosFormData.get('task_started_at'));
                        formData.append('file_seq_no', obj.file_seq_no);
                        formData.append('task_result_content', this.axiosFormData.get('task_result_content'));
                        let res = await axios.post(this.ajaxPath + 'downloadFile', formData);
                        if (res.data.result == 'success') {
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
            } else if (lastFileName == 'txt') {
                blob = new Blob([ia], {
                    'type': 'text/plain,charset=shift-jis'
                });
            } else if (lastFileName == 'log') {
                blob = new Blob([ia], {
                    'type': 'text/plain'
                });
            } else if (lastFileName == 'xls') {
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
        //转换base64
        convertToBase64: function (file) {
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
        //不明处理
        async unKnowEvent() {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let url = '/api/biz/b00013/s00019/makeContact';
                let parameter = this.axiosFormData;
                let C00800_6_uploadFiles = [];
                if (this.uploadFiles.length !== 0) {
                    for (let i = 0; i < this.uploadFiles.length; i++) {
                        if (this.uploadFiles[i].file_seq_no != null) {
                            C00800_6_uploadFiles.push(this.uploadFiles[i])
                        } else {
                            C00800_6_uploadFiles.push(
                                {
                                    file_name: this.uploadFiles[i].file_name,
                                    file_data: this.uploadFiles[i].file_data
                                }
                            )
                        }
                    }
                }
                let G00000_1 = JSON.parse(JSON.stringify(this.G00000_1));
                G00000_1.C00100_4 = this.G00000_1.C00100_4;
                G00000_1.C00800_6_uploadFiles = C00800_6_uploadFiles;
                G00000_1.G00000_35.C00700_36 = this.$refs.dateBuss.$data.saveFromData === '' ? null : this.convertToTime(this.$refs.dateBuss.$data.saveFromData);
                G00000_1.G00000_35.C00700_37 = this.$refs.dateBuss.$data.saveToData === '' ? null : this.convertToTime(this.$refs.dateBuss.$data.saveToData);
                G00000_1.G00000_35.C00100_38 = this.$refs.dateBuss.$data.iHourValue;
                G00000_1.C00200_34 = this.$refs.un_know_t.lastName;
                parameter.set('task_result_content', JSON.stringify(G00000_1));
                await axios.post(url, parameter).then((res) => {
                    if (res.data.result == 'success') {
                        window.location.href = '/tasks';
                    } else {
                        _this.$refs.alert.show(res.data.err_message);
                    }
                })
            } catch (e) {
                this.$refs.alert.show(e);
                // this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false)
            }
        },
        //确认按钮
        async verifyData() {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let url = '/api/biz/b00013/s00019/done';
                let parameter = this.axiosFormData;
                let C00800_6_uploadFiles = [];
                if (this.uploadFiles.length !== 0) {
                    for (let i = 0; i < this.uploadFiles.length; i++) {
                        if (this.uploadFiles[i].file_seq_no != null) {
                            C00800_6_uploadFiles.push(this.uploadFiles[i])
                        } else {
                            C00800_6_uploadFiles.push(
                                {
                                    file_name: this.uploadFiles[i].file_name,
                                    file_data: this.uploadFiles[i].file_data
                                }
                            )
                        }
                    }
                }
                let G00000_1 = JSON.parse(JSON.stringify(this.G00000_1));
                G00000_1.C00100_4 = this.G00000_1.C00100_4;
                G00000_1.C00800_6_uploadFiles = C00800_6_uploadFiles;
                G00000_1.G00000_35.C00700_36 = this.$refs.dateBuss.$data.saveFromData === '' ? null : this.convertToTime(this.$refs.dateBuss.$data.saveFromData);
                G00000_1.G00000_35.C00700_37 = this.$refs.dateBuss.$data.saveToData === '' ? null : this.convertToTime(this.$refs.dateBuss.$data.saveToData);
                G00000_1.G00000_35.C00100_38 = this.$refs.dateBuss.$data.iHourValue;
                parameter.set('task_result_content', JSON.stringify(G00000_1));
                await axios.post(url, parameter).then((res) => {
                    if (res.data.result == 'success') {
                        window.location.href = '/tasks';
                    } else {
                        _this.$refs.alert.show(res.data.err_message);
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false);
                this.dialog = false;
            }
        },
        //返回按钮
        async backHistory() {
            await this.prependFunctionToBackHistory();
            window.history.back()
        },
        //一时保存
        async retainData() {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let url = '/api/biz/b00013/s00019/hold';
                let parameter = this.axiosFormData;
                let C00800_6_uploadFiles = [];
                if (this.uploadFiles.length !== 0) {
                    for (let i = 0; i < this.uploadFiles.length; i++) {
                        if (this.uploadFiles[i].file_seq_no != null) {
                            C00800_6_uploadFiles.push(this.uploadFiles[i])
                        } else {
                            C00800_6_uploadFiles.push(
                                {
                                    file_name: this.uploadFiles[i].file_name,
                                    file_data: this.uploadFiles[i].file_data
                                }
                            )
                        }
                    }
                }
                let G00000_1 = JSON.parse(JSON.stringify(this.G00000_1));
                G00000_1.C00100_4 = this.G00000_1.C00100_4;
                G00000_1.C00800_6_uploadFiles = C00800_6_uploadFiles;
                G00000_1.G00000_35.C00700_36 = this.$refs.dateBuss.$data.saveFromData === '' ? null : this.convertToTime(this.$refs.dateBuss.$data.saveFromData);
                G00000_1.G00000_35.C00700_37 = this.$refs.dateBuss.$data.saveToData === '' ? null : this.convertToTime(this.$refs.dateBuss.$data.saveToData);
                G00000_1.G00000_35.C00100_38 = this.$refs.dateBuss.$data.iHourValue;
                parameter.set('task_result_content', JSON.stringify(G00000_1));
                await axios.post(url, parameter).then((res) => {
                    if (res.data.result == 'success') {
                        if (res.data.data.length !== 0) {
                            let uploadFiles = [];
                            for (let i = 0; i < res.data.data.length; i++) {
                                uploadFiles.push(
                                    {
                                        file_name: res.data.data[i].name,
                                        file_path: res.data.data[i].file_path,
                                        file_seq_no: res.data.data[i].seq_no
                                    }
                                )
                            }
                            _this.uploadFiles = uploadFiles;
                        }
                        _this.getWorkingHours();
                        _this.informPopup('保存しました');
                    } else {
                        _this.$refs.alert.show(res.data.err_message);
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false);
                this.dialog = false;
            }
        },
        //获取后台作业时间,重新调用work_time接口
        async getWorkingHours() {
            let url = '/api/biz/common/mail/getWorktime';
            const workTimeResultN = await axios.post(url, this.axiosFormData);
            if (workTimeResultN.data.result == 'success') {
                let time_work_new = workTimeResultN.data.data;
                let fromDate = time_work_new.started_at == null ? '' : time_work_new.started_at.replace(/-/g, '/').substring(0, time_work_new.started_at.length - 3);
                let toDate = time_work_new.finished_at == null ? '' : time_work_new.finished_at.replace(/-/g, '/').substring(0, time_work_new.finished_at.length - 3);
                let hour = time_work_new.work_time == null ? 0 : time_work_new.work_time;
                this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime = fromDate == '' || null ? '' : this.convertToLocalTime(fromDate);
                this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime = toDate == '' || null ? '' : this.convertToLocalTime(toDate);
                this.$refs.dateBuss.$data.mailHourFlag = true;
                this.$refs.dateBuss.$data.iHour = hour;
                this.$refs.dateBuss.$data.name = hour;
                this.$refs.dateBuss.$data.saveFromData = this.$refs.dateBuss.$refs.dateTimeFrom.$data.dateTime;
                this.$refs.dateBuss.$data.saveToData = this.$refs.dateBuss.$refs.dateTimeTo.$data.dateTime;
                //设置禁用状态下的作业时间
                this.minuteOutValue = (hour*60).toFixed(0);
            }
        },
        //后台返回后 转换时区
        convertToLocalTime: function (utcDate, outPutFormat = 'YYYY/MM/DD HH:mm') {
            return moment.utc(utcDate).local().format(outPutFormat)
        },
        convertToTime: function(date){
            if (date == ''){
                return null;
            }
            return moment(date).utc().format('YYYY/MM/DD HH:mm:ss');
        }
    }
}
</script>

<style scoped lang="scss">
    /*组件CSS重置*/
    .v-input--selection-controls {
        margin-top: 10px;
    }
    .line-height-40 {
        line-height: 40px;
    }
    .checkbox-style {
        margin-right: 20px !important;
    }
    /* row class重写*/
    .row, .row_new {
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
    /*业务组件样式*/
    .item-title {
        margin: 15px 0;
        font-size: 16px;
        display: inline-block;
    }
    .item-info {
        padding-left: 40px;
        .info-title {
            font-size: 16px;
            margin: 0;
        }
    }
    .text-box {
        border: solid 1px #aaaaaa;
        border-radius: 2px;
        height: 38px;
        line-height: 38px;
        text-align: center;
        width: 80px;
        outline: none;
        font-size: 16px;
        margin-right: 5px;
    }
    .text-box:disabled{
        background-color: #fafafa;
        cursor: no-drop;
    }
    .multi-row-text-box {
        min-width: 100%;
        max-width: 100%;
        min-height: 70px;
        max-height: 70px;
        border: 1px solid #aaaaaa;
        outline: none;
        padding: 10px;
    }
    .multi-row-text-box:disabled{
        background-color: #fafafa;
        cursor: no-drop;
    }
    .footer-btn_box {
        button.success {
            background-color: #4db6ac !important;
            border-color: #4db6ac !important;
        }
    }
    .btn-icon {
        display: flex;
    }
    .bottom12_button {
        width: 100%;
    }
    /*不明页面代码*/
    .UnKown-content-box{
        h1{
            margin: 3px 0;
            font-size: 16px;
            font-weight: bold;
            color: red;
        }
        textarea{
            max-width: 100%;
            min-width: 100%;
            max-height: 450px;
            min-height: 450px;
            padding: 10px;
            margin: 10px 0;
            border-top: dotted 1px #cccccc;
            color: red;
            font-size: 14px;
        }
    }
</style>
