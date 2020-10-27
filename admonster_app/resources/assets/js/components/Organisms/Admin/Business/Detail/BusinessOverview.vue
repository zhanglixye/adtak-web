<template>
    <div id="business-overview" class="elevation-1">
        <v-list>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>info</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t('businesses.detail.overview') }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
        <v-container pt-0 id="business-overview-main">
            <v-list subheader two-line>
                <v-list-tile>
                    <v-list-tile-content>
                        <v-list-tile-title>{{ $t('businesses.business_name') }}</v-list-tile-title>
                        <v-list-tile-sub-title>{{ business_detail.business_name }}</v-list-tile-sub-title>
                    </v-list-tile-content>
                </v-list-tile>
                <v-list-tile>
                    <v-list-tile-content>
                        <v-list-tile-title>{{ $t('businesses.company_name') }}</v-list-tile-title>
                        <v-list-tile-sub-title>{{ business_detail.company_name }}</v-list-tile-sub-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list>
            <v-list subheader three-line>
                <v-list-tile>
                    <v-list-tile-content>
                        <v-list-tile-title>{{ $t('businesses.description') }}</v-list-tile-title>
                        <v-list-tile-sub-title>{{ business_detail.business_description }}</v-list-tile-sub-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list>
        </v-container>
    </div>
</template>

<script>
export default {
    props: {
        business_detail: { type: Object }
    }
}
</script>

<style scoped>
#business-overview {
    background-color: #ffffff;
}
#business-overview-main {
    height: 350px;
}
</style>
