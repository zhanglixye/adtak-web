<template>
    <div id="search-list">
        <div class="data-content">
            <!-- table -->
            <v-data-table
                :headers="showHeaders"
                :items="searchList"
                class="elevation-1"
                hide-actions
            >
                <!-- table header -->
                <template slot="headers" slot-scope="props">
                    <tr>
                        <th
                            v-for="(header,i) in props.headers"
                            :key="i"
                            :class="['text-xs-' + header.align]"
                            :style="{ width: header.width }"
                        >
                            <span>{{ header.text }}</span>
                        </th>
                    </tr>
                </template><!-- /table header -->
                <!-- table items -->
                <template slot="items" slot-scope="props">
                    <tr
                        :active="props.selected"
                        @click="goToBusinessPage(props.item)"
                        style="cursor:pointer;"
                    >
                        <template v-for="header in showHeaders">
                            <td
                                v-if="'business_id' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.business_id }}
                            </td>
                            <td
                                v-else-if="'business_name' === header.value"
                                :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item.business_name }}
                            </td>
                            <td
                                v-else-if="'company_name' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.company_name }}
                            </td>
                            <td
                                v-else-if="'business_description' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.business_description }}
                            </td>
                        </template>
                    </tr>
                </template><!-- /table items -->
                <!-- pagenation -->
                <template slot="no-data">
                    <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
                </template><!-- /pagenation -->
            </v-data-table><!-- /table -->
            <progress-circular v-if="loading"></progress-circular>
        </div>
    </div>
</template>

<script>
import store from '../../../../stores/Master/Businesses/store.js'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        ProgressCircular
    },
    data: () => ({
        searchList: [],
        loading: false
    }),
    created () {
        // リスト検索
        this.searchListAsync(this.searchParams)
    },
    computed: {
        searchParams () {
            return store.state.searchParams
        },
        showHeaders () {
            return store.state.showHeaders
        },
        businessDetailUri() {
            return function(item) {
                let uri = '/master/businesses/' + item.business_id
                return uri
            }
        },
    },
    methods: {
        searchListAsync (searchParams) {
            this.loading = true
            axios.get('/api/master/businesses', searchParams)
                .then((res) => {
                    store.commit('setSearchParams', { params: searchParams })
                    this.searchList = res.data.businesses
                    this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        goToBusinessPage (item) {
            window.location.href = this.businessDetailUri(item)
        },
    },
}
</script>
