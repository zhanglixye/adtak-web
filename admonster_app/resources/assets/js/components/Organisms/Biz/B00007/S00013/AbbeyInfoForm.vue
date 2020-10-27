<template>
    <div>
        <div class="block-one">
            <div class="menu-name">
                <span class="project-title">メニュー名：</span>
                <svg width="20px" height="20px" @click="popupMenuSearch" v-if="!accomplish"><g transform="matrix(1 0 0 1 -599 -325 )"><path d="M 12.265625 12.265625  C 13.3193108974359 11.2119391025641  13.8461538461538 9.94391025641026  13.8461538461538 8.46153846153846  C 13.8461538461538 6.97916666666667  13.3193108974359 5.71113782051282  12.265625 4.65745192307692  C 11.2119391025641 3.60376602564102  9.94391025641026 3.07692307692308  8.46153846153846 3.07692307692308  C 6.97916666666667 3.07692307692308  5.71113782051282 3.60376602564102  4.65745192307692 4.65745192307692  C 3.60376602564103 5.71113782051282  3.07692307692308 6.97916666666667  3.07692307692308 8.46153846153846  C 3.07692307692308 9.94391025641026  3.60376602564103 11.2119391025641  4.65745192307692 12.265625  C 5.71113782051282 13.3193108974359  6.97916666666667 13.8461538461538  8.46153846153846 13.8461538461538  C 9.94391025641026 13.8461538461538  11.2119391025641 13.3193108974359  12.265625 12.265625  Z M 19.5552884615385 17.3798076923077  C 19.8517628205128 17.6762820512821  20 18.036858974359  20 18.4615384615385  C 20 18.8782051282051  19.8477564102564 19.2387820512821  19.5432692307692 19.5432692307692  C 19.2387820512821 19.8477564102564  18.8782051282051 20  18.4615384615385 20  C 18.0288461538462 20  17.6682692307692 19.8477564102564  17.3798076923077 19.5432692307692  L 13.2572115384615 15.4326923076923  C 11.8229166666667 16.4262820512821  10.224358974359 16.9230769230769  8.46153846153846 16.9230769230769  C 7.31570512820513 16.9230769230769  6.21995192307692 16.7007211538462  5.17427884615385 16.2560096153846  C 4.12860576923077 15.8112980769231  3.22716346153846 15.2103365384615  2.46995192307692 14.453125  C 1.71274038461538 13.6959134615385  1.11177884615385 12.7944711538462  0.667067307692308 11.7487980769231  C 0.222355769230769 10.703125  0 9.60737179487179  0 8.46153846153846  C 0 7.31570512820513  0.222355769230769 6.21995192307692  0.667067307692308 5.17427884615385  C 1.11177884615385 4.12860576923077  1.71274038461538 3.22716346153846  2.46995192307692 2.46995192307692  C 3.22716346153846 1.71274038461539  4.12860576923077 1.11177884615385  5.17427884615385 0.667067307692308  C 6.21995192307692 0.222355769230769  7.31570512820513 0  8.46153846153846 0  C 9.6073717948718 0  10.703125 0.222355769230769  11.7487980769231 0.667067307692308  C 12.7944711538462 1.11177884615385  13.6959134615385 1.71274038461539  14.453125 2.46995192307692  C 15.2103365384615 3.22716346153846  15.8112980769231 4.12860576923077  16.2560096153846 5.17427884615385  C 16.7007211538462 6.21995192307692  16.9230769230769 7.31570512820513  16.9230769230769 8.46153846153846  C 16.9230769230769 10.224358974359  16.4262820512821 11.8229166666667  15.4326923076923 13.2572115384615  L 19.5552884615385 17.3798076923077  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 599 325 )" /></g></svg>
                <input type="text" v-model="menuName" :disabled="accomplish" onfocus="this.select()">
            </div>
            <div class="abbeyID">
                <span class="project-title">AbbeyID：</span>
                <input type="text" v-model="abbeyID" :disabled="accomplish" onfocus="this.select()">
            </div>
        </div>
        <div class="block-two">
            <span class="project-title">判定：</span>
            <v-radio-group row v-model="judge">
                <v-radio class="mr-6" label="OK" value="1" :disabled="accomplish||okState"></v-radio>
                <v-radio class="mr-6" label="NG" value="0" :disabled="ngState||accomplish"></v-radio>
                <v-radio class="mr-6" label="不明あり" value="2" :disabled="accomplish"></v-radio>
            </v-radio-group>
        </div>
        <div class="block-three">
            <p class="project-title">チェック結果：</p>
            <textarea rows="5" v-model="checkResult" :disabled="accomplish"></textarea>
            <p class="project-title">エラー内容：</p>
            <textarea rows="5" v-model="errorContent" :disabled="accomplish"></textarea>
        </div>
        <popupMenuSearch
            ref="popupMenuSearch"
            :defaultKeyWord="menuName"
            :loadingDisplay="loadingDisplay"
            :formData="formData"
            :initData="initData"
            :projectInfo="projectInfo"
            @getMsg='getMsg'
        ></popupMenuSearch>
    </div>
