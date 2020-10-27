<template>
    <div>
        <AbbeyList
            v-show="abbeySwitch"
            ref="abbeyList"
            :initData="initData"
            :formData="axiosFormData"
            :accomplish="accomplish"
            :loadingDisplay="loadingDisplay"
            :business_flag_show="business_flag_show"
            @setInfoMsg="setInfoMsg"
        >
        </AbbeyList>
        <AbbeyInfo
            v-show="!abbeySwitch"
            ref="abbeyInfo"
            :initData="initData"
            :formData="axiosFormData"
            :accomplish="accomplish"
            :loadingDisplay="loadingDisplay"
            :business_flag_show="business_flag_show"
            @setListMsg="setListMsg"
        >
        </AbbeyInfo>
    </div>
</template>

<script>
import AbbeyList from '../../../Organisms/Biz/B00007/S00013/AbbeyList'
import AbbeyInfo from '../../../Organisms/Biz/B00007/S00013/AbbeyInfo'
export default {
    name: 'S000013',
    components:{
        AbbeyList,
        AbbeyInfo
    },
    props:{
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
    },
    data:()=>({
        business_flag_show:true,
        abbeySwitch:true,
        timerOne:null
    }),
    computed:{
        axiosFormData() {
            let formData = new FormData();
            formData.append('step_id', this.initData['request_info']['step_id']);
            formData.append('request_id', this.initData['request_info']['request_id']);
            formData.append('request_work_id', this.initData['request_info']['id']);
            formData.append('task_id', this.initData['task_info']['id']);
            formData.append('task_started_at', this.initData['task_started_at']);
            formData.append('task_result_content', this.initData.task_result_info.content);
            return formData;
        },
        accomplish(){
            return this.initData.task_info.status == 2 ? true : false;
        }
    },
    mounted(){
        this.examineElWidth();
        //刷新页面时提示
        if (this.accomplish == false){
            window.onbeforeunload = () => {
                return '';
            };
        }

    },
    beforeDestroy() {
        //清除定时器
        clearInterval(this.timerOne);
    },
    methods:{
        //向info组件发送数据
        setInfoMsg:function (data) {
            this.abbeySwitch = data.infoShow;
            this.$refs.abbeyInfo.projectInfo = null;
            this.$refs.abbeyInfo.projectInfo = data.projectInfo;
        },
        setListMsg:function () {
            this.abbeySwitch = true;
            this.$refs.abbeyList.getMsg();
        },
        //定义一个定时器来判断当前组件的宽度
        examineElWidth:function () {
            let _this = this;
            let el = document.getElementsByClassName('grow');
            let judge = () => {
                let width = _this.$el.offsetWidth;
                if (width > 1000){
                    _this.business_flag_show = false;
                    el[0].style.width = '94%';
                } else {
                    _this.business_flag_show = true;
                    el[0].style.width = 'auto';
                }
            };
            this.timerOne = setInterval(judge,1000);
        },
        //获取所有组件最新的数据
        getAllComponentData:function () {
            var dataTemplate = JSON.parse(this.axiosFormData.get('task_result_content'));
            var item_array = JSON.parse(JSON.stringify(this.$refs.abbeyList.$refs.abbeyListTable.projectList));
            dataTemplate.item_array = item_array.map((item)=>{
                delete item.webProperties;
                return item;
            });
            dataTemplate.client_name = this.$refs.abbeyList.client_name;
            dataTemplate.unknown_comment = this.$refs.abbeyList.$refs.unKown.lastName;
            dataTemplate.common_work_time = {
                started_at:((this.$refs.abbeyList.$refs.dateBuss.fromDate._i === undefined)||(this.$refs.abbeyList.$refs.dateBuss.fromDate._i === 'Invalid date'))?null:this.convertToTime(this.$refs.abbeyList.$refs.dateBuss.fromDate._i),
                finished_at:((this.$refs.abbeyList.$refs.dateBuss.toDate._i === undefined)||(this.$refs.abbeyList.$refs.dateBuss.toDate._i === 'Invalid date'))?null:this.convertToTime(this.$refs.abbeyList.$refs.dateBuss.toDate._i),
                total:this.$refs.abbeyList.$refs.dateBuss.iHour
            };
            dataTemplate.zip_array = this.$refs.abbeyList.$refs.abbeyListTable.$refs.popupZip.projectList;
            return dataTemplate;
        },
        //前台给后台
        convertToTime: function(date){
            return moment(date).utc().format('YYYY/MM/DD HH:mm:ss');
        }
    }
}
</script>

<style scoped>
    @import "../../../../../sass/biz/b00006.scss";
</style>
