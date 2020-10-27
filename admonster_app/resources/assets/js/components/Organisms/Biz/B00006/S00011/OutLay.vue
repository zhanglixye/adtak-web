<template>
    <v-card class="card_t">
        <template>
            <v-form class="v-form"
                    v-model="valid"
                    ref="form"
                    style="height: 100%;"
            >
                <div style="padding: 16px 16px 0">
                    <div>メールに添付されたExcelファイルをダウンロードし、ファイルを開いてください。</div>
                    <div>
                        <span>開いたファイルの『★交通費（AP）メール』シートと『★交通費（常駐）メール』シートどちらに記載がありますか？</span>
                        <a class="ml-3" @click="showPdfLink('AP')">確認方法</a>
                    </div>
                </div>
                <div class="mt-3" style="padding: 0 16px 0">
                    <div style="display: flex;justify-content: center">
                        <v-btn
                                :class="[{'unbindColor':colorO},'ma-btn']"
                                @click="colorO=true;colorON=false;firstType=0;"
                        >
                            ★交通費（AP）メール
                        </v-btn>
                        <v-btn
                                :class="[{'unbindColor':colorON},'ma-0']"
                                @click="colorO=false;colorON=true;firstType=1;"
                        >
                            ★交通費（常駐）メール
                        </v-btn>
                    </div>
                </div>
                <div class="stepT" style="background-color: #fff;padding: 0 16px 0">
                    <div class="mt-3">
                        <div>記載があるシートの下記項目情報を入力してください。</div>
                        <div>
                            <span>また金額明細の合計と合計金額欄（太枠内）が合っているか確認してください。</span>
                            <a class="ml-3" @click="showPdfLink('KJ')">入力・確認方法</a>
                        </div>
                    </div>
                    <div class="mt-4 check_dia_bo text-center" style="padding:25px 50px;width: 60%;margin: auto">
                        <div style="height: 70px">
                            <v-menu
                                    ref="menu"
                                    v-model="menu"
                                    :close-on-content-click="false"
                                    :return-value.sync="date"
                                    transition="scale-transition"
                                    offset-y
                                    max-width="290px"
                                    min-width="290px"
                                    style="width: 100%"
                            >
                                <v-text-field
                                        v-model="date"
                                        label="会計年月"
                                        prepend-icon="event"
                                        readonly
                                        slot="activator"
                                ></v-text-field>

                                <v-date-picker
                                        v-model="date"
                                        type="month"
                                        no-title
                                        scrollable
                                        locale="ja"
                                        @change="changeByPicker"
                                >
<!--                                    <v-spacer></v-spacer>-->
<!--                                    <v-btn flat color="primary" @click="menu = false">キャンセル</v-btn>-->
<!--                                    <v-btn flat color="primary" @click="$refs.menu.save(date)">確認</v-btn>-->
                                </v-date-picker>
                            </v-menu>
                        </div>
                        <v-text-field @blur="suppleZero" v-model="s12_receive1" :rules="rules" label="社員番号"></v-text-field>
                        <v-text-field v-model="s12_receive2" :rules="fullRulesJp" style="margin-top: 10px" label="フリガナ"></v-text-field>
                        <v-text-field v-model="s12_receive3" style="margin-top: 10px" label="氏名"></v-text-field>
                        <v-text-field class="bg-white" :rules="NumberRules" v-model="value_m" @blur="thousand_mark" type="text" prefix="￥" style="margin-top: 10px" label="申請合計金額"></v-text-field>
                        <!--文字-->
                        <div class="ming">従業員情報</div>
                    </div>
                    <div class="mt-3">
                        上記の入力情報、金額確認で不備・不明はありますか？
                    </div>
                    <div class="mt-3">
                        <v-radio-group v-model="radioTypeSaveValueRecord" class="justify-center" row @change="handleCheckboxAP">
                            <v-radio
                                    class="mr-10"
                                    v-for="item in radioTypes"
                                    :key="item.label"
                                    :label="item.label"
                                    :value="item.value"
                            >
                            </v-radio>
                        </v-radio-group>
                    </div>
                        <un-know-cost-a-p-dialog @unkownEvent="unkownEvent" ref="knowCostAP" :title="title"></un-know-cost-a-p-dialog>
                </div>
                <div class="row" style="margin: 0;padding:0px 16px 16px;background-color: #fff">
                    <div>
                        <v-btn
                                color="success"
                                class="ma-0 mr-3"
                                style="width: 100px"
                                @click="changePdf"
                                :disabled="disabledFlag"
                        >
                            次へ
                        </v-btn>
                        <v-btn
                                color="#949394"
                                dark
                                class="ma-0 mr-3"
                                style="width: 100px"
                                @click="savePeData"
                        >
                            一時保存
                        </v-btn>
                    </div>
                </div>
            </v-form>
            <alert-dialog ref="alert"></alert-dialog>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
        </template>
    </v-card>
