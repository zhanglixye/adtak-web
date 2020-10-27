<template>
    <div>
        <v-card>
            <v-card-title primary-title>
                <div>
                    <h3 class="black--text">{{ $t('user.list.user_information') }}</h3>
                </div>
            </v-card-title>
            <v-card-text>
                <v-container grid-list-md>
                    <v-layout>
                        <v-flex xs12 sm3 md3 nowrap>
                            <template>
                                <div>
                                    <v-avatar size="80px" :tile="false" >
                                        <img  style="margin-bottom: 35px" :src="user.user_image_path">
                                    </v-avatar>
                                    <a style="position: absolute;margin-left: 14px;margin-top: 13px"
                                       @click="openImageUploadDialog()"
                                     >{{ $t('user.dialog.update_image_title') }}</a>
                                </div>
                            </template>
                            <v-text-field
                                    v-model="user.name"
                                    :label="$t('list.column.full_name')"
                                    box
                                    readonly
                            ></v-text-field>
                            <v-text-field
                                    v-model="user.email"
                                    :label="$t('list.column.email')"
                                    box
                                    readonly
                            ></v-text-field>
                            <v-btn color="primary" @click="openUpdatePasswordDiaLog()">{{ $t('user.action.update_password')}}</v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
        </v-card>
        <v-divider></v-divider>
        <v-card>
            <v-card-title primary-title>
                <div>
                    <h3 class="black--text">{{ $t('user.list.user_profile') }}</h3>
                </div>
            </v-card-title>
            <v-card-text>
                <v-container grid-list-md>
                    <v-layout wrap row>
                        <v-flex xs12 sm3 md3>
                            <v-text-field
                                    v-model="user.nickname"
                                    :label="$t('list.column.nickname')"
                            ></v-text-field>
                            <v-select
                                    :items="items"
                                    item-text="label"
                                    item-value="value"
                                    :label="$t('list.column.sex')"
                                    required
                                    v-model="user.sex"
                            ></v-select>
                        </v-flex>
                    </v-layout>
                    <v-layout>
                        <v-flex>
                            <span>{{ $t('user.list.birthday') }}</span>
                        </v-flex>
                    </v-layout>
                    <v-layout>
                        <v-flex xs12 sm1 md1>
                            <v-select :label="$t('user.list.year_label')" v-model="formData.year" :items="year"></v-select>
                        </v-flex>
                        <v-flex xs12 sm1 md1>
                            <v-select :label="$t('user.list.month_label')" v-model="formData.month" :items="month"></v-select>
                        </v-flex>
                        <v-flex xs12 sm1 md1>
                            <v-select :label="$t('user.list.day_label')" v-model="formData.day" :items="day" :disabled="dayClickFlg"></v-select>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-btn large color="primary" @click="updateProfile()">{{ $t('common.button.update') }}</v-btn>

            </v-card-actions>
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
        <update-password-dialog
            ref="UpdatePasswordDialog"
            :user ="user"
            @update-password="updatePassword"
        >
        </update-password-dialog>
        <update-image-dialog
            ref="UpdateImageDialog"
            @update-image="updateUserImage"
        ></update-image-dialog>
        <progress-circular v-if="loading"></progress-circular>
    </div>
</template>

