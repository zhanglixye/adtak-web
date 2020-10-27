<template>
    <div id="search-condition" class="elevation-1">
        <v-form v-model="valid">
            <div>
                <!--main-->
                <div id="search-condition-main">
                    <v-layout row wrap>
                        <!--件名-->
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-text-field
                                v-model="searchParams.order_detail_name"
                                prepend-icon="subject"
                                hide-details
                                :label="$t('list.search_condition.system_subject')"
                                :placeholder="$t('list.search_condition.partial_search')"
                                @keyup.enter="search"
                            ></v-text-field>
                        </v-flex>
                        <!--件名-->
                        <!--日付-->
                        <v-flex xs12 sm12 md4 pr-3 d-flex align-center>
                            <v-icon style="margin-top: 15px;">event</v-icon>
                            <date-picker
                                v-model="searchParams.from"
                                :enterEvent="search"
                                hide-details
                                :label="`${$t('list.search_condition.created_at')}${$t('list.search_condition.from')}`"
                            ></date-picker>
                            <span style="text-align: center;">～</span>
                            <date-picker
                                v-model="searchParams.to"
                                date-to
                                :enterEvent="search"
                                hide-details
                                :label="`${$t('list.search_condition.created_at')}${$t('list.search_condition.to')}`"
                            ></date-picker>
                        </v-flex>
                        <!--日付-->
                        <!--システムステータス-->
                        <v-flex xs12 sm12 md3 pr-3>
                            <v-select
                                v-model="searchParams.status"
                                :items="status"
                                item-text="text"
                                item-value="value"
                                :label="$t('list.search_condition.status')"
                                prepend-icon="local_shipping"
                                dense
                                hide-details
                            ></v-select>
                        </v-flex>
                        <!--システムステータス-->
                        <v-spacer></v-spacer>
                        <div class="btn-center-block">
                            <v-tooltip top>
                                <v-btn
                                    v-show="!show"
                                    color="primary"
                                    fab
                                    small
                                    @click="search"
                                    slot="activator"
                                >
                                    <v-icon>search</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.search') }}</span>
                            </v-tooltip>
                            <v-btn icon @click="show = !show">
                                <v-icon>{{ toggleIcon }}</v-icon>
                            </v-btn>
                        </div>
                    </v-layout>
                </div>
                <!--main-->

                <!--detail-->
                <v-slide-y-transition>
                    <div id="search-condition-detail" v-show="show">
                        <v-layout row wrap>
                            <!--表示名-->
                            <v-flex xs12 sm12 md3 pr-3 style="max-width: 150px;">
                                <v-select
                                    class="displayName"
                                    :label="$t('order.order_details.search_condition.display.label')"
                                    ref="country"
                                    item-text="text"
                                    item-value="column"
                                    prepend-icon="mdi-label"
                                    v-model="displayColumn"
                                    :items="displayNameItems"
                                    :no-data-text="$t('order.order_details.search_condition.display.no_data')"
                                    dense
                                ></v-select>
                            </v-flex>
                            <v-flex xs12 sm12 md3 pr-3>
                                <div style="display: flex!important; align-items: center;">
                                    <template v-if="isDateFormat">
                                        <date-picker
                                            v-model="searchParams.display_from"
                                            :enterEvent="search"
                                            hide-details
                                            :label="$t('list.search_condition.from')"
                                        ></date-picker>
                                        <span style="text-align: center;">～</span>
                                        <date-picker
                                            v-model="searchParams.display_to"
                                            date-to
                                            :enterEvent="search"
                                            hide-details
                                            :label="$t('list.search_condition.to')"
                                        ></date-picker>
                                    </template>
                                    <template v-else>
                                        <v-text-field
                                            v-model="searchParams.display_text"
                                            hide-details
                                            :placeholder="$t('list.search_condition.partial_search')"
                                            @keyup.enter="search"
                                        ></v-text-field>
                                    </template>
                                </div>
                            </v-flex>
                            <!--表示名-->
                            <!--カスタムステータス-->
                            <v-flex pr-3 style="max-width: 220px;">
                                <v-select
                                    :label="$t('order.order_details.search_condition.custom_status.status.label')"
                                    v-model="selectedCustomStatusId"
                                    item-text="text"
                                    item-value="id"
                                    :items="customStatuses"
                                    :no-data-text="$t('order.order_details.search_condition.custom_status.status.no_data')"
                                    dense
                                >
                                    <template slot="prepend">
                                        <v-icon>mdi-label</v-icon>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex pr-3 style="max-width: 190px;">
                                <v-select
                                    v-model="selectedAttributeId"
                                    :label="$t('order.order_details.search_condition.custom_status.attribute.label')"
                                    item-text="text"
                                    item-value="id"
                                    :items="attributes"
                                    dense
                                ></v-select>
                            </v-flex>
                            <!--カスタムステータス-->
                        </v-layout>
                        <div class="btn-center-block pt-2">
                            <v-btn dark color="grey" @click="clear">{{ $t('common.button.reset') }}</v-btn>
                            <v-btn color="primary" @click="search">{{ $t('common.button.search') }}</v-btn>
                        </div>
                    </div>
                </v-slide-y-transition>
                <!--detail-->
            </div>
        </v-form>
    </div>
