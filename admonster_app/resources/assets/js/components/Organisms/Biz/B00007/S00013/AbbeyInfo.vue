<template>
    <v-card class="max-box height-100" style="max-height: 640px;">
        <div class="form-title-box">
            <div :class="{returnBtn:business_flag_show,returnBtnTwo:!business_flag_show}" @click="switchModule">
                <svg width="19px" height="12px"><g transform="matrix(1 0 0 1 -40 -17 )"><path d="M 19 1  L 17 1  L 17 5  L 3.83 5  L 7.41 1.41  L 6 0  L 0 6  L 6 12  L 7.41 10.59  L 3.83 7  L 19 7  L 19 1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 40 17 )" /></g></svg>
            </div>
            <div>素材</div>
            <div>詳細</div>
        </div>
        <div class="form-center-box">
            <AbbeyInfoMaterial
                :materialInfo="projectInfo"
            ></AbbeyInfoMaterial>
            <div class="form-center">
                <AbbeyInfoForm
                    ref="abbeyInfoForm"
                    :accomplish="accomplish"
                    :loadingDisplay="loadingDisplay"
                    :formData="formData"
                    :initData="initData"
                    :projectInfo="projectInfo"
                ></AbbeyInfoForm>
                <AbbeyInfoUploading
                    ref="abbeyInfoUploading"
                    :accomplish="accomplish"
                    :loadingDisplay="loadingDisplay"
                    :initData="initData"
                    :formData="formData"
                    @receiveResultFile="resultFile"
                    @informPopup="informPopup"
                ></AbbeyInfoUploading>
            </div>
        </div>
        <v-btn color="warning" class="ack-button" @click="switchModule(projectInfo)">確定</v-btn>
        <popup-inform ref="PopupInform" :message="message"></popup-inform>
    </v-card>
</template>

<script>
import AbbeyInfoMaterial from './AbbeyInfoMaterial';
import AbbeyInfoForm from './AbbeyInfoForm';
import AbbeyInfoUploading from './AbbeyInfoUploading'
import PopupInform from './popupInform'
export default {
    name: 'AbbeyInfo',
    components:{
        AbbeyInfoMaterial,
        AbbeyInfoForm,
        AbbeyInfoUploading,
        PopupInform
    },
    props:{
        business_flag_show:Boolean,
        initData: { type: Object, require: true },
        loadingDisplay: { type: Function, require: true },
        formData:{ type: FormData, require: true },
        accomplish:Boolean
    },
    data(){
        return {
            infoShow:true,
            projectInfo: null,
            message:''
        }
    },
    watch:{
        projectInfo:function () {
            this.setFormMsg();
        }
    },
    methods:{
        //点击返回时，与确定时，返回【列表模块】
        switchModule:function (msg) {
            this.getFormMsg();
            var data = {
                infoShow:true,
                projectInfo:msg,
            };
            this.$emit('setListMsg',data);  //向父级组件传递切换状态，控制【v-if】【v-else】
            this.$refs.abbeyInfoUploading.imgList = [];
            this.$refs.abbeyInfoUploading.size = 0;
            this.$refs.abbeyInfoUploading.thumbnail = '';
            this.$refs.abbeyInfoUploading.upload_state = false;
        },
        //接收上传组件返回的数据，赋值给【file_path】
        resultFile:function (msg) {
            this.projectInfo.result_capture.seq_no = msg.data.seq_no;
            this.projectInfo.result_capture.file_name = msg.data.file_name;
            this.projectInfo.result_capture.file_path = msg.data.file_path;
            this.projectInfo.result_capture.file_size = msg.data.file_size ;
            this.projectInfo.result_capture.display_size = msg.data.display_size;
        },
        //获取表单组件的值
        getFormMsg:function(){
            this.projectInfo.check.abbey_id = this.$refs.abbeyInfoForm.abbeyID == ''? '' : Number(this.$refs.abbeyInfoForm.abbeyID);
            this.projectInfo.check.menu_name = this.$refs.abbeyInfoForm.menuName;
            this.projectInfo.check.result = this.$refs.abbeyInfoForm.judge == '' ? '' : Number(this.$refs.abbeyInfoForm.judge);
            this.projectInfo.check.error_message = this.$refs.abbeyInfoForm.errorContent;
            this.projectInfo.check.result_comment = this.$refs.abbeyInfoForm.checkResult;
        },
        //设置表单组件的值
        setFormMsg:function(){
            this.$refs.abbeyInfoForm.abbeyID = this.projectInfo.check.abbey_id;
            this.$refs.abbeyInfoForm.menuName = this.projectInfo.check.menu_name;
            this.$refs.abbeyInfoForm.judge = String(this.projectInfo.check.result);
            this.$refs.abbeyInfoForm.errorContent = this.projectInfo.check.error_message;
            this.$refs.abbeyInfoForm.checkResult = this.projectInfo.check.result_comment;
            if (this.projectInfo.result_capture.file_path !== ''){
                this.$refs.abbeyInfoUploading.thumbnailDefaultShow(this.projectInfo.result_capture.file_path);
                this.$refs.abbeyInfoUploading.size = this.projectInfo.result_capture.file_size
            }
        },
        //设置弹窗消息
        informPopup:function (msg) {
            this.message = msg;
            this.$refs.PopupInform.dialog = true;
        },
    },
}
</script>