<script>
import UpdatePasswordDialog from './UpdatePasswordDialog'
import ProgressCircular from './../../Atoms/Progress/ProgressCircular'
import AlertDialog from './../../Atoms/Dialogs/AlertDialog'
import UpdateImageDialog from './UpdateImageDialog'
export default {
    name: 'UserSettingDetail',
    data:() =>({
        loading: false,
        user:{
            'name': '',
            'email': '',
            'user_image_path': '',
            'birthday': '',
            'sex': '',
            'image': [],
        },
        year:[],
        month:[],
        formData:{
            year: null,
            month:null,
            day: null
        },

    }),
    computed:{
        dayClickFlg (){
            let dayClickFlg = true
            if (this.day.length != 0){
                dayClickFlg = false
            }
            return dayClickFlg
        },
        items (){
            return [
                { label: this.$t('list.sex_category.male'), value: 1 },
                { label: this.$t('list.sex_category.female'), value: 2 },
                { label: this.$t('list.sex_category.other'), value: 3 },
            ]
        },
        day (){
            let day = [];
            let days = this.getDaysInMonth(this.formData.year, this.formData.month)
            for (let i = 1; i <= days ; i++) {
                day.push(i)
            }
            return day
        },
        birthdayString() {
            let birthdayString = ''
            if (this.formData.year != null && this.formData.month != null && this.formData.day != null){
                birthdayString= this.formData.year.toString() + '/' + this.formData.month.toString() + '/' + this.formData.day.toString()
                birthdayString = moment(birthdayString, 'YYYY/M/D').format('YYYY-MM-DD')
            }
            return birthdayString
        }
    },
    components:{
        UpdatePasswordDialog,
        ProgressCircular,
        AlertDialog,
        UpdateImageDialog
    },
    created() {
        this.getUserDetail()
    },
    methods: {
        updateProfile(){
            if (this.formData.year != null && (this.formData.month == null || this.formData.day == null)){
                this.$refs.alert.show(this.$t('user.dialog.error_message.err_date'))
                return
            }
            if (this.birthdayString != ''){
                this.user.birthday = this.birthdayString
            }
            this.loading = true
            axios.post('/api/user/updateProfile', {
                user_id : this.user.id,
                nickname : this.user.nickname,
                birthday : this.user.birthday,
                sex : this.user.sex,
            })
                .then((res) =>{
                    console.log(res)
                }).catch((error) => {
                    console.log(error)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }).finally(() => {
                    this.loading = false
                })
        },
        updatePassword(userId,oldpassword,newPassword){
            this.loading = true
            axios.post('/api/user/updatePassword', {
                user_id : userId,
                old_password : oldpassword,
                new_password : newPassword
            })
                .then((res) =>{
                    if (res.data.status === 200){
                        console.log(res)
                        this.$refs.UpdatePasswordDialog.close()
                        document.getElementById('logout-form').submit()
                    } else if (res.data.message === 'old_password is wrong'){
                        this.$refs.alert.show(this.$t('user.dialog.error_message.old_password_error'))
                    }
                }).catch((error) => {
                    console.log(error)
                    this.$refs.alert.show(this.$t('common.message.internal_error'))
                }).finally(() => {
                    this.loading = false
                })

        },
        openUpdatePasswordDiaLog:async function () {
            this.$refs.UpdatePasswordDialog.show()
        },
        openImageUploadDialog:async function () {
            this.$refs.UpdateImageDialog.show()
        },
        getUserDetail(){
            this.loading = true
            axios.get('/api/user/')
                .then((res) => {
                    this.user = res.data.user
                    if (this.user.birthday != null){
                        this.formData.year = parseInt(this.user.birthday.split('-')[0])
                        this.formData.month = parseInt(this.user.birthday.split('-')[1])
                        this.formData.day = parseInt(this.user.birthday.split('-')[2])
                    }
                    this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        updateUserImage: async function(uploadFiles){
            console.log(uploadFiles)
            this.loading = true
            // 画像データをblobURL -> base64
            const files = uploadFiles
            const convert = this.convertToBase64
            await Promise.all(files.map(async upload_file => upload_file = await convert(upload_file)))
            for (let i = 0; i <  files.length; i++){
                delete files[i].type
            }
            axios.post('/api/user/updateUserImage',
                {
                    image_file: files,
                    user_id: this.user.id
                })
                .then((res) => {
                    if (res.data.status === 200){
                        location.reload(true)
                        this.loading = false
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {

                // base64データが入っている場合は処理しない
                if ('data' == file.file_data.substring(0, 4)) resolve(file)

                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob'
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
        init(){
            this.getYear();
            this.getMonth();
        },
        getYear(){
            let date = new Date;
            let current_year = date.getFullYear();
            for (let i = 0; i < 100; i++) {
                let y = current_year - i;
                this.year.push(y);
            }
        },
        getMonth(){
            for (let i = 1; i < 13; i++) {
                this.month.push(i);
            }
        },
        getDaysInMonth(year, month) {
            month = parseInt(month, 10);
            let d = new Date(year, month, 0);
            return d.getDate();
        },
    },
    mounted() {
        this.init();
    }
}
</script>

<style scoped>

</style>
