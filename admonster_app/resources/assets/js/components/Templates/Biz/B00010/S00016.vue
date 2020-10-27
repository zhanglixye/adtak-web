<template>
    <div>
        <!--TWO 経費申請承認组件-->
        <pdf-recognition
            v-show="AdmittedToJump && !recShow"
            ref="pdfRecognition"
            :loadingDisplay="loadingDisplay"
            :initData="initData"
            :heightExpand="businessHeight"
            :axiosFormData="axiosFormData"
        ></pdf-recognition>
        <email-deal
            v-show="!AdmittedToJump && !recShow"
            :flag_business="flag_business"
            :initData="initData"
            :edit="edit"
            :loadingDisplay="loadingDisplay"
            :axiosFormData="axiosFormData"
            :defaultEmailStatus="defaultEmailStatus"
            ref="emailDeal"
            @changeRec="changeRecP"
            @emailRecData="dealEmailRecData"
            @backCpdf="backCpdf"
        ></email-deal>
        <read-deal-recognize
            style="overflow: hidden auto;"
            @emailSend="emailSend"
            :data="readRecoginceData"
            :axiosFormData="axiosFormData"
            @changeEmail="changeEmail"
            v-show="!flag_business_rec && recShow"
            ref="readRecognize"
        ></read-deal-recognize>
    </div>
</template>

<script>
import PdfRecognition from '../../../Organisms/Biz/B00010/S00016/PdfRecognition.vue';
import EmailDeal from '../../../Organisms/Biz/Common/EmailDeal';
import ReadDealRecognize from '../../../Organisms/Biz/B00006/S00011/ReadDealRecognize';
export default {
    components: {EmailDeal , PdfRecognition , ReadDealRecognize},
    props:{
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
    },
    data: () => ({
        flag_business:false,
        businessHeight:640,
        AdmittedToJump:true,
        readRecoginceData:null,
        flag_business_rec:false,
        //默认不调用邮件默认接口
        defaultEmailStatus:false,
        recShow:false,
    }),
    computed:{
        axiosFormData() {
            let formData = new FormData();
            formData.append('step_id', this.initData['request_info']['step_id']);
            formData.append('request_id', this.initData['request_info']['request_id']);
            formData.append('request_work_id', this.initData['request_info']['id']);
            formData.append('task_id', this.initData['task_info']['id']);
            formData.append('task_started_at', this.initData['task_started_at']);
            formData.append('task_result_content', JSON.stringify(JSON.parse(this.initData.task_result_info.content).results))
            return formData;
        },
    },
    mounted(){
        this.init();
    },
    methods:{
        init(){
            console.log('initContent：',JSON.parse(this.initData['task_result_info'].content));
            let initContent = JSON.parse(this.initData['task_result_info'].content);
            let time = {
                'started_at':this.initData['task_result_info'].started_at,
                'finished_at':this.initData['task_result_info'].finished_at,
                'work_time':this.initData['task_result_info'].work_time,
            };
            //跳转组件模块
            this.lastDisplayedPageShow(initContent.lastDisplayedPage);
            //获取作业状态
            let result = initContent.results.type;
            // 未开始状态
            if (result == -1){
                console.log('未开始状态');
                return;
            }
            //不明状态
            if (result == 1){
                //调转到承认模块
                this.$refs.emailDeal.changeRec();
                this.$refs.readRecognize.$data.typeZero = false;
                //data中的body赋值
                this.$refs.readRecognize.$data.unKnowFlag = false;
            }
            //type 为0 直接显示承认页面
            if (result == 0) {
                //经费处理保存回显
                this.loadingDisplay(true);
                if (initContent.G00000_27 !== undefined || Object.keys(initContent.G00000_27).length > 1) {
                    if (Object.keys(initContent.G00000_27).length != 1) {
                        //设置为保存状态
                        this.$refs.emailDeal.$data.saveStateType = true;
                        //设置email的save值
                        this.$refs.emailDeal.saveInitValue(initContent.G00000_27, time);
                    }
                }
                //this.$refs.emailDeal.$refs.checkboxDialog.$data.checkboxData = initContent.listItemsTypeZero;
                //调转到承认模块
                this.loadingDisplay(false);
                this.$refs.emailDeal.changeRec(this.$refs.emailDeal.changeRec(initContent.G00000_27.G00000_33));
                this.$refs.readRecognize.$data.typeZero = false;
            }
            //保存过状态
            if (result == 4){
                //阻止请求默认接口
                this.defaultEmailStatus = false;
                //经费处理保存回显
                if (initContent.G00000_27 !== undefined){
                    if (Object.keys(initContent.G00000_27).length != 0){
                        this.$refs.emailDeal.$data.saveStateType = true;
                        //设置email的save值
                        this.$refs.emailDeal.saveInitValue(initContent.G00000_27,time);
                    }
                }
            }
        },
        //lastDisplayedPage保存跳转组件
        lastDisplayedPageShow(page){
            //为 0 跳转到 PDF 页面
            //为 1 跳转到 发邮件 页面
            if (page == 0){
                this.AdmittedToJump = true;
                this.recShow = false;
            } else if (page == 1){
                this.AdmittedToJump = false;
                this.defaultEmailStatus = true;
                this.recShow = false;
            }
        },
        backCpdf(){
            this.AdmittedToJump = true;
            this.defaultEmailStatus = false
        },
        //email deal callback data
        dealEmailRecData(eventData){
            this.readRecoginceData = eventData;
        },
        changeRecP(){
            //接收调转承认事件
            this.flag_business_rec = false;
            this.recShow = true;
        },
        //review值判断是否有review页面save
        emailSend(){
            this.$refs.emailDeal.$data.reviewDisplay = false;
            this.$refs.emailDeal.save();
        },
        changeEmail(){
            //接收跳转email页面
            this.flag_business_rec = true;
            this.recShow = false;
        },
    }
}
</script>

<style scoped>
    @import "../../../../../sass/biz/b00010.scss";
.v-btn {
    text-decoration: none;
}
/*#s00012 .container{*/
/*    padding: 20px;*/
/*    display: flex;*/
/*    height: 100%;*/
/*}*/
</style>