</template>

<script>
import UnKnowCostAPDialog from '../../../../Atoms/Biz/B00006/UnKnowCostAPDialog';
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog';
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog';
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    components:{ConfirmDialog, AlertDialog,UnKnowCostAPDialog,FilePreviewDialog},
    props:{
        axiosFormData:{type:FormData,require:true},
        loadingDisplay: { type: Function, require: true },
    },
    data:()=>{
        return {
            valid : false,
            match : true,
            fullRulesJp: [
                // eslint-disable-next-line no-control-regex
                v => (/[\u0800-\u4e00]/g.test(v) || !v) || 'カタカナを入力してください'
            ],
            NumberRules:[
                v =>(!/[^\d{1,3}(,\d{3})*$]/g.test(v) || !v) || '整数のみ入力できます'
            ],
            title:false,
            colorO:false,
            colorON:false,
            date: null,
            menu: false,
            modal: false,
            radioTypes: [
                { label : 'あり', value: 0 },
                { label : 'なし', value: 1 }
            ],
            s12_receive1:'',
            s12_receive2:'',
            s12_receive3:'',
            value_m:'',
            //一致 不一致
            radioTypeSaveValueRecord:'',
            radioTypeSaveValue2:'',
            //はい：0, いいえ：1
            firstType:0,
            //secondType:'',
            ajaxPath:'/api/biz/b00006/s00011/',
            ajaxCommonMailPath:'/api/biz/common/mail/',
            disabledFlag:true
        }
    },
    computed:{
        //半角验证
        rules(){
            const rules = [];
            if (this.match) {
                const rule =
                        // eslint-disable-next-line no-control-regex
                        v => (!/[^\x00-\xff]/g.test(v) || !v) || '半角文字を入力してください';
                rules.push(rule)
            }
            return rules;
        },
    },
    watch:{
        radioTypeSaveValueRecord(value){
            if (value == 0){
                this.radioTypeSaveValue2 = 1;
                //this.disabledFlag = true;
            } else {
                this.radioTypeSaveValue2 = 0;
                //this.disabledFlag = false;
            }
            this.checkBtnDisabled();
        },
        radioTypeSaveValue2(value){
            if (value == 0){
                this.radioTypeSaveValueRecord = 1;
            } else {
                this.radioTypeSaveValueRecord = 0;
            }
        },
        colorO(){
            this.checkBtnDisabled();
        },
        colorON(){
            this.checkBtnDisabled();
        }
    },
    methods: {
        //小于4位补0
        suppleZero(event){
            let value = event.target.value;
            if (value.length == '3'){
                this.s12_receive1 = '0' + value;
            } else if (value.length == '2'){
                this.s12_receive1 = '00' + value;
            } else if (value.length == '1'){
                this.s12_receive1 = '000' + value;
            }
        },
        //千分符转换
        thousand_mark(eventObj) {
            let value = eventObj.target.value;
            // eslint-disable-next-line no-constant-condition
            if (value == '' || isNaN(Number(value)) || /[^\d]/.test(value)) {
                this.value_m = '';
            } else {
                let value_thousand = Number(value).toLocaleString();
                this.value_m = value_thousand;
            }
        },
        handleCheckboxAP(bool) {
            if (!bool) {
                if (this.firstType == 0){
                    this.title = false;
                } else {
                    this.title = true;
                }
                this.$refs.knowCostAP.$data.dialog = true;
            }
        },
        changePdf() {
            this.savePeData('save');
        },
        //保存pe数据
        async savePeData(string) {
            try {
                this.loadingDisplay(true);
                let savePeData = this.getPeData();
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('task_result_content',JSON.stringify(savePeData));
                let saveFlag = 'tmpSaveExpenseData';
                //判断保存或者一时保存
                if (string == 'save' || string == 'unkown'){
                    saveFlag = 'saveExpenseData';
                }
                let res = await axios.post('/api/biz/b00006/s00011/'+saveFlag,formData);
                if (res.data.result == 'success') {
                    if (string == 'save'){
                        //跳转到pdf上传页面
                        this.$emit('changePdf');
                        this.$emit('newPdf',this.firstType)
                        //将新文件名抛出
                        let apFileName = this.s12_receive1 + '_' + this.s12_receive2;
                        this.$emit('apFileName',apFileName);
                        //file_seq_no restart
                        let file25 = res.data.data.C00800_25_uploadFiles;
                        let file26 = res.data.data.C00800_26_uploadFiles;
                        this.$parent.$refs.pdfUp.$data.uploadFiles = file25;
                        this.$parent.$refs.pdfUp.$data.uploadFiles2 = file26;
                        if (file25 !== undefined && file25.length != 0){
                            //file
                            let fileString = '';
                            this.uploadFiles = [];
                            for (const value of file25){
                                this.uploadFiles.push({
                                    'file_name':value.file_name,
                                    'file_path':value.file_path,
                                    //暂时不设置
                                    'file_seq_no':value.file_seq_no,
                                })
                                fileString += ('<span>'+ value.file_name + '</span>' + ',');
                                this.$parent.$refs.pdfUp.$refs.file1.$data.clearFlag = true;
                            }
                            fileString = fileString.substring(0,fileString.length - 1);
                            this.$parent.$refs.pdfUp.$refs.file1.$data.dragDropFileName = fileString;
                        }
                        if (file26 !== undefined && file26.length != 0){
                            //file
                            let fileString2 = '';
                            this.uploadFiles2 = [];
                            for (const value of file26){
                                this.uploadFiles2.push({
                                    'file_name':value.file_name,
                                    'file_path':value.file_path,
                                    //暂时不设置
                                    'file_seq_no':value.file_seq_no,
                                })
                                fileString2 += ('<span>'+ value.file_name + '</span>' + ',');
                                this.$parent.$refs.pdfUp.$refs.file2.$data.clearFlag = true;
                            }
                            fileString2 = fileString2.substring(0,fileString2.length - 1);
                            this.$parent.$refs.pdfUp.$refs.file2.$data.dragDropFileName = fileString2;
                        }
                    } else {
                        if (string == 'unkown'){
                            window.location.href = '/tasks';
                        }
                        this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                    }
                } else {
                    this.$refs.alert.show(res.data.err_message)
                    return ;
                }
            } catch (e) {
                console.log(e)
                this.loadingDisplay(false);
            } finally {
                this.loadingDisplay(false);
            }
        },
        //默认数据回显
        saveInitValue(content){
            //console.log(content);
            this.firstType = content.C00500_2;
            if (content.C00500_2 == 1){
                this.colorO=false;
                this.colorON=true;
            } else if (content.C00500_2 == 0){
                this.colorO=true;
                this.colorON=false;
            } else {
                console.log('暂时不考虑');
            }
            this.s12_receive1 = content.C00100_3;
            this.s12_receive2 = content.C00100_4;
            this.s12_receive3 = content.C00100_5;
            this.value_m = this.formatNumber(content.C00101_6);
            this.date = content.C00100_7;
            this.radioTypeSaveValue2 = content.C00500_8;
            if (this.radioTypeSaveValue2 == 0){
                this.disabledFlag = false;
            }
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean1 = content.C00400_10[0];
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean2 = content.C00400_10[1];
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean3 = content.C00400_10[2];
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean4 = content.C00400_10[3];
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean5 = content.C00400_10[4];
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean6 = content.C00400_10[5];
            this.$refs.knowCostAP.$data.unKnowCheckboxBoolean7 = content.C00400_10[6];
            this.$refs.knowCostAP.$data.unKnowTextareaValue1 = content.C00200_11;
        },
        //获取提交的数据
        getPeData(){
            let G00000_1 ={};
            G00000_1.C00500_2 = this.firstType;
            G00000_1.C00100_3 = this.s12_receive1;
            G00000_1.C00100_4 = this.s12_receive2;
            G00000_1.C00100_5 = this.s12_receive3;
            G00000_1.C00101_6 = this.value_m === undefined?'':this.value_m.replace(/,/g,'');
            G00000_1.C00100_7 = this.date;
            G00000_1.C00500_8 = this.radioTypeSaveValue2;
            G00000_1.C00400_10 = [this.$refs.knowCostAP.$data.unKnowCheckboxBoolean1,this.$refs.knowCostAP.$data.unKnowCheckboxBoolean2,this.$refs.knowCostAP.$data.unKnowCheckboxBoolean3,this.$refs.knowCostAP.$data.unKnowCheckboxBoolean4,this.$refs.knowCostAP.$data.unKnowCheckboxBoolean5,this.$refs.knowCostAP.$data.unKnowCheckboxBoolean6,this.$refs.knowCostAP.$data.unKnowCheckboxBoolean7];
            G00000_1.C00200_11 = this.$refs.knowCostAP.$data.unKnowTextareaValue1;
            return G00000_1;
        },
        unkownEvent(){
            this.savePeData('unkown');
        },
        formatNumber(number){
            if (number == null){
                number = '';
                return number
            } else {
                if (String(number).indexOf('.') == '-1'){
                    return String(number).replace(/(?=(?!(\b))(\d{3})+$)/g,',')
                } else {
                    return String(number).split('.')[0].replace(/(?=(?!(\b))(\d{3})+$)/g,',')+ '.' + String(number).split('.')[1];
                }
            }
        },
        showPdfLink(flag){
            try {
                // let work_id = this.axiosFormData.get('request_work_id');
                // let task_id = this.axiosFormData.get('task_id');
                let type = '';
                if (flag == 'AP'){
                    const file = {
                        name: '添付ファイル記入・確認画面登録マニュアル.pdf',
                        file_path: 'manuals/B00006/添付ファイル記入・確認画面登録マニュアル.pdf',
                    }
                    this.$refs.filePreviewDialog.show([file], [], type);
                }
                if (flag == 'KJ'){
                    const file = {
                        name: '従業員情報登録・金額確認マニュアル.pdf',
                        file_path: 'manuals/B00006/従業員情報登録・金額確認マニュアル.pdf',
                    }
                    this.$refs.filePreviewDialog.show([file], [], type);
                }
                // if (flag == 'CH'){
                //     let itemA = document.createElement('a');
                //     itemA.href = this.ajaxCommonMailPath+work_id+'/'+task_id+'/downloadPdf?file_name=経費申請マニュアル04.pdf&file_path=b00006/pdf/4.pdf';
                //     itemA.click();
                // }
            } catch (e) {
                console.log(e);
            }
        },
        changeByPicker(){
            this.menu = false;
            this.$refs.menu.save(this.date)
        },
        checkBtnDisabled(){
            if ((!this.colorO && !this.colorON) || (this.radioTypeSaveValue2 == 1) || (this.radioTypeSaveValue2 === '')){
                this.disabledFlag = true;
            } else {
                this.disabledFlag = false;
            }
        }
    },
}
</script>

<style scoped lang="scss">
    .card_t{
        height: 100%;
        display: flex;
        .v-form{
            flex-grow: 1;
            .check_dia_bo{
                border: 1px solid rgba(0,0,0,0.12);
                position: relative;
                .ming{
                    background-color: white;
                    position: absolute;
                    left: 30px;
                    top: -15px;
                    color: #aaaaaa;
                    font-size: 18px
                }
            }
        }
    }
    a{
        font-size: 12px;
        color: #1976d2;
        text-decoration: underline;
    }
    .unbindColor{
       background-color:#c2c2c2 !important;
    }
    //////////////////////
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
    .ma-btn{
        margin: 0 40px 0 0;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
</style>