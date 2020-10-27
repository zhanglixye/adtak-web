<template>
    <v-textarea
        class="mt-2 c00200"
        :readonly="readonly"
        :disabled="disabled"
        :label="label"
        v-bind="option"
        v-model="model"
        @click.stop=""
        :dark="dark"
    ></v-textarea>
</template>

<script>
export default {
    props: {
        value: {type: String, required: false, default: ''},
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
.c00200 >>> .v-label,
.c00200 >>> textarea {
    font-size: 12px !important;
}
</style>
