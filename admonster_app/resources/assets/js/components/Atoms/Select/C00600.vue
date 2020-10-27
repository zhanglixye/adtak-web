<template>
    <div class="c00600" @click.stop="">
        <v-select
            :readonly="readonly"
            :disabled="disabled"
            :items="items"
            v-model="model"
            :label="label"
            v-bind="option"
            :dark="dark"
        ></v-select>
    </div>
</template>

<script>
export default {
    props: {
        value: {type: [String, Number, Array], required: false, default: ''},
        items: {type: Array, required: false, default: () => []},
        readonly: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
        label: {type: String, required: false, default: ''},
        option: {type: Object, required: false, default: () => ({})},
        dark: {type: Boolean, required: false, default: false},
    },
    data: function() {
        return {
            search: null
        }
    },
    computed: {
        model: {
            get: function () {
                return this.value
            },
            set: function (val) {
                this.$emit('input', val)
            }
        },
    },
}
</script>

<style scoped>
/* 枠の幅を超えて表示させない */
/*  vue.js v2.1.6以上だと不要
    横幅より長い場合は中の文字の末を自動で「...」に置き換える
*/
.c00600 >>> .v-select__selections {
    overflow: hidden;
    font-size: 12px;
}

.c00600 >>> .v-select__slot .v-label {
    font-size: 12px;
    color: rgba(0,0,0,.87) !important;
}
</style>
