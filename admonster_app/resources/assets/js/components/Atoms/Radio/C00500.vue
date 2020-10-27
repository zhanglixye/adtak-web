<template>
    <!-- ラジオボタン -->
    <v-radio-group
        class="c00500"
        :readonly="readonly"
        :disabled="disabled"
        v-model="model"
        :label="label"
        v-bind="option.radioGroup"
        @click.stop=""
        :dark="dark"
    >
        <v-radio
            v-for="(label, index) in labels"
            :key="index"
            :label="label"
            :value="index"
            v-bind="option.radio"
        ></v-radio>
    </v-radio-group>
</template>

<script>
export default {
    props: {
        value: {type: Number, required: false, default: -1},
        readonly: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
        label: {type: String, required: false, default: ''},
        labels: {type: Array, required: false, default: () => []},
        option: {type: Object, required: false, default: () => ({radioGroup:{}, radio:{}})},
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
/* ラベルが少し上に上がっていたので中央に移動 */
.c00500 >>> .v-label {
    margin-bottom: 0;
    text-overflow: ellipsis;
    overflow: hidden;
    display: block;
}

.c00500 >>> .v-input__control {
    width: 100%;
}

.c00500 >>> .v-input--radio-group__input .v-label {
    font-size: calc(12px * 0.75);
}

.c00500 >>> .v-input--radio-group__input .v-radio .v-label {
    font-size: 12px;
}
.c00500 >>> .v-radio--is-disabled .v-label,
.c00500 >>> .v-radio--is-disabled .v-icon {
    color: rgba(0,0,0,.54) !important;
}
</style>
