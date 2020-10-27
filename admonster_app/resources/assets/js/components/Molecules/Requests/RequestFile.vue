<template>
    <div id="file">
        <div class="file-content pa-3" :style="{ height: selfHeight, maxHeight: selfMaxHeight, minHeight: selfMinHeight }">
            <v-chip label small class="">{{$t('common.file.file_info')}}</v-chip>
            <div class="file-info-wrap pt-2 pb-3 pl-1">
                <div>
                    <span>{{$t('common.file.file_info_details.name')}}：&nbsp;</span><span>{{ requestFile['name'] }}</span>
                </div>
                <div>
                    <span>{{$t('common.file.file_info_details.request_line_number')}}：</span><span>{{ requestFile['row_no'] }}</span>
                </div>
                <div>
                    <span>{{$t('common.file.file_info_details.capture_datetime')}}：</span><span>{{ requestFile['created_at'] | formatDateYmdHm(true) }}</span>
                </div>
                <div>
                    <span>{{$t('common.file.file_info_details.capture_person')}}：</span>
                    <span>
                        <v-tooltip top>
                            <v-avatar slot="activator" size="26px" class="ma-1">
                                <img :src="user_image_path(requestFile['created_user_id'])">
                            </v-avatar>
                            <span>{{ user_name(requestFile['created_user_id']) }}</span>
                        </v-tooltip>
                    </span>
                </div>
            </div>
            <v-chip label small class="">{{ $t('common.file.capture_data') }}</v-chip>
            <div class="file-item-data pt-2 pb-3 pl-1">
                <div>
                    <template v-for="(item, index) in requestFile['content']">
                        <v-layout row wrap :key="index">
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
import LabelComponent from '../../Atoms/Labels/Label'
import ItemType from '../../Molecules/ImportedFiles/ItemType'

export default {
    components:{
        LabelComponent,
        ItemType
    },
    props: {
        requestFile: { type: Object, required: true },
        candidates: { type: Array, required: true },
        labelData: { type: Object, required: false },
        height: {type: String, required: false, default: ''},// auto or cssの単位付き数値
        maxHeight: {type: String, required: false, default: ''},// cssの単位付き数値
        minHeight: {type: String, required: false, default: ''},
    },
    data: ()=> ({
    }),
    computed: {
        selfHeight () {
            if (this.height === 'auto') return 'auto'
            if (this.height === '') return ''
            return `calc(${this.height})`
        },
        selfMaxHeight () {
            if (this.maxHeight === 'auto') return 'auto'
            if (this.height === '') return ''
            return `calc(${this.maxHeight})`
        },
        selfMinHeight () {
            if (this.minHeight === 'auto') return 'auto'
            if (this.height === '') return ''
            return `calc(${this.minHeight})`
        },
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
}
#file .file-content {
    overflow-y: auto;
}
</style>
