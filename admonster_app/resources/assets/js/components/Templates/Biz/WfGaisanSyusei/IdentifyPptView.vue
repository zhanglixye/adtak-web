<template>
    <v-app id="identify-ppt">
        <app-menu :drawer="drawer"></app-menu>
        <app-header></app-header>
        <v-content id="data-content">
            <v-alert
                v-model="alert.success"
                dismissible
                type="success"
            >
                {{ $t('common.message.saved') }}
            </v-alert>
            <v-alert
                v-model="alert.error"
                dismissible
                type="error"
            >
                {{ $t('common.message.save_failed') }}
            </v-alert>
            <v-alert
                v-model="alert.inactive"
                dismissible
                type="warning"
            >
                {{ inactiveMessage }}
            </v-alert>
            <v-alert
                v-model="alert.inactivated"
                dismissible
                type="error"
            >
                {{ $t('tasks.alert.message.save_failed_by_inactivated') }}
            </v-alert>
            <v-alert
                v-model="alert.returnMessage"
                dismissible
                type="warning"
            >
                {{ returnMessage }}
            </v-alert>

            <confirm-modal></confirm-modal>

            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <v-flex xs12 mb-2>
                        <h5 class="headline ma-0">
                            {{ $t('biz.wf_gaisan_syusei.identify_ppt.h1') }}
                        </h5>
                    </v-flex>
                    <v-flex xs12>
                        <page-header back-button></page-header>
                    </v-flex>
                    <v-flex xs12 mb-4 v-if="resData">
                        <master-sheet :master-data="resData.master_data" :jobno="resData.request_work.code"></master-sheet>
                    </v-flex>
                    <v-flex xs12 sm6 md5 v-if="resData">
                        <mail-from-support :res-data="resData"></mail-from-support>
                    </v-flex>
                    <v-flex xs12 sm6 md7 v-if="resData">
                        <steps :res-data.sync="resData" v-model="loading" :alerts="alert" @alert-type="getAlertType" @getData="getData" ref="steps"></steps>
                    </v-flex>
                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import ConfirmModal from '../../../Organisms/Biz/WfGaisanSyusei/ConfirmModal.vue'
import MasterSheet from '../../../Organisms/Biz/WfGaisanSyusei/IdentifyPpt/MasterSheet.vue'
import MailFromSupport from '../../../Organisms/Biz/WfGaisanSyusei/IdentifyPpt/MailFromSupport.vue'
import Steps from '../../../Organisms/Biz/WfGaisanSyusei/IdentifyPpt/Steps.vue'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

const eventHub = new Vue();

export default {
    components: {
        PageHeader,
        ConfirmModal,
        MasterSheet,
        MailFromSupport,
        Steps,
        ProgressCircular,
    },
    props: {
        eventHub: eventHub,
        requestWorkId: {
            type: Number,
            require: true
        },
        taskId: {
            type: Number,
            require: true
        }
    },
    data: () => ({
        drawer: false,
        loading: false,
        message: '',
        inactiveMessage: '',
        returnMessage: '',
        alert: {
            success: false,
            error: false,
            warning: false
        },
        resData: null,
    }),
    created () {
        window.getApp = this;
        this.getData('initial')
    },
    methods:{
        getData: function(type) {
            this.loading = true
            axios.get('/api/biz/wf_gaisan_syusei/identify_ppt/' + this.requestWorkId + '/' + this.taskId + '/create')
                .then((res) => {
                    if (type == 'initial') {
                        // 初回ロード時
                        this.resData = res.data
                        if (this.resData.message != null){
                            this.returnMessage = this.resData.message;
                            this.alert.returnMessage = true;
                        }
                        if (this.resData.is_inactive_task) {
                            this.inactiveMessage = this.$t('tasks.alert.message.is_inactive');
                            this.alert.inactive = true;
                        }
                    } else {
                        // DB登録完了時
                        this.$refs.steps.setData(JSON.parse(JSON.stringify(res.data)))
                    }
                    this.loading = false
                })
                .catch((err) => {
                    this.loading = false
                    console.log(err);
                });
        },

        getAlertType: function(type){
            this.alert[type] = true
        }
    }
}
</script>
