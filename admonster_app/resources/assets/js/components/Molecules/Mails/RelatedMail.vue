<template>
    <v-card style="min-height: 100%;">
        <v-card-title primary-title class="related-mail">
            <v-layout column wrap>
                <div class="mail-subject mt-0 mb-4">{{ mail.subject }}</div>
                <div style="display: flex;">
                    <span class="subheading font-weight-medium">{{ from }}</span>
                    <v-spacer></v-spacer>
                    <span v-if="mail.recieved_at" style="white-space: nowrap;">
                        {{ mail.recieved_at | formatDateYmdHm }}
                    </span>
                </div>
                <span class="body-1" v-if="mail.to !== null">{{ 'To: ' + mail.to }}</span>
                <span class="body-1" v-else>{{ 'To: ' }}</span>
                <span v-if="mail.cc">{{ 'Cc: ' + mail.cc }}</span>
                <span v-if="mail.bcc">{{ 'Bcc: ' + mail.bcc }}</span>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <div v-html="mail.original_body"></div>
        </v-card-text>
        <div v-show="show">
            <v-divider class="my-0"></v-divider>
            <v-container grid-list-md>
                <v-layout row wrap>
                    <v-flex md12>
                        <v-icon>attachment</v-icon>
                        {{ mailAttachments.length }}{{$t('allocations.request_content.odditional_file')}}
                    </v-flex>
                    <template v-for="(attachment, i) in mailAttachments">
                        <v-flex md12 :key="i">
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
            </v-container>
        </div>
    </v-card>
</template>

<script>
export default {
    props: {
        mail: { type: Object, require: true },
    },
    data: () =>({

    }),
    computed: {
        mailAttachments: {
            //  getter 関数
            get: function() {
                let array = [];
                const mailAttachments = ('attachments' in this.mail) ? this.mail.attachments : this.mail.request_mail_attachments
                if (mailAttachments.length > 0) {
                    mailAttachments.forEach(item => {
                        let uri = '/utilities/download_file?file_path='
                        let file_name = item.name
                        let file_path = item.file_path
                        uri = uri + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
                        array.push({
                            attachment_file_name: file_name,
                            attachment_file_path: file_path,
                            attachment_download_uri: uri,
                        });
                    });
                }
                return array
            },
        },
        show: {
            get: function () {
                if (this.mailAttachments.length > 0){
                    return true
                } else {
                    return false
                }
            }
        },
        from () {
            const from = this.mail.pivot.from
            return from ? from : this.mail.from.split('&lt;')[0]
        },
    }
}
</script>
<style scoped>
.related-mail {
    padding-top: 0px;
}
</style>
