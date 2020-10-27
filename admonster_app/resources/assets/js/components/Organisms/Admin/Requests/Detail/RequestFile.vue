<template>
    <div id="file" class="pa-3" :style="{ height: '66vh' }">
        <div class="title mb-3">
            <v-icon>insert_drive_file</v-icon>
            <span>依頼ファイル</span>
        </div>
        <v-divider class="ma-0"></v-divider>
        <div class="mt-3">
            <v-chip label small class="">ファイル情報</v-chip>
            <div class="file-info-wrap pt-2 pb-3 pl-1">
                <div>
                    <span>ファイル名：&nbsp;</span><span>{{ requestFile.name }}</span>
                </div>
                <div>
                    <span>依頼行番号：</span><span>{{ requestFile.row_no }}</span>
                </div>
                <div>
                    <span>取込日時：</span><span>{{ requestFile.created_at | formatDateYmdHm(true) }}</span>
                </div>
                <div>
                    <span>取込担当者：</span>
                    <span>
                        <v-tooltip top>
                            <v-avatar slot="activator" size="26px" class="ma-1">
                                <img :src="user_image_path(requestFile.created_user_id)">
                            </v-avatar>
                            <span>{{ user_name(requestFile.created_user_id) }}</span>
                        </v-tooltip>
                    </span>
                </div>
            </div>
            <v-chip label small class="">取込データ</v-chip>
            <div class="file-item-data pt-2 pb-3 pl-1">
                 <div>
                    <template v-for="item in requestFileItemData">
                        <v-layout row wrap :key="item.label_id">
                            <v-flex xs3 class="font-weight-bold">
                                <label-component :label-id="item.label_id" :lang-data="labelData"></label-component>
                            </v-flex>
                            <v-flex xs9>
                                <item-type :item="item" v-if="item.value"></item-type>
                            </v-flex>
                        </v-layout>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LabelComponent from '../../../../Atoms/Labels/Label'
import ItemType from '../../../../Molecules/ImportedFiles/ItemType'

export default {
    components:{
        LabelComponent,
        ItemType
    },
    props: {
        requestFile: { type: Object, required: true },
        requestFileItemData: { type: Array },
        candidates: { type: Array, required: true },
        labelData: { type: Object, required: false }
    },
    created() {
        console.log('依頼ファイル')
        console.log(this.labelData)
    },
    methods: {
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
    },
}
</script>

<style scoped>
#file {
    background-color: #fff;
    overflow-y: auto;
}
</style>
