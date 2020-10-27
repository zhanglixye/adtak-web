<template>
    <div class="edit_container">
        <quill-editor class="editor"
                      v-model="content"
                      ref="myQuillEditor"
                      :options="editorOption"
        >

        </quill-editor>
    </div>
</template>

<script>
////引入编辑器组件
import VueQuillEditor from 'vue-quill-editor';
//use editor
Vue.use(VueQuillEditor);

import * as Quill from 'quill';
//Quill编辑器的字体
var fonts = ['Meiryo', 'Meiryo-UI','MS-Gothic','MS-PGothic','MS-UI-Gothic','MS-Mincho','MS-PMincho'];
var Font = Quill.import('formats/font');
Font.whitelist = fonts; //将字体加入到白名单
Quill.register(Font, true);
const toolOptions = [
    ['bold', 'italic', 'underline'],    //加粗，斜体，下划线，删除线
    [{ 'font': fonts}],     //字体
    [{ 'size': [false, 'large', 'huge'] }], // 字体大小
    [{ 'color': [] }, { 'background': [] }],     // 字体颜色，字体背景颜色
    [{ 'align': [] }],    //对齐方式
    ['clean'],    //清除字体样式
];
export default {
    props:{
        cont:String,
    },
    data(){
        return {
            content:null,
            editorOption:{
                placeholder: 'ここに署名文章を作成してください',
                modules:{
                    toolbar:{
                        container:toolOptions
                    }
                },
                theme:'snow',
            },
        }
    },
    computed: {
        editor() {
            return this.$refs.myQuillEditor.quill
        },
    },
    created(){
        this.content = this.cont;
    },
    mounted() {
        this.editor.container.style.height = `${220}px`;
    },
    methods:{

    },
}
</script>

<style scoped>

</style>