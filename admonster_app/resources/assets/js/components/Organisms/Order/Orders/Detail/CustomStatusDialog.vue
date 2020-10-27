<template>
    <!-- 確認モーダル -->
    <div id="custom-status-dialog-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="600px">
                <v-form ref="form" @submit.prevent v-model="valid">
                    <v-card class="custom-status">
                        <v-card-title>
                            <span class="headline" v-if="forKey === null">
                                {{ $t('order.orders.setting.custom_status.dialog.add_custom_status') }}
                            </span>
                            <span class="headline" v-else>
                                {{ $t('order.orders.setting.custom_status.dialog.edit_custom_status') }}
                            </span>
                        </v-card-title>
                        <v-flex xs10 offset-xs1>
                            <v-text-field
                                :label="$t('order.orders.setting.custom_status.dialog.custom_status_name')"
                                v-model="customStatus.customStatusName"
                                counter="10"
                                :rules="rules"
                            ></v-text-field>
                            <div>
                                {{ $t('order.orders.setting.custom_status.dialog.attribute_setting') }}
                                <v-btn flat icon color="primary" @click="addAttribute()">
                                    <v-icon>mdi-plus-circle</v-icon>
                                </v-btn>
                            </div>
                            <div class="elevation-1">
                                <draggable :list="customStatus.attributes" :options="{ animation: 300, handle: '.handle' }">
                                    <li
                                        class="list-group-item"
                                        style="overflow: auto;"
                                        v-for="attribute in customStatus.attributes"
                                        :key="attribute.forKey"
                                    >
                                        <span style="float: left; margin-top: 5px;" class="handle">
                                            <v-icon>mdi-menu</v-icon>
                                        </span>
                                        <v-text-field
                                            class="pt-0"
                                            style="float: left; min-width: 360px;"
                                            v-model="attribute.name"
                                            counter="10"
                                            :rules="rules"
                                        ></v-text-field>
                                        <v-btn
                                            style="float: right; color: rgba(0, 0, 0, .54); margin-top: 15px;"
                                            small
                                            icon
                                            @click="deleteAttribute(attribute.forKey, attribute.isUseCustomStatusAttribute)"
                                        >
                                            <v-icon>close</v-icon>
                                        </v-btn>
                                    </li>
                                </draggable>
                            </div>
                        </v-flex>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="grey" dark @click="close">{{ $t('common.button.cancel') }}</v-btn>
                            <v-btn color="primary" :disabled="isDisabled" @click="save()">{{ $t('common.button.setting') }}</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-dialog>
        </v-layout>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </div>
    <!-- / 確認モーダル -->
</template>

<script>
import store from '../../../../../stores/Order/Orders/Detail/store'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'

export default {
    components: {
        ConfirmDialog,
    },
    data () {
        return {
            valid: false,
            modal: false,
            forKey: null,
            rules: [
                v => v.length > 0 || this.$t('order.orders.setting.custom_status.dialog.error.no_text'), // min
                v => v.length <= 10 || this.$t('order.orders.setting.custom_status.dialog.error.limit_text', { number: 10 }), // max
            ],
            customStatus: {
                forKey: null,
                customStatusId: null,
                customStatusName: '',
                attributes: [],
                labelId: null,
                isUseCustomStatus: 0
            },
            deleteAttributeIds: []
        }
    },
    computed: {
        isCreateMode: function() {
            return this.customStatus === undefined
        },
        isDisabled: function() {
            return !this.valid
        },
        attributes: function() {
            return this.customStatus.attributes
        },
        forKeyCustomStatus: function() {
            return store.state.processingData.temporaryForKey.customStatus
        },
        forKeyAttribute: function() {
            return store.state.processingData.temporaryForKey.attribute
        },
    },
    watch: {
        attributes: {
            async handler() {
                await this.$nextTick()
                this.$refs.form.validate()
            },
        },
    },
    methods: {
        save: function() {
            if (this.forKey === null) {
                store.dispatch('addCustomStatus', this.customStatus) // 追加
            } else {
                store.dispatch('updateCustomStatus', { customStatus: this.customStatus, deleteAttributeIds: this.deleteAttributeIds }) // 更新
            }
            this.close()
        },
        deleteAttribute: async function(forKey, isUseCustomStatusAttribute) {
            // 編集の場合のみ削除確認ダイアログ表示
            if (this.forKey !== null) {
                const messages = []
                if (isUseCustomStatusAttribute) messages.push(this.$t('order.orders.setting.custom_status.message.status_used_in_order_detail'))
                if (isUseCustomStatusAttribute) messages.push(this.$t('order.orders.setting.custom_status.message.deleting_after_unselected'))
                messages.push(this.$t('order.orders.setting.custom_status.message.delete'))
                const message = messages.join('<br>')
                if (!(await this.$refs.confirm.show(message))) return
                if (isUseCustomStatusAttribute) {
                    if (!await this.$refs.confirm.show(`<h4>${this.$t('order.orders.setting.custom_status.message.last_confirmation')}</h4>
                        <div style="color: red;">※${this.$t('order.orders.setting.custom_status.message.deleting_after_unselected')}</div>
                        <div style="color: red;">${this.$t('order.orders.setting.custom_status.message.delete_last_confirmation')}</div>`,
                    this.$t('common.button.delete'))) return
                }
            }

            const deleteAttributeId = this.customStatus.attributes.find(item => item.forKey === forKey)['id']
            if (deleteAttributeId !== null) this.deleteAttributeIds.push(deleteAttributeId) // deleteAttributeId !== nullはDB登録済み

            this.customStatus.attributes = this.customStatus.attributes.filter(item => item.forKey !== forKey)
        },
        open: function(forKey = null) {
            this.clear()
            if (forKey !== null) {
                this.forKey = forKey
                const newCustomStatuses = JSON.parse(JSON.stringify(store.state.processingData.customStatuses))
                this.customStatus = newCustomStatuses.find(customStatus => customStatus.forKey === this.forKey)
            } else {
                // 追加の場合は、属性を3つ表示する
                for (let count = 0; count < 3; count++) {
                    this.addAttribute()
                }
            }
            this.modal = true
        },
        close: function() {
            this.modal = false
        },
        clear: function() {
            this.forKey = null
            store.commit('addForKeyCustomStatus')
            this.customStatus = { forKey: this.forKeyCustomStatus, customStatusId: null, customStatusName: '', attributes: [], labelId: null, isUseCustomStatus: 0 }
            this.deleteAttributeIds = []
        },
        addAttribute: function() {
            store.commit('addForKeyAttribute')
            this.customStatus.attributes.push({ id: null, name: '', forKey: this.forKeyAttribute, labelId: null, isUseCustomStatusAttribute: 0 })
        },
    },
}
</script>

<style scoped>
.sortable-ghost {
    background-color: #dcdcdc;
}
.text-color {
    color: rgba(0, 0, 0, 0.87);
}
.cursor-pointer {
    cursor: pointer;
}
.sortable-ghost {
    background-color: #dcdcdc;
}
.handle {
    color: rgba(0, 0, 0, 0.54);
    padding: 10px;
    cursor: move;
}
</style>