<style scoped>
    /*响应*/
    @media (max-width: 1680px) {
        .form-title-box .returnBtn{
            width: 8% !important;
        }
        .form-title-box .returnBtn + div{
            width: 42% !important;
        }
        .form-title-box .returnBtnTwo{
            width: 5% !important;
        }
        .form-title-box .returnBtnTwo + div{
            width: 45% !important;
        }
    }
    @media (max-width: 1440px) {
        .form-title-box .returnBtn{
            width: 10% !important;
        }
        .form-title-box .returnBtn + div{
            width: 40% !important;
        }
        .form-title-box .returnBtnTwo{
            width: 6% !important;
        }
        .form-title-box .returnBtnTwo + div{
            width: 44% !important;
        }
    }
    /*清除浮动*/
    .form-title-box::after{
        display: block;
        content: "";
        clear: both;
    }
    .max-box{
        padding: 30px 24px;
    }
    .height-100{
        height: 100%;
    }
    .form-title-box{
        box-sizing: border-box;
        border-radius: 0 !important;
        background-color: rgba(250, 250, 250, 1);
        border: solid 1px rgba(215, 215, 215, 1);
    }
    .form-title-box div{
        float: left;
        width: 50%;
        height: 44px;
        padding: 8px 0;
        line-height: 28px;
        text-align: center;
        color: #555555;
        font-weight: bold;
    }
    .form-title-box div:after{
        display: block;
        height: 28px;
        float: right;
        content: "";
        border-right: solid 1px #aaaaaa;
    }
    .form-title-box div:nth-last-child(1):after{
        display: none;
    }
    .form-title-box .returnBtn{
        width: 6%;
        cursor: pointer;
    }
    .form-title-box .returnBtnTwo{
        width: 4%;
        cursor: pointer;
    }
    .form-title-box .returnBtn + div{
        width: 44%;
    }
    .form-title-box .returnBtnTwo + div{
        width: 46%;
    }
    .form-center-box{
        height: 490px;
        margin-bottom: 20px;
        border: solid 1px rgba(215, 215, 215, 1);
        border-top: none;
        overflow-y: auto;
    }
    .form-center-box::-webkit-scrollbar{
        display: none;
    }
    .form-center{
        padding: 20px;
    }
    .ack-button{
        display: block;
        width: 100px;
        font-size: 16px;
        margin: 0 auto;

    }
    .v-btn{
        padding: 0;
    }
</style>
