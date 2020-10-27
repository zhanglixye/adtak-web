<template>
    <v-form ref="form" v-model="valid" lazy-validation>
    <v-layout row wrap>
        <v-flex xs12 md6>
            <v-select
                v-model="businessId"
                :label="$t('reports.business.label')"
                :placeholder="$t('reports.business.placeholder')"
                :items="businesses"
                item-text="business_name"
                item-value="business_id"
                :menu-props="{ maxHeight: '300' }"
                :rules="[rules.required]"
            ></v-select>
            <v-select
                v-model="reportId"
                :label="$t('reports.report.label')"
                :placeholder="$t('reports.report.placeholder')"
                :items="selectableReports"
                item-text="report_name"
                item-value="report_id"
                :menu-props="{ maxHeight: '300' }"
                :rules="[rules.required]"
            ></v-select>
            <!-- カスタム部分 -->
            <template v-if="reportId && businessId === 6">
                <date-picker
                    v-model="accountingYearMonth"
                    append-icon="event"
                    :label="$t('reports.accounting_year_month.label')"
                    picker-type="month"
                    :rules="[rules.required]"
                ></date-picker>
                <date-picker
                    v-model="transferDate"
                    append-icon="event"
                    :label="$t('reports.transfer_date.label')"
                    :rules="[rules.required]"
                ></date-picker>
            </template>
            <template v-else-if="reportId && [8,12,14].includes(businessId)">
                <div class="d-flex align-center">
                    <date-picker
                        v-model="startedAt"
                        append-icon="event"
                        label="開始日"
                        :rules="[rules.required]"
                    ></date-picker>
                    <span style="text-align: center;max-width: 40px;">～</span>
                    <date-picker
                        v-model="finishedAt"
                        append-icon="event"
                        date-to
                        label="終了日"
                        :rules="[rules.required]"
                    ></date-picker>
                </div>
            </template>
            <template v-else-if="reportId && businessId === 16">
                <date-picker
                    v-model="yearMonth"
                    append-icon="event"
                    label="配信年月"
                    picker-type="month"
                    :rules="[rules.required]"
                ></date-picker>
            </template>
            <!-- カスタム部分 -->
            <div>
                <v-btn :disabled="!valid" color="primary" @click="output()">{{ $t('common.button.output') }}</v-btn>
            </div>
        </v-flex>
        <v-flex xs12 md6>
            <v-card color="primary" dark v-show="reportId">
                <v-card-text>
                    <div class="body-1">
                        <div v-html="reportDescription"></div>
                    </div>
                </v-card-text>
            </v-card>
        </v-flex>
        <progress-circular v-if="loading"></progress-circular>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </v-layout>
    </v-form>
</template>

<script>
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import DatePicker from '../../../Atoms/Pickers/DatePicker'

export default {
    components: {
        AlertDialog,
        ConfirmDialog,
        ProgressCircular,
        DatePicker
    },
    props: {
        reports: { type: Array, require: true },
    },
    data: () => ({
        loading: false,
        businessId: null,
        reportId: null,
        // カスタム入力項目
        form: {},
        rules: {
            required: value => !!value || '入力必須です',
        },
        valid: true,
    }),
    computed: {
        businesses () {
            // 業務の重複を削除する
            let tmp = [];
            return this.reports.filter(report => {
                if (tmp.indexOf(report['business_id']) === -1) {
                    tmp.push(report['business_id']);
                    return report;
                }
            });
        },
        selectableReports () {
            return this.reports.filter(report => this.businessId === report['business_id'])
        },
        reportDescription () {
            const report = this.selectableReports.find(report => this.reportId === report['report_id'])
            return report ? report['report_description'] : ''
        },
        accountingYearMonth: {
            set (val) {
                // Re-render by assigning a new object
                this.form = Object.assign({}, this.form, {'accounting_year_month': val})
            },
            get () {
                return this.form['accounting_year_month'] ? this.form['accounting_year_month'] : ''
            }
        },
        yearMonth: {
            set (val) {
                this.form = Object.assign({}, this.form, {
                    'started_at': val,
                    'finished_at': moment(val).endOf('month').toISOString()
                })
            },
            get () {
                return this.form['started_at'] ? this.form['started_at'] : ''
            }
        },
        businessCode () {
            // 業務コード（Bからはじまる6桁のコード）
            const businessId = (this.businessId === null || this.businessId === undefined) ? 0 : this.businessId
            return 'B' + ('00000' + businessId).slice(-5)
        },
        transferDate: {
            set (val) {
                this.form = Object.assign({}, this.form, {'transfer_date': val})
            },
            get () {
                return this.form['transfer_date'] ? this.form['transfer_date'] : ''
            }
        },
        startedAt: {
            set (val) {
                this.form = Object.assign({}, this.form, {'started_at': val})
            },
            get () {
                return this.form['started_at'] ? this.form['started_at'] : ''
            }
        },
        finishedAt: {
            set (val) {
                this.form = Object.assign({}, this.form, {'finished_at': val})
            },
            get () {
                return this.form['finished_at'] ? this.form['finished_at'] : ''
            }
        },
    },
    watch: {
        businessId () {
            // businessIdを変更したらnullにする
            this.reportId = null
            this.form = {}
        }
    },
    methods: {
        loadingDisplay (bool) {
            this.loading = bool
        },
        async output () {
            // バリデーションチェック
            if (!this.$refs.form.validate()) {
                return
            }
            // 確認ダイアログ表示
            if (await this.$refs.confirm.show(Vue.i18n.translate('reports.message.output'))) {
                try {
                    this.loadingDisplay(true)

                    let formData = new FormData()
                    formData.append('business_id', this.businessId)
                    formData.append('business_code', this.businessCode)
                    formData.append('report_id', this.reportId)
                    formData.append('form', JSON.stringify(this.form))
                    const res = await axios.post('/api/reports/output', formData)

                    this.loadingDisplay(false)

                    if (res.data.result === 'success') {
                        // 非同期処理なので成功メッセージは無いと思われる
                        // this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                        const isNullOrUndefined = (val) => val === null || val === undefined
                        const fileName = isNullOrUndefined(res.data['file_name']) ? '' : res.data['file_name']// file.name
                        const filePath = isNullOrUndefined(res.data['file_path']) ? '' : res.data['file_path']// file.path
                        const link = document.createElement('a')
                        let uri = '/api/utilities/downloadFromLocal?'
                        uri += `file_name=${encodeURIComponent(fileName)}&`
                        uri += `file_path=${encodeURIComponent(filePath)}`
                        link.href = uri
                        // ファイル名が空文字列の場合、urlの末の文字列が入る問題を修正
                        const downloadFailureFileName = fileName === '' ? 'notFileName' : fileName // DL失敗時の表示ファイル名
                        link.setAttribute('download', downloadFailureFileName)
                        document.body.appendChild(link)
                        link.click()
                        document.body.removeChild(link)
                    } else {
                        this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
                    }
                } catch (err) {
                    console.log(err)
                    this.loadingDisplay(false)
                    this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
                }
            }
        },
    }
}
</script>
