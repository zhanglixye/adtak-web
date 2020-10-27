<template>
    <div :class="[{'-drag': isDrag == 'new', '-undrag': isDrag != 'new'}]"
         @dragover.prevent="checkDrag($event, 'new', true)"
         @dragleave.prevent="checkDrag($event, 'new', false)"
         @drop.prevent="onDrop"
    >
        <i aria-hidden="true" class="fa fa-plus"></i>
        <div class="drop">
            <div :class="[diff?pt_3:pt_2,'pl-3','pr-3']" style="display: flex;">
                <div class="flex-ai-fs-0">
                    <label :for="emit_message + 'upload_check_files'" class="file-upload mb-0" style="font-weight: normal">
                        <span v-show="!disabledStatus">
                            <v-icon style="color: #1976d2">mdi-link-variant</v-icon>
                            {{$t('file_upload.pick_file')}}
                        </span>
                        <input type="file" class="drop__input" style="display:none;"
                               :id="emit_message + 'upload_check_files'"
                               name="upload_check_files"
                               app
                               @change="onDrop"

                               multiple
                        >
                    </label>
                </div>
                <div class="flex-center-ai">
                    <div @click="test" class="text-center file-name mr-2 ml-2" v-html="dragDropFileName"></div>
                    <span v-show="!disabledStatus">
                        <i v-show="clearFlag" @click="clearFile" aria-hidden="true" class="v-icon material-icons theme--light" style="cursor: pointer">cancel</i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import fileUploadMixin from '.././../../../mixins/fileUploadMixin'
export default {
    mixins: [fileUploadMixin],
    props: {
        apFileName:String,
        disabledStatus:{
            type: Boolean,
            default:false
        },
        //S00011 business diff
        diff:{type:Boolean,require:false,default:false},
    },
    data() {
        return {
            //文件内容动态变化
            dragDropFileName:'',
            //记录选择条数
            selectedNumber:0,
            clearFlag:false,
            pt_2:'pt-2 pb-2',
            pt_3:'pt-5 pb-5',
        }
    },
    methods: {
        //inputタグとドラッグ&ドロップから呼ばれる
        onDrop (event) {
            //初始化selectedNumber为0
            this.selectedNumber = 0;
            this.isDrag = null;
            const files = event.target.files ? event.target.files : event.dataTransfer.files;

            // アップロードファイル数のチェック
            if (this.maxFileCnt && files.length > this.maxFileCnt) {
                eventHub.$emit('open-notify-modal', { message: this.$t('file_upload.message.over_max_file_cnt') + this.maxFileCnt });
                return false
            }

            // 特定ファイルのチェック
            const specificFileTypeCheck = file => {

                // ブラックリストフィルタリング
                if (this.forbid_file_types != undefined) {
                    for (const type of this.forbid_file_types) {
                        if (file.type == type) return false
                    }
                    return true
                }

                // ホワイトリストフィルタリング
                if (this.allow_file_types != undefined) {

                    // 許可されたファイル
                    for (const type of this.allow_file_types) {
                        if (file.type == type) return true
                    }
                    return false
                }

                // 指定が無し
                return true
            }
            const specificFiles = []
            let isOneOrMoreForbidFiles = false
            for (const file of files) {
                const isAllow = specificFileTypeCheck(file)
                if (isAllow) {
                    specificFiles.push(file)
                } else {
                    isOneOrMoreForbidFiles = true
                }
            }

            if (isOneOrMoreForbidFiles) {
                eventHub.$emit('open-notify-modal', {message: this.$t('file_upload.message.error')});
            }

            if (specificFiles.length === 0) return

            const file_list = [];
            //初始化清空
            this.dragDropFileName = '';

            for (const file of specificFiles){
                const file_attribute = {
                    id: this.totalId,
                    name: file.name,
                    size: file.size,
                    data: URL.createObjectURL(file),
                    lastModified: file.lastModified,
                    type: file.type,
                };
                file_list.push(file_attribute);
                this.totalId += 1;
                // kaiwait zlx added
                let file_attr_name;
                if (this.apFileName == undefined){
                    file_attr_name = file_attribute['name'];
                } else {
                    let name = file_attribute['name'];
                    if (this.selectedNumber == 0){
                        file_attr_name = this.apFileName + '.'+ this.getFileType(name);
                    } else {
                        let numberFileLine = '_' + (this.selectedNumber<10?'0'+this.selectedNumber:this.selectedNumber);
                        //console.log(numberFileLine);
                        file_attr_name = this.apFileName + numberFileLine + '.' + this.getFileType(name);
                    }
                    file_attribute['name'] = file_attr_name;
                }
                this.selectedNumber += 1;
                if (this.selectedNumber == specificFiles.length) {
                    this.dragDropFileName += ('<span>'+file_attr_name+'</span>' + '');
                } else {
                    this.dragDropFileName += ('<span>'+file_attr_name+'</span>' + ',');
                }
                this.clearFlag = true;
            }
            //console.log('文件选择数据');
            //console.log(file_list);
            eventHub.$emit(this.emit_message, {'file_list': file_list})
        },
        //点击下载文件测试用
        test(event){
            //console.log(event.target.innerText);
            this.$emit('clickDownload',event.target.innerText);
        },
        clearFile(){
            if (this.disabledStatus){
                return;
            }
            this.dragDropFileName = '';
            this.clearFlag = false;
            //解决不能选择同一个文件 触发onchange问题 test
            $('.drop__input').val('');
            this.$emit('clearFile');
        },
        //获取文件类型
        getFileType(filePath){
            let startIndex = filePath.lastIndexOf('.');
            if (startIndex != -1)
                return filePath.substring(startIndex+1, filePath.length).toLowerCase();
            else return '';
        }
    }
};
</script>

<style scoped lang="scss">
    .-undrag{
        border: dashed;
        border-color: gray;
    }

    .-drag{
        border: dashed;
        border-color: red;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    .flex-center-ai{
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .flex-ai-fs-0{
        display: flex;
        align-items: center;
        flex-shrink: 0
    }
    label.file-upload{
        cursor: pointer;
        color: #1976d2;
    }
</style>