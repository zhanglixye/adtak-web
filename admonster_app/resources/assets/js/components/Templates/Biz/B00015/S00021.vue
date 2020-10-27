<template>
    <div>
        <xone-media-material-link
            :maxHeight="businessHeight"
            :initData="initData"
            :edit="edit"
            :loadingDisplay="loadingDisplay"
            :axiosFormData="axiosFormData"
            :workState="workState"
        />
    </div>
</template>

<script>
import XoneMediaMaterialLink from '../../../Organisms/Biz/B00015/S00021/XoneMediaMaterialLink';
export default {
    name: 'S00021',
    components: {
        XoneMediaMaterialLink
    },
    props: {
        initData: {type: Object, require: true},
        edit: {type: Boolean, require: false, default: false},
        loadingDisplay: {type: Function, require: true}
    },
    data: () => ({
        businessHeight: 640
    }),
    computed: {
        axiosFormData() {
            let formData = new FormData();
            formData.append('step_id', this.initData['request_info']['step_id']);
            formData.append('request_id', this.initData['request_info']['request_id']);
            formData.append('request_work_id', this.initData['request_info']['id']);
            formData.append('task_id', this.initData['task_info']['id']);
            formData.append('task_started_at', this.initData['task_started_at']);
            formData.append('task_result_content', this.initData['task_result_info']['content']);
            return formData;
        },
        workState(){
            return JSON.parse(this.initData.task_result_info.content).results.type
        }
    }
}
</script>

<style scoped lang="scss">
    @import "../../../../../sass/biz/b00006.scss";
</style>
