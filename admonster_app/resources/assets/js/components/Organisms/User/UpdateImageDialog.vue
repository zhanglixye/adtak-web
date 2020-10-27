<template>
    <v-layout row justify-center>
        <v-dialog v-model="dialog" persistent max-width="500">

            <v-card>
                <v-card-title class="headline grey lighten-2">{{ $t('user.dialog.update_image_title') }}</v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12>
                                <div>
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
                                <file-upload :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types" :maxFileCnt ="max_file_cnt" ></file-upload>
                                <div class="caption text-xs-right mb-1">
                                    <span>※ {{ $t('file_upload.allowed_extensions') }}</span>&ensp;
                                    <span v-for="(item, index) in allow_file_type_names" :key="index">
                                        <span>{{ item }}</span>
                                        <span v-show="(index + 1 < allow_file_type_names.length)" class="ml-1 mr-1">|</span>
                                    </span>
                                </div>
                                <!-- / ファイルアップロード -->
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" text @click.native="dialog = false">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn color="primary" text @click.native="updateImage" :disabled="imageValidateFlg">{{ $t('common.button.update') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>

<script>
import FileUpload from './../../Atoms/Upload/FileUpload'
export default {
    name: 'UpdateImageDialog',
    data:()=>({
        dialog: false,
        max_file_cnt: 1,
        allow_file_types: [
            'image/jpeg','image/png'
        ],
        uploadFiles: [],
        file_upload_emit_message: 'update-user-image',
        allow_file_type_names: [
            '.jpeg',
            '.png',
            '.jpg',
            '.JPG'
        ],

    }),
    components:{
        FileUpload
    },
    created() {
        // ファイルアップロード用
        let self = this
        eventHub.$on(this.file_upload_emit_message, function(data){
            self.uploadFiles = []
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
    },
    computed: {
        imageValidateFlg () {
            return this.uploadFiles.length !== 1;
        }
    },
    methods: {
        show() {
            this.dialog = true
            this.uploadFiles = []
        },
        removeUploadingFile (index) {
            this.uploadFiles.splice(index, 1)
        },
        updateImage() {
            this.$emit('update-image', this.uploadFiles)
        }

    },
    watch:{

    }
}
</script>

<style scoped>

</style>
