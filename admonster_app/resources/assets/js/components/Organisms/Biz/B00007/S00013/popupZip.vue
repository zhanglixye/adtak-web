<template>
    <v-dialog v-model="dialog" persistent width="940">
        <div class="content-box">
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters" style="display: flex;">
                            <div class="col col-10 pa-0">
                                <div style="font-size: 16px">パスワードを入力しzipファイルを展開してください</div>
                            </div>
                            <div class="col col-2 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider style="margin: 0;"></v-divider>
                <div class="project-box">
                    <ul class="project-list">
                        <li class="project" v-for="(item,index) in projectList" v-bind:key="index">
                            <img src="/images/biz/b00007/u285.svg" alt="">
                            <p>{{item.file_name}}</p>
                            <button class="zipBtn" :class="{congelation:accomplish}" ref="zipBtn" @click="zipProject($event,index,projectArr)" :value="index" :disabled="item.unzip_flag || accomplish">{{item.unzip_flag?'展開済':'展開する'}}</button>
                            <input v-show="!accomplish" class="password" type="password" v-model="item.unZipPassword" placeholder="パスワードを入力">
                        </li>
                    </ul>
                </div>
            </v-card>
        </div>
    </v-dialog>
</template>

<script>
export default {
    name: 'popupZip',
    props:{
        loadingDisplay: { type: Function, require: true },
        formData: { type: FormData, require: true },
        initData:Object,
        projectArr:Array,
        accomplish:Boolean
    },
    data () {
        return {
            dialog:false,
            projectList:[]
        }
    },
    created(){
        this.loadListData();
    },
    methods:{
        //组件加载时获取解压列表数据
        loadListData:function(){
            let _this = this;
            this.projectList = JSON.parse(this.initData.task_result_info.content).zip_array;
            this.projectList.forEach((item)=>{
                _this.$set(item,'unZipPassword','')
            })
        },
        //点击【解压】按钮后，如果密码正确，设置解压按钮禁用，并添加解压项目到项目列表    =》  详情参考4.4.1
        async zipProject(event,index,arr){
            try {
                let dom = event.target;
                this.loadingDisplay(true);
                var _this = this;
                var url = '/api/biz/b00007/s00013/unZipMaterialWithPasswd';
                let formData = new FormData();
                formData.append('step_id', this.initData['request_info']['step_id']);
                formData.append('request_id', this.initData['request_info']['request_id']);
                formData.append('request_work_id', this.initData['request_info']['id']);
                formData.append('task_id', this.initData['task_info']['id']);
                formData.append('task_started_at', this.initData['task_started_at']);
                formData.append('task_result_content',JSON.stringify(this.$parent.$parent.$parent.$parent.getAllComponentData()));
                formData.append('file_path',this.projectList[index].file_path);
                formData.append('passwd',this.projectList[index].unZipPassword);
                await axios.post(url,formData).then((res)=>{
                    if (res.data.result == 'success'){
                        _this.$refs.zipBtn[dom.value].innerHTML = '展開済';
                        _this.$set(_this.projectList[index],'unzip_flag',true);
                        res.data.data.forEach((item)=>{
                            _this.$parent.projectList.push(
                                {
                                    material: {
                                        seq_no: item.seq_no,
                                        file_name: item.file_name,
                                        file_path: item.file_path,
                                        file_size: item.file_size,
                                        display_size: item.display_size,
                                        check_file_name: item.check_file_name
                                    },
                                    result_capture: {
                                        seq_no: '',
                                        file_name: '',
                                        file_path: '',
                                        file_size: '',
                                        display_size: '',
                                    },
                                    check: {
                                        result: '',
                                        abbey_id: '',
                                        menu_name: '',
                                        error_message: '',
                                        result_comment: '',
                                    },
                                    webProperties: {
                                        projectCheck: false,
                                        resultShade: null,
                                        materialShade: null,
                                        indexVal:arr.length,
                                        resultThumbnailState:true,
                                        resultThumbnailShow:true,
                                        materialThumbnailShow:true,
                                        materialThumbnail:'',
                                        resultThumbnail:''
                                    }
                                }
                            );
                            _this.$parent.loadMaterialThumbnail(arr.length-1);
                        });
                        _this.$nextTick(function(){
                            _this.$parent.$refs.parentNode.$el.scrollTop = _this.$parent.$refs.parentNode.$el.scrollHeight;
                        });
                    } else {
                        _this.$parent.$parent.$parent.errInformPopup(res.data.err_message);
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false);
            }
        }
    },
}
</script>

<style scoped>
    img{display: block}
    li{list-style: none}
    p{margin: 0 !important;}
    /*清除浮动*/
    .project-title::after{
        display: block;
        content: "";
        clear: both;
    }
    /*-*-*-*-*-*/
    .content-box{
        width: 100%;
        /*height: 602px;*/
        background-color: #ffffff;
    }
    .project-box{
        padding: 30px;
    }
    .project-list{
        padding: 0;
        max-height: 496px;
        border: solid 1px rgba(215, 215, 215, 1);
        border-bottom: none;
        overflow-y: auto;
    }
    .project-list::-webkit-scrollbar{
        display: none;
    }
    .project-list .project{
        height: 45px;
        padding: 0 15px;
        border-bottom: solid 1px rgba(215, 215, 215, 1);
        background-color: rgba(252, 252, 252, 1);
    }
    .project-list .project img{
        transform: translateY(12px);
        float: left;
    }
    .project-list .project p{
        width: 38%;
        white-space:nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 14px;
        color: #555555;
        line-height: 45px;
        float: left;
        transform: translateX(8px);
    }
    .project-list .project .password{
        width: 28%;
        height: 30px;
        line-height: 30px;
        background-color: #ffffff;
        border: solid 1px rgba(215, 215, 215, 1);
        text-align: right;
        font-size: 14px;
        padding: 0 10px;
        outline: none;
        transform: translateY(7px);
        float: right;
    }
    .project-list .project .zipBtn{
        width: 100px;
        height: 30px;
        line-height: 30px;
        margin-left: 10px;
        float: right;
        color: #ffffff;
        font-size: 14px;
        transform: translateY(7px);
        background-color: rgba(0, 200, 170, 1);
        border-radius: 2px;
        outline: none;
        cursor: pointer;
    }
    .project-list .project .zipBtn.congelation{
        background-color: rgba(0, 200, 170, 1) !important;
        cursor: not-allowed !important;
    }
    .project-list .project .zipBtn:disabled{
        background-color: rgba(138, 229, 199, 1);
        cursor: pointer;
    }
</style>
