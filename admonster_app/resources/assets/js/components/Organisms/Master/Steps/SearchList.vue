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
                    <tr :active="props.selected">
                        <template v-for="header in showHeaders">
                            <td
                                v-if="'step_id' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.id }}
                            </td>
                            <td
                                v-else-if="'step_name' === header.value"
                                :key="header.text"
                                class="text-xs-left"
                            >
                                {{ props.item.name }}
                            </td>
                            <td
                                v-else-if="'updated_at' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                {{ props.item.updated_at }}
                            </td>
                            <td
                                v-else-if="'updated_user' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                <users-overview
                                    :users="updatedUsers(props.item)"
                                    :candidates="candidates"
                                ></users-overview>
                            </td>
                            <td
                                v-else-if="'before_work_template' === header.value"
                                :key="header.text"
                                class="text-xs-center"
                            >
                                <v-chip
                                    style="cursor: pointer"
                                    color="primary"
                                    label
                                    outline
                                    @click="templateEditShow(props.item)"
                                >
                                    <span>{{ $t('list.config') }}</span>
                                </v-chip>
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
            <template-edit-dialog ref="TemplateEditDialog" :labels="labels" @set-step="setStep"></template-edit-dialog>
        </div>
    </div>
</template>

<script>
import store from '../../../../stores/Master/Steps/store.js'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import TemplateEditDialog from './TemplateEditDialog'

export default {
    components: {
        ProgressCircular,
        UsersOverview,
        TemplateEditDialog,
    },
    data: () => ({
        searchList: [],
        candidates: [],
        labels: {},
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
    },
    methods: {
        searchListAsync (searchParams) {
            this.loading = true
            axios.get('/api/master/steps', searchParams)
                .then((res) => {
                    store.commit('setSearchParams', { params: searchParams })
                    this.searchList = res.data.list
                    this.candidates = res.data.candidates
                    this.labels = res.data.labels
                    this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        updatedUsers (item) {
            return {
                allocated_user_ids: item.updated_user_id ? [item.updated_user_id] : [],
                completed_user_ids: [],
            }
        },
        setStep (step) {
            this.searchList = this.searchList.map(item => item.id == step.id ? step : item)
        },
        templateEditShow: async function (step) {
            this.$refs.TemplateEditDialog.show(step)
        },
    },
}
</script>
