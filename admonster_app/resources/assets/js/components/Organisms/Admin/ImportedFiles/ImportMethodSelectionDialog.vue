<template>
    <v-dialog v-model="fileImportDialog" persistent width="600">
        <v-card id="fileImportDialog">
            <v-card-title class="headline">
                {{ $t('imported_files.import_dialog.header') }}
            </v-card-title>
            <v-card-text>
                <v-radio-group v-model="radios" style="max-height: 30px;" v-if="!isOrderList">
                    <v-radio
                        color="primary"
                        :label="$t('imported_files.import_dialog.form.select_order.radio_group_label')"
                        value="1"
                    ></v-radio>
                </v-radio-group>
                <v-text-field
                    :label="$t('imported_files.import_dialog.form.select_order.label')"
                    :placeholder="placeFileName"
                    v-model="fileName"
                    counter="256"
                    :rules="[max]"
                    :hint="$t('imported_files.import_dialog.form.select_order.hint')"
                ></v-text-field>
                <v-radio-group v-model="radios" style="max-height: 30px;" v-if="!isOrderList">
                    <v-radio
                        color="primary"
                        :label="$t('imported_files.import_dialog.form.select_business.radio_group_label')"
                        value="2"
                    ></v-radio>
                </v-radio-group>
                <v-select
                    v-if="!isOrderList"
                    item-value="id"
                    item-text="name"
                    name="business_id"
                    :items="businesses"
                    v-model="selectedBusinessId"
                    prepend-icon="work"
                    hide-details
                    :label="$t('imported_files.import_dialog.form.select_business.label')"
                    :placeholder="$t('imported_files.import_dialog.form.select_business.placeholder')"
                ></v-select>
            </v-card-text>
            <v-form :action="actionPath" method="post" @submit.prevent="formSubmit" ref="form">
                <input type="hidden" name="_token" :value="csrf">
                <input type="hidden" name="order_name" :value="fileName === '' ? placeFileName : fileName">
                <input type="hidden" name="file_name" :value="uploadFile.tmpFileInfo.file_name">
                <input type="hidden" name="tmp_file_dir" :value="uploadFile.tmpFileInfo.file_dir">
                <input
                    type="hidden"
                    name="tmp_file_path"
                    :value="uploadFile.tmpFileInfo.file_path"
                >
                <!-- ボタン -->
                <v-card-actions class="justify-center">
                    <v-btn @click="close()" v-if="isFileImport">
                        {{ $t('common.button.cancel') }}
                    </v-btn>
                    <v-btn @click="change" v-else>{{ $t('common.button.back') }}</v-btn>
                    <v-btn type="submit" color="primary" :disabled="disabledButton">
                        {{ $t('imported_files.import_dialog.button.import') }}
                    </v-btn>
                </v-card-actions>
                <!-- / ボタン -->
            </v-form>
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
        <progress-circular v-if="loading"></progress-circular>
    </v-dialog>
</template>

<script>
import store from '../../../../stores/Admin/ImportedFiles/store'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        AlertDialog,
        ProgressCircular
    },
    props: {
        fileImportDialog: { type: Boolean, required: false, default: false },
        isFileImport: { type: Boolean, required: false, default: false },
        isOrderList: { type: Boolean, required: false, default: false }
    },
    data: () => ({
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        eventHub: eventHub,
        loading: false,
        radios: '1',
        placeFileName: store.state.uploadFile.file_name.replace(/\.[^/.]+$/, ''),
        fileName: store.state.uploadFile.file_name.replace(/\.[^/.]+$/, ''),

        selectedBusinessId: null,
        businesses: [],
        file_upload_emit_message: 'file-upload',
        uploadFile: store.state.uploadFile,
        max_file_cnt: 1,
    }),
    computed: {
        max () {
            return this.radios !== '1' || this.fileName.length <= 256 || ''
        },
        disabledButton () {
            return this.radios === '1' && this.fileName.length > 256
        },
        actionPath () {
            return '/imported_files/order_setting'
        }
    },
    created () {
        this.getManagedBusinesses()
    },
    methods: {
        formSubmit () {
            if (this.radios === '1') {
                this.$refs.form.$el.submit()
            } else {
                const messages = []
                if (!this.selectedBusinessId) {
                    messages[0] = Vue.i18n.translate('imported_files.import_dialog.validation.business')
                }
                if (Object.keys(this.uploadFile).length < 1) {
                    messages[1] = Vue.i18n.translate('imported_files.import_dialog.validation.file')
                }
                if (messages.length > 0) {
                    this.$refs.alert.show(messages.join('<br />'), () => {})
                    return false
                }
                const param = '?business_id=' + encodeURIComponent(this.selectedBusinessId) + '&file_name=' + encodeURIComponent(this.uploadFile.tmpFileInfo.file_name) + '&tmp_file_dir=' + encodeURIComponent(this.uploadFile.tmpFileInfo.file_dir) + '&tmp_file_path=' + encodeURIComponent(this.uploadFile.tmpFileInfo.file_path)

                window.location.href = '/imported_files/confirm' + param
            }
        },
        close () {
            if (Object.keys(this.uploadFile).length > 0) {
                // 一時ファイルを削除
                this.deleteTmpFile(this.uploadFile.tmpFileInfo)
            }
            this.$emit('update:fileImportDialog', false)
        },
        change () {
            this.$emit('update:importMethodSelectionDialog', true)
            this.$emit('update:fileImportDialog', false)
        },
        deleteTmpFile (filePathInfo) {
            // 一時ファイルを削除
            axios.post('/api/imported_files/tmp_file_delete', {
                tmp_file_info: filePathInfo
            })
                .then((res) => {
                    console.log(res)
                    store.commit('resetUploadFile')
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('imported_files.error_messages.delete_file') })
                })
                .finally(() => {
                    this.loading = false
                    this.uploadFile = {}
                })
        },
        getManagedBusinesses () {
            this.loading = true
            axios.get('/api/imported_files/target_business_list')
                .then((res) => {
                    if (res.data.list.length > 0) {
                        this.businesses = res.data.list
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('imported_files.error_messages.get_business_list') })
                })
                .finally(() => {
                    this.loading = false
                })
        }
    }
}
</script>

<style scoped>
/* ラベルが少し上に上がっていたので中央に移動 */
.radio >>> .v-label {
    margin-bottom: 0;
    text-overflow: ellipsis;
    overflow: hidden;
    display: block;
}
</style>