<template>
    <div id="settings">
        <div class="elevation-1">
            <div class="data-content">
                    <table cellpadding=15 sellspacing=15
                        style='border-collapse: separate; border-spacing: 15px; width: 100%; '>
                    <tr>
                        <th style="width: 300px">{{ $t('order.orders.setting.item.item') }}</th>
                        <th style="width: 300px">{{ $t('order.orders.setting.item.current_item_name') }}</th>
                        <th>{{ $t('order.orders.setting.item.new_item_name') }}</th>
                    </tr>
                    <template v-for="(item) in items">
                        <tr :key="item.id">
                            <td class="text-cut" style="max-width: 150px;">
                                <v-tooltip top>
                                    <span slot="activator" style="white-space: nowrap;">{{ item.item }}</span>
                                    <span>{{ item.item }}</span>
                                </v-tooltip>
                            </td>
                            <td class="text-cut" style="max-width: 150px;">
                                <v-tooltip top>
                                    <span slot="activator" style="white-space: nowrap;">{{ item.display }}</span>
                                    <span>{{ item.display }}</span>
                                </v-tooltip>
                            </td>
                            <td>
                                <v-form
                                    v-model="valid"
                                    @submit.prevent=""
                                >
                                    <v-text-field
                                        :value="item.newDisplay"
                                        @input="update($event, item.id)"
                                        single-line
                                        counter="256"
                                        :rules="rules.displayName"
                                        :placeholder="item.display"
                                        :hint="$t('order.orders.setting.item.text_hint')"
                                        clearable
                                    >
                                        <template slot="prepend">
                                            <v-tooltip top>
                                                <v-icon slot="activator" @click="copy(item.id)">forward</v-icon>
                                                <span>{{ $t('order.orders.setting.item.copy_the_current_display_name') }}</span>
                                            </v-tooltip>
                                        </template>
                                    </v-text-field>
                                </v-form>
                            </td>
                        </tr>
                    </template>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import store from '../../../../../../stores/Order/Orders/Detail/Item/store'

export default {
    components: {
    },
    data: () => ({
        rules: {
            displayName: [
                v => v === null || v.length <= 256 || Vue.i18n.translate('order.orders.setting.item.error.limit_display_name', {number : 256})// max
            ]
        },
    }),
    computed: {
        valid: {
            set(val) {
                store.commit('setValid', val)
            },
            get() {
                return store.state.processingData.valid
            }
        },
        items: {
            get() {
                return store.getters.items
            }
        }
    },
    methods: {
        isChanged: function(itemId) {
            const item = this.items.find(item => item.id === itemId)
            return item.display !== item.newDisplay
        },
        copy: function(itemId) {
            const item = this.items.find(item => item.id === itemId)
            this.update(item.display, item.id)
        },
        update: function(value, itemId) {
            const newValue = [null, undefined].includes(value) ? '' : value
            const items = JSON.parse(JSON.stringify(this.items))
            items.forEach(item => {
                if (item.id === itemId) item.newDisplay = newValue
            })
            store.commit('setProcessingData', {items: items})
        },
    },
}
</script>

<style scoped>
th {
    color: rgba(0,0,0,.54);
    text-align: center;
}

.text-cut {
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
