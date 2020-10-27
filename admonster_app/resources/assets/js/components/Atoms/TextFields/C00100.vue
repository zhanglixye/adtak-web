<template>
    <v-text-field
        class="mt-2 c00100"
        :label="label"
        :readonly="readonly"
        :disabled="disabled"
        v-bind="option"
        v-model="model"
        @click.stop=""
        :dark="dark"
    ></v-text-field>
</template>

<script>
export default {
    props: {
        value: {type: [String, Number], required: false, default: ''},
        readonly: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
        label: {type: String, required: false, default: ''},
        option: {type: Object, required: false, default: () => ({})},
        dark: {type: Boolean, required: false, default: false},
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
.c00100 >>> .v-label {
    font-size: 12px !important;
}
</style>
