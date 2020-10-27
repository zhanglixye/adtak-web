<template>
<v-app id="file-import-confirm">
    <app-menu :drawer="drawer"></app-menu>
    <app-header
        :title="$t('imported_files.index.title')"
        :subtitle="$t('imported_files.index.subtitle')"
    ></app-header>
    <v-content>
        <v-container fluid grid-list-md>
       <!-- <file-contents></file-contents> -->
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-title class="pb-1">
                            <v-icon>insert_drive_file</v-icon>
                            <span>
                                <span class="subheading">{{ $t('imported_files.file_name') }} : </span>
                                <span>{{ inputs.tmp_file_info.file_name }}</span>
                            </span>
                        </v-card-title>
                        <v-card-text class="pt-0">
                            <v-layout row wrap align-center>
                                <div>
                                    <template>
                                        <span class="caption">
                                            <span>【{{ $t('imported_files.import_config') }}】</span>
                                            <span>{{ $t('imported_files.header_row_num') }} : {{ importConfInfo.header_row_num }}</span>
                                            <span class="mr-1">,</span>
                                            <span>{{ $t('imported_files.data_start_row_num') }} : {{ importConfInfo.data_start_row_num }}</span>
                                        </span>
                                    </template>
                                </div>
                                <v-spacer></v-spacer>
                                <div>
                                     <template>
                                        <span class="mr-3">
                                            <span>{{ $t('imported_files.import_target_request_cnt') }}:<span class="title pl-1 pr-1">{{ rows.length - (orgHeaderRowNum ? 1 : 0) }}</span>{{ $t('list.case') }}</span>
                                        </span>
                                    </template>
                                    <template>
                                        <span class="mr-2">
                                            <span v-if="errors.length > 0">
                                                <span @click="errorItemListDialog = !errorItemListDialog">{{ $t('imported_files.error_cnt') }}:<a class="red--text title pl-1 pr-1">{{ errors.length }}</a></span>{{ $t('imported_files.points') }}
                                            </span>
                                            <span v-else>
                                                <span>{{ $t('imported_files.error_cnt') }}:<span class="title pl-1">{{ errors.length }}</span></span>{{ $t('imported_files.points') }}
                                            </span>
                                        </span>
                                    </template>
                                    <template>
                                        <v-tooltip top>
                                            <template v-if="errors.length > 0">
                                                <v-btn slot="activator" icon class="mr-0 ml-1">
                                                    <v-icon :color="showOnlyErrors ? 'red' : 'grey darken-1'" @click="pickUpErrors()">mdi-filter-outline</v-icon>
                                                </v-btn>
                                                <span v-if="showOnlyErrors">{{ $t('imported_files.see_all') }}</span>
                                                <span v-else>{{ $t('imported_files.see_only_errors') }}</span>
                                            </template>
                                            <template v-else>
                                                <v-btn slot="activator" icon disabled color="grey darken-1" class="mr-0 ml-1">
                                                    <v-icon>mdi-filter-outline</v-icon>
                                                </v-btn>
                                                <span>{{ $t('imported_files.no_errors') }}</span>
                                            </template>
                                        </v-tooltip>
                                    </template>
                                    <template>
                                        <v-tooltip top>
                                            <v-btn slot="activator" icon class="mr-0 ml-1">
                                                <v-icon
                                                    @click="switchTable"
                                                    :class="showVerticalTable ? 'rotate90' : ''"
                                                    color="grey darken-1"
                                                >mdi-rotate-left-variant</v-icon>
                                            </v-btn>
                                            <span>{{ $t('imported_files.toggle_vertically_and_horizontally') }}</span>
                                        </v-tooltip>
                                    </template>
                                </div>
                            </v-layout>

                            <!-- 縦表示（行番号がヘッダー） -->
                            <div v-show="showVerticalTable" class="scroll-table-wrap">
                                <table id="vertical-table" ref="verticalTable">
                                    <thead>
                                        <tr>
                                            <th class="text-xs-center">{{ $t('imported_files.item_name_for_system') }}</th>
                                            <template v-for="(rowNum, i) in rowNums">
                                                <th class="text-xs-center" :key="i">
                                                    <span>{{ rowNum }}{{ $t('imported_files.line') }}</span>
                                                    <span v-show="(rowNum == orgHeaderRowNum)" class="caption">{{ $t('list.table_header') }}</span><br>
                                                    <span v-show="(rowNum == orgHeaderRowNum)" class="caption">({{ $t('imported_files.data_row_caption') }})</span>
                                                </th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(row, index) in headerKeyRows">
                                            <tr
                                                :ref="row['error'] ? 'errorRow' : 'correctRow'"
                                                :class="row['error'] ? 'errorRowClass' : 'correctRowClass'"
                                                :key="index"
                                            >
                                                <th :class="row['error'] ? 'has-error-tbody-th text-xs-center' : 'text-xs-center' ">
                                                    <label-component :label-id="row['label_id']" :lang-data="labelData"></label-component>
                                                </th>
                                                <template v-for="(cell, index2) in row['row_data']">
                                                    <td :class="isErrorCellInHeaderKeyRows(cell['data']) ? 'error-cell text-xs-center' : 'text-xs-center'" :key="index2">
                                                        <span v-for="item in cell['data']" :key="item['value']">
                                                            <span v-if="item['is_org_header_cell']">{{ item['value'] }}</span>
                                                            <item-type v-else :item="item"></item-type>
                                                        </span>
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <!-- / 縦表示（行番号がヘッダー） -->

                            <!-- 横表示（項目名がヘッダー） -->
                            <div v-show="!showVerticalTable" class="scroll-table-wrap">
                                <table id="horizontal-table" ref="horizontalTable">
                                    <thead>
                                        <tr>
                                            <th class="text-xs-center">{{ $t('imported_files.row') }}No.</th>
                                            <template v-for="(header, i) in headers">
                                                <th class="text-xs-center" :class="header['error'] ? 'has-error-th text-xs-center' : 'text-xs-center'" :key="i">
                                                    <label-component :label-id="header['label_id']" :lang-data="labelData"></label-component>
                                                </th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(row, index) in rows">
                                            <tr v-if="!row['is_org_header_row']" id="" :ref="row['has_error'] ? 'errorRow' : 'correctRow'" :key="index">
                                                <th class="text-xs-center">{{ row['row_num'] }}</th>
                                                <template v-for="cell in row['data']">
                                                    <td v-for="(value, index2) in cell" :class="value.error ? 'error-cell text-xs-center' : 'text-xs-center' " :key="index2">
                                                        <item-type :item="value"></item-type>
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <!-- / 横表示（項目名がヘッダー） -->

                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>

            <v-layout row wrap justify-center mt-2>
                <v-btn
                    color="grey"
                    dark
                    @click="cancel()"
                    class="back-btn"
                >{{ $t('common.button.cancel') }}</v-btn>
                <v-btn
                    color="primary"
                    @click="store()"
                >{{ $t('common.button.file_import') }}</v-btn>
            </v-layout>

            <page-footer back-button></page-footer>

        </v-container>

        <error-item-list-dialog v-if="errorItemListDialog" :error-item-list-dialog.sync="errorItemListDialog" :errors="errors" :label-data="labelData"></error-item-list-dialog>
        <progress-circular v-if="loading"></progress-circular>
    </v-content>

    <notify-modal></notify-modal>
    <to-top></to-top>
    <app-footer></app-footer>
