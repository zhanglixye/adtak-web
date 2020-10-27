<template>
    <div :class="['item-config', dark ? 'dark' : 'no-dark', specialColors ? 'special-colors': '']">
        <template v-if="item.item_type === ITEM_CONFIG_TYPE.TEXT_BOX">
            <C00100 :option="item.option" :label="label" readonly :value="value" :dark="dark" :disabled="disabled"></C00100>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.TEXT_BOX_1">
            <C00101 :option="item.option" :label="label" readonly :value="value" :dark="dark" :disabled="disabled"></C00101>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.TEXTAREA">
            <c00200 :option="item.option" :label="label" readonly :value="value" :dark="dark" :disabled="disabled"></c00200>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.COMBOBOX">
            <c00300 :option="item.option" :label="label" readonly :items="itemConfigValues" :value="value" :dark="dark" :disabled="disabled"></c00300>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.CHECKBOX">
            <c00400 :option="c00400Option" :label="label" readonly :labels="itemConfigValues" :value="value" :dark="dark" :disabled="disabled"></c00400>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.RADIO">
            <c00500 :option="c00500Option" :label="label" readonly :labels="itemConfigValues" :value="value" :dark="dark" :disabled="disabled"></c00500>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.SELECT">
            <c00600 :option="item.option" :label="label" readonly :items="itemConfigValues" :value="value" :dark="dark" :disabled="disabled"></c00600>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.PICKERS">
            <c00700 :option="item.option" :label="label" :value="value" :dark="dark" disabled></c00700>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.FILE_INPUT">
            <c00800
                :label="label"
                :value="value"
                :dark="dark"
                :comparisonContents="comparisonContents"
                :candidates="candidates"
                width
                :filePreviewWidth.sync="computedFilePreviewWidth"
                :localStorageKey="localStorageKey"
                :stepId="stepId"
            ></c00800>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT">
            <c00801
                :label="label"
                :comparisonContents="comparisonContents"
                :candidates="candidates"
                :value="value"
                :dark="dark"
                :filePreviewWidth.sync="computedFilePreviewWidth"
                :localStorageKey="localStorageKey"
                :stepId="stepId"
            ></c00801>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.TEXT_EDITOR">
            <c00900 :option="item.option" :label="label" readonly :value="value" :dark="dark" :disabled="disabled"></c00900>
        </template>
        <template v-else-if="isEmpty">
            <!-- 空の場合は何も出力しない -->
            <span>&nbsp;</span>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.STRING">
            <span :class="[dark  ? 'text-color-white' : '', 'fs-12']" v-html="value"></span>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.ARRAY">
            <span :class="[dark ? 'text-color-white' : '', 'fs-12']" v-html="typeArray"></span>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.MAP">
            <span :class="[dark ? 'text-color-white' : '', 'fs-12']" v-html="typeMap"></span>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.ARRAY_MAP">
            <span :class="[dark ? 'text-color-white' : '', 'fs-12']" v-html="typeArrayMap"></span>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.FILE">
            <!-- ファイル名を表示し、クリックによりファイルダウンロードする -->
            <a class="fs-12" :href="'typeFile'" v-html="'value'"></a>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.IMAGE">
            <!-- 画像のサムネイルを表示し、画像クリックにより別ウインドウで原寸大を表示する -->
            <image-and-movie :filePath="value" :height="100" :dark="dark"></image-and-movie>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.URL">
            <!-- URLを表示し、クリックにより別ウインドウで遷移先を表示する（外部サイトへのリンク） -->
            <external-link :uri="value" :dark="dark"></external-link>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.TEXT_AREA">
            <v-textarea
                readonly
                :disabled="disabled"
                :value="value"
                :dark="dark"
            ></v-textarea>
        </template>
        <template v-else-if="item.item_type === ITEM_CONFIG_TYPE.TASK_RESULT">
            <template v-if="isContact">
                <v-badge left color="red">
                    <template slot="badge">
                        <v-icon dark>priority_high</v-icon>
                    </template>
                    <a :class="[dark ? 'text-color-white' : '', 'fs-12', !content.mail_id[0] ? 'unlink' : '']" v-html="$t(`common.task_result.result_text.type.${CONST.PREFIX}${this.value}`)" @click.stop="showMailPreview"></a>
                </v-badge>
            </template>
            <template v-else>
                <span :class="[dark ? 'text-color-white' : '', 'fs-12']" v-html="$t(`common.task_result.result_text.type.${CONST.PREFIX}${this.value}`)"></span>
            </template>
        </template>
    </div>
</template>

<script>
import ExternalLink from '../Links/ExternalLink'
import ImageAndMovie from '../Media/ImageAndMovie'
import C00100 from '../../Atoms/TextFields/C00100'
import C00101 from '../../Atoms/TextFields/C00101'
import C00200 from '../../Atoms/Textareas/C00200'
import C00300 from '../../Atoms/Combobox/C00300'
import C00400 from '../../Atoms/Checkbox/C00400'
import C00500 from '../../Atoms/Radio/C00500'
import C00600 from '../../Atoms/Select/C00600'
import C00700 from '../../Atoms/Pickers/C00700'
import C00800 from '../../Atoms/FileInput/C00800'
import C00801 from '../../Atoms/FileInput/C00801'
import C00900 from '../../Atoms/TextEditors/C00900'

