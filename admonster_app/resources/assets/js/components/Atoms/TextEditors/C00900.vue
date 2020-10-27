<template>
    <div class="c00900" style="position: relative;" @click.stop="">
        <label :class="['v-label', dark ? 'theme--dark' : 'theme--light']">{{label}}</label>
            <div ref="editor" class="mt-2" style="background-color: white">
                <div
                    ref="editorContainer"
                    :class="[
                        'editor-container',
                        disabled ? '' : 'default-border'
                    ]"
                    :style="{'height': editorHeight, 'width': editorWidth, 'width': editorWidth}"
                ></div>
            </div>
    </div>
</template>

<script>
import Quill from 'quill'// document:https://quilljs.com/docs/quickstart/

const isNumber = (value) => {
    return ((typeof value === 'number') && (isFinite(value)))
}

export default {
    props: {
        value: {type: String, required: false, default: ''},
        readonly: {type: Boolean, required: false, default: false},
        disabled: {type: Boolean, required: false, default: false},
        label: {type: String, required: false, default: ''},
        valueType: {type: String, required: false, default: ''},// 優先度 valueType > option.valueType
        option: {type: Object, required: false, default: () => ({valueType: '', height: '', width: '', quillOption:{}})},
        dark: {type: Boolean, required: false, default: false},
    },
    data: () => ({
        editor: null,
    }),
    computed: {
        editorHeight: function () {
            const height = this.option.height

            // 数値の場合
            if (isNumber(height)) return `${height}px`

            return height
        },
        editorWidth: function () {
            const width = this.option.width

            // 数値の場合
            if (isNumber(width)) return `${width}px`

            return width
        },
        quillOption: function () {
            // 未設定の場合はデフォルト設定とする
            let option = this.option.quillOption
            if (!option) {
                option = {
                    theme: 'snow',
                    // boundary: document.body,
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline' /*, 'strike' */],
                            // ['blockquote', 'code-block'],
                            // [{ 'header': 1 }, { 'header': 2 }],
                            // [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            // [{ 'script': 'sub' }, { 'script': 'super' }],
                            // [{ 'indent': '-1' }, { 'indent': '+1' }],
                            // [{ 'direction': 'rtl' }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            // [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'font': [] }],
                            [{ 'align': [] }],
                            ['clean'],
                            ['link'],
                            // ['link', 'image', 'video']
                        ],
                    },
                    placeholder: this.placeholder,
                    readOnly: false
                }
            }
            if (this.readonly) {
                const a = JSON.parse(JSON.stringify(option))
                if (Object.prototype.hasOwnProperty.call(a, 'modules') && Object.prototype.hasOwnProperty.call(a['modules'], 'toolbar')) {
                    a['modules']['toolbar'] = []
                }
                a['theme'] = 'bubble'
                return a
            } else {
                return option
            }
        }
    },
    watch: {
        readonly: function () {
            this.createQuill()
        },
        // propsの変更に対応
        value: function (val) {
            this.editor.root.innerHTML = val
        }
    },
    mounted: function() {
        this.createQuill()
    },
    methods: {
        update: function () {
            if (this.editor.getText()) {
                let val = ''

                //propsのvalueTypeを優先する処理
                const valueType = this.valueType === '' ? (this.option.valueType ? this.option.valueType : '') : this.valueType

                switch (valueType) {
                case 'html':
                    val = this.editor.root.innerHTML
                    break;
                default:
                    val = this.editor.getText()
                    break;
                }
                this.$emit('input', val)
            } else {
                this.$emit('input', '')
            }
        },
        createQuill: function () {
            // snowで表示されたtoolbarの削除
            for (const iterator of this.$refs.editor.getElementsByClassName('ql-toolbar ql-snow')) {
                this.$refs.editor.removeChild(iterator)
            }

            // Quillの再設定
            this.editor = new Quill(this.$refs.editorContainer, this.quillOption)
            this.editor.enable(!this.readonly)
            this.editor.root.innerHTML = this.value
            this.editor.on('text-change', () => this.update())
        }
    },
}
</script>

<style scoped>
.default-border {
    border: 1px solid #ccc;
}

/* toolbarの表示崩壊の対策 */
.c00900{
    white-space: normal;
    text-align: left;
}

.editor-container {
    font-size: 14px;
}

.v-label {
    height: 20px;
    line-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    top: -15px;
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
