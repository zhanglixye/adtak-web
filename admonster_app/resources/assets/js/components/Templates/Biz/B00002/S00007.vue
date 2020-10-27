<template>
    <v-layout justify-space-between column fill-height pa-3>
        <div>
            <alert-dialog ref="alert"></alert-dialog>
            <confirm-dialog ref="confirm"></confirm-dialog>
        </div>
        <div>
            <v-text-field
                v-model="taskResultContent['clerk_email']"
                :label="$t('biz.b00002.s00007.send_mail_to')"
                :readonly="true"
            >
            </v-text-field>
        </div>
        <div style="text-align: right;">
            <template v-if="edit">
                <v-btn color="primary" dark @click="save()">
                    {{ $t('common.button.approval') }}
                </v-btn>
            </template>
        </div>
    </v-layout>
</template>

<script>
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'

export default {
    components: {
        AlertDialog,
        ConfirmDialog,
    },
    props: {
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
        backBeforePage: { type: Function, required: false, default: function () {} },
    },
    data: () => ({
        taskResultContent: {
            results: {
                type: _const.TASK_RESULT_TYPE.ON,
            },
        },
    }),
    computed: {},
    watch: {},
    created () {
        this.init()
    },
    methods: {
        init () {
            this.taskResultContent['clerk_email'] = this.initData['clerk_email']
        },
        async save () {
            if (await this.$refs.confirm.show(Vue.i18n.translate('biz.b00002.s00007._modal.p1'))) {
                try {
                    this.loadingDisplay(true)
                    this.taskResultContent['results']['type'] = _const.TASK_RESULT_TYPE.DONE

                    let formData = new FormData()
                    formData.append('step_id', this.initData['request_info']['step_id'])
                    formData.append('request_id', this.initData['request_info']['request_id'])
                    formData.append('request_work_id', this.initData['request_info']['id'])
                    formData.append('task_id', this.initData['task_info']['id'])
                    formData.append('task_started_at', this.initData['task_started_at'])

                    formData.append('task_result_content', JSON.stringify(this.taskResultContent))
                    const res = await  axios.post('/api/biz/b00002/s00007/store', formData)

                    console.log(res)
                    this.loadingDisplay(false)

                    const self = this
                    if (res.data.result === 'success') {
                        this.$refs.alert.show(
                            Vue.i18n.translate('common.message.saved'),
                            function () {
                                self.backBeforePage()
                            }
                        )
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

<style scoped>
</style>
