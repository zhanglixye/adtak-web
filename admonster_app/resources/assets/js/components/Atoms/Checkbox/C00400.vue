<template>
    <div style="position: relative;" @click.stop="">
        <!-- 複数チェックボックス -->
        <label class="c00400" :class="['v-label', dark ? 'theme--dark' : 'theme--light']">{{label}}</label>
        <template v-if="labels.length > 1">
            <v-checkbox
                class="c00400"
                v-for="(label, index) in labels" :key="index"
                :readonly="readonly"
                :disabled="disabled"
                :input-value="model === null ? null : model[index]"
                @change="emitArray($event, index)"
                :label="label"
                v-bind="option"
                :dark="dark"
            ></v-checkbox>
        </template>
        <!-- 単一のチェックボックス -->
        <template v-else-if="labels.length === 1">
            <v-checkbox
                class="c00400"
                :readonly="readonly"
                :disabled="disabled"
                v-model="model"
                :label="labels[0]"
                v-bind="option"
                @click.stop=""
                :dark="dark"
            ></v-checkbox>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        value: {type: [Boolean, Array], required: false, default: () => []},
        labels: {type: Array, required: false, default: () => []},
        readonly: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
        label: {type: String, required: false, default: ''},// 複数のチェックボックスがある場合は使われない
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
    methods: {
        emitArray: function (val, index) {
            // propsは操作できないのでコピー
            const copyArray = JSON.parse(JSON.stringify(this.value))

            // valueの引数がtrueの場合は、チェックを外した際に、nullが返ってくる
            if (val === null) val = false
            copyArray[index] = val// 代入
            this.$emit('input', copyArray)
        }
    }
}
</script>

<style scoped>
.c00400 >>> label.v-label {
    text-overflow: ellipsis;
    overflow: hidden;
    display: block;
    font-size: 12px;
}
.c00400 >>> .v-input--is-disabled .v-label,
.c00400 >>> .v-input--is-disabled .v-icon {
    color: rgba(0,0,0,.54) !important;
}

.c00400 >>> .v-input__control {
    width: 100%;
}

label.c00400 {
    height: 20px;
    line-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    top: -14px;
    left: 0px;
    right: auto;
    position: absolute;
    -webkit-transform-origin: top left;
    transform-origin: top left;
    white-space: nowrap;
    pointer-events: none;
    font-size: calc(12px * 0.75);
}
</style>
