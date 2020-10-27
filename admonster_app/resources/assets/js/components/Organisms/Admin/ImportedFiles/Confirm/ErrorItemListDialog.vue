<template>
    <v-dialog v-model="errorItemListDialog" persistent scrollable max-width="600px">
        <v-card>
            <v-card-title class="headline">{{ $t('imported_files.error_item_list_dialog.header') }}</v-card-title>
            <v-divider class="ma-0"></v-divider>
            <v-card-text style="max-height: 300px;">
                <div class="pl-2 pr-3">
                    <table id="import-file-error-item-table">
                        <tr v-for="(error, index) in errors" :key="index">
                            <th class="pa-2">{{ $t('imported_files.error') }}{{ index + 1 }}</th>
                            <td class="pa-2">
                                <span>
                                    <span>{{ $t('imported_files.row') }}No.{{ error.row_num }} -- </span>
                                    <label-component v-if="!isNoUseLabel" :label-id="error.label_id" :lang-data="labelData"></label-component>
                                    <span v-else>{{ error.display }}</span>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </v-card-text>
            <v-divider class="ma-0"></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click="close">{{ $t('common.button.close') }}</v-btn>
          </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import LabelComponent from '../../../../Atoms/Labels/Label'
export default {
    components: {
        LabelComponent
    },
    props: {
        eventHub: eventHub,
        errorItemListDialog: { type: Boolean, required: true },
        errors: { type: Array, required: true },
        labelData: { type: Object, required: false, default: null }
    },
    data: () => ({
        loading: false,
        rows: [],
    }),
    computed: {
        isNoUseLabel () {
            return this.labelData === null
        }
    },
    methods: {
        close () {
            this.$emit('update:errorItemListDialog', false)
        },
    }
}
</script>
<style scpoed>
#import-file-error-item-table {
    width: 100%;
}
#import-file-error-item-table tr {
    border-bottom: solid 1px #F5F5F5;
}
#import-file-error-item-table tr th,
#import-file-error-item-table tr td {

}
#import-file-error-item-table tr th {
    width: 20%;
    border-right: solid 1px #F5F5F5;
}
#import-file-error-item-table tr td {
    width: 80%;
}
</style>
