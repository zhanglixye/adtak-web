<template>
    <v-layout row wrap>
        <v-flex xs12 md6>
            <div class="d-flex align-center">
                <date-picker
                    v-model="period.from"
                    append-icon="event"
                    :label="$t('reports.period.label') + $t('reports.period.from')"
                ></date-picker>
                <span style="text-align: center;max-width: 40px;">～</span>
                <date-picker
                    v-model="period.to"
                    append-icon="event"
                    date-to
                    :label="$t('reports.period.label') + $t('reports.period.to')"
                ></date-picker>
            </div>
            <autocomplete
                v-model="selectedBusinessIds"
                chips
                dense
                item-text="name"
                item-value="id"
                :items="businesses"
                :label="$t('reports.business.label')"
                multiple
                :no-data-text="$t('reports.business.no_data_text')"
                slot-prepend-item-type="all"
                :placeholder="$t('reports.business.placeholder')"
            ></autocomplete>
            <autocomplete
                v-model="stepIds"
                chips
                dense
                item-text="name"
                item-value="id"
                :items="canSelectedSteps"
                :label="$t('reports.step.label')"
                multiple
                :no-data-text="$t('reports.step.no_data_text')"
                slot-prepend-item-type="all"
                :placeholder="$t('reports.step.placeholder')"
                :disabled="disableStepSelectbox"
            ></autocomplete>
            <div>
                <v-btn :disabled="!canOutput" color="primary" @click="output()">{{ $t('common.button.output') }}</v-btn>
            </div>
        </v-flex>
        <v-flex xs12 md6>
            <v-card color="primary" dark>
                <v-card-title>
                    <div class="subheading">{{ reportTitle }}</div>
                </v-card-title>
                <v-card-text>
                    <div class="body-1">
                        <div v-for="description in reportDescription" :key="description">{{ description }}</div>
                    </div>
                </v-card-text>
            </v-card>
        </v-flex>
        <progress-circular v-if="loading"></progress-circular>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </v-layout>
</template>

<script>
import DatePicker from '../../../Atoms/Pickers/DatePicker'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import Autocomplete from '../../../Atoms/Autocompletes/Autocomplete'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        AlertDialog,
        Autocomplete,
        ConfirmDialog,
        ProgressCircular,
        DatePicker
    },
    props: {
        businesses: { type: Array, required: false, default: () => [] },
        steps: { type: Array, required: false, default: () => [] },
    },
    data: () => ({
        loading: false,
        businessIds: [],
        // 選択されている作業を管理（画面に表示されていない作業についても管理）
        stepIds: [],
        period: {from: '', to: ''},
        reportType: 'businessReport'

    }),
    computed: {
        canOutput () {
            // 期間項目が入力されているか
            if (this.period.from === '' || this.period.to === '') return false
            // 業務名、作業名が入力されているか
            if (this.businessIds.length === 0 || this.stepIds.length === 0) return false
            return true
        },
        canSelectedSteps () {
            if (!this.steps) return []
            const items = this.steps.filter(step => this.businessIds.includes(step.business_id))
            return items
        },
        disableStepSelectbox () {
            return this.selectedBusinessIds.length === 0
        },
        selectedBusinessIds: {
            get () {
                return this.businessIds
            },
            set (val) {
                const diffIds = val.filter(id => this.businessIds.indexOf(id) == -1)
                const selectedBusinesses = this.steps.filter(step =>
                    diffIds.includes(step.business_id)
                )

                const unselectedStepIds = selectedBusinesses.filter(item => !this.stepIds.includes(item.value))
                this.stepIds = this.stepIds.concat(unselectedStepIds.map(item => item.value))
                this.businessIds = val
            }
        },
        reportDescription () {
            // 説明文が決まっていないため、まだ表示しない
            return []
        },
        reportTitle () {
            return Vue.i18n.translate('reports.workload_by_business_title')
        }
    },
    methods: {
        loadingDisplay (bool) {
            this.loading = bool
        },
        async output () {
            if (await this.$refs.confirm.show(Vue.i18n.translate('reports.message.output'))) {
                try {
                    this.loadingDisplay(true)

                    let formData = new FormData()
                    formData.append('reportType',this.reportType)
                    this.businessIds.forEach(function (businessId) {
                        formData.append('businessIds[]',businessId)
                    })
                    this.stepIds.forEach(function (stepId) {
                        formData.append('stepIds[]',stepId)
                    })
                    formData.append('period', JSON.stringify(this.period))
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
