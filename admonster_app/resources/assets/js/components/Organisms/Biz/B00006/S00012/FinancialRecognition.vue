<template>
    <div :style="{height:height}">
        <v-card style="height: 100%">
            <v-form class="pa-3 ma-0 flex_column" style="height: 100%">
                <div class="financial_title">
                    経費申請承認
                </div>
                <div style="text-align: center">
                    <span v-show="disposeState===true">
                        {{
                            procedure === 1 ?
                            pdfList.before_file_list[index].file_type === 0 ? "『★交通費(AP) メール』" :
                            pdfList.before_file_list[index].file_type === 1 ? "『★交通費(常駐)メール』" : "" :
                            procedure === 2 ?
                            pdfList2.approval_file_list[index2].file_type === 0 ? "『★交通費(AP) メール』" :
                            pdfList2.approval_file_list[index2].file_type === 1 ? "『★交通費(常駐)メール』" : "" : "未定义"
                        }}
                    </span>
                    <span v-show="disposeState===false">
                        {{
                            procedure === 1 ?
                            pdfList.before_file_list[index].file_type === 0 ? "『★交通費(AP) メール』" :
                            pdfList.before_file_list[index].file_type === 1 ? "『★交通費(常駐)メール』" : "" :
                            procedure === 2 ?
                            pdfList2.approval_file_list[index2].file_type === 0 ? "『★交通費(AP) メール』" :
                            pdfList2.approval_file_list[index2].file_type === 1 ? "『★交通費(常駐)メール』" : "" : "未定义"
                        }}
                    </span>
                </div>
                <v-carousel
                    :cycle="false"
                    :class="{pdfShow:procedure===1,pdfHide:procedure!==1}"
                    light
                    hide-delimiters
                    class="mt-3"
                    v-model="index">
                    <v-carousel-item style="height: 100%;" v-for="(item, i) in pdfList.before_file_list" :key="i">
                        <pdf class="pdf-view" :src="item.file_data"/>
                    </v-carousel-item>
                </v-carousel>
                <v-carousel
                    :cycle="false"
                    :class="{pdfShow:procedure===2,pdfHide:procedure!==2}"
                    light
                    hide-delimiters
                    class="mt-3"
                    v-model="index2">
                    <v-carousel-item style="height: 100%;" v-for="(item, i) in pdfList2.approval_file_list" :key="i">
                        <pdf class="pdf-view" :src="item.file_data"/>
                    </v-carousel-item >
                </v-carousel>
                <div class="row ma-0" style="position: absolute;bottom: 16px;width: 100%">
                    <v-btn
                        color="#949394"
                        dark
                        style="width: 100px;"
                        class="ma-0 mr-3"
                        @click="passiveBtn"
                    >
                        {{procedure===1 ? "保留" : procedure===2 ? "戻る" : "未定义"}}
                    </v-btn>
                    <v-btn
                        v-show="showPositiveBtn"
                        class="ma-0"
                        color="success"
                        style="width: 100px"
                        @click="positiveBtn"
                    >
                        {{procedure===1 ? "承認" : procedure===2 ? "完了" : "未定义"}}
                    </v-btn>
                </div>
            </v-form>
        </v-card>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
