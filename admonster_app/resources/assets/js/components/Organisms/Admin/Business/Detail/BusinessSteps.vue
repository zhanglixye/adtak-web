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
                <v-tab href="#business-step-list" class="primary--text">
                    <v-icon color="grey lighten-3">work</v-icon>
                    <span class="tab-title ml-1">{{ $t('businesses.detail.tab.overview') }}</span>
                </v-tab>
                <v-tab href="#business-step-candidates" class="primary--text">
                    <v-icon color="grey lighten-3">assignment_ind</v-icon>
                    <span class="tab-title ml-1">{{ $t('businesses.detail.tab.candidates')}}</span>
                </v-tab>
            </v-tabs>
        </v-toolbar>
        <v-tabs-items v-model="tabs" class="white elevation-1">
            <v-tab-item
                :key="1"
                :value="'business-step-list'"
            >
                <business-step-list :steps="steps"></business-step-list>
            </v-tab-item>
            <v-tab-item
                :key="2"
                :value="'business-step-candidates'"
            >
                <business-step-candidates :steps="steps" :candidates="candidates"></business-step-candidates>
            </v-tab-item>
        </v-tabs-items>
    </div>
</template>

<script>
import BusinessStepList from './BusinessStepList';
import BusinessStepCandidates from './BusinessStepCandidates';

export default {
    components: {
        BusinessStepList,
        BusinessStepCandidates
    },
    props: {
        steps: { type: Array },
        candidates: { candidates: Array }
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
