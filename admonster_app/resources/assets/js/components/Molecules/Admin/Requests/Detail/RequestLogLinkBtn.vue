<template>
    <v-tooltip top class="mr-1">
        <v-btn slot="activator"
            icon
            :href="uri"
            :disabled="disabled"
        >
            <v-icon class="refrect-lr icon-color-default">reply</v-icon>
        </v-btn>
        <span>{{ tooltipText }}</span>
    </v-tooltip>
</template>

<script>
export default {
    props: {
        log: Object
    },
    data: () => ({
        uri: '',
        tooltipText: '',
        disabled: false
    }),
    mounted () {
        this.generateLinkData(this.log)
    },
    methods: {
        generateLinkData (log) {
            const allocationLogTypes = [
                _const.REQUEST_LOG_TYPE.ALLOCATION_COMPLETED,
                _const.REQUEST_LOG_TYPE.ALLOCATION_CHANGED,
            ]
            const approvalLogTypes = [
                _const.REQUEST_LOG_TYPE.APPROVAL_REJECTED,
                _const.REQUEST_LOG_TYPE.APPROVAL_COMPLETED,
                _const.REQUEST_LOG_TYPE.STEPS_RETURNED
            ]

            if (allocationLogTypes.includes(log.type)) {
                this.uri = log.request_work_id ? '/allocations/' + log.request_work_id + '/edit' : ''
                this.tooltipText = log.request_work_id ? Vue.i18n.translate('request_logs.link_btn_text.to_allocation_edit') : Vue.i18n.translate('request_logs.link_btn_text.disabled_link_for_old_data')
                this.disabled = log.request_work_id ? false : true
            } else if (approvalLogTypes.includes(log.type)) {
                this.uri = log.request_work_id ? '/approvals/' + log.request_work_id + '/edit' : ''
                this.tooltipText = log.request_work_id ? Vue.i18n.translate('request_logs.link_btn_text.to_approval_edit') : Vue.i18n.translate('request_logs.link_btn_text.disabled_link_for_old_data')
                this.disabled = log.request_work_id ? false : true
            } else if (log.type === _const.REQUEST_LOG_TYPE.DELIVERY_COMPLETED) {
                this.uri = log.request_work_id ? '/deliveries/' + log.request_work_id : ''
                this.tooltipText = log.request_work_id ? Vue.i18n.translate('request_logs.link_btn_text.to_delivery_detail') : Vue.i18n.translate('request_logs.link_btn_text.disabled_link_for_old_data')
                this.disabled = log.request_work_id ? false : true
            } else if (log.type === _const.REQUEST_LOG_TYPE.ALL_TASKS_COMPLETED) {
                this.uri = log.request_work_id ? '/management/works?' + encodeURIComponent('request_work_ids[]') + '=' + encodeURIComponent(log.request_work_id) : ''
                this.tooltipText = log.request_work_id ? Vue.i18n.translate('request_logs.link_btn_text.to_works') : Vue.i18n.translate('request_logs.link_btn_text.disabled_link_for_old_data')
                this.disabled = log.request_work_id ? false : true
            }
        }
    }
}
</script>
