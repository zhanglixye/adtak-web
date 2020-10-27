<template>
    <v-dialog v-model="dialog" persistent width="800">
        <v-card>
            <v-card-title>
                <span class="headline">{{ $t('order.order_details.show.information_component_management.order_detail_info.title') }}</span>
            </v-card-title>
            <v-card-text>
                <v-list two-line>
                    <draggable :list="columns" :options="{ animation: 300, handle: '.handle' }">
                        <v-list-tile v-for="column in columns" :key="column.column_id">
                            <v-list-tile-avatar>
                                <v-checkbox
                                    :input-value="column.selected"
                                    class="pa-0 ma-0"
                                    color="primary"
                                    hide-details
                                    @click.native="selected(column.column_id)"
                                >
                                    <v-icon slot="prepend" class="info-icon-size handle">menu</v-icon>
                                </v-checkbox>
                            </v-list-tile-avatar>
                            <v-list-tile-content>
                                <v-list-tile-sub-title>
                                    <v-textarea
                                        v-model="column.value"
                                        :label="getDisplayName(column.label_id)"
                                        rows="1"
                                        disabled
                                    ></v-textarea>
                                </v-list-tile-sub-title>
                            </v-list-tile-content>
                        </v-list-tile>
                    </draggable>
                </v-list>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn color="grey" dark @click="close">{{ $t('common.button.back') }}</v-btn>
                <v-btn color="primary" light @click="reflect" :disabled="!valid">{{ $t('common.button.reflect') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    data() {
        return {
            dialog: false,
            columns: [],
            cursorIndex: null,
        }
    },
    computed: {
        labelData() {
            return store.state.processingData.labelData
        },
        getDisplayName() {
            return function(labelId) {
                return this.labelData[this.$i18n.locale()][labelId]
            }
        },
        valid() {
            return this.columns.filter(column => column.selected === true).length > 0
        },
    },
    methods: {
        show(cursorIndex) {
            const columns = JSON.parse(JSON.stringify(store.state.processingData.orderDetailData.columns))
            this.columns = Object.entries(columns).map(([key, column]) => Object.assign(column, { column_id: key, selected: false }))
            this.dialog = true
            this.cursorIndex = cursorIndex
        },
        reflect() {
            eventHub.$emit('set-column-value', {
                columns: this.columns.filter(column => column.selected).map(column => Object.assign(column, { label_value: this.getDisplayName(column.label_id) })),
                cursorIndex: this.cursorIndex
            })
            this.close()
        },
        close() {
            this.columns = []
            this.dialog = false
        },
        selected(index) {
            this.columns.filter(column => column.column_id == index).forEach(column => column.selected = !column.selected)
        },
    },
}
</script>

<style scoped>
</style>
