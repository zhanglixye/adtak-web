<template>
<v-app id="file-import">
    <app-menu :drawer="drawer"></app-menu>
    <app-header
        :title="$t('imported_files.index.title')"
    ></app-header>
    <v-content id="list">
        <v-container fluid grid-list-md>
            <v-layout row wrap>
                <v-flex xs12>
                    <!-- 最新n件のファイルリスト用コンポーネント -->
                    <latest-imported-file-list></latest-imported-file-list>
                </v-flex>
                <v-flex xs12>
                    <!-- ファイル検索用コンポーネント -->
                    <imported-file-search-condition :search-condition-detail-show="searchConditionDetailShow"></imported-file-search-condition>
                </v-flex>
                <v-flex xs12 v-if="!searchList()">
                    <div class="mb-3 mt-3">
                        <file-upload :emit_message="file_upload_emit_message" :allow_file_types="allow_file_types" :max-file-cnt="max_file_cnt"></file-upload>
                    </div>
                </v-flex>
                <v-flex xs12>
                    <!-- ファイル一覧用コンポーネント -->
                    <imported-file-search-list v-if="searchList()" :file-import-dialog.sync="fileImportDialog"></imported-file-search-list>
                </v-flex>
            </v-layout>
        </v-container>
        <file-import-dialog
            :is-file-import.sync="isFileImport"
            :file-import-dialog.sync="fileImportDialog"
            :import-method-selection-dialog.sync="importMethodSelectionDialog"
            v-if="fileImportDialog"
        ></file-import-dialog>
        <import-method-selection-dialog
            :file-import-dialog.sync="importMethodSelectionDialog"
            :import-method-selection-dialog.sync="fileImportDialog"
            v-if="importMethodSelectionDialog"
            :isFileImport="isFileImport"
        ></import-method-selection-dialog>
    </v-content>

    <notify-modal></notify-modal>
    <to-top></to-top>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import store from '../../../../stores/Admin/ImportedFiles/store'
import LatestImportedFileList from './../../../Organisms/Admin/ImportedFiles/LatestImportedFileList'
import ImportedFileSearchCondition from './../../../Organisms/Admin/ImportedFiles/ImportedFileSearchCondition'
import ImportedFileSearchList from './../../../Organisms/Admin/ImportedFiles/ImportedFileSearchList'
import FileImportDialog from './../../../Organisms/Admin/ImportedFiles/FileImportDialog'
import FileUpload from '../../../Atoms/Upload/FileUpload'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import ToTop from '../../../Atoms/Buttons/ToTop'
import ImportMethodSelectionDialog from './../../../Organisms/Admin/ImportedFiles/ImportMethodSelectionDialog'


export default {
    components:{
        LatestImportedFileList,
        ImportedFileSearchCondition,
        ImportedFileSearchList,
        FileImportDialog,
        FileUpload,
        NotifyModal,
        ToTop,
        ImportMethodSelectionDialog
    },
    props: {
        eventHub: eventHub,
        inputs: { type: Function }
    },
    data: ()=> ({
        drawer: true,
        fileImportDialog: false,
        importMethodSelectionDialog: false,
        isFileImport: false,
        searchConditionDetailShow: false,

        // ファイルアップロード用
        importTargetBusinessId: 1,
        uploadFile: store.state.uploadFile,
        file_upload_emit_message: 'append-files-for-update-request-add-info',

        allow_file_types: [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        max_file_cnt: 1,
    }),
    created() {
        let self = this
        const params = this.inputs()
        if (params) {
            // params.show.searchList = true
            // GETパラメータで検索条件が渡された場合はstoreの検索条件を更新
            store.commit('resetSearchParams')
            store.commit('setSearchParams', { params: params })
            // 検索結果一覧を表示
            store.commit('openSearchList')
            // GETパラメータでの検索時のみデフォルトで検索の詳細条件を展開
            this.searchConditionDetailShow = true
        }

        // ファイルアップロード用
        eventHub.$on(this.file_upload_emit_message, function(data) {
            // check_fileに追加
            const file = data.file_list[0]
            const result = {
                err_description: '',
                file_name: file.name,
                file_size: file.size,
                file_data: file.data,
                file_path: '',
                type: file.type,
            }
            self.uploadFile = result

            // ローカルに一時保存
            self.moveToTemporary(self.uploadFile)
        })
    },
    methods: {
        searchList () {
            return store.state.show.searchList
        },
        latestImportedFileList () {
            // TODO 下記、最新ファイル一覧の表示切替変数の使用
            return store.state.show.latest_imported_file_list
        },
        moveToTemporary: async function (uploadFile) {
            this.loading = true
            // 画像データをblobURL -> base64
            // const file = this.uploadFile
            const file = uploadFile
            const convert = this.convertToBase64
            await convert(file)
            delete this.uploadFile.type
            axios.post('/api/imported_files/tmp_upload',{
                upload_file: this.uploadFile
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        this.uploadFile.tmpFileInfo = res.data.tmp_file_info
                        store.commit('setUploadFile', this.uploadFile)
                        this.importMethodSelectionDialog = true
                        this.isFileImport = true
                    } else {
                        eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('file_upload.message.upload_failed') });
                    }
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('file_upload.message.upload_failed') });
                })
                .finally(() => {
                    this.loading = false
                });
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
        }
    }
}
</script>
