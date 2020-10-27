<template>
    <div class="s00011_container">
        <!--ONE 经费申请组件-->
        <out-lay @apFileName="apFile" @newPdf="newPdf" style="overflow: hidden auto;" ref="outLay" :loadingDisplay="loadingDisplay" :axiosFormData="axiosFormData" @changePdf="changePdf($event)" v-show="flag_pdf_up"></out-lay>
        <!--pdf上传页面-->
        <pdf-up :diff="diff" :apFileName="apFileName" :new_pdf="new_pdf" ref="pdfUp" :loadingDisplay="loadingDisplay" :axiosFormData="axiosFormData" @backEmail="pdfJumpEmail" :heightExpand="businessHeight" @backOutLay="flag_pdf_up = true" v-show="!flag_pdf_up&&!flag_business_rec&&!recShow"></pdf-up>
        <!--temporarily placed-->
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
                @backCpdf="backCpdf"
                :defaultEmailStatus="defaultEmailStatus"
        ></email-deal>
        <!-- 经费承认-->
        <read-deal-recognize ref="readRecognize" :axiosFormData="axiosFormData" style="overflow: hidden auto;" @emailSend="emailSend" :data="readRecoginceData" @changeEmail="changeEmail" v-show="!flag_business_rec && recShow"></read-deal-recognize>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>
<script>
import OutLay from '../../../Organisms/Biz/B00006/S00011/OutLay';
import PdfUp from '../../../Organisms/Biz/B00006/S00011/PdfUp';
import EmailDeal from '../../../Organisms/Biz/Common/EmailDeal';
import ReadDealRecognize from '../../../Organisms/Biz/B00006/S00011/ReadDealRecognize';
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog';
export default {
    components: {AlertDialog, ReadDealRecognize,EmailDeal,OutLay,PdfUp},
    props:{
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
        maxHeight: { type: String, required: false, default: ''},// cssの単位付き数値
    },
    data: () => ({
        flag_business:false,
        businessHeight:914,
        flag_pdf_up:true,
        new_pdf:true,
        flag_business_rec:false,
        readRecoginceData:null,
        recShow:false,
        //默认不调用邮件默认接口
        defaultEmailStatus:false,
        apFileName:'',
        diff:true,
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
        changePdf(){
            //接收调转pdf事件
            this.flag_pdf_up = false;
        },
        newPdf(firstType){
            if (firstType == 1){
                this.new_pdf = false;
            } else {
                this.new_pdf = true;
            }
        },
        //ap file 接收
        apFile(name){
            this.apFileName = name;
        },
        changeRecP(){
            //接收调转承认事件
            this.flag_business_rec = false;
            this.recShow = true;
        },
        changeEmail(){
            //接收跳转email页面
            this.flag_business_rec = true;
            this.recShow = false;
        },
        backCpdf(){
            this.flag_business_rec = false;
            this.defaultEmailStatus = false;
            //将文件状态变为改动状态
            this.$refs.pdfUp.$data.fileChangeFlag = false;
            this.$refs.pdfUp.$data.fileChangeFlag2 = false;
        },
        //email deal callback data
        dealEmailRecData(eventData){
            //console.log(eventData);
            this.readRecoginceData = eventData;
        },
        //pdf -> email
        pdfJumpEmail(){
            this.flag_business_rec = true;
            this.defaultEmailStatus = true;
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
            //this.lastDisplayedPageShow(3);
            this.lastDisplayedPageShow(initContent.lastDisplayedPage);
            let result = initContent.results.type;
            // 未开始状态
            if (result == -1){
                return;
            }
            //console.log(result);
            //不明状态
            if (result == 1){
                this.lastDisplayedPageShow(3);
                //调转到承认模块
                this.$refs.emailDeal.changeRec();
                this.$refs.readRecognize.$data.typeZero = false;
                //data中的body赋值
                let unKnowText = '不備/不明アリで処理されました。ご確認ください<br>' + initContent.G00000_1.C00200_11;
                this.$refs.readRecognize.$data.unKnowFlag = false;
                this.$refs.readRecognize.$data.unKnowText = unKnowText;
            }
            //type 为0 直接显示承认页面
            if (result == 0){
                //经费处理保存回显
                this.loadingDisplay(true);
                if (initContent.G00000_27 !== undefined || Object.keys(initContent.G00000_27).length > 1){
                    if (Object.keys(initContent.G00000_27).length != 1){
                        //设置为保存状态
                        this.$refs.emailDeal.$data.saveStateType = true;
                        //设置email的save值
                        this.$refs.emailDeal.saveInitValue(initContent.G00000_27,time);
                    }
                }
                //this.$refs.emailDeal.$refs.checkboxDialog.$data.checkboxData = initContent.listItemsTypeZero;
                //调转到承认模块
                this.loadingDisplay(false);
                this.$refs.emailDeal.changeRec(initContent.G00000_27.G00000_33);
                this.$refs.readRecognize.$data.typeZero = false;
            }
            //保存过状态
            if (result == 4){
                //经费一保存回显
                if (initContent.G00000_1 !== undefined){
                    if (Object.keys(initContent.G00000_1).length != 0){
                        this.$refs.outLay.saveInitValue(initContent.G00000_1,time);
                        this.apFileName = initContent.G00000_1.C00100_3 + '_' + initContent.G00000_1.C00100_4;
                    }
                }
                //pdf回显
                if (initContent.C00800_24 !== undefined){
                    if (Object.keys(initContent.C00800_24).length != 2){
                        // if (initContent.C00800_24.C00800_26 === undefined || initContent.C00800_24.C00800_26.length == 0){
                        //     this.pdf_show = false;
                        // }
                        if (this.$refs.outLay.$data.firstType == 1){
                            this.new_pdf = false;
                        } else {
                            this.new_pdf = true;
                        }
                        //console.log(initContent.C00800_24);
                        this.$refs.pdfUp.saveInitValue(initContent.C00800_24);
                    }
                }
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
            // 默认为null 或 1 不做处理
            if (page == 1 || page == null){
                // code 暂时不做处理
            }
            //为2跳转到pdf
            // if (page == 2){
            //     this.changePdf();
            // }
            //为3跳转到emailDeal
            if (page == 3 || page == 2){
                this.changePdf();
                this.changeEmail();
            }
        },
        //review值判断是否有review页面save
        emailSend(){
            this.$refs.emailDeal.$data.reviewDisplay = false;
            this.$refs.emailDeal.save();
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
