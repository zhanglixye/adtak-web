<template>
    <v-app id="file-import-Preference">
        <app-menu :drawer="drawer"></app-menu>
        <app-header
            :title="$t('imported_files.index.title')"
            :subtitle="$t('imported_files.index.setting_subtitle')"
        ></app-header>
        <v-content>
            <v-container fluid grid-list-md style="padding: 12px;">
                <v-layout row wrap>
                    <v-flex xs12>
                        <page-header :prepend-function-to-back-history="deleteTmpFileInfo" back-button></page-header>
                    </v-flex>
                    <v-flex xs12>
                        <v-form
                            :action="actionPath"
                            method="post"
                            ref="form"
                            @submit.prevent
                            v-model="valid"
                        >
                            <input type="hidden" name="_token" :value="csrf">
                            <input type="hidden" name="order_name" :value="orderName">
                            <input type="hidden" name="order_id" :value="orderId">
                            <input type="hidden" name="file_name" :value="inputs.tmp_file_info.file_name">
                            <input
                                type="hidden"
                                name="tmp_file_dir"
                                :value="inputs.tmp_file_info.tmp_file_dir"
                            >
                            <input
                                type="hidden"
                                name="tmp_file_path"
                                :value="inputs.tmp_file_info.tmp_file_path"
                            >
                            <div id="search-condition" class="elevation-1" style="margin-bottom: 15px;">
                                <v-layout row wrap>
                                    <span
                                        :style="{ color: 'rgba(0,0,0,.54)', margin: '0 auto', display: 'flex' }"
                                    >
                                        <span>
                                            <div style="font-weight: bolder; margin-top: 15px;">
                                                {{ `${$t('imported_files.order_imported_setting.subject')}（${$t('imported_files.order_imported_setting.search_key')}）` }}
                                            </div>
                                            <div>
                                                {{ $t('imported_files.order_imported_setting.system_on_by_search_items') }}
                                            </div>
                                        </span>
                                        <div>
                                            <div
                                                :class="[show ? 'secDetail' : 'sec', 'hide-scrollbar']"
                                                :style="subjects.length !== 0 ? 'border: 2px solid rgba(0,0,0,.54);' : 'border: 2px solid red;'"
                                            >
                                                <template v-if="!readonly">
                                                    <draggable
                                                        v-model="subjects"
                                                        class="hide-scrollbar"
                                                        style="overflow-y: hidden;"
                                                    >
                                                        <span
                                                            v-for="(subject, index) in selectedSubjects"
                                                            :key="index"
                                                            style="height: 24px; line-height: 24px;"
                                                        >
                                                            <v-chip
                                                                label
                                                                color="primary"
                                                                text-color="white"
                                                                close
                                                                @input="remove(subject.row)"
                                                                style="align-items: end; margin: 2.5px; padding-bottom: 2.5px ;cursor: move; height: 24px;"
                                                            >
                                                                {{ subject.display }}
                                                            </v-chip>
                                                            <span v-if="!checkEndText(index)">_</span>
                                                        </span>
                                                    </draggable>
                                                </template>
                                                <template v-else>
                                                    <div
                                                        class="hide-scrollbar"
                                                        style="overflow-y: hidden;"
                                                    >
                                                        <span
                                                            v-for="(subject, index) in selectedSubjects"
                                                            :key="index"
                                                            style="height: 24px; line-height: 24px;"
                                                        >
                                                            <v-chip
                                                                label
                                                                color="primary"
                                                                text-color="white"
                                                                disabled
                                                                @input="remove(subject.row)"
                                                                style="align-items: end; margin: 2.5px; padding-bottom: 2.5px ;cursor: move; height: 24px;"
                                                            >
                                                                {{ subject.display }}
                                                            </v-chip>
                                                            <span v-if="!checkEndText(index)">_</span>
                                                        </span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="considerations" v-if="subjects.length !== 0">
                                                {{ $t('imported_files.order_imported_setting.subject_considerations.length_limit') }}
                                            </div>
                                            <div class="considerations" style="color: red;" v-else>
                                                {{ $t('imported_files.order_imported_setting.subject_considerations.please_selected_subject') }}
                                            </div>
                                        </div>
                                        <v-btn icon @click="show = !show">
                                            <v-icon>{{ toggleIcon }}</v-icon>
                                        </v-btn>
                                    </span>
                                </v-layout>
                            </div>
                            <div class="elevation-1">
                                <div class="data-content">
                                    <table
                                        cellpadding="15"
                                        sellspacing="15"
                                        style="border-collapse: separate; border-spacing: 15px; width: 100%;"
                                    >
                                        <tr>
                                            <th :style="{ color: 'rgba(0,0,0,.54)', 'text-align': 'center', 'width': '110px' }">
                                                {{ $t('imported_files.order_imported_setting.subject') }}
                                            </th>
                                            <th :style="{ color: 'rgba(0,0,0,.54)', 'text-align': 'center', 'width': '90px' }">
                                                {{ $t('imported_files.order_imported_setting.items.column') }}
                                            </th>
                                            <th :style="{ color: 'rgba(0,0,0,.54)', 'text-align': 'center', 'width': '400px' }">
                                                {{ $t('imported_files.order_imported_setting.items.item_name') }}
                                            </th>
                                            <th :style="{ color: 'rgba(0,0,0,.54)', 'text-align': 'center' }">
                                                {{ $t('imported_files.order_imported_setting.items.display_name') }}
                                            </th>
                                            <th :style="{ color: 'rgba(0,0,0,.54)', 'text-align': 'center', 'width': '150px' }">
                                                {{ $t('imported_files.order_imported_setting.items.data_format') }}
                                            </th>
                                            <th :style="{ color: 'rgba(0,0,0,.54)', 'text-align': 'center', 'width': '150px' }">
                                                {{ $t('imported_files.order_imported_setting.items.data_input_rule') }}
                                            </th>
                                        </tr>
                                        <template v-for="(item, i) in items">
                                            <tr :key="i">
                                                <td
                                                    width="110px"
                                                    class="text-xs-center"
                                                    @click.stop=""
                                                >
                                                    <v-checkbox
                                                        class="justify-center"
                                                        v-model="subjects"
                                                        :value="item.row"
                                                        :disabled="readonly"
                                                        style="margin-left: 7px;" color="primary"
                                                    ></v-checkbox>
                                                </td>
                                                <td class="text-xs-center overflow">
                                                    <span slot="activator">{{ item.row }}</span>
                                                </td>
                                                <td
                                                    class="text-xs-left"
                                                    style="overflow: hidden; text-overflow: ellipsis; max-width: 200px;"
                                                >
                                                    <v-tooltip top>
                                                        <span
                                                            slot="activator"
                                                            style="white-space: nowrap;"
                                                        >
                                                            {{ item.itemName }}
                                                        </span>
                                                        <span>{{ item.itemName }}</span>
                                                    </v-tooltip>
                                                </td>
                                                <td class="text-xs-left overflow">
                                                    <v-text-field
                                                        v-model="item.display"
                                                        single-line
                                                        counter="256"
                                                        :disabled="readonly"
                                                        :rules="[rules.check, rules.required]"
                                                    ></v-text-field>
                                                </td>
                                                <td class="text-xs-left overflow" style="padding-bottom: 20px;">
                                                    <v-select
                                                        v-model="item.dataType"
                                                        item-value="value"
                                                        item-text="label"
                                                        :items="dataTypeCandidates"
                                                        hide-details
                                                        single-line
                                                        :disabled="readonly"
                                                    ></v-select>
                                                </td>
                                                <td class="text-xs-center overflow">
                                                    <span
                                                        v-if="item.dataType !== itemTypeUrlId"
                                                        slot="activator"
                                                        @click.stop="openDataEntryRuleDialog(item.dataType, i)"
                                                        :style="{ 'text-decoration': 'underline', 'color': '#4DB6AC', 'cursor': 'pointer' }"
                                                    >
                                                        {{ $t('imported_files.order_imported_setting.setting') }}
                                                    </span>
                                                    <span v-else>{{ $t('imported_files.order_imported_setting.url_is_unsettable') }}</span>
                                                </td>
                                                <date-data-entry-rule-dialog :readonly="readonly" @setting="setting" ref="dateDataEntryRuleDialog"></date-data-entry-rule-dialog>
                                                <text-data-entry-rule-dialog :readonly="readonly" @setting="setting" ref="textDataEntryRuleDialog"></text-data-entry-rule-dialog>
                                                <number-data-entry-rule-dialog :readonly="readonly" @setting="setting" ref="numberDataEntryRuleDialog"></number-data-entry-rule-dialog>
                                            </tr>
                                        </template>
                                    </table>
                                    <div style="text-align: right;">
                                        {{ $t('imported_files.order_imported_setting.import_considerations') }}
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: center;" class="mt-2">
                                <v-btn
                                    @click="formSubmit"
                                    color="primary"
                                    :disabled="disabledButton"
                                >
                                    {{ $t('common.button.next') }}
                                </v-btn>
                            </div>
                        </v-form>
                        <page-footer :prepend-function-to-back-history="deleteTmpFileInfo" back-button></page-footer>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-content>
        <progress-circular v-if="loading"></progress-circular>
        <alert-dialog width='400' ref="alert"></alert-dialog>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import draggable from 'vuedraggable'
