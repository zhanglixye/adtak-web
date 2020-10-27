<template>
    <v-app id="inspire">
        <app-menu :drawer="drawer"></app-menu>
        <app-header
                :title="business_detail=== null ? '': business_detail.business_name"
                :subtitle="$t('master.businesses.detail.title')"
        ></app-header>
        <v-content id="data-content">
            <confirm-modal></confirm-modal>
            <v-container fluid grid-list-md v-if="business_detail">
                <!-- Main -->
                <v-layout row wrap>
                    <v-flex xs12>
                        <page-header :alerts="alerts"></page-header>
                    </v-flex>
                    <v-flex xs12 md6>
                        <business-overview :business_detail="business_detail"></business-overview>
                    </v-flex>
                    <v-flex xs12 md6>
                        <business-candidates
                            :admins="admin_users"
                            :candidates="operator_users"
                            :business_id="businessId"
                            :operator_candidate_users="operator_candidate_users"
                            :admin_candidate_users="admin_candidate_users"
                            @update-detail="updateDetail"
                        ></business-candidates>
                    </v-flex>
                    <v-flex xs12>
                        <business-steps :steps="steps" :candidates="operator_users" @update-detail="updateDetail"></business-steps>
                    </v-flex>
                </v-layout>
                <!-- Main -->
                <v-flex xs12 md6>
                    <v-btn
                            color="primary"
                            text
                            @click="copy"
                    >
                        コピー
                    </v-btn>
                </v-flex>

                <page-footer back-button></page-footer>

            </v-container>
        </v-content>
        <app-footer></app-footer>
        <progress-circular v-if="loading"></progress-circular>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader';
import PageFooter from '../../../Organisms/Layouts/PageFooter';
import ConfirmModal from '../../../Organisms/Admin/Business/Detail/ConfirmModal';
import BusinessOverview from '../../../Organisms/Admin/Business/Detail/BusinessOverview';
import BusinessSteps from '../../../Organisms/Master/Businesses/Detail/BusinessSteps';
import BusinessCandidates from '../../../Organisms/Master/Businesses/Detail/BusinessCandidates';
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular';

export default {
    components: {
        PageHeader,
        PageFooter,
        ConfirmModal,
        BusinessOverview,
        BusinessCandidates,
        BusinessSteps,
        ProgressCircular
    },
    data: () => ({
        drawer: false,
        loading: false,
        alerts: {
            success: {
                model: false,
                dismissible: true,
                color: '#4caf50',
                icon: 'check_circle',
                message: Vue.i18n.translate('common.message.success'),
            },
            internal_error: {
                model: false,
                dismissible: true,
                icon: 'warning',
                message: Vue.i18n.translate('common.message.internal_error'),
            }
        },

        //DBから取得
        business_detail: null,
        admin_users: [],
        operator_users: [],
        operator_candidate_users: [],
        admin_candidate_users: [],
        steps: []
    }),
    props: {
        eventHub: eventHub,
        businessId: {
            type: Number,
            require: true
        },
    },
    methods: {
        getBusinessDetails() {
            this.loading = true
            axios.post('/api/master/businesses/detail', {
                business_id: this.businessId
            })
                .then((res) => {
                    console.log(res)
                    this.business_detail = res.data.business_detail[0]
                    this.admin_users = res.data.admin_users
                    this.operator_users = res.data.operator_users
                    this.operator_candidate_users = res.data.operator_candidate_users
                    this.admin_candidate_users = res.data.admin_candidate_users
                    this.steps = res.data.steps
                })
                .catch((err) => {
                    // TODO エラー時処理
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        copy(){
            this.loading = true
            axios.post('/api/master/businesses/copy', {
                business_id: this.businessId,
                operators: this.operator_users
            })
                .then((res) => {
                    console.log(res)
                    this.loading = false
                })
                .catch((err) => {
                    // TODO エラー時処理
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        updateDetail(){
            this.getBusinessDetails()
        }

    },
    created () {
        let self = this;
        self.getBusinessDetails();

        eventHub.$on('reload', function () {
            this.$vuetify.goTo('#inspire')
            self.getBusinessDetails()
        })
    }
}
</script>

<style>
    #data-content {
        position: relative;
    }
</style>
