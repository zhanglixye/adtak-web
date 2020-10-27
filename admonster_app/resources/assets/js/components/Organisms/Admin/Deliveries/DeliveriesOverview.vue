<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap align-center>
                <v-flex xs12>
                    <v-list v-if="request.id">
                        <v-list-tile>
                            <v-list-tile-content>
                                <v-list-tile-title>{{ $t('deliveries.edit.overview.title', { user_name: taskResult.user_name }) }}</v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>
                                <v-list-tile-title><a class="text-underline" @click="download()">{{ request.request_name }}</a></v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>
                                <v-list-tile-title><a class="text-underline" @click="showReferenceView([taskUri])">{{ $t('deliveries.edit.overview.task_result') }}</a></v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                        <v-list-tile>
                            <v-list-tile-content>
                                <v-list-tile-title><a class="text-underline" @click="downloadJson()">{{ $t('deliveries.edit.overview.download') }}</a></v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                    </v-list>
                </v-flex>
                <v-flex xs12 class="pa-3 pt-5">
                    <v-chip>{{$t('list.search_condition.delivered')}}</v-chip>
                    <div>
                        <date-time-picker
                            v-model="deliveryDate"
                            :disabled="!isEdit || isCancel"
                            class="d-inline-flex"
                            style="max-width: 250px;"
                        ></date-time-picker>
                        <v-checkbox
                            v-model="isCancel"
                            :label="$t('deliveries.edit.setting.assign_date_cancel')"
                            :disabled="!isEdit"
                            hide-details
                            class="d-inline-flex"
                        ></v-checkbox>
                    </div>
                </v-flex>
            </v-layout>
        </v-card-text>
        <reference-view-dialog ref="referenceViewDialog"></reference-view-dialog>
    </v-card>
</template>

<script>
import ReferenceViewDialog from '../../../Atoms/Dialogs/ReferenceViewDialog'
import DateTimePicker from '../../../Atoms/Pickers/DateTimePicker'

export default {
    components: {
        ReferenceViewDialog,
        DateTimePicker,
    },
    props: {
        request: { type: Object, required: true },
        taskResult: { type: Object, required: true },
        deliveryInfo: { type: Object, required: false, default: () => ({}) },
    },
    data: () => ({
        isCancel: false,
        deliveryDate: '',
    }),
    created () {
        // 初期値セット
        this.isCancel = this.deliveryInfo.delivery_status != _const.DELIVERY_STATUS.NONE && this.deliveryInfo.is_assign_date == _const.FLG.ACTIVE && !this.deliveryInfo.assign_delivery_at
        this.deliveryDate = this.deliveryInfo.assign_delivery_at ? this.deliveryInfo.assign_delivery_at : ''
    },
    computed: {
        taskUri () {
            return this.request ? '/biz/' + this.request.step_url + '/' + this.request.request_work_id + '/' + this.taskResult.task_id + '/create' : ''
        },
        isEdit () {
            return this.deliveryInfo.is_assign_date == _const.FLG.ACTIVE && this.deliveryInfo.delivery_status != _const.DELIVERY_STATUS.DONE
        },
    },
    methods: {
        showReferenceView (uris) {
            if (!uris) return
            this.$refs.referenceViewDialog.show(uris)
        },
        async downloadJson () {
            this.loading = true
            const content = JSON.stringify(this.taskResult.content)
            const a = document.createElement('a')
            a.download = this.$t('deliveries.edit.overview.download') + '.txt'
            a.href = URL.createObjectURL(new Blob([content], {type: 'text.plain'}))
            a.dataset.downloadurl = ['text/plain', a.download, a.href].join(':')
            a.click();
            this.loading = false
        },
        async download () {
            this.loading = true
            try {
                const formData = new FormData()
                formData.append('task_result_id', this.taskResult.task_result_id)
                const res = await axios.post('/api/deliveries/createTaskResultZipFile', formData)

                const a = document.createElement('a')
                a.download = res.data.file_name
                a.href = '/api/utilities/downloadFromLocal?file_path=' + encodeURIComponent(res.data.file_path) + '&file_name=' + encodeURIComponent(res.data.file_name)
                a.click()
                this.loading = false
            } catch (e) {
                this.loading = false
                console.log(e)
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
            }
        },
    },
}
</script>