import store from '../../../../stores/Admin/ImportedFiles/ImportedSetting/store'
import DateDataEntryRuleDialog from '../../../Organisms/Admin/ImportedFiles/DateDataEntryRuleDialog'
import TextDataEntryRuleDialog from '../../../Organisms/Admin/ImportedFiles/TextDataEntryRuleDialog'
import NumberDataEntryRuleDialog from '../../../Organisms/Admin/ImportedFiles/NumberDataEntryRuleDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'

export default {
    components: {
        draggable,
        PageHeader,
        PageFooter,
        DateDataEntryRuleDialog,
        TextDataEntryRuleDialog,
        NumberDataEntryRuleDialog,
        ProgressCircular,
        AlertDialog
    },
    props: {
        eventHub: eventHub,
        inputs: { type: Object, required: true }
    },
    data: () => ({
        loading: false,
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        valid: false,
        subjects: [],
        items: [],
        rules: {
            check: v => v.length <= 256 || '',
            required: v => v !== '' || Vue.i18n.translate('imported_files.order_imported_setting.no_display_name_error')
        },
        dataTypeCandidates: [
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.text_data_entry_rule.subject'),
                value: _const.ITEM_TYPE.STRING.ID,
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.subject'),
                value: _const.ITEM_TYPE.DATE.ID,
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.url_data_entry_rule'),
                value: _const.ITEM_TYPE.URL.ID,
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.subject'),
                value: _const.ITEM_TYPE.NUM.ID,
            },
        ],
        show: false,
        drawer: false,
        orderName: '',
    }),
    computed: {
        selectedSubjects () {
            return this.subjects.map(subject => {
                const subjectInfo = this.items.filter(item => item.row === subject)
                return subjectInfo[0]
            })
        },
        actionPath () {
            return '/imported_files/order_confirm'
        },
        disabledButton () {
            return !this.valid || this.subjects.length === 0
        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },
        itemTypeStringId () {
            return _const.ITEM_TYPE.STRING.ID
        },
        itemTypeNumId () {
            return _const.ITEM_TYPE.NUM.ID
        },
        itemTypeDateId () {
            return _const.ITEM_TYPE.DATE.ID
        },
        itemTypeUrlId () {
            return _const.ITEM_TYPE.URL.ID
        },
        readonly: {
            set (isReadonly) {
                return store.commit('setReadonly', isReadonly)
            },
            get () {
                return store.state.readonly
            }
        },
        orderId: {
            set (val) {
                return store.commit('setOrderId', val)
            },
            get () {
                return store.state.orderId
            }
        },
        fileImportType() {
            return store.state.orderId === null ? _const.FILE_IMPORT_TYPE.NEW : _const.FILE_IMPORT_TYPE.ADD
        },
    },
    created () {
        this.orderId = this.inputs.order_id
        this.readonly = this.inputs.readonly
        this.orderName = this.inputs.order_name
        if (this.fileImportType === _const.FILE_IMPORT_TYPE.ADD) {
            this.getSettingInformation()
        } else if (this.fileImportType === _const.FILE_IMPORT_TYPE.NEW) {
            this.getImportFileInfo()
        }
    },
    watch: {
        items: {
            immediate: true,
            async handler() {
                await this.$nextTick()
                this.$refs.form.validate()
            },
        },
    },
    methods: {
        deleteData () {
            store.commit('resetSubjects')
            store.commit('resetItems')
            store.commit('resetFileInfo')
            store.commit('resetSettingRule')
        },
        async deleteTmpFileInfo () {
            this.loading = true
            this.deleteData()
            try {
                // 一時ファイルを削除
                const tmpFileInfo = {
                    'file_name': this.inputs.tmp_file_info.file_name,
                    'file_dir': this.inputs.tmp_file_info.tmp_file_dir,
                    'file_path': this.inputs.tmp_file_info.tmp_file_path
                }
                await axios.post('/api/imported_files/tmp_file_delete', {
                    tmp_file_info: tmpFileInfo
                })
            } catch (e) {
                console.log(e)
            }
            this.loading = false
        },
        async getSettingInformation () {
            try {
                this.loading = true
                const formData = new FormData()
                formData.append('tmp_file_info', JSON.stringify(this.inputs['tmp_file_info']))
                formData.append('data_start_row_no', store.state.dataStartRowNo)
                formData.append('order_id', this.orderId)
                const res = await axios.post('/api/imported_files/get_setting_information', formData)
                if (res.data.status !== 200) {
                    throw Object.assign(new Error(res.data.message), {name: res.data.error, statusCode: res.data.status})
                }

                const self = this
                if (res.data.file_items.length === 0) {// ファイルの項目が無い場合のエラー
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('imported_files.no_items'),
                        function () {
                            self.deleteTmpFileInfo()
                            self.deleteData()
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = `/order/order_details?order_id=${self.orderId}`
                            }
                        }
                    )
                } else if (res.data.content_count === 0) {// データがない場合のエラー
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('imported_files.no_data'),
                        function () {
                            self.deleteTmpFileInfo()
                            self.deleteData()
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = `/order/order_details?order_id=${self.orderId}`
                            }
                        }
                    )
                } else if (res.data.error_messages.length > 0) {
                    const message = res.data.error_messages[0]// すべては表示しない
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate(`imported_files.${message}`),
                        function () {
                            self.deleteTmpFileInfo()
                            self.deleteData()
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = `/order/order_details?order_id=${self.orderId}`
                            }
                        }
                    )
                }
                this.orderName = res.data.order_name
                this.items = res.data.items

                // データ入力ルールの
                store.commit('resetSettingRule')
                for (const key in res.data.setting_rules) {
                    this.setting({ inputRule: JSON.parse(res.data.setting_rules[key]['rule']), displayRule: res.data.setting_rules[key]['display_format'] }, key)
                }
                this.subjects = res.data.subject_part_no
            } catch (e) {
                console.log(e)
                this.$refs.alert.show(
                    Vue.i18n.translate('common.message.read_failed')
                )
            }
            this.loading = false
        },
        async getImportFileInfo () {
            try {
                this.loading = true
                const formData = new FormData()
                formData.append('tmp_file_info', JSON.stringify(this.inputs['tmp_file_info']))
                formData.append('data_start_row_no', store.state.dataStartRowNo)
                const res = await axios.post('/api/imported_files/read_setting_order_file', formData)
                if (res.data.status !== 200) {
                    throw Object.assign(new Error(res.data.message), {name: res.data.error, statusCode: res.data.status})
                }

                this.items = res.data.items
                const self = this
                const upperLimitItems = 50
                if (res.data.items.length === 0) {
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('imported_files.no_items'),
                        function () {
                            self.deleteTmpFileInfo()
                            self.deleteData()
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = '/order/orders'
                            }
                        }
                    )
                } else if (res.data.items.length > upperLimitItems) {
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('imported_files.upper_limit_items', {'number': upperLimitItems}),
                        function () {
                            self.deleteTmpFileInfo()
                            self.deleteData()
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = '/order/orders'
                            }
                        }
                    )
                } else if (res.data.content_count === 0) {
                    // ダイアログ表示
                    this.$refs.alert.show(
                        Vue.i18n.translate('imported_files.no_data'),
                        function () {
                            self.deleteTmpFileInfo()
                            self.deleteData()
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = '/order/orders'
                            }
                        }
                    )
                }

                // 取り込んできたファイルとstoreに保存しているファイルが同一か判断
                if (
                    store.state.fileInfo.file_name === this.inputs.tmp_file_info.file_name
                    && store.state.fileInfo.tmp_file_path === this.inputs.tmp_file_info.tmp_file_path
                    && store.state.items.length === this.items.length
                ) {
                    let isDifferentFile = false
                    for (const i in this.items) {
                        if (this.items[i]['itemName'] !== store.state.items[i]['itemName']) {
                            // 違うファイルを取り込んでいる
                            isDifferentFile = true
                            break
                        }
                    }

                    if (isDifferentFile) {
                        this.deleteData()
                    } else {
                        this.items = store.state.items
                        this.subjects = store.state.subjects
                    }
                } else {
                    this.deleteData()
                }
            } catch (e) {
                console.log(e)
                this.$refs.alert.show(
                    Vue.i18n.translate('common.message.read_failed')
                )
            }
            this.loading = false
        },
        formSubmit () {
            store.commit('setSubjects', { params: this.subjects })
            store.commit('setItems', { params: this.items })
            store.commit('setFileInfo', { params: this.inputs.tmp_file_info })
            this.$refs.form.$el.submit()
        },
        remove (row) {
            this.subjects = this.subjects.filter(item => item !== row)
            this.subjects = [...this.subjects]
        },
        checkEndText (index) {
            return this.subjects.length -1 === index
        },
        setting (form, index) {
            store.commit('setSettingRule', { params: { form: form, index: index } })
        },
        openDataEntryRuleDialog (dataType, i) {
            switch (dataType) {
            case this.itemTypeStringId:
                this.$refs.textDataEntryRuleDialog[i].show(i, store.state.settingRules[i])
                break
            case this.itemTypeNumId:
                this.$refs.numberDataEntryRuleDialog[i].show(i, store.state.settingRules[i])
                break
            case this.itemTypeDateId:
                this.$refs.dateDataEntryRuleDialog[i].show(i, store.state.settingRules[i])
                break
            }
        }
    }
}
</script>

<style scoped>
.v-chip >>> .v-chip__content {
    align-items: end;
}
.sec {
    margin-top: 7px;
    white-space: nowrap;
    height: 33px;
    width: 750px;
    min-width: 500px;
    margin-left: 12px;
    overflow: scroll;
    border-radius: 4px;
}
.considerations {
    text-align: left;
    margin-left: 10px;
}
.secDetail {
    margin-top: 7px;
    min-height: 33px;
    width: 750px;
    min-width: 500px;
    margin-left: 12px;
    overflow: scroll;
    border-radius: 4px;
}
.hide-scrollbar::-webkit-scrollbar {/* for Chrome and Safari */
    display: none;
}
.hide-scrollbar {
    scrollbar-width: none;/* for Firefox */
    -ms-overflow-style: none;/* IE, Edge 対応 */
}
</style>
