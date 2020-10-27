<template>
    <div>
        <template v-if="item.item_type === ITEM_TYPE.STRING.ID">
            <span>{{ item.value }}</span>
        </template>
        <template v-else-if="item.item_type === ITEM_TYPE.NUM.ID">
            <span>{{ item.value }}</span>
        </template>
        <template v-else-if="item.item_type === ITEM_TYPE.DATE.ID">
            <span>{{ item.value }}</span>
        </template>
        <template v-else-if="item.item_type === ITEM_TYPE.AMOUNT.ID">
            <span>{{ item.value }}</span>
        </template>
        <template v-else-if="item.item_type === ITEM_TYPE.URL.ID">
            <!-- URLを表示し、クリックにより別ウインドウで遷移先を表示する（外部サイトへのリンク） -->
            <external-link :uri="item.value"></external-link>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.RADIO || item.item_type === ITEM_CONFIG_TYPE.SELECT">
            <template v-for="(itemConfigValue, key) in item.item_config_values">
                <label-component
                    v-if="item.value === itemConfigValue.sort"
                    :label-id="itemConfigValue.label_id"
                    :lang-data="langData"
                    :key="key"
                ></label-component>
            </template>
        </template>
        <template v-else-if="!item.value">
            <!-- 空の場合は何も出力しない -->
            <span>&nbsp;</span>
        </template>
        <template v-else>
            <span>{{ item.value }}</span>
        </template>
    </div>
</template>

<script>
import ExternalLink from '../Links/ExternalLink'
import LabelComponent from '../../Atoms/Labels/Label'

export default {
    components: {
        ExternalLink,
        LabelComponent,
    },
    props: {
        item: { type: Object, require: true },
        langData: { type: Object, require: false, default: () => {} },
    },
    data: () => ({
        ITEM_TYPE: _const.ITEM_TYPE,
        ITEM_CONFIG_TYPE: _const.ITEM_CONFIG_TYPE,
    }),
    computed: {
        // typeAmount () {
        //     return '¥' + this.item.val.toLocaleString()
        // },
    }
}
</script>

<style scoped>
</style>
