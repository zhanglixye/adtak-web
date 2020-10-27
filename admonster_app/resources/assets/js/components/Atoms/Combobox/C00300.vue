<template>
    <div @click.stop="">
        <v-combobox
            class="mt-2 c00300"
            v-model="model"
            :items="items"
            :search-input.sync="search"
            :label="label"
            :readonly="readonly"
            v-bind="option"
            :dark="dark"
            :disabled="disabled"
        ></v-combobox>
    </div>
</template>

<script>
export default {
    props: {
        value: {type: [Number, String, Array], required: false, default: ''},
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
/*  vue.js v2.1.2以上だと不要
    横幅より長い場合は中の文字を自動で表示しない
*/
.c00300 >>> .v-select__selections {
    overflow: hidden;
}

.c00300 >>> .v-label,
.c00300 >>> .v-select__selections {
    font-size: 12px !important;
}
.c00300 >>> .v-input__append-inner,
.c00300 >>> .v-input__prepend-inner {
    margin-top: 14px;
}
</style>
