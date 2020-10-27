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
                            <div class="col col-10 pa-0">
                                <div style="font-size: 16px">署名設定</div>
                            </div>
                            <div class="col col-2 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                    <div style="padding: 24px 24px 0">
                        <v-text-field
                                label="タイトル"
                                required
                                v-model="title"
                        ></v-text-field>
                    </div>
                    <v-card-text class="pb-0">
                        <editor-sign ref="editor_sign" class="mt-3" style="color: rgba(0, 0, 0, 0.87)"></editor-sign>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn
                                color="success"
                                @click="saveData"
                                style="width: 100px;margin-left: 6px"
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
            tab:'tab-sign',
            title:'',
            loading:false,
        }
    },
    methods:{
        async saveData(){
            try {
                this.loading=true;
                let resultObj = {};
                resultObj.title = this.title;
                resultObj.content = this.$refs.editor_sign.$data.content;
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('task_result_content',JSON.stringify(resultObj));
                //发送请求
                const res = await axios.post('/api/biz/common/mail/saveSignTemplates', formData)
                this.loading = false;
                if (res.data.result === 'success') {
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                    this.dialog = false;
                } else {
                    this.$refs.alert.show(res.data.err_message)
                }
            } catch (e) {
                console.log(e)
                this.loading = false
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            }
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