</template>

<script>
import popupMenuSearch from './popupMenuSearch'
export default {
    name: 'AbbeyInfoForm',
    components:{
        popupMenuSearch,
    },
    props:{
        accomplish:Boolean,
        projectInfo:Object,
        loadingDisplay: { type: Function, require: true },
        initData: { type: Object, require: true },
        formData:{ type: FormData, require: true }
    },
    data(){
        return {
            menuName:null,
            abbeyID:null,
            judge:'',
            checkResult:'',
            errorContent:''
        }
    },
    computed:{
        okState:function () {
            if (this.checkResult === '' || this.errorContent !== ''){
                return true
            } else {
                return false
            }
        },
        ngState:function () {
            return this.errorContent === '' ? true : false
        }
    },
    watch:{
        errorContent:function () {
            if (this.errorContent != '' && this.judge == 1){
                this.judge = ''
            } else if (this.errorContent == '' && this.judge == 0){
                this.judge = ''
            }
        }
    },
    methods:{
        //打开菜单名字搜索弹窗
        popupMenuSearch:function () {
            this.$refs.popupMenuSearch.dialog = true;
        },
        //获取子组件传过来的值
        getMsg:function (menu,id) {
            this.menuName = menu;
            this.abbeyID = id;
        }
    }
}
</script>

<style scoped>
    @import "../../../../../../sass/biz/b00007.scss";
    /*清除默认样式*/
    p{margin: 0 !important;}
    /*响应*/
    @media (max-width: 1680px) {
        .form-center .block-one .menu-name input{
            width: 340px !important;
        }
    }
    @media (max-width: 1440px) {

    }
    /*清除浮动*/
    .block-one:after{
        display: block;
        content: "";
        clear: both;
    }
    .block-one,.block-two{
        line-height: 30px;
    }
    .block-one > div{
        float: left
    }
    .project-title{
        font-weight: bold;
        color: #555555;
    }
    .block-one input{
        outline: none;
        text-align: right;
        font-size: 14px;
        color: #7F7F7F;
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    .block-one .menu-name{
        margin-right: 80px;
        position: relative;
    }
    .block-one .menu-name input{
        padding-right: 40px;
        width: 420px;
    }
    .block-one .menu-name svg{
        position: absolute;
        right: 10px;
        top: 5px;
        cursor: pointer;
    }
    .block-one .abbeyID input{
        width: 120px;
        padding-right: 10px;
    }
    .block-two{
        margin-top: 20px;
    }
    .block-two input{
        width: 16px;
        height: 16px;
        margin: 0 5px 0 8px;
        transform: translateY(2px);
    }
    .block-two label{
        font-size: 18px;
        color: #555555;
        margin-right: 20px;
    }
    .block-three{
        margin-top: 5px;
    }
    .block-three p{
        line-height: 50px;
    }
    .block-three textarea{
        width: 100%;
        min-height: 130px;
        outline: none;
        padding: 10px;
        font-size: 14px;
        border: solid 1px rgba(215, 215, 215, 1);
    }
</style>
