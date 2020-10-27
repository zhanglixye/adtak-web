<template>
    <div id="mail-from-support-block">
        <v-layout row wrap mb-3 class="mail-overview-wrap">
            <v-card v-if="resData.target_mails.length > 0" class="mail-overview">
                <template v-for="(item, index) in resData.target_mails">
                    <v-layout
                        row
                        wrap
                        pt-1
                        pb-1
                        pr-2
                        @click="openMail(item.id)"
                        :class="{'selected-mail brown lighten-4': (item.id === selectedMail.id)}"
                        :key="index"
                    >
                        <v-flex class="head text--primary align-start">
                            <v-icon small class="icon-mail">email</v-icon>
                            <span class="from">{{ item.from }}</span>
                            <v-spacer></v-spacer>
                            <div class="d-flex align-center">
                                <v-icon v-if="item.request_mail_attachments.length > 0" color="grey lighten-1" small class="mr-1">attachment</v-icon>
                                <v-icon v-else small class="mr-1"></v-icon>
                                <span class="recieved-at">{{ item.recieved_at | formatDateYmdHm(true) }}</span>
                            </div>
                        </v-flex>
                        <div class="subject-body-wrap">
                            <div class="subject">{{ item.subject }}</div>
                            <div class="body grey--text">{{ item.subject }}</div>
                        </div>
                    </v-layout>
                    <v-divider v-if="index + 1 < resData.target_mails.length" :key="`divider-${index}`" class="ma-0"></v-divider>
                </template>
            </v-card>
            <v-card v-else>
                <v-card-title>{{ $t('biz.wf_gaisan_syusei.identify_ppt.no_mails') }}</v-card-title>
            </v-card>
        </v-layout>

        <v-layout>
            <v-card v-if="selectedMail">
                <v-card-title primary-title>
                    <div>
                        <div class="mb-2" style="font-size: 1.375rem;">{{ selectedMail.subject }}</div>
                        <v-layout align-start>
                            <div class="caption mr-1">From:</div>
                            <div class="grey--text caption">{{ selectedMail.from }}</div>
                        </v-layout>
                        <v-layout align-start>
                            <div class="caption mr-1">To: </div>
                            <div class="grey--text caption">{{ selectedMail.to }}</div>
                        </v-layout>
                        <v-layout align-start>
                            <div class="caption mr-1">Cc: </div>
                            <div class="grey--text caption">{{ selectedMail.cc }}</div>
                        </v-layout>
                    </div>
                </v-card-title>
                <v-divider class="ma-0"></v-divider>
                <v-card-text style="max-height:500px;overflow-y:scroll;">
                    <div>
                        <span v-html="selectedMail.body"></span>
                    </div>
                </v-card-text>

                <v-divider class="ma-0"></v-divider>

                <v-card-actions v-if="mailAttachments.length > 0">
                    <v-layout row wrap>
                        <v-flex md12>
                            <v-icon>attachment</v-icon>
                            {{ mailAttachments.length }}{{ $t('allocations.request_content.odditional_file') }}
                        </v-flex>
                        <template v-for="(attachment, index) in mailAttachments">
                            <v-flex md12 :key="index">
                                <v-card>
                                    <v-card-actions>
                                        {{ attachment.attachment_file_name }}
                                        <v-spacer></v-spacer>
                                        <v-tooltip top>
                                            <v-btn
                                                :href="attachment.attachment_download_uri"
                                                slot="activator"
                                                color="primary"
                                            >
                                                <v-icon color="white">cloud_download</v-icon>
                                            </v-btn>
                                            <span>{{ $t('common.button.download')}}</span>
                                        </v-tooltip>
                                    </v-card-actions>
                                </v-card>
                            </v-flex>
                        </template>
                    </v-layout>
                </v-card-actions>
            </v-card>
        </v-layout>
    </div>
</template>

<script>
export default {
    props: {
        resData: Object
    },
    data: () => ({
        selectedMail: ''
    }),
    created: function () {
        if (this.resData.task_result_content.item01) {
            this.openMail(this.resData.task_result_content.item01)
        }
    },
    computed: {
        mailAttachments () {
            let array = [];
            if (this.selectedMail && this.selectedMail.request_mail_attachments.length > 0) {
                this.selectedMail.request_mail_attachments.forEach(function (item) {
                    let uri = '/biz/wf_gaisan_syusei/identify_ppt/download_attachment_file?attachment_file_path='
                    let file_name = item.name
                    let file_path = item.file_path
                    uri = uri + encodeURIComponent(file_path) + '&attachment_file_name=' + encodeURIComponent(file_name)
                    array.push({
                        attachment_file_name: file_name,
                        attachment_file_path: file_path,
                        attachment_download_uri: uri,
                    });
                });
            }
            return array;
        },
    },
    methods: {
        openMail (selectedMailId) {
            this.selectedMail = this.resData.target_mails.find(item => (item.id === selectedMailId))

            if (!this.resData.is_done_task) {
                eventHub.$emit('setSelectedMail', this.selectedMail)
            }
        }
    }
}
</script>
