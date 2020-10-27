<template>
    <!-- 実作業 -->
    <div id="steps-block">
        <!-- 素材 -->
        <v-container fluid>
            <v-layout mb-4 style="width: 90%;">
                <v-text-field :label="$t('biz.abbey_check.abbey_check.step.client_name')" :disabled="isDoneTask" hide-details v-model="form.client_name" ></v-text-field>
            </v-layout>
            <v-layout xs12>
                    <p>{{ $t('biz.abbey_check.abbey_check.step.material') }}</p>
                    <a class="ml-auto" @click="allDL"><p>{{ $t('biz.abbey_check.abbey_check.all_download') }}</p></a>
            </v-layout>
            <!-- チェック対象 -->
            <div v-for="check_file in form.check_files" :key="check_file.task_result_file_seq_no">
                <list-item :result.sync="check_file" :isDoneTask="isDoneTask"></list-item>
            </div>
            <!-- /チェック対象 -->

            <!-- ファイルアップロード -->
            <file-upload xs12 v-if="!isDoneTask" :emit_message="material_file_upload_emit_message" :allow_file_types="allow_file_types"></file-upload>
            <!-- /ファイルアップロード -->
        </v-container>
        <!-- /素材 -->

        <!-- 結果キャプチャ -->
        <v-container fluid>
            <p>{{ $t('biz.abbey_check.abbey_check.step.result_capture') }}</p>
            <!-- キャプチャ -->
            <v-layout row wrap align-start justify-start mx-2>
                <v-flex
                v-for="result_file in form.result_files"
                :key="'r'+result_file.task_result_file_seq_no"
                xs2
                px-1
                pb-2
                >
                    <v-layout justify-center column>
                        <v-tooltip top class="center-block">
                            <v-btn class="result-file-btn" xs1 flat icon v-if="!isDoneTask" @click="resultFileDelete(result_file)" color="red lighten-2" slot="activator">
                                <v-icon>clear</v-icon>
                            </v-btn>
                            <span>{{ $t('common.button.delete') }}</span>
                        </v-tooltip>
                        <v-progress-circular v-if="result_file.file_data == undefined || result_file.file_data == ''" indeterminate color="primary"></v-progress-circular>
                        <image-and-movie v-else :file="{name: result_file.file_name, data: result_file.file_data, mimeType: 'image/*'}" :height="90"></image-and-movie>
                    </v-layout>
                </v-flex>
            </v-layout>
            <!-- キャプチャ -->
            <!-- ファイルアップロード -->
            <file-upload xs12 v-if="!isDoneTask" :emit_message="result_capture.emit_message" :allow_file_types="result_capture.allow_file_types"></file-upload>
            <!-- /ファイルアップロード -->
        </v-container>
        <!-- /結果キャプチャ -->
        <!-- 処理するボタン -->
        <v-layout wrap ml-3>
            <v-flex xs12 class="text-xs-right">
                <span v-if="!isDoneTask">
                    <v-btn color="amber darken-3" :dark="valid" :disabled="!valid" @click="openModal('')">
                        {{ $t('biz.abbey_check.common.btn_submit') }}
                    </v-btn>
                </span>
                <span v-else class="ml-3">
                    <span v-if="task_exclusion || form.process_info.results.comment == ''">
                        <v-icon mr-1 style="font-size:15px;">done</v-icon>
                        <span>{{ $t('biz.abbey_check.common.status_done') }}</span>
                    </span>
                </span>
            </v-flex>
        </v-layout>
        <!-- /処理するボタン -->

        <v-layout wrap ml-3 class="justify-end align-center">
            <v-flex xs12 class="text-xs-right" v-if="!isDoneTask">
                <a @click="showIrregularBlock = !showIrregularBlock">
                    <span>{{ $t('biz.abbey_check.common.irregularBtnsBox_trigger') }}</span>
                    <v-icon small v-if="!showIrregularBlock">expand_more</v-icon>
                    <v-icon small v-else>expand_less</v-icon>
                </a>
            </v-flex>
            <v-expand-transition>
                <div class="mt-3 text-xs-right" v-if="(showIrregularBlock || (isDoneTask && form.process_info.results.comment !== '')) && !task_exclusion">
                    <v-card color="" class="text-xs-right">
                        <v-card-text>
                            <v-flex xs12 class="judge-btns-wrap">
                                <!-- 処理後の処理するボタン -->
                                <span  class="text-xs-left">
                                    <span v-if="isDoneTask && (done == form.process_info.results.type)">
                                        <v-badge
                                            color="amber darken-3"
                                            right
                                            overlap
                                        >
                                            <v-icon
                                                slot="badge"
                                                dark
                                                small
                                            >done</v-icon>
                                            <v-btn v-if="done == form.process_info.results.type" color="amber darken-3" disabled>
                                                {{ $t('biz.abbey_check.common.btn_submit') }}
                                            </v-btn>
                                        </v-badge>
                                        <span>{{ $t('biz.abbey_check.common.status_done') }}</span>
                                        <br>
                                    </span>
                                </span>
                                <!-- /処理後 処理するボタン -->
                                <!-- 処理中・後 処理する以外のボタン -->
                                <span v-for="(irregularBtn, index) in irregularBtns" :key="index">
                                    <span v-if="!isDoneTask">
                                        <v-btn v-html="irregularBtn.text" color="amber darken-3" :dark="valid" :disabled="!valid" @click="openModal(irregularBtn.resultType)">
                                        </v-btn>
                                    </span>
                                    <span v-else-if="(done != form.process_info.results.type)" class="text-xs-left">
                                        <span v-if="(irregularBtn.resultType == form.process_info.results.type)">
                                            <v-badge
                                                color="amber darken-3"
                                                right
                                                overlap
                                            >
                                                <v-icon
                                                    slot="badge"
                                                    dark
                                                    small
                                                >done</v-icon>
                                                <v-btn v-if="irregularBtn.resultType == form.process_info.results.type" v-html="irregularBtn.text" color="amber darken-3" disabled></v-btn>
                                                <v-btn v-if="done == form.process_info.results.type" color="amber darken-3" disabled>
                                                    {{ $t('biz.abbey_check.common.btn_submit') }}
                                                </v-btn>
                                            </v-badge>
                                            <span>{{ $t('biz.abbey_check.common.status_done') }}</span>
                                            <br>
                                            <div v-if="form.process_info.results.comment" class="text-xs-left">
                                                <span>[{{ $t('biz.abbey_check.common.comment') }}]</span>
                                                <span>{{ form.process_info.results.comment }}</span>
                                            </div>
                                        </span>
                                    </span>
                                </span>
                                <!-- /処理中・後 処理する以外のボタン -->
                            </v-flex>
                        </v-card-text>
                    </v-card>
                </div>
            </v-expand-transition>
        </v-layout>

        <search-modal></search-modal>
    </div>
    <!-- / 実作業 -->
</template>

<script>
import motionMixin from '../../../../../mixins/motionMixin'
import ListItem from './ListItem.vue'
import SearchModal from '../../../../Organisms/Biz/AbbeyCheck/AbbeyCheck/SearchModal.vue'
import FileUpload from '../../../../Atoms/Upload/FileUpload'
import ImageAndMovie from '../../../../Molecules/Media/ImageAndMovie'

import JSZip from 'jszip'
import saveAs from 'file-saver'

export default {
    name: 'steps',
    mixins: [motionMixin],
    components: {
        ListItem,
        SearchModal,
        FileUpload,
        ImageAndMovie
    },
    props: {
        loading: Boolean,
        alert: Object,
        resData: Object
    },
    data: () => ({
        result_capture: {
            emit_message: 'result-capture-append',
            allow_file_types: ['image/jpeg','image/png','image/gif'],
        },
        done: _const.TASK_RESULT_TYPE.DONE,
        task_exclusion: false,
        material_file_upload_emit_message: 'append-check-file',
        notify_modal_emit_message: 'redirect',
        search_abbey_data: {
            abbey_id: 0,
            file_name: '',
        },
        search_modal: false,
        valid: true,
        showIrregularBlock: false,
        isDoneTask: false,
        allow_file_types: ['image/jpeg','image/png','image/gif','video/mp4'],
        // 送信時に使用する
        form: {
            task_id: null,
            step_id: null,
            started_at: null,
            request_work_id: null,
            request_id: null,
            client_name: '',
            result_files: [
                /*操作中
                {
                    task_result_file_seq_no: Number,
                    file_name: String,
                    file_data: String,
                    file_path: String,
                }
                */
                /*送信時
                    Number
               */
            ],
            check_files: [
                /*
                {
                    task_result_file_seq_no: Number,
                    menu_name: String,
                    check_file_name: String,
                    aspect_ratio: String,
                    abbey_id: Number,
                    err_description: '',
                    file_name: String,
                    file_size: Number,
                    file_data: String,
                    file_path: String,
                    type: String,
                    is_success: null,
                    err_detail: ''
                }
                */
            ],
            // 必須
            process_info: {
                results: {
                    type: _const.TASK_RESULT_TYPE.DONE,
                    comment: ''
                }
            }
        }
    }),
    created: function () {
        const self = this;

        eventHub.$on('submit', function() {
            self.submit()
        })

        eventHub.$on('submitFromIrregularModal', function(data) {
            self.form.process_info.results.comment = data.comment
            self.submit()
        })

        eventHub.$on('set-abbey-id', function(data) {
            for (const item of self.form.check_files) {
                if (item.task_result_file_seq_no === data.task_result_file_seq_no){
                    item.abbey_id = data.abbey_id
                    break
                }
            }
        }),
        eventHub.$on('delete', function(data) {
            for (const i in self.form.check_files) {
                if (self.form.check_files[i].task_result_file_seq_no === data.task_result_file_seq_no ){
                    self.form.check_files.splice(i, 1)
                    break
                }
            }
        })
        eventHub.$on(this.material_file_upload_emit_message, function(data){
            // check_filesに追加
            for (const file of data.file_list){
                const result = {
                    task_result_file_seq_no: -file.id - 1,
                    menu_name: '',
                    check_file_name: '',
                    aspect_ratio: '',
                    abbey_id:'',
                    err_description: '',
                    file_name: file.name,
                    file_size: file.size.toLocaleString(),
                    file_data: file.data,
                    file_path: '',
                    type: file.type,
                    is_success: null,
                    err_detail: ''
                }
                self.form.check_files.push(result)
            }
        })

        eventHub.$on(this.notify_modal_emit_message, function(){
            if (window.history.length > 1) {
                window.history.back()
            } else {
                window.location.href = '/tasks'
            }
        })

        eventHub.$on('set-result-file', async function(data){
            self.form.result_files = data.result_files
        })

        eventHub.$on(this.result_capture.emit_message, function(data){
            // check_filesの追加
            for (const file of data.file_list){
                const tmpFile = {
                    file_name: file.name,
                    file_data: file.data,
                    task_result_file_seq_no: -file.id - 1,
                    file_path: '',
                }
                self.form.result_files.push(tmpFile)
            }
        })

        const resData = JSON.parse(JSON.stringify(this.resData))
        this.setData(resData)
    },
    mounted() {
        this.form.result_files.forEach(async file => {
            if (file.file_data == undefined || file.file_data == ''){
                axios.post('/api/biz/abbey_check/abbey_check/download_file', {file_path: file.file_path, file_name: file.file_name})
                    .then((res) =>{
                        file.file_data = res.data.data
                    })
                    .catch((err) =>{
                        console.log(err)
                    })
            }
        });
    },
    computed: {
        irregularBtns () {
            return [
                { text: this.$t('biz.abbey_check.common.task_result_type_text.prefix' + _const.TASK_RESULT_TYPE.CONTACT), resultType: _const.TASK_RESULT_TYPE.CONTACT, align: 'center' },
                { text: this.$t('biz.abbey_check.common.task_result_type_text.prefix' + _const.TASK_RESULT_TYPE.HOLD), resultType: _const.TASK_RESULT_TYPE.HOLD, align: 'center' }
            ]
        },
        toStopTheProcess(){
            return _const.TASK_RESULT_TYPE.CONTACT;
        }
    },
    methods: {
        allDL:async function(){

            // BlobURL convert to blob
            const convertToBlob = (file_data) => {
                return new Promise((resolve,reject)=>{
                    var xhr = new XMLHttpRequest()
                    xhr.responseType = 'blob'
                    xhr.onload = () => {
                        resolve(xhr.response)
                    }
                    xhr.onerror = (e) => reject(e)
                    xhr.open('GET', file_data)
                    xhr.send()
                })
            }

            const zip = new JSZip();

            // Create base64 list
            const base64List = this.form.check_files.filter(check_file => 'data' == check_file.file_data.substring(0,4))
            base64List.forEach(check_file => {
                const searchWord = 'base64,'
                const index = check_file.file_data.indexOf(searchWord)
                const data = check_file.file_data.substring(index + searchWord.length)
                zip.file(check_file.check_file_name, data, {base64: true})
            })

            // Create blobURL list
            const blobURLList = this.form.check_files.filter(check_file => 'blob:' == check_file.file_data.substring(0,5))
            for (const file of blobURLList) {
                zip.file(file.check_file_name, await convertToBlob(file.file_data))
            }

            // out put
            zip.generateAsync({type:'blob'})
                .then(content => {
                    saveAs(content, 'all_file.zip')
                })
        },
        resultFileDelete: function(file) {
            if (file.task_result_file_seq_no < 0){
                // メモリから削除
                URL.revokeObjectURL(file.file_data)
            }
            for (const i in this.form.result_files) {
                if (this.form.result_files[i].task_result_file_seq_no === file.task_result_file_seq_no ){
                    this.form.result_files.splice(i, 1)
                    break
                }
            }
        },
        setData: function(data) {
            this.setHiddenFormData(data)
            if (data.task_result_content) {
                this.setFixedData(data.task_result_content)
            }
            this.task_exclusion = (data.task_status == 3)? true: false
            if (data.is_done_task || this.task_exclusion) {
                this.isDoneTask = true
            }
        },
        getFileSize() {
            // var file = new File(img_data, "tmp")
        },
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {

                // base64データが入っている場合は処理しない
                if ('data' == file.file_data.substring(0,4)) resolve(file)

                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob'
                xhr.onload = () => {
                    var reader = new window.FileReader()
                    reader.readAsDataURL(xhr.response)
                    reader.onloadend = () => {
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
        // 保存
        submit:async function() {

            this.$emit('input', true)

            this.closeModal()
            this.scrollToTop()

            // 送信用データの生成
            const sendForm = JSON.parse(JSON.stringify(this.form))

            // 画像データをblobURL -> base64
            const uploadFiles = sendForm.check_files.filter(check_file => (check_file.task_result_file_seq_no < 0))
            const convert = this.convertToBase64
            await Promise.all(uploadFiles.map(async check_file => check_file = await convert(check_file)))
            for (let i = 0; i <  sendForm.check_files.length; i++){
                sendForm.check_files[i].task_result_file_seq_no = i
                delete sendForm.check_files[i].type
            }

            // 結果の画像をblobURL -> base64
            await Promise.all(sendForm.result_files.map(async result_file =>await convert(result_file)))
            for (let i = 0; i <  sendForm.result_files.length; i++){
                sendForm.result_files[i].task_result_file_seq_no = i + sendForm.check_files.length
            }

            axios.post('/api/biz/abbey_check/abbey_check/store',  sendForm)
                .then((res) => {
                    if (res.data.result == 'success') {
                        this.$emit('input', false)
                        eventHub.$emit('open-notify-modal', {message: this.$t('common.message.saved'), emitMessage: this.notify_modal_emit_message})

                    } else if (res.data.result == 'inactivated') {
                        console.log(res.data);
                        eventHub.$emit('open-notify-modal', {message: this.$t('tasks.alert.message.save_failed_by_inactivated') })
                    } else {
                        console.log(res.data);
                        eventHub.$emit('open-notify-modal', {message: this.$t('common.message.save_failed') })
                    }
                    this.$emit('input', false)
                })
                .catch((err) => {
                    console.log('err')
                    console.log(err);
                    this.$emit('input', false)
                    eventHub.$emit('open-notify-modal', {message: this.$t('common.message.save_failed') })
                })

        },
        openModal: function(resultType) {
            let message = ''
            this.form.process_info.results.type = (resultType == '') ? _const.TASK_RESULT_TYPE.DONE : resultType

            if (resultType == _const.TASK_RESULT_TYPE.CONTACT) {
                // 「不明あり」の場合
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.CONTACT
                message = this.$t('biz.abbey_check.common._modal.irregular.prefix' + resultType)
                eventHub.$emit('open-irregular-modal',{
                    'message': message,
                })
            } else if (resultType == _const.TASK_RESULT_TYPE.HOLD) {
                // 「保留」の場合
                this.form.process_info.results.type = _const.TASK_RESULT_TYPE.HOLD
                message = this.$t('biz.abbey_check.common._modal.irregular.prefix' + resultType) + this.$t('biz.abbey_check.abbey_check._modal.unclear.p2')
                eventHub.$emit('open-modal',{
                    'message': message,
                })
            } else {
                message = this.$t('biz.abbey_check.abbey_check._modal.fix.p1') + this.$t('biz.abbey_check.abbey_check._modal.fix.p2')
                eventHub.$emit('open-modal',{
                    'message': message
                })
            }
        },
        closeModal: function() {
            eventHub.$emit('close-abbey-result-modal')
            eventHub.$emit('close-modal')
        },
        setHiddenFormData: function(data) {
            this.form.task_id = data.task_id
            this.form.step_id = data.request_work.step_id
            this.form.started_at = data.started_at.date
            this.form.request_work_id = data.request_work.id
            this.form.request_id = data.request_work.request_id
            this.form.request_mail_id = data.request_mail.id
        },
        setFixedData: function(data) {
            // 配列の長さを0
            this.form.client_name = data.client_name
            this.form.check_files.splice(0);
            this.form.check_files = data.check_files
            this.form.result_files = data.result_files
            if (data.results) {
                this.form.process_info.results = data.results
            }
        }
    }
}
</script>
