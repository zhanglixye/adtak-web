<template>
    <div id="related-data-block">
        <v-layout mt-0 mb-5>
            <v-card width="100%">
                <v-card-text>
                    <v-flex xs12 class="ref-info-block">
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">JOBNO</v-subheader>
                            </v-flex>
                            <v-flex xs8>
                                {{ resData.request_work.code }}
                            </v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">受注明細番号</v-subheader>
                            </v-flex>
                            <v-flex xs8>{{ resData.master_data.order_line_num }}</v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">代理店名</v-subheader>
                            </v-flex>
                            <v-flex xs8>{{ resData.delivery_data_set.sas_data.item02 }}</v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">広告主名</v-subheader>
                            </v-flex>
                            <v-flex xs8>{{ resData.delivery_data_set.sas_data.item03 }}</v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">キャンペーン名</v-subheader>
                            </v-flex>
                            <v-flex xs8>{{ resData.delivery_data_set.sas_data.item04 }}</v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">サイト名</v-subheader>
                            </v-flex>
                            <v-flex xs8>{{ resData.delivery_data_set.sas_data.item05 }}</v-flex>
                        </v-layout>
                        <v-layout row align-center>
                            <v-flex xs4>
                                <v-subheader style="justify-content:center;">メニュー名</v-subheader>
                            </v-flex>
                            <v-flex xs8>{{ resData.delivery_data_set.sas_data.item06 }}</v-flex>
                        </v-layout>
                    </v-flex>
                </v-card-text>
            </v-card>
        </v-layout>
    </div>
</template>

<script>
export default {
    props: {
        resData: Object
    }
}
</script>