//引入 vue-pdf 依赖
import pdf from 'vue-pdf'
export default {
    components: {AlertDialog,pdf},
    props: {
        heightExpand:Number,
        initData: { type: Object, require: true },
        loadingDisplay: { type: Function, require: true },
        axiosFormData:{type:FormData,require:true}
    },
    data:()=>{
        return {
            //PDF列表
            pdfList:{
                before_file_list:[
                    {
                        file_name:'',
                        file_path:'',
                        file_data:'',
                        file_type:''
                    }
                ]
            },
            //承认后的PDF列表
            pdfList2:{
                approval_file_list:[
                    {
                        file_name:'',
                        file_path:'',
                        file_data:'',
                        file_type:''
                    }
                ]
            },
            //当前显示的PDF索引值
            index:0,
            index2:0,
            //操作步骤
            procedure:1,
            //设置按钮隐藏
            showPositiveBtn:true
        }
    },
    computed:{
        height:function () {
            return (this.heightExpand === 0)?'100%':this.heightExpand + 'px';
        },
        //经费处理状态（AP：true/常驻：false）
        disposeState:function () {
            let arr = [];
            for (var i=0;i<this.pdfList.before_file_list.length;i++){
                arr.push(this.pdfList.before_file_list[i].file_type)
            }
            return arr.indexOf(1) === -1 ? true : false;
        },
    },
    watch:{
        index:function () {
            this.bannerCirculation();
        },
        index2:function () {
            this.bannerCirculation();
            this.bannerItemSetCss();
        }
    },
    created(){
        this.getPdfList();
    },
    mounted(){
        this.bannerCirculation();
        this.bannerContentSetCss();
        this.bannerItemSetCss();
        this.JumpAfterCompletion();
    },
    methods:{
        //加载组件时获得PDF列表
        async getPdfList() {
            this.pdfList = JSON.parse(this.initData.task_result_info.content);
        },
        //经费确认的PDF列表
        async expenditureAffirm(){
            try {
                this.loadingDisplay(true)
                let date = new Date();
                let time_zone = 0 - date.getTimezoneOffset() / 60;
                let parameter = new FormData();
                parameter.append('step_id', this.initData['request_info']['step_id']);
                parameter.append('request_id', this.initData['request_info']['request_id']);
                parameter.append('request_work_id', this.initData['request_info']['id']);
                parameter.append('task_id', this.initData['task_info']['id']);
                parameter.append('task_started_at', this.initData['task_started_at']);
                parameter.append('time_zone', time_zone);
                const res = await axios.post('/api/biz/b00006/s00012/approval',parameter);
                this.pdfList2 = JSON.parse(res.data.task_result_info.content);
            } catch (e) {
                console.log(e);
                this.loadingDisplay(false)
            } finally {
                this.loadingDisplay(false)
            }
        },
        //灰色按钮
        async passiveBtn() {
            let msg = JSON.parse(this.initData.task_result_info.content);
            if (msg.results.type === 0){
                window.location.href = '/tasks';
            } else {
                if (this.procedure === 1){
                    try {
                        this.loadingDisplay(true);
                        await axios.post('/api/biz/b00006/s00012/tmpSave',this.axiosFormData)
                        this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                        window.location.href = '/tasks';
                    } catch (e) {
                        this.$refs.alert.show(e);
                        this.loadingDisplay(false)
                    } finally {
                        this.loadingDisplay(false)
                    }
                } else if (this.procedure === 2){
                    this.pdfList.approval_file_list = this.pdfList2.approval_file_list;
                    this.procedure = 1;
                }
            }
        },
        //绿色按钮
        async positiveBtn() {
            if (this.disposeState === true){
                if (this.procedure === 1){
                    if (this.pdfList.approval_file_list.length === 0){
                        this.expenditureAffirm();
                        this.procedure = 2;
                    } else {
                        this.pdfList2.approval_file_list = this.pdfList.approval_file_list;
                        this.procedure = 2;
                    }
                } else if (this.procedure === 2){
                    try {
                        this.loadingDisplay(true);
                        await axios.post('/api/biz/b00006/s00012/done',this.axiosFormData)
                        this.$refs.alert.show(Vue.i18n.translate('common.message.success'));
                        window.location.href = '/tasks';
                    } catch (e) {
                        this.loadingDisplay(false);
                        this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
                    } finally {
                        this.loadingDisplay(false)
                    }
                }
            } else if (this.disposeState === false){
                if (this.procedure === 1){
                    if (this.pdfList.approval_file_list.length === 0){
                        this.expenditureAffirm();
                        this.procedure = 2;
                    } else {
                        this.pdfList2.approval_file_list = this.pdfList.approval_file_list;
                        this.procedure = 2;
                    }
                } else if (this.procedure === 2){
                    this.$parent.AdmittedToJump = false;
                    this.$parent.defaultEmailStatus = true
                }
            }
        },
        //PDF切换按钮隐藏（变相禁止无限轮播）
        bannerCirculation:function(){
            let btnPrev = document.getElementsByClassName('v-carousel__prev');
            let btnNext = document.getElementsByClassName('v-carousel__next');
            if (this.index===0){
                btnPrev[0].style.display = 'none';
            } else {
                btnPrev[0].style.display = 'block';
            }
            if (this.index===this.pdfList.before_file_list.length-1){
                btnNext[0].style.display = 'none';
            } else {
                btnNext[0].style.display = 'block';
            }
            if (this.index2===0){
                btnPrev[1].style.display = 'none';
            } else {
                btnPrev[1].style.display = 'block';
            }
            if (this.index2===this.pdfList.before_file_list.length-1){
                btnNext[1].style.display = 'none';
            } else {
                btnNext[1].style.display = 'block';
            }
        },
        //设置PDF轮播容器的CSS，因为不允许在app.scss种修改样式
        bannerContentSetCss:function () {
            let vWindowContainer = document.getElementsByClassName('v-window__container');
            for (let i=0;i<vWindowContainer.length;i++){
                vWindowContainer[i].style.height = '100%'
            }
        },
        //设置PDF轮播项目的CSS，因为不允许在app.scss种修改样式
        bannerItemSetCss:function () {
            let vResponsive = document.getElementsByClassName('v-responsive');
            for (let i=0;i<vResponsive.length;i++){
                vResponsive[i].style.height = '100%';
                vResponsive[i].style.overflowY = 'auto';
                document.styleSheets[0].addRule('.v-responsive::-webkit-scrollbar', 'display:none');
            }
        },
        //管理员权限，承认完成后再次进入后跳转页面
        JumpAfterCompletion:function () {
            let msg = JSON.parse(this.initData.task_result_info.content);
            if (msg.results.type === 0){
                if (this.disposeState === true) {
                    if (this.pdfList.approval_file_list.length === 0) {
                        this.expenditureAffirm();
                    } else {
                        this.pdfList2.approval_file_list = this.pdfList.approval_file_list;
                    }
                    this.procedure = 2;
                    this.showPositiveBtn = false;
                }
            }
        }
    },
}
</script>

<style scoped lang="scss">
    /*pdf-vue 溢出隐藏 修改为 溢出滚动*/
    .pdf-view{
        width: 100%;
    }
    .pdfShow{
        margin-bottom: 56px;
        height: 500px !important;
    }
    .pdfHide{
        margin-top: 0 !important;
        height: 0 !important;
    }
</style>

