<template>
    <div>
        <!--THREE 右侧邮件处理组件-->
        <email-deal
                v-show="flag_business_rec"
                @changeRec="changeRecP"
                :flag_business="flag_business"
                :initData="initData"
                :edit="edit"
                :loadingDisplay="loadingDisplay"
                :axiosFormData="axiosFormData"
                @emailRecData="dealEmailRecData"
                ref="emailDeal"
                @backCpdf="backWork"
                :defaultEmailStatus="defaultEmailStatus"
                :title_email_flag="title_email_flag"
                :allow_file_types="allow_file_types"
        ></email-deal>
        <!-- 经费承认-->
        <read-deal-recognize ref="readRecognize" :axiosFormData="axiosFormData" style="overflow: hidden auto;" @emailSend="emailSend" :data="readRecoginceData" @changeEmail="changeEmail" v-show="!flag_business_rec"></read-deal-recognize>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>
<script>
import EmailDeal from '../../../Organisms/Biz/Common/EmailDeal';
import ReadDealRecognize from '../../../Organisms/Biz/B00006/S00011/ReadDealRecognize';
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog';
export default {
    components: {AlertDialog, ReadDealRecognize,EmailDeal},
    props:{
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
        maxHeight: { type: String, required: false, default: ''},// cssの単位付き数値
    },
    data: () => ({
        flag_business:false,
        flag_business_rec:true,
        readRecoginceData:null,
        //默认不调用邮件默认接口
        defaultEmailStatus:false,
        //クリックして宛先を非表示
        title_email_flag:false,
        allow_file_types:null,
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
    created(){
        //create 获取不到$refs属性的值修改为mounted
    },
    mounted(){
        //初始化result所有数据
        this.init();
    },
    methods:{
        changeRecP(){
            //接收调转承认事件
            this.flag_business_rec = false;
        },
        changeEmail(){
            //接收跳转email页面
            this.flag_business_rec = true;
        },
        //email deal callback data
        dealEmailRecData(eventData){
            this.readRecoginceData = eventData;
        },
        init(){
            let initContent = JSON.parse(this.initData['task_result_info'].content);
            let time = {
                'started_at':this.initData['task_result_info'].started_at,
                'finished_at':this.initData['task_result_info'].finished_at,
                'work_time':this.initData['task_result_info'].work_time,
            };
                //console.log(initContent);
                //跳转组件模块
            if (initContent.results === undefined) {
                return;
            }
            this.lastDisplayedPageShow(initContent.lastDisplayedPage);
            //this.lastDisplayedPageShow(initContent.lastDisplayedPage);
            let result = initContent.results.type;
            // 未开始状态
            if (result == -1){
                //请求默认接口
                this.defaultEmailStatus = true;
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
            if (result == 0){
                //经费处理保存回显
                if (initContent.G00000_27 !== undefined || Object.keys(initContent.G00000_27).length > 1){
                    if (Object.keys(initContent.G00000_27).length != 1){
                        //设置为保存状态
                        this.$refs.emailDeal.$data.saveStateType = true;
                        //设置email的save值
                        this.$refs.emailDeal.saveInitValue(initContent.G00000_27,time);
                    }
                }
                //调转到承认模块
                this.$refs.emailDeal.changeRec(initContent.G00000_27.G00000_33);
                this.$refs.readRecognize.$data.typeZero = false;
            }
            //保存过状态
            if (result == 4){
                //请求默认接口
                this.defaultEmailStatus = false;
                //经费处理保存回显
                if (initContent.G00000_27 !== undefined || Object.keys(initContent.G00000_27).length > 1){
                    if (Object.keys(initContent.G00000_27).length != 1){
                        //设置为保存状态
                        this.$refs.emailDeal.$data.saveStateType = true;
                        //设置email的save值
                        this.$refs.emailDeal.saveInitValue(initContent.G00000_27,time);
                    }
                }
            }
        },
        //lastDisplayedPage保存跳转组件
        lastDisplayedPageShow(page){
            //为3跳转到emailDeal
            if (page == 1){
                this.changeEmail();
            }
        },
        //review值判断是否有review页面save
        emailSend(){
            this.$refs.emailDeal.$data.reviewDisplay = false;
            this.$refs.emailDeal.save();
        },
        //跳转到作业一览
        backWork(){
            window.location.href = '/tasks';
        }
    }
}
</script>

<style scoped>
    @import "../../../../../sass/biz/b00006.scss";
    .v-btn {
        text-decoration: none;
    }
    #s00011 .container{
        padding: 20px;
        display: flex;
        height: 100%;
    }
</style>