</v-app>
</template>

<script>
import store from '../../../../stores/Admin/ImportedFiles/store'
import PageFooter from './../../../Organisms/Layouts/PageFooter'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import ToTop from '../../../Atoms/Buttons/ToTop'

import LabelComponent from '../../../Atoms/Labels/Label'
import ItemType from '../../../Molecules/ImportedFiles/ItemType'
import ErrorItemListDialog from './../../../Organisms/Admin/ImportedFiles/Confirm/ErrorItemListDialog'


export default {
    components:{
        PageFooter,
        NotifyModal,
        ProgressCircular,
        ToTop,
        LabelComponent,
        ItemType,
        ErrorItemListDialog
    },
    props: {
        eventHub: eventHub,
        inputs: { type: Object }
    },
    data: () => ({
        ITEM_TYPE: _const.ITEM_TYPE,

        drawer: false,
        loading: false,
        errorItemListDialog: false,
        showOnlyErrors: false,
        showVerticalTable: true,

        stepId: '',
        headers: [],
        rows: [],
        errors: [],

        headerKeyRows: [],
        rowNums: [],
        importConfInfo: [],

        // ラベルデータ
        labelData: {},
        orgHeaderRowNum: null
    }),
    computed: {
    },
    created () {
        console.log(this.inputs)
        this.getFileContents()

        eventHub.$on('click-close', function(){
            // 依頼取込成功後
            if (window.history.length > 1) {
                window.history.back()
            } else {
                window.location.href = '/imported_files'
            }
        })
    },
    mounted: function () {
    },
    methods: {
        getFileContents () {
            this.loading = true

            axios.post('/api/imported_files/read_file', this.inputs)
                .then((res) => {
                    this.stepId = res.data.step_id
                    this.headers = res.data.data.headers
                    this.rows = res.data.data.rows
                    this.errors = res.data.data.errors
                    this.labelData = res.data.data.label_data
                    this.orgHeaderRowNum = res.data.data.org_header_row_num
                    this.headerKeyRows = res.data.data.header_key_rows
                    this.rowNums = res.data.data.row_nums
                    this.importConfInfo = res.data.data.import_conf_info
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('common.message.read_failed') })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        store () {
            if (this.errors.length > 0) {
                eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('common.message.not_importable_with_errors') })
                return false
            }
            this.loading = true

            axios.post('/api/imported_files/store', {
                'business_id': this.inputs.business_id,
                'step_id': this.stepId,
                'tmp_file_info': this.inputs.tmp_file_info,
                'rows': this.rows,
                'errors': this.errors
            })
                .then((res) => {
                    if (res.data.result == 'success') {
                        store.commit('resetUploadFile')
                        eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('common.message.requests_imported'), emitMessage: 'click-close' })
                    } else {
                        eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('common.message.save_failed') })
                    }
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('common.message.save_failed') })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        cancel () {
            this.loading = true
            // 一時ファイルを削除
            let tmp_file_info = {
                'file_name': this.inputs.tmp_file_info.file_name,
                'file_dir': this.inputs.tmp_file_info.tmp_file_dir,
                'file_path': this.inputs.tmp_file_info.tmp_file_path
            }
            axios.post('/api/imported_files/tmp_file_delete',{
                tmp_file_info: tmp_file_info
            })
                .then((res) => {
                    console.log(res)
                    store.commit('resetUploadFile')
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: Vue.i18n.translate('common.message.file_delete_failed') })
                })
                .finally(() => {
                    this.loading = false
                    if (window.history.length > 1) {
                        window.history.back()
                    } else {
                        window.location.href = '/imported_files'
                    }
                });
        },
        pickUpErrors () {
            this.showOnlyErrors = !this.showOnlyErrors
            if (this.showOnlyErrors) {
                for (var i = 0; i < this.$refs.correctRow.length; i++) {
                    let ref = this.$refs.correctRow[i]
                    ref.style.display = 'none'
                }
            } else {
                for (var j = 0; j < this.$refs.correctRow.length; j++) {
                    let ref = this.$refs.correctRow[j]
                    ref.style.display = 'table-row'
                }
            }
        },
        switchTable () {
            this.showVerticalTable = !this.showVerticalTable
        },
        isErrorCellInHeaderKeyRows (val) {
            let isError = false
            Object.keys(val).forEach(function(key) {
                if (this[key]['error']) {
                    isError = true
                }
            }, val);

            return isError
        }
    }
}
</script>

<style scoped>
.scroll-table-wrap {
    height: 70vh;
    white-space: nowrap;
}
#file-import-confirm table {
    width: 100%;
    height: 100%;
    display: block;
    overflow-x: auto;
    overflow-y: auto;
    border: solid 1px #F5F5F5;
    border-collapse: collapse;
}
#file-import-confirm table thead tr,
#file-import-confirm table tbody th {
    background-color: #F5F5F5;
}
#file-import-confirm table th {
    padding: 10px;
    border: solid 1px #FFFFFF;
}
#file-import-confirm table td {
    padding: 3px 10px;
    border: solid 1px #F5F5F5;
}
#file-import-confirm table .error-cell {
    background-color: #FFEBEE;
}
#file-import-confirm table .has-error-th {
    border-bottom: 3px solid red;
}
#file-import-confirm table .has-error-tbody-th {
    border-right: 3px solid red;
}
.rotate90 {
    transform:rotate(90deg);
}
</style>
