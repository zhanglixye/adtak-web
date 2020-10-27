<template>
    <v-dialog v-model="createFormDialog" persistent width="800">
        <v-card>
            <v-card-title class="headline" primary-title>{{ $t('requests.detail.additional_info.add') }}</v-card-title>
            <v-card-text>
                <form>
                    <v-textarea
                        v-model="content"
                        outline
                        hide-details
                        label="テキストを入力..."
                    ></v-textarea>

                    <div v-if="uploadFiles.length > 0">
                        <div class="font-weight-bold mt-3">{{ $t('file_upload.uploading_files') }}</div>
                        <span>
                            <span v-for="(file, index) in uploadFiles" :key="index">
                                <span>
                                    <v-chip
                                        close
                                        color="light-blue lighten-2"
                                        small
                                        outline
                                        @input="removeUploadingFile(index)"
                                    >{{ file.file_name }}</v-chip>
                                </span>
                            </span>
                        </span>
                    </div>

                    <!-- ファイルアップロード -->
                    <div class="mt-1">
                        <file-upload :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types"></file-upload>
                        <div class="caption text-xs-right">
                            <span>※ {{ $t('file_upload.allowed_extensions') }}</span>&ensp;
                            <span v-for="(item, index) in allow_file_type_names" :key="item">
                                <span>{{ item }}</span>
                                <span v-show="(index + 1 < allow_file_type_names.length)" class="ml-1 mr-1">|</span>
                            </span>
                        </div>
                    </div>
                    <!-- / ファイルアップロード -->

                </form>
            </v-card-text>

            <!-- クライアントページへの表示設定 -->
            <v-card-text>
                <v-layout row wrap>
                    <v-flex class="pl-3" xs12 sm6>
                        <v-switch
                            v-model="isOpenToClient"
                            :label="$t('requests.detail.client.switch_label') + ': ' + textForIsOpenToClientSwitcher"
                            color="primary"
                        ></v-switch>
                    </v-flex>
                    <v-flex class="pl-3" xs12 sm6>
                        <v-switch
                            v-model="isOpenToWork"
                            :label="$t('requests.detail.work.switch_label') + ': ' + textForIsOpenToWorkSwitcher"
                            color="primary"
                        ></v-switch>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <!-- / クライアントページへの表示設定 -->

            <!-- 処理ボタン -->
            <v-card-actions class="justify-center">
                <v-btn @click="close()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click="save()">{{ $t('common.button.save') }}</v-btn>
            </v-card-actions>
            <!-- / 処理ボタン -->
        </v-card>
    </v-dialog>
</template>

<script>
import requestDetailMixin from '../../../../../mixins/Admin/requestDetailMixin'

import FileUpload from '../../../../Atoms/Upload/FileUpload.vue'

export default {
    name: 'additionalInfoFormDialog',
    mixins: [requestDetailMixin],
    props: {
        requestId: { type: Number, required: true },
        requestWorkId: { type: Number },
        createFormDialog: { type: Boolean, required: true },
    },
    data: () => ({
        //loading
        loading: false,
        content: '',
        isPublic: false,
        isOpenToClient: false,
        isOpenToWork: false,

        // ファイルアップロード用
        file_upload_emit_message: 'append-files-for-create-request-add-info',

        // 仮
        uploadFiles: []
    }),
    components:{
        // ProgressCircular
        FileUpload
    },
    created () {
        let self = this

        // ファイルアップロード用
        eventHub.$on(this.file_upload_emit_message, function(data){
            // check_filesに追加
            for (const file of data.file_list){
                const result = {
                    err_description: '',
                    file_name: file.name,
                    file_size: file.size,
                    file_data: file.data,
                    file_path: '',
                    type: file.type,
                }
                self.uploadFiles.push(result)
            }
        })

        eventHub.$on('after-success', function(){
            // DB登録成功後
            self.$emit('callGetAdditionalInfos')
            self.content = ''
            self.$emit('update:createFormDialog', false)
        })
    },
    mounted () {
    },
    computed: {
        textForIsOpenToClientSwitcher () {
            if (this.isOpenToClient) {
                return 'on'
            } else {
                return 'off'
            }
        },
        textForIsOpenToWorkSwitcher () {
            return this.isOpenToWork ? 'on' : 'off'
        }
    },
    methods: {
        save () {
            this.saveRequestAdditionalInfo()
        },
        close () {
            this.$emit('update:createFormDialog', false)
        },
        saveRequestAdditionalInfo:async function () {
            this.loading = true

            const files = this.uploadFiles
            const convert = this.convertToBase64
            console.log('promise all start')
            await Promise.all(files.map(async upload_file => upload_file = await convert(upload_file)))
            console.log('promise all end')
            for (let i = 0; i <  this.uploadFiles.length; i++){
                delete this.uploadFiles[i].type
            }

            axios.post('/api/request_additional_infos/store',{
                request_id: this.requestId,
                request_work_id: this.requestWorkId,
                content: this.content,
                request_additional_info_attachments: this.uploadFiles,
                is_open_to_client: this.isOpenToClient,
                is_open_to_work: this.isOpenToWork,
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        this.uploadFiles = []
                        eventHub.$emit('open-notify-modal', { message: '保存しました。', emitMessage: 'after-success'})
                    } else if (res.data.result == 'warning') {
                        eventHub.$emit('open-notify-modal', { message: '保存に失敗しました。' })
                    } else if (res.data.result == 'error') {
                        eventHub.$emit('open-notify-modal', { message: '保存に失敗しました。' })
                    }
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: '保存に失敗しました。' })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        removeUploadingFile (index) {
            this.uploadFiles.splice(index, 1)
        }
    }
}
</script>