export default {
    components: {
        ExternalLink,
        ImageAndMovie,
        C00100,
        C00101,
        C00200,
        C00300,
        C00400,
        C00500,
        C00600,
        C00700,
        C00800,
        C00801,
        C00900,
    },
    props: {
        item: { type: Object, required: true },
        comparisonContents: { type: Array, required: false, default: () => [] },
        candidates: { type: Array, required: false, default: () => [] },
        labelData: { type: Object, required: true},
        content: { type: [Object, Array], required: false, default: null },
        path: { type: String, required: true },
        commnetWidth: { type: [String, Number], required: false, default: 200},
        dark: {type: Boolean, required: false, default: false},
        color: { type: String, required: false, default: undefined},
        filePreviewWidth: { type: Number, required: false, default: 600 },
        stepId: { type: Number, required: true},
        localStorageKey: { type: String, required: true },
        disabled: {type: Boolean, required: false, default: false},
    },
    data: () => ({
        ITEM_CONFIG_TYPE: _const.ITEM_CONFIG_TYPE,
        CONST: _const,
    }),
    computed: {
        computedFilePreviewWidth : {
            set (val) {
                this.$emit('update:filePreviewWidth', Number(val))
            },
            get () {
                return this.filePreviewWidth
            }
        },
        c00500Option () {
            if (this.color === undefined) return this.item.option// 指定が無い場合
            const option = JSON.parse(JSON.stringify(this.item.option))
            if (!('radio' in option)) option['radio'] = {}// 準備
            if ('radio' in option) {
                option['radio'] = Object.assign(option['radio'], {color: this.color})// 指定したい色を入れる
            }
            return option
        },
        c00400Option () {
            if (this.color === undefined) return this.item.option// 指定が無い場合
            const option = JSON.parse(JSON.stringify(this.item.option))
            const key = 'color'
            option[key] = this.color// 指定したい色を入れる
            return option
        },
        isContact () {
            if (this.item.group === 'results') return this.content.type === _const.TASK_RESULT_TYPE.CONTACT
            return false
        },
        value () {
            if (this.content === null || this.content === undefined) return null
            return Object.prototype.hasOwnProperty.call(this.content, this.item.key) ? this.content[this.item.key] : null
        },
        typeArray () {
            // 改行して表示
            return this.value.join('<br>')
        },
        typeMap () {
            // 言語ファイルを利用して表示
            return Vue.i18n.translate(this.path + '.' + this.item.key + '.prefix' + this.value)
        },
        typeArrayMap () {
            // 言語ファイルを利用 ＆ 改行して表示
            let val = this.value
            val.forEach(v => v = Vue.i18n.translate(this.path + '.' + this.item.key + '.prefix' + v))
            return val.join('<br>')
        },
        typeFile () {
            // ファイルダウンロード
            let uri = '/utilities/download_file?file_path='
            let file_name = this.value.split('/').pop()
            let file_path = this.value
            uri = uri + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
            return uri
        },
        isEmpty () {
            // 空 ( null or undefined or '' or [] or {} ) を判定
            if (!this.value) {
                if ( this.value !== 0 && this.value !== false ) {
                    return true;
                }
            } else if (typeof this.value == 'object'){ // Array or Object
                return Object.keys(this.value).length === 0;
            }
            return false;
        },
        label () {
            // グループに所属していない場合は空文字列を返す
            if (this.item.item_key === this.item.group && this.item.item_type !== null) return ''
            return this.labelData[Vue.i18n.locale()][this.item.label_id]
        },
        itemConfigValues () {
            const isNullOrUndefined = (val) => {
                return (val === undefined || val === null)
            }

            const localLangData =  this.labelData[Vue.i18n.locale()]
            return this.item.item_config_values.flatMap(x => isNullOrUndefined(localLangData[x.label_id]) ? [] : localLangData[x.label_id])
        },
        specialColors () {
            return this.item.item_type === _const.ITEM_CONFIG_TYPE.TEXT_EDITOR
        }
    },
    methods: {
        showMailPreview () {
            if (!this.content.mail_id[0]) return
            eventHub.$emit('showMailPreview', {
                mailId: this.content.mail_id[0]
            })
        },
    },
}
</script>

<style scoped>
.v-input {
    /* font-size: 16px; */
    font-size: 12px;
}
.v-text-field {
    /* padding-top: 12px; */
    /* margin-top: 4px; */
    padding-top: 0;
    margin-top: 0;
}
a.unlink {
    cursor: default;
    color: inherit;
}
a.unlink:hover {
    text-decoration: none;
}
/* dark mode default color */
.text-color-white {
    color: white !important;
}
/* ---C00900 */
.item-config.special-colors >>> .ql-picker-label {
    color: #757575;
}

.item-config.special-colors >>> .ql-toolbar.ql-snow .ql-fill{
    fill: #757575;
}

.item-config.special-colors >>> .ql-toolbar.ql-snow .ql-stroke {
    stroke: #757575;
}
/* ---C00900 */

.fs-12 {
    font-size: 12px;
}
</style>
<style>
.item-config label {
    color: #9e999a !important;
    font-weight: normal;
}
.item-config input,
.item-config textarea {
    color: rgba(0,0,0,.87) !important;
}
.item-config input[type="text"] {
	margin-top: 10px;
}
.item-config .v-input--is-disabled .v-input__slot:before {
    border: none;
}
.item-config .v-input--is-disabled .v-input__append-inner {
    display: none;
}
.item-config .v-text-field {
    padding-top: 0;
}
.item-config .v-messages {
    min-height: auto;
}
</style>
