<template>
    <!-- a component that displays money-->
    <!-- Japanese version -->
    <!-- non-decimal -->
    <v-text-field
        id="text-field"
        class="mt-2 c00101"
        :label="label"
        :readonly="readonly"
        v-bind="option"
        v-model="inputValue"
        :rules="[rules.max]"
        prepend-icon="mdi-currency-jpy"
        :mask="mask"
        @click.stop=""
        @focus="isInputMode=true"
        @blur="blur"
        :dark="dark"
        :disabled="disabled"
    ></v-text-field>
</template>

<script>
export default {
    props: {
        value: {type: Number, required: false, default: 0},
        readonly: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
        label: {type: String, required: false, default: ''},
        option: {type: Object, required: false, default: () => ({})},
        dark: {type: Boolean, required: false, default: false},
    },
    data() {
        return {
            rules: {
                max: value => {
                    const message = `0 ~ ${this.max}`
                    if (value === '') return message
                    value = value.toString().replace(/,/g, '')
                    return  0 <= Number(value) || Number(value) <= this.max || message
                }
            },
            max: 9999999999,
            isInputMode: false, // true: ####### false: ###,###,###
            inputValue: ''
        }
    },
    watch: {
        value: function (val) {
            this.inputValue = val ? val : 0
        }
    },
    created() {
        this.inputValue = this.value ? this.value : 0
    },
    computed: {
        mask : function () {
            if (this.isInputMode) return this.max.toString().replace(/\d/g , '#')
            return this.inputValue.toString().replace(/(\d)(?=(\d{3})+$)/g , '$1,').replace(/\d/g , '#')
        },
    },
    methods: {
        blur: function () {
            this.isInputMode = false
            if (this.inputValue === '') {
                this.inputValue = '0'
            } else if (/^00+$/.test(this.inputValue)){ // すべて0の場合
                this.inputValue = '0'
            } else if (/^0\d+/.test(this.inputValue)){ // 先頭に0がついている場合
                this.inputValue = this.inputValue.replace(/^0+/ , '')
            }
            this.$emit('input', Number(this.inputValue))
        }
    },
}
</script>

<style scoped>
/* 入力内容を右揃えに表示 */
.c00101 >>> #text-field {
    text-align: right;
}

.c00101 >>> .v-label {
    font-size: 12px !important;
}
</style>
