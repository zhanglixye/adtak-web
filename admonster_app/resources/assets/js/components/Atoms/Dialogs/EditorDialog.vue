<template>
    <v-dialog class="editor-dialog" v-model="dialog" persistent>
        <v-card>
            <v-card-title>
                {{ title }}
                <v-spacer></v-spacer>
                <v-btn icon small @click="cancel()">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <!-- Editor -->
            <!-- C00900は同期を前提とした動作を行うので、v-modelを使わないようにしている -->
            <c00900
                :value="value"
                @input="setInputValue"
                :option="option"
            ></c00900>
            <!-- /Editor -->
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel')}}</v-btn>
                <v-btn color="primary" @click="ok()">{{ $t('common.button.ok')}}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import C00900 from '../TextEditors/C00900'

export default {
    components: {
        C00900,
    },
    props: {
        title: {type: String, required: false, default: ''},
        valueType: {type: String, required: false, default: ''},// 優先度 valueType > option.valueType
        placeholder: {type: String, required: false, default: ''}// コンポーネント作成時の値のみ使用
    },
    data: () => ({
        dialog: false,
        quill: null,
        option: {
            height: 500,
            width: '',
            valueType: 'html',
            quillOption: {}
        },
        value:'',// nullになることがある
        inputValue:'',
        resolve: null,
        reject: null,
    }),
    created() {
        this.initialize()
    },
    methods: {
        initialize () {
            this.option.quillOption = {
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
        },
        show (message) {
            this.clear()
            this.value = message
            this.dialog = true
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        ok () {
            this.resolve({value : this.inputValue, isEnter : true})
            this.clear()
            this.dialog = false
        },
        cancel () {
            this.resolve({value : this.inputValue, isEnter : false})
            this.clear()
            this.dialog = false
        },
        clear () {
            /*
            理由：
            空文字列の後に再度空文字列のコメントが来た場合に、
            前の入力した内容が表示され続ける問題を回避
            詳細：
            C00900が変更なしと検知しwatchが動作しないためnullを代入
            */
            this.value = null
            this.inputValue = ''
        },
        setInputValue (val) {
            // C00900.vueで使用しているquillは文字の入力を行わない場合、以下の条件の文字列を返すため対応
            // value type text: '\n' html: '<p><br></p>'
            this.inputValue = val === '\n' || val === '<p><br></p>' ? '' : val
        }
    }
}
</script>

<style scoped>
.v-card__title {
    padding: 0 0 0 16px;
}
</style>