</template>

<script>
import DatePicker from '../../../Atoms/Pickers/DatePicker'
import store from '../../../../stores/Order/OrderDetails/store'

export default {
    components: {
        DatePicker,
    },
    props: {
        orderId: { type: Number, required: true }
    },
    data: () => ({
        valid: false,
        // 検索条件（詳細）の開閉フラグ
        show: false,
        //検索条件
        searchParams: JSON.parse(JSON.stringify(store.state.searchParams)),
    }),
    computed: {
        displayLangCode () {
            return store.state.displayLangCode
        },
        status () {
            return [
                { text: this.$t('order.order_details.search_condition.status.all'), value: null },
                { text: this.$t('order.order_details.search_condition.status.active'), value: _const.FLG.ACTIVE },
                { text: this.$t('order.order_details.search_condition.status.inactive'), value: _const.FLG.INACTIVE },
            ]
        },
        stateSearchParams () {
            return store.state.searchParams
        },
        labelData () {
            return store.state.processingData.labelData
        },
        customStatuses () {
            const customStatuses = store.state.processingData.customStatuses.slice()
            if (customStatuses.length === 0) return []
            customStatuses.unshift({ attributes: [{ id: null, text: '' }], id: null, text: '' })
            return customStatuses
        },
        attributes () {
            const selectedCustomStatus = this.customStatuses.find(customStatus => customStatus['id'] === this.selectedCustomStatusId)
            if (selectedCustomStatus === undefined) return [{ id: null, text: '' }]
            return selectedCustomStatus['attributes']
        },
        selectedCustomStatusId: {
            set (id) {
                this.selectedAttributeId = null
                store.commit('setSelectedCustomStatusId', id)
            },
            get () {
                return this.stateSearchParams.selected_custom_status_id
            }
        },
        selectedAttributeId: {
            set (id) {
                store.commit('setSelectedAttributeId', id)
            },
            get () {
                return this.stateSearchParams.selected_attribute_id
            }
        },
        isDateFormat () {
            return this.searchParams.display_item_type === _const.ITEM_TYPE.DATE.ID
        },
        displayNameItems () {
            return store.state.processingData.orderFileImportColumnConfigs.map(item => ({
                value: item.label_id,
                text: store.state.processingData.labelData[this.displayLangCode][item.label_id],
                column: item.column,
                display_item_type: item.item_type,
                id: item.id
            }))
        },
        displayColumn: {
            set(column) {
                const orderFileImportColumnConfig = store.state.processingData.orderFileImportColumnConfigs.find(item => item.column === column)
                this.searchParams.display_id = orderFileImportColumnConfig === undefined ? null : orderFileImportColumnConfig.id
                this.searchParams.display_item_type = orderFileImportColumnConfig === undefined ? null : orderFileImportColumnConfig.item_type
                this.searchParams.display_column = orderFileImportColumnConfig === undefined ? null : orderFileImportColumnConfig.column
                // リセット
                this.searchParams.display_from = ''
                this.searchParams.display_to = ''
                this.searchParams.display_text = ''
            },
            get() {
                return this.searchParams.display_column
            }

        },
        toggleIcon () {
            return this.show ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
        },

    },
    watch: {
        searchParams: {
            handler: function (val) {
                store.commit('setSearchParams', val)
            },
            deep: true
        },
        stateSearchParams: {// state内のsearchParamsを監視
            handler: function (val) {
                Object.assign(this.searchParams, val)
            },
            deep: true
        },
    },
    methods: {
        search () {
            store.commit('setSearchParams', { page: 1 })
            store.dispatch('searchOrderDetailList')
        },
        clear () {
            //検索条件を初期状態に戻す
            store.commit('resetSearchParams')
            store.dispatch('searchOrderDetailList')
        },
    },
}
</script>

<style scoped>
.displayName >>> .v-select__selections {
    max-width: 70px;
    white-space: nowrap;
    overflow: hidden;
}
</style>
