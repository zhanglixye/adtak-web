<template>
    <div class="edit_container">
        <base-dialog :axiosFormData="axiosFormData" ref="dialog" v-show="false" class="solo_sign"></base-dialog>
        <template-dialog :axiosFormData="axiosFormData" ref="dialog_tem" v-show="false" class="solo_sign"></template-dialog>
        <quill-editor class="editor"
                      v-model="content"
                      ref="myQuillEditor"
                      :options="editorOption"
                      @blur="onEditorBlur($event)" @focus="onEditorFocus($event)"
                      @change="onEditorChange($event)"
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
import BaseDialog from '../../../../Atoms/Biz/Common/Mails/BaseDialog';
import TemplateDialog from '../../../../Atoms/Biz/Common/Mails/TemplateDialog';
//quill编辑器的字体
var fonts = ['Meiryo', 'Meiryo-UI','MS-Gothic','MS-PGothic','MS-UI-Gothic','MS-Mincho','MS-PMincho'];
var Font = Quill.import('formats/font');
Font.whitelist = fonts; //将字体加入到白名单
Quill.register(Font, true);
const toolOptions = [
    ['bold', 'italic', 'underline'],    //加粗，斜体，下划线，删除线
    [{ 'font': fonts }],     //字体
    [{ 'size': [false, 'large', 'huge'] }], // 字体大小
    [{ 'color': [] }, { 'background': [] }],     // 字体颜色，字体背景颜色
    [{ 'align': [] }],    //对齐方式
    ['clean'],    //清除字体样式
    ['sourceEditor'],
    ['signSetting']
];
export default {
    components:{
        TemplateDialog,
        BaseDialog

    },
    props:{
        flag_editor:Boolean,
        heightChange:Number,
        //axios formData
        axiosFormData:{type:FormData,require:true}
    },
    data() {
        return {
            content:'',
            editorOption:{
                modules:{
                    toolbar:{
                        container:toolOptions,
                        handlers:{
                            shadeBox:null,
                            sourceEditor:()=>{
                                //添加工具方法
                                this.$refs.dialog_tem.$data.dialog = true;
                            },
                            signSetting:()=>{
                                this.$refs.dialog.$data.dialog = true;
                            }
                        }
                    }
                },
                theme:'snow',
                placeholder: 'ここにメール本文を作成してください',
            },
            height:0
        };
    },
    computed: {
        editor() {
            return this.$refs.myQuillEditor.quill
        },
    },
    watch:{
        flag_editor:function () {
            this.$nextTick(function() {
                this.hintGetFun();
                this.initButton();
            });
            //组件prop flag_editor变化时监听高度
            //this.initHeight();
        },
        heightChange:function () {
            //初始化默认高度
            //this.initHeight(true);
        }
    },
    mounted() {
        //默认高度
        //this.initHeight(true);
    },
    methods:{
        onEditorReady(){

        },
        onEditorBlur(){

        },
        onEditorFocus(){//获得焦点事件

        },
        onEditorChange(){//内容改变事件

        },
        async hintGetFun(){
            try {
                this.editor.focus();
            } catch (e) {
                window.console.log(e);
            }
        },
        initButton(){
            //copy按钮的位置
            const sourceEditorButton = document.querySelector('.ql-sourceEditor');
            sourceEditorButton.style.cssText = 'width:105px;line-height:18px';
            sourceEditorButton.innerText = 'MailTemplate';
            //设置按钮的位置
            const signSettingButton = document.querySelector('.ql-signSetting');
            signSettingButton.style.cssText = 'width:50px;line-height:18px';
            signSettingButton.innerHTML = '<i aria-hidden="true" class="editor-edit v-icon material-icons theme--light" style="cursor: pointer;font-size: 22px">create</i>';
        },
        initHeight(flag){
            //兼容1920
            let result = 256;
            if (flag){
                result = result + this.heightChange;
            }
            this.height = result;
            this.editor.container.style.height = `${result}px`;
        },
        //插入值
        insert(text){
            this.content = this.content + text;
        }
    },

}
</script>
<style scoped lang="scss">
    .editor{

    }
</style>