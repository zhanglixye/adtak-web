<template>
    <v-dialog v-model="dialog" persistent :width="boxWidth">
        <v-card>
            <div class="max-box">
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col col-12 pa-0">
                                <span style="cursor: pointer" @click="dialog=false">
                    <svg width="19px" height="12px"><g transform="matrix(1 0 0 1 -40 -17 )"><path d="M 19 1  L 17 1  L 17 5  L 3.83 5  L 7.41 1.41  L 6 0  L 0 6  L 6 12  L 7.41 10.59  L 3.83 7  L 19 7  L 19 1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 40 17 )" /></g></svg>
                    &nbsp;作業画面に戻る
                </span>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider style="margin: 0;"></v-divider>
                <div class="content-box">
                    <div class="content-head">
                        <div class="content-left">
                            <div>
                                <p class="title">ファイル名</p>
                                <p class="msg">{{materialInfo.file_name}}</p>
                            </div>
                            <div>
                                <p class="title">ピクセル（WxH）</p>
                                <p class="msg">{{materialInfo.display_size}}</p>
                            </div>
                            <div>
                                <p class="title">ファイルサイズ</p>
                                <p class="msg">{{materialInfo.file_size}}&nbsp;Byte</p>
                            </div>
                        </div>
                        <div class="content-right">
                            <p>検索ワード：</p>
                            <div class="search">
                                <input type="text" v-model="keyWord" onfocus="this.select()">
                                <div class="btn-box">
                                    <button @click="pitchOn">選定</button>
                                    <button @click="search">検索</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-body">
                        <table cellspacing="0">
                            <thead>
                            <tr>
                                <td>仕様</td>
                                <td>用途</td>
                                <td>Abbey&nbsp;ID</td>
                                <td>ピクセル（WxH）</td>
                                <td>ファイルサイズ</td>
                                <td>ファイル形式</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in projectList" :class="{pitchOn:item.state}" :key="index" @click="select(item,index)">
                                <td class="text-overflow" :title="item.specification">{{item.specification}}</td>
                                <td>{{item.purpose==1?"画像":item.purpose==2?"動画（音声あり）":item.purpose==3?"動画（音声なし）":item.purpose==4?"代替画像":item.purpose==5?"静止/代替画像":item.purpose==6?"動画（音声必須）":item.purpose==7?"画像（右パネル）":item.purpose==8?"画像（左パネル）":"?"}}</td>
                                <td>{{item.abbey_id}}</td>
                                <td>{{item.width}}*{{item.hight}}</td>
                                <td>{{item.file_size}}&nbsp;{{item.file_size_unit==1?"KB":item.file_size_unit==2?"MB":"?"}}</td>
                                <td class="text-overflow" v-html="getFileFormant(item.file_format)"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'popupMenuSearch',
    props:{
        defaultKeyWord:String,
        projectInfo:Object,
        loadingDisplay: { type: Function, require: true },
        initData: { type: Object, require: true },
        formData:{ type: FormData, require: true }
    },
    data () {
        return {
            dialog:false,   //弹窗开关
            boxWidth:null,  //当前窗口的宽度
            //搜索项目的列表
            keyWord:null,
            file_format_list: {
                1:'MP4',
                2:'PNG',
                4:'JPEG',
                8:'GIF89a',
            },
            materialInfo:{
                file_name:'',
                display_size:'',
                file_size:''
            },
            projectList:[]
        }
    },
    watch:{
        defaultKeyWord:function () {
            this.keyWord = this.defaultKeyWord;
        },
        projectInfo:function(){
            this.materialInfo.file_name = this.projectInfo.material.file_name;
            this.materialInfo.display_size = this.projectInfo.material.display_size;
            this.materialInfo.file_size = this.projectInfo.material.file_size;
        },
        dialog:function () {
            if (this.dialog === true){
                this.materialInfo.file_name = this.projectInfo.material.file_name;
                this.materialInfo.display_size = this.projectInfo.material.display_size;
                this.materialInfo.file_size = this.projectInfo.material.file_size;
                this.search()
            } else {
                this.materialInfo.file_name = '';
                this.materialInfo.display_size = '';
                this.materialInfo.file_size = '';
            }
        }
    },
    mounted() {
        this.boxSize(); //在其他父组件当中调用，窗口大小可能不会有变化，所以保险起见先调用一次该方法
        window.onresize = this.boxSize; //在窗口大小改变时，执行该方法，计算并判断弹窗大小
    },
    methods:{
        //判断窗口大小，设置弹窗盒子的宽度  =》  详情参考2.4.1
        boxSize:function(){
            let width = document.body.clientWidth;
            if (width < 1400){
                this.boxWidth = 1000;
                // window.console.log(this.boxWidth);
            } else {
                this.boxWidth = 1536;
                // window.console.log(this.boxWidth);
            }
        },
        //点击当前项目，设置当前项目为【选中】并取消【其他项目的选中】，再点击一次当前项目【取消当前项目的选中】   =》  详情参考2.4.2
        select:function (item,index) {
            item.state = !item.state;
            for (var i=0;i<this.projectList.length;i++){
                if (i === index){
                    continue;
                } else {
                    this.projectList[i].state = false;
                }
            }
        },
        //点击确定按钮，传递当前选中项目的内容到，表单内容中 =》  详情参考2.4.3
        pitchOn:function () {
            for (var i=0;i<this.projectList.length;i++){
                if (this.projectList[i].state === true){
                    this.$emit('getMsg',this.projectList[i].specification,this.projectList[i].abbey_id);
                    break;
                } else {
                    this.$emit('getMsg',null,null);
                }
            }
            this.dialog = false;
        },
        //搜素
        async search () {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let formData = new FormData();
                formData.append('step_id', this.initData['request_info']['step_id']);
                formData.append('request_id', this.initData['request_info']['request_id']);
                formData.append('request_work_id', this.initData['request_info']['id']);
                formData.append('task_id', this.initData['task_info']['id']);
                formData.append('task_started_at', this.initData['task_started_at']);
                formData.append('task_result_content',JSON.stringify(this.$parent.$parent.$parent.$parent.getAllComponentData()));
                formData.append('search_word',this.keyWord);
                await axios.post('/api/biz/b00007/s00013/search',formData).then((res)=>{
                    this.projectList = res.data.data;
                    this.projectList.forEach((item)=>{
                        _this.$set(item,'state',false)
                    })
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //计算【ファイル形式】
        getFileFormant: function(format_bit){
            let format = ''
            for (const key of Object.keys(this.file_format_list)) {
                if ( (parseInt(format_bit, 2) & key ) == key) {
                    if (format == '') {
                        format += this.file_format_list[key];
                    } else {
                        format += '&nbsp;&nbsp;'+this.file_format_list[key];
                    }
                }
            }
            return format
        }
    }
}
</script>

<style scoped>
    /*重置*/
    p{margin: 0 !important;}
    /*清除浮动*/
    .content-head:after{
        display: block;
        content: "";
        clear: both;
    }
    /*-*-*-*-*-*-*-*-*/
    .text-overflow{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
    }
    .max-box{
        width: 100%;
    }
    .title-box svg{
        transform: translateY(2px);
    }
    .title-box span{
        cursor: pointer;
    }
    .content-box{
        padding: 0 8%;
        background-color: #fafafa;
    }
    .content-box:before{
        display: block;
        content: "";
        height: 20px;
    }
    .content-box:after{
        display: block;
        content: "";
        height: 50px;
    }
    .content-head{
        margin: 0 0 20px 0;
        padding: 25px 30px;
        background-color: #ffffff;
        box-shadow:0 1px 2px rgba(0, 0, 0, 0.15);
    }
    .content-head .content-left,.content-head .content-right{
        width: 50%;
        height: 105px;
        float: left;
    }
    .content-head .content-left{
        padding-right: 70px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .content-head .content-left div{
        height: 32px;
        line-height: 32px;
        display: flex;
    }
    .content-head .content-left .title{
        width: 27%;
        font-size: 14px !important;
        line-height: 32px !important;
        font-weight: bold;
        color: #ffffff;
        padding-left: 8px;
        background-color: rgba(0, 200, 170, 1);
    }
    .content-head .content-left .msg{
        width: 73%;
        font-size: 15px;
        color: #4d4d4d;
        padding-left: 20px;
        background-color: rgba(218, 242, 234, 1);
    }
    .content-head .content-right{
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .content-head .content-right p{
        line-height: 30px;
    }
    .content-head .content-right .search{
        display: flex;
        justify-content: space-between;
    }
    .content-head .content-right .search input{
        outline: none;
        width: 82%;
        height: 30px;
        transform: translateY(40px);
        padding: 0 5px;
        font-size: 16px;
        color: #7f7f7f;
        border-bottom: solid 1px rgba(170, 170, 170, 1)
    }
    .content-head .content-right .search .btn-box{
        display: flex;
        width: 15%;
        height: 70px;
        flex-direction: column;
        justify-content: space-between;
    }
    .content-head .content-right .search .btn-box button{
        width: 100%;
        height: 30px;
        font-size: 14px;
        border-radius: 2px;
        color: #ffffff;
        outline: none;
        background-color: rgba(0, 200, 170, 1);
    }
    .content-head .content-right .search .btn-box button:active{
        background-color: rgba(3, 166, 147, 1);
    }
    .content-body{
        padding: 20px;
        background-color: #ffffff;
        box-shadow:0 1px 2px rgba(0, 0, 0, 0.15);
    }
    .content-body tr{
        display: flex;
    }
    .content-body td:nth-child(1),
    .content-body td:nth-child(2),
    .content-body td:nth-child(4),
    .content-body td:nth-child(5){
        width: 19%;
    }
    .content-body td:nth-child(3){
        width: 9%;
    }
    .content-body td:nth-child(6){
        width: 15%;
    }
    .content-body table{
        width: 100%;
        border: solid 1px rgba(215, 215, 215, 1);
    }
    .content-body thead tr{
        background-color: rgba(250, 250, 250, 1);
    }
    .content-body thead td{
        text-align: center;
        font-size: 15px;
        height: 45px;
        line-height: 44px;
        color: #555555;
        border-right: solid 1px rgba(215, 215, 215, 1);
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    .content-body td:nth-last-child(1){
        border-right: none;
    }
    .content-body tbody{
        display: block;
        max-height: 370px;
        overflow-y: auto;
    }
    .content-body tbody::-webkit-scrollbar{
        display: none;
    }
    .content-body tbody .pitchOn{
        background-color: rgba(158, 158, 158, 1) !important;
    }
    .content-body tbody .pitchOn td{
        color: #ffffff;
    }
    .content-body tbody tr:hover{
        background-color: rgba(252, 252, 252, 1);
    }
    .content-body tbody td{
        height: 40px;
        line-height: 39px;
        text-align: center;
        font-size: 14px;
        color: #555555;
        border-right: solid 1px rgba(240, 240, 240, 1);
        border-bottom: solid 1px rgba(240, 240, 240, 1);
    }
</style>
