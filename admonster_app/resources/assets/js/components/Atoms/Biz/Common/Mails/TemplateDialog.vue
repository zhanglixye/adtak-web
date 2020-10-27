<template>
    <div class="inline-bl">
        <v-dialog
                v-model="dialog"
                max-width="600"
                persistent
        >
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col-11 pa-0" style="font-size: 16px">
                                <v-tabs
                                        v-model="tab"
                                        @change="tab_change"
                                >
                                    <v-tab
                                            :href="'#tab-sign'+(i+1)"
                                            style="text-decoration: none"
                                            v-for="(item,i) in defaultTemData"
                                            :key="i"
                                    >
                                        {{item.title}}
                                    </v-tab>
                                </v-tabs>
                            </div>
                            <div class="col-1 pa-0" style="display: flex;flex-direction: row-reverse;align-items:center;">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <div v-show="tabFlag[i]" :class="'tab-sign'+(i + 1)" :key="'sign'+(i + 1)" v-for="(item,i) in defaultTemData">
                    <div style="padding: 24px 24px 0">
                        <v-text-field
                                label="タイトル"
                                required
                                v-model="title[i]"
                        ></v-text-field>
                    </div>
                    <v-card-text style="padding: 0 24px 20px">
                        <editor-sign :cont="item.content" :ref="'textSign'+(i + 1)" class="mt-3" style="color: rgba(0, 0, 0, 0.87)"></editor-sign>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn
                                color="success"
                                @click="saveData"
                                style="margin-left: 6px;width: 100px"
                        >
                            保存
                        </v-btn>

                        <v-btn
                                color="success"
                                @click="dialog = false"
                                style="width: 100px;margin-left: 16px"
                        >
                            閉じる
                        </v-btn>
                    </v-card-actions>
                </div>
            </v-card>
        </v-dialog>
        <!--pagination-->
        <progress-circular v-if="loading"></progress-circular>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
import EditorSign from '../../../../Molecules/Biz/Common/Mails/EditorSign';
import ProgressCircular from '../../../Progress/ProgressCircular';
import AlertDialog from '../../../Dialogs/AlertDialog';
export default {
    components: {
        AlertDialog,
        ProgressCircular,
        EditorSign
    },
    props:{
        //axios formData
        axiosFormData:{type:FormData,require:true}
    },
    data () {
        return {
            dialog: false,
            tabFlag:[true],
            tab:'tab-sign1',
            signValue:'',
            tabValue:'',
            //默认模板数据
            defaultTemData:null,
            loading: false,
            title:[],
            condition_cd:[]
        }
    },
    watch:{
        defaultTemData(val) {
            for (let i of val.keys()) {
                if (i != (val.length - 1)){
                    this.tabFlag.push(false);
                }
                this.title.push(val[i].title);
                this.condition_cd.push(val[i].condition_cd)
            }
        }
    },
    methods:{
        tab_change(number){
            var indexT = number.charAt(number.length - 1) - 1;
            this.tabFlag.forEach((value,index) => {
                if (indexT == index){
                    this.tabFlag[index] = true;
                } else {
                    this.tabFlag[index] = false;
                }
            });
        },
        //保存数据
        async saveData(){
            try {
                this.loading = true;
                let formData = this.getData();
                //console.log(formData.get('task_started_at'));
                //console.log(formData.get('task_result_content'));
                //发送请求
                const res = await axios.post('/api/biz/common/mail/saveBodyTemplates',formData)
                if (res.data.result === 'success') {
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                    this.dialog = false;
                } else {
                    this.$refs.alert.show(res.data.err_message)
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loading = false;
            }
        },
        //获取数据
        getData(){
            let formData = new FormData();
            let formDataJson = [];
            for (let h in this.defaultTemData){
                let obj = {};
                let signFlag = 'textSign'+(Number(h) + Number(1));
                obj['content'] = this.$refs[signFlag][0].$data.content;
                obj['title'] = this.title[h];
                obj['condition_cd'] = this.condition_cd[h];
                formDataJson.push(obj);
            }
            formData.append('step_id',this.axiosFormData.get('step_id'));
            formData.append('request_id',this.axiosFormData.get('request_id'));
            formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
            formData.append('task_id',this.axiosFormData.get('task_id'));
            formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
            formData.append('task_result_content',JSON.stringify(formDataJson));
            return formData;
        }
    }
}
</script>

<style scoped>
    .inline-bl{
        display: inline-block;
    }
    .v-card__actions{
        padding: 18px;
    }
    /* row class重写*/
    .row {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        -webkit-box-flex: 1;
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        margin-right: -12px;
        margin-left: -12px;
    }
    hr{
        margin: 0;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
</style>