<template>
<v-app id="inspire">
    <app-menu :drawer="drawer"></app-menu>
    <app-header
        :title="business_detail.business_name"
        :subtitle="$t('businesses.detail.title')"
    ></app-header>
    <v-content id="data-content">
        <edit-modal
            :users="users()"
            :steps="steps"
            :candidates="candidates"
            @success="showSuccessMessage"
            @error="showErrorMessage"
        ></edit-modal>
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
                    <business-candidates :admins="admins" :candidates="candidates"></business-candidates>
                </v-flex>
                <v-flex xs12>
                    <business-steps :steps="steps" :candidates="candidates"></business-steps>
                </v-flex>
            </v-layout>
            <!-- Main -->

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
import EditModal from '../../../Organisms/Admin/Business/Detail/EditModal';
import ConfirmModal from '../../../Organisms/Admin/Business/Detail/ConfirmModal';
import BusinessOverview from '../../../Organisms/Admin/Business/Detail/BusinessOverview';
import BusinessCandidates from '../../../Organisms/Admin/Business/Detail/BusinessCandidates';
import BusinessSteps from '../../../Organisms/Admin/Business/Detail/BusinessSteps';
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular';

export default {
    components: {
        PageHeader,
        PageFooter,
        EditModal,
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
        steps: [],
        candidates: [],
        admins: []
    }),
    props: {
        eventHub: eventHub,
        businessId: {
            type: Number,
            require: true
        },
    },
    methods: {
        getBusinessDetails () {
            this.loading = true
            axios.post('/api/businesses/detail',{
                business_id: this.businessId
            })
                .then((res) => {
                    this.business_detail = res.data.business_detail
                    this.steps = res.data.steps
                    this.candidates = res.data.candidates
                    this.admins = res.data.admins
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        users () {
            if (this.business_detail){
                return {
                    admin_user_ids: this.business_detail.admin_user_ids ? this.business_detail.admin_user_ids.split(',') : [],
                    candidate_user_ids: this.business_detail.candidate_user_ids ? this.business_detail.candidate_user_ids.split(',') : [],
                }
            }
        },
        showSuccessMessage () {
            this.alerts['success'].model = true
        },
        showErrorMessage () {
            this.alerts['internal_error'].model = true
        },
    },
    created() {
        let self = this;
        self.getBusinessDetails();

        eventHub.$on('reload', function() {
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
