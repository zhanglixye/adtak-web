<template>
    <v-layout row justify-center>
        <v-dialog v-model="dialog" persistent max-width="600px">
            <v-card>
                <v-card-title class="grey lighten-2">
                    <span class="headline">{{ $t('list.add_user') }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <v-layout wrap>
                            <v-flex xs12 sm6 md6>
                                <v-text-field :label="$t('list.column.surname')" required v-model="user.surname" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md6>
                                <v-text-field :label="$t('list.column.name')" required v-model="user.name" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.email')" v-model="user.email" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.nickname')" required v-model="user.nickname" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md4>
                                <date-picker-without-buttons
                                        :label="$t('list.column.birthday')"
                                        placeholder="yyyy/mm/dd"
                                        :dateValue="user.birthday"
                                        @change="user.birthday = $event"
                                ></date-picker-without-buttons>
                            </v-flex>
                            <v-flex xs12 sm6 md4>
                                <v-select
                                        :items="items"
                                        item-text="label"
                                        item-value="value"
                                        :label="$t('list.column.sex')"
                                        required
                                        v-model="user.sex"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12 sm6 md4>
                                <v-text-field :label="$t('list.column.postal_code')" required v-model="user.postal_code" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md6>
                                <v-text-field :label="$t('list.column.tel')" required v-model="user.tel" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12 sm6 md6>
                                <v-select
                                        :items="timezoneItems"
                                        item-text="label"
                                        item-value="value"
                                        :label="$t('list.column.timezone')"
                                        required
                                        v-model="user.timezone"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.address')" required v-model="user.address" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.password')" required v-model="user.password" append-icon="autorenew" @click:append="createRandomPassword"></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field :label="$t('list.column.remarks')" required v-model="user.remarks" clearable></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <div>
                                    <div class="v-label theme--light">{{ $t('list.column.user_image') }}</div>
                                    <span>
                                        <span v-for="(file, index) in user.image" :key="index">
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
                                <file-upload v-show="user.image.length === 1 ? false : true" :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types" :maxFileCnt ="max_file_cnt" ></file-upload>
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
                    <v-btn color="primary" text @click.native="addUser" :disabled="validateFlg">{{ $t('common.button.add') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-layout>
</template>

<script>
import DatePickerWithoutButtons from  './../../../Atoms/Pickers/DatePickerWithoutButtons'
import FileUpload from './../../../Atoms/Upload/FileUpload';
export default {
    components: {
        DatePickerWithoutButtons,
        FileUpload

    },
    data: () => ({
        dialog: false,
        show1: false,
        user: {
            'surname': '',
            'name': '',
            'email': '',
            'nickname': '',
            'birthday': '',
            'sex': '',
            'postal_code': '',
            'tel': '',
            'address': '',
            'password': '',
            'remarks': '',
            'user.timezone':'Asia/Tokyo',
            'image': []
        },
        items: [
            { label: Vue.i18n.translate('list.sex_category.male'), value: 1 },
            { label: Vue.i18n.translate('list.sex_category.female'), value: 2 },
            { label: Vue.i18n.translate('list.sex_category.other'), value: 3 },
        ],
        timezoneItems: [
            { label: Vue.i18n.translate('common.timezone.Asia/Shanghai'), value: 'Asia/Shanghai' },
            { label: Vue.i18n.translate('common.timezone.Asia/Tokyo'), value: 'Asia/Tokyo' },
        ],

        uploadFlg: true,
        max_file_cnt: 1,
        allow_file_types: [
            'image/jpeg','image/png'
        ],
        uploadFiles: [],
        file_upload_emit_message: 'append-files-for-update-request-add-info',
        allow_file_type_names: [
            '.jpeg',
            '.png',
            '.jpg',
            '.JPG'
        ],
    }),
    created() {
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
                // self.uploadFiles.push(result)
                self.user.image.push(result)
            }
        })
    },
    methods: {
        show(){
            this.user = {
                'surname': '',
                'name': '',
                'email': '',
                'nickname': '',
                'birthday': '',
                'sex': '',
                'postal_code': '',
                'tel': '',
                'address': '',
                'password': '',
                'remarks': '',
                'timezone': 'Asia/Tokyo',
                'image': []
            }
            this.dialog = true

        },
        close(){
            this.dialog = false
        },
        removeUploadingFile (index) {
            this.user.image.splice(index, 1)
        },
        addUser() {
            this.$emit('add-user', this.user)

        },
        createRandomPassword(){
            const pasArr = [
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                '_', '-', '$', '%', '&', '@', '+', '!'
            ];
            let password = '';
            const pasArrLen = 8;
            for (let i=0; i<pasArrLen; i++){
                let ran = Math.floor(Math.random() * pasArr.length);
                password += pasArr.splice(ran, 1)[0]
            }
            this.user.password = password
        }
    },
    computed: {
        validateFlg () {
            if (this.user.surname != '' && this.user.name != '' && this.user.email != '' && this.user.password != ''){
                return false
            } else {
                return true
            }
        }
    }

}
</script>

<style scoped>

</style>
