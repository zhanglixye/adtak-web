<template>
    <div id="mail-from-support-block">

        <v-layout row wrap mb-3>
            <v-flex md12>
                <v-card v-if="resData.identified_ppt_info">
                    <v-card-title>
                        <span class="title font-weight-light">{{ $t('biz.wf_gaisan_syusei.input_ppt_info.target_file') }}</span>
                    </v-card-title>
                    <v-card-actions>
                        {{ resData.identified_ppt_info.name }}
                        <v-spacer></v-spacer>
                        <v-tooltip top>
                            <v-btn
                                :href="resData.identified_ppt_info.url"
                                slot="activator"
                                color="primary"
                            >
                                <v-icon color="white">cloud_download</v-icon>
                            </v-btn>
                            <span>{{ $t('common.button.download')}}</span>
                        </v-tooltip>
                    </v-card-actions>
                </v-card>
                <v-card v-else>
                    <v-card-title>
                        <span class="title font-weight-light">{{ $t('biz.wf_gaisan_syusei.input_ppt_info.no_target_file') }}</span>
                    </v-card-title>
                </v-card>
            </v-flex>
        </v-layout>
        <v-layout row align-center>
            <v-icon>arrow_right</v-icon>
            <a href="javascript:void(0)" class="guide" @click="openGuide('/images/biz/wf_gaisan_syusei/input_ppt_info_guide.png')">
                <span>{{ $t('biz.wf_gaisan_syusei.input_ppt_info.link_guide') }}</span>
            </a>
        </v-layout>
    </div>
</template>

<script>
export default {
    props: {
        resData: Object
    },
    data: () => ({
    }),
    methods: {
        openGuide: function(path) {
            window.open(path,  null, 'width=1000, height=700, resizable=1')
        }
    }
}
</script>
