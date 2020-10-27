<template>
    <div id="mail-to-agancy-block">
        <v-card>
            <v-card-title primary-title>
                <div>
                    <v-layout align-start mb-3>
                        <v-icon class="mr-3">email</v-icon>
                        <div class="" style="font-size: 1.375rem;">{{ $t('biz.wf_gaisan_syusei.common.request_mail_box_title') }}</div>
                    </v-layout>
                    <div class="mb-2" style="font-size: 1.375rem;">{{ requestMail.subject }}</div>
                    <v-layout align-start>
                        <div class="caption mr-1">From:</div>
                        <div class="grey--text caption">{{ requestMail.from }}</div>
                    </v-layout>
                    <v-layout align-start>
                        <div class="caption mr-1">To: </div>
                        <div class="grey--text caption">{{ requestMail.to }}</div>
                    </v-layout>
                    <v-layout align-start>
                        <div class="caption mr-1">Cc: </div>
                        <div class="grey--text caption">{{ requestMail.cc }}</div>
                    </v-layout>
                </div>
            </v-card-title>
            <v-divider class="ma-0"></v-divider>
            <v-card-text style="max-height:400px;overflow-y:scroll;">
                <div>
                    <span v-html="requestMail.body"></span>
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
export default {
    props: {
        requestMail: Object
    }
}
</script>
