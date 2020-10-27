<template>
    <div id="list-content">
        <!-- <v-list>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>assignment_ind</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t('businesses.detail.business_step') }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list> -->
        <v-toolbar tabs color="grey darken-1">
            <v-tabs
                v-model="tabs"
                fixed-tabs
                color="transparent"
                centered
                flat
            >
                <v-tabs-slider></v-tabs-slider>
                <!--<v-tab href="#business-step-list" class="primary&#45;&#45;text">-->
                    <!--<v-icon color="grey lighten-3">work</v-icon>-->
                    <!--<span class="tab-title ml-1">{{ $t('businesses.detail.tab.overview') }}</span>-->
                <!--</v-tab>-->
                <v-tab href="#business-step-candidates" class="primary--text">
                    <v-icon color="grey lighten-3">assignment_ind</v-icon>
                    <span class="tab-title ml-1">{{ $t('master.businesses.tab.work_candidates_list')}}</span>
                </v-tab>
                <v-tab href="#business-step-educate-candidates" class="primary--text">
                    <v-icon color="grey lighten-3">assignment_ind</v-icon>
                    <span class="tab-title ml-1">{{ $t('master.businesses.tab.educate_candidates_list')}}</span>
                </v-tab>
            </v-tabs>
        </v-toolbar>
        <v-tabs-items v-model="tabs" class="white elevation-1">
            <!--<v-tab-item-->
                <!--:key="1"-->
                <!--:value="'business-step-list'"-->
            <!--&gt;-->
                <!--<business-step-list :steps="steps"></business-step-list>-->
            <!--</v-tab-item>-->
            <v-tab-item
                :key="1"
                :value="'business-step-candidates'"
            >
                <business-step-candidates :steps="steps" :candidates="candidates" @update-detail="updateDetail"></business-step-candidates>
            </v-tab-item>
            <v-tab-item
                    :key="2"
                    :value="'business-step-educate-candidates'"
            >
                <business-step-educate-candidates :steps="steps" :candidates="candidates" @update-detail="updateDetail"></business-step-educate-candidates>
            </v-tab-item>
        </v-tabs-items>
    </div>
</template>

<script>
// import BusinessStepList from './BusinessStepList';
import BusinessStepCandidates from './BusinessStepCandidates';
import BusinessStepEducateCandidates from './BusinessStepEducateCandidates';

export default {
    components: {
        // BusinessStepList,
        BusinessStepCandidates,
        BusinessStepEducateCandidates
    },
    props: {
        steps: { type: Array, required: true},
        candidates: { type: Array, required: true}
    },
    methods: {
        users (step) {
            if (step){
                let work_user_ids = step.work_user_ids ? step.work_user_ids.split(',') : [];
                return work_user_ids;
            }
        },
        updateDetail () {
            this.$emit('update-detail')
        }

    },
    data: () => ({
        tabs: null
    })
}
</script>
<style scoped>
.tab-title {
    color: #fff;
}
.tabs__content
{
    min-height: 100vh;
}
</style>
