<template>
    <v-dialog
        v-model="dialog"
        persistent
        :width="width"
    >
        <v-card>
            <v-card-title
                class="pb-2"
                primary-title
            >
                <v-flex class="headline xs8 text-cut" style="flex-basis: auto;">{{ itemName }}</v-flex>
                <span class="headline">{{ $t('order.order_details.dialog.setting_display_format.display_format') }}</span>
            </v-card-title>

            <v-card-text>
                <span>{{ $t('order.order_details.dialog.setting_display_format.data_type', { item_type: itemType }) }}</span>
                <div style="max-height: 66vh; overflow-y: auto; margin-top: 16px">
                    <span
                        v-for="displayFormat in displayFormats"
                        :style="{ 'display': 'flex', 'margin-left': '32px' }"
                        :key="displayFormat.id"
                    >
                        <v-checkbox
                            color="primary"
                            :label="displayFormat.text"
                            v-model="selectedDisplayFormats"
                            :value="displayFormat.id"
                            :key="displayFormat.id"
                        ></v-checkbox>
                    </span>
                    <span
                        :style="{ 'display': 'flex', 'margin-top': '16px', 'margin-left': '32px' }"
                        v-if="displayFormats.length === 0"
                    >{{ $t('order.order_details.dialog.setting_display_format.here_display_type_unsettable') }}</span>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="grey"
                    dark
                    text
                    @click="close"
                >
                    {{ $t('common.button.cancel') }}
                </v-btn>
                <v-btn
                    v-if="displayFormats.length !== 0"
                    color="primary"
                    text
                    @click="save"
                >
                    {{ $t('common.button.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>

export default {
    props: {
        width: { type: [Number, String], required: false, default: 500 },
        callback: { type: String, required: true }
    },
    data: () => ({
        dialog: false,
        itemName: '',
        displayFormats: [],
        selectedDisplayFormats: [],
        orderFileImportColumnConfigId: null,
        itemType: ''
    }),
    methods: {
        show (itemName, displayFormats, selectedDisplayFormats, orderFileImportColumnConfigId, itemType) {
            this.dialog = true
            this.itemName = itemName
            this.displayFormats = displayFormats
            this.selectedDisplayFormats = selectedDisplayFormats
            this.orderFileImportColumnConfigId = orderFileImportColumnConfigId
            this.itemType = itemType
        },
        close () {
            this.dialog = false
        },
        save () {
            eventHub.$emit(this.callback, this.selectedDisplayFormats, this.orderFileImportColumnConfigId)
            this.close()
        },
    }
}
</script>

<style scoped>
.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
</style>
