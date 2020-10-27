<template>
    <div id="search-list">
        <!-- toolbar -->
        <!-- <v-layout row wrap align-center>
            <v-spacer></v-spacer>
            <v-tooltip top>
                <v-btn flat icon color="primary" slot="activator">
                    <v-icon>settings</v-icon>
                </v-btn>
                <span>{{ $t('list.config') }}</span>
            </v-tooltip>
        </v-layout> -->
        <!-- toolbar -->

        <div class="data-content">
            <!-- table -->
            <v-data-table
                :headers="headers"
                :items="businesses"
                hide-actions
                class="elevation-1"
            >
                <template slot="headers" slot-scope="props">
                    <tr>
                        <th
                            v-for="(header,i) in props.headers"
                            :key="i"
                            :class="['text-xs-' + header.align]"
                            :style="{ width: header.width }"
                        >
                            <v-tooltip top v-if="header.detail">
                                <span slot="activator">{{ header.text }}</span>
                                <span>{{ header.detail ? header.detail : header.text }}</span>
                            </v-tooltip>
                            <span v-else>{{ header.text }}</span>
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr :active="props.selected">
                        <td class="text-xs-left">
                            <a :href="businessDetailUri(props.item)">
                                {{ props.item.business_name }}
                            </a>
                        </td>
                        <!-- <td class="text-xs-left">{{ props.item.company_name }}</td> -->
                        <td class="text-xs-center">-</td>
                        <td class="text-xs-center">
                            <!-- <a :href="businessStatesUri(props.item)"> -->
                                {{ props.item.imported_count }}
                            <!-- </a> -->
                        </td>
                        <td class="text-xs-center">
                            <!-- <a :href="businessStatesUri(props.item)"> -->
                                {{ props.item.excluded_count }}
                            <!-- </a> -->
                        </td>
                        <td class="text-xs-center">
                            <!-- <a :href="businessStatesUri(props.item)"> -->
                                {{ props.item.wip_count }}
                            <!-- </a> -->
                        </td>
                        <td class="text-xs-center">
                            <!-- <a :href="businessStatesUri(props.item)"> -->
                                {{ props.item.completed_count }}
                            <!-- </a> -->
                        </td>
                        <td class="text-xs-center">
                            <!-- <a :href="businessStatesUri(props.item)"> -->
                                {{ props.item.wip_count + props.item.completed_count }}
                            <!-- </a> -->
                        </td>
                    </tr>
                </template>
                <template slot="no-data">
                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                </template>
            </v-data-table>
            <!-- table -->

            <progress-circular v-if="loading"></progress-circular>
        </div>
    </div>
</template>

<script>
import store from '../../../../stores/Admin/Businesses/store.js'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    data: () => ({
        businesses: [],

        //loading
        loading: false
    }),
    components: {
        ProgressCircular,
    },
    created () {
        let self = this;

        // 検索イベント登録
        eventHub.$on('search', function({ searchParams }) {
            self.searchBusinessListAsync(searchParams)
        })

        // 検索
        this.searchBusinessListAsync(this.searchParams)
    },
    computed: {
        searchParams () {
            return store.state.searchParams
        },
        headers (){
            return  [
                { text: Vue.i18n.translate('businesses.business_name'), value: 'business_name', align: 'center', sortable: false, width: '' },
                // { text: Vue.i18n.translate('businesses.company_name'), value: 'company_name', align: 'center', sortable: false, width: '' },
                { text: Vue.i18n.translate('businesses.workload'), value: 'workload_count', align: 'center', sortable: false, width: '100px' }, // TODO: 表頭のみ表示するが値の算出方法は検討中
                { text: Vue.i18n.translate('businesses.request'), detail: Vue.i18n.translate('businesses.count'), value: 'imported_count', align: 'center', sortable: false, width: '100px' },
                { text: Vue.i18n.translate('businesses.excluded'), detail: Vue.i18n.translate('businesses.count'), value: 'excluded_count', align: 'center', sortable: false, width: '100px' },
                { text: Vue.i18n.translate('businesses.wip'), detail: Vue.i18n.translate('businesses.count'), value: 'wip_count', align: 'center', sortable: false, width: '100px' },
                { text: Vue.i18n.translate('businesses.completed'), detail: Vue.i18n.translate('businesses.count'), value: 'completed_count', align: 'center', sortable: false, width: '100px' },
                { text: Vue.i18n.translate('businesses.all_active_count'), value: 'all_active_count', align: 'center', sortable: false, width: '100px' },
            ]
        },
        businessDetailUri () {
            return function (item) {
                return '/management/businesses/' + item.business_id
            }
        },
        businessStatesUri () {
            let self = this
            return function (item) {
                let uri = '/management/business_states?business_name=' + encodeURIComponent(item.business_name)
                uri = uri + '&date_type=' + encodeURIComponent(self.searchParams.date_type)
                uri = uri + '&from=' + encodeURIComponent(self.searchParams.from)
                uri = uri + '&to=' + encodeURIComponent(self.searchParams.to)

                return uri
            }
        },
    },
    methods: {
        searchBusinessListAsync (searchParams) {
            this.loading = true
            axios.post('/api/businesses', searchParams)
                .then((res) => {
                // 検索条件をstoreに保存
                    store.commit('setSearchParams', { params: searchParams })
                    // 検索結果を画面に反映
                    this.businesses = res.data.businesses
                    this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                })
        }
    },
}
</script>
