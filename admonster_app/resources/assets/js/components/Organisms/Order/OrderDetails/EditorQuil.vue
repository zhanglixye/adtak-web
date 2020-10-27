<template>
    <div class="edit_container">
        <quill-editor
            class="editor"
            v-model="content"
            ref="myQuillEditor"
            :options="editorOption"
        ></quill-editor>
    </div>
</template>

<script>
import VueQuillEditor from 'vue-quill-editor'
import * as Quill from 'quill'
Vue.use(VueQuillEditor)
const fonts = ['Meiryo', 'Meiryo-UI','MS-Gothic','MS-PGothic','MS-UI-Gothic','MS-Mincho','MS-PMincho']
const Font = Quill.import('formats/font')
Font.whitelist = fonts
Quill.register(Font, true)
const toolOptions = [
    ['bold', 'italic', 'underline'],
    [{ font: fonts }],
    [{ size: [false, 'large', 'huge'] }],
    [{ color: [] }, { background: [] }],
    [{ align: [] }],
    ['clean'],
]
const Block = Quill.import('blots/block');
Block.tagName = 'div';  // default: 'p'
Quill.register(Block);

export default {
    props: {
        heightAdd: { type: Number, required: true },
    },
    data() {
        return {
            content: '',
            editorOption: {
                modules: {
                    toolbar: {
                        container: toolOptions,
                        handlers: {
                            shadeBox: null,
                        },
                    },
                },
                theme: 'snow',
                placeholder: Vue.i18n.translate('order.order_details.show.mail.body.here_mail_body_create_please'),
            },
            height: 0,
        }
    },
    computed: {
        editor() {
            return this.$refs.myQuillEditor.quill
        },
    },
    watch: {
        heightAdd: function() {
            this.initHeight(true)
        },
    },
    mounted() {
        this.initHeight(true)
    },
    methods: {
        initHeight(flag) {
            let result = 256
            if (flag) {
                result = result + this.heightAdd
            }
            this.height = result
            this.editor.container.style.height = `${result}px`
        },
        insertText(text, cursorIndex = null) {
            // 挿入位置が指定されていない場合は最後にテキストを追加
            if (cursorIndex === null) {
                const htmlContent = this.content // contentの中身を一時保存
                // textをhtmlデータに変換
                this.$refs.myQuillEditor.quill.setText(text)
                const convertedHtml = this.content
                this.content = htmlContent + convertedHtml // 追加
            // 挿入位置が指定された場合は前後のテキストを切り取って結合する
            } else {
                const textContent = this.$refs.myQuillEditor.quill.getText()
                const textBefore = textContent.substr(0, cursorIndex);
                const textAfter = textContent.substr(cursorIndex, textContent.length);
                const resultContent = textBefore + text + textAfter
                this.$refs.myQuillEditor.quill.setText(resultContent)
            }
        },
        insertHtml(html) {
            this.content = this.content + html
        },
        getSelection() {
            return this.$refs.myQuillEditor.quill.getSelection()
        },
    },
}
</script>
