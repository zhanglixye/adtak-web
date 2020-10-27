<template>
    <div class="">
        <common-page
            ref="commonPage"
            :axiosFormData="axiosFormData"
            :edit="edit"
            :loadingDisplay="loadingDisplay"
        >
        </common-page>
    </div>
</template>

<script>
import CommonPage from '../../../Organisms/Biz/Common/DynamicPage/DynamicPage';
export default {
    name: 'S00024',
    components: {CommonPage},
    props:{
        initData: { type: Object, require: true },
        edit: { type: Boolean, require: false, default: false },
        loadingDisplay: { type: Function, require: true },
        maxHeight: { type: String, required: false, default: ''},// cssの単位付き数値
    },
    computed:{
        axiosFormData() {
            let formData = new FormData();
            formData.append('step_id', this.initData['request_info']['step_id']);
            formData.append('request_id', this.initData['request_info']['request_id']);
            formData.append('request_work_id', this.initData['request_info']['id']);
            formData.append('task_id', this.initData['task_info']['id']);
            formData.append('id', this.initData['task_result_info']['id']);
            formData.append('task_started_at', this.initData['task_started_at']);
            formData.append('task_result_content', JSON.stringify(JSON.parse(this.initData.task_result_info.content).components))
            return formData;
        },
    },
    mounted(){
        //初始化result所有数据
        this.init();
    },
    methods:{
        init(){
            let initContent = JSON.parse(this.initData['task_result_info'].content);
            if (initContent.results === undefined) {
                return;
            }
            let result = initContent.results.type;
            //未开始状态
            if (result == -1){
                return;
            }
            //承认状态
            if (result == 0){
                this.$refs.commonPage.$data.statusBool = true;
                this.$refs.commonPage.$data.approvalStatus = false;
            }
            //不明状态
            if (result == 1){
                this.$refs.commonPage.$data.unKnowStatus = true;
                this.$refs.commonPage.$data.statusBool = true;
                this.$refs.commonPage.$data.approvalStatus = false;
                this.$refs.commonPage.$data.unKnowData = initContent.results.comment;
            }
            //保存过状态
            if (result == 4) {
                this.$refs.commonPage.saveInitValue(initContent);
                return;
            }
        }
    }
}
</script>

<style scoped>
    @import "../../../../../sass/biz/b00006.scss";
    .v-btn {
        text-decoration: none;
    }
</style>