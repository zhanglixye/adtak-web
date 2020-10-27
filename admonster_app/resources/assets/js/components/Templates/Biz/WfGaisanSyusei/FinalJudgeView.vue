<template>
    <v-app id="final-judge">
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

            <confirm-modal></confirm-modal>
            <return-steps-modal v-if="resData" :res-data="resData"></return-steps-modal>

            <v-container fluid grid-list-md>
                <v-layout row wrap>
                    <v-flex xs12 mb-2>
                        <h5 class="headline ma-0">
                            {{ $t('biz.wf_gaisan_syusei.final_judge.h1') }}
                        </h5>
                    </v-flex>
                    <v-flex xs12>
                        <page-header back-button></page-header>
                    </v-flex>
                </v-layout>
                <v-layout>
                    <v-flex xs12 v-if="resData">
                        <related-data :res-data="resData"></related-data>
                    </v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex xs12 v-if="resData">
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
import ReturnStepsModal from '../../../Organisms/Biz/WfGaisanSyusei/FinalJudge/ReturnStepsModal.vue'
import RelatedData from '../../../Organisms/Biz/WfGaisanSyusei/FinalJudge/RelatedData.vue'
import Steps from '../../../Organisms/Biz/WfGaisanSyusei/FinalJudge/Steps.vue'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

const eventHub = new Vue();

export default {
    components: {
        PageHeader,
        ConfirmModal,
        ReturnStepsModal,
        RelatedData,
        Steps,
        ProgressCircular
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
        inactiveMessage: '',
        alert: {
            success: false,
            error: false
        },
        resData: null
    }),
    created () {
        window.getApp = this;
        this.getData('initial')
    },
    methods:{
        getData: function(type) {
            this.loading = true
            axios.get('/api/biz/wf_gaisan_syusei/final_judge/' + this.requestWorkId + '/' + this.taskId + '/create')
                .then((res) => {
                    if (type == 'initial') {
                        // 初回ロード時
                        this.resData = res.data
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
