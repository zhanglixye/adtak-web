<template>
    <div>
        <p class="project-title">結果キャプチャー：</p>
        <div class="hello">
            <div class="upload">
                <!--上传部分-->
                <div class="upload_warp" v-if="!accomplish">
                    <div class="upload_warp_left" @click="fileClick">
                        <img src="/images/biz/b00007/upload.png" alt="">
                    </div>
                    <div class="upload_warp_right" @drop="drop($event)" @dragenter="dragenter($event)" @dragover="dragover($event)">
                        またはここにファイルをドロップ
                    </div>
                </div>
                <!--统计部份-->
                <div class="upload_warp_text">
                    <span v-show="imgList.length != 0">{{imgList.length}}</span>
                    <span v-show="thumbnail != ''">1</span>
                    <span v-show="thumbnail == '' && imgList.length == 0">0</span>
                    &nbsp;件選択済，{{bytesToSize(this.size)}}
                </div>
                <!--展示部分-->
                <input @change="fileChange($event)" type="file" id="upload_file" accept=".mp4,.jpg,.jpeg,.png,.gif" style="display: none"/>
                <div class="upload_warp_img" v-show="imgList.length!=0">
                    <div class="upload_warp_img_div" v-for="(item,index) of imgList" :key="index">
                        <div class="upload_warp_img_div_top">
                            <div class="upload_warp_img_div_text">
                                {{item.file.name}}
                            </div>
                            <img src="/images/biz/b00007/del.png" class="upload_warp_img_div_del" @click="fileDel(index)" alt="">
                        </div>
                        <img :src="item.file.thumbnail" alt="">
                    </div>
                </div>
                <div class="upload_warp_img" v-show="thumbnail!=''&&imgList.length==0">
                    <div class="upload_warp_img_div">
                        <img v-show="!accomplish" src="/images/biz/b00007/del.png" class="thumbnail_del" @click="thumbnail_del" alt="">
                        <img :src="thumbnail" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AbbeyInfoUploading',
    props:{
        accomplish:Boolean,
        loadingDisplay: { type: Function, require: true },
        initData: { type: Object, require: true },
        formData:{ type: FormData, require: true }
    },
    data () {
        return {
            imgList: [],
            size: 0,
            thumbnail:'',
            restrict_file_type:['mp4','jpeg','png','gif'],
            upload_state:false
        }
    },
    methods:{
        fileClick() {
            if (this.imgList.length >= 1 || this.thumbnail != ''){
                this.$emit('informPopup','一個しかアップロードできない。')
            } else {
                document.getElementById('upload_file').click()
            }
        },
        fileChange(el) {
            if (!el.target.files[0].size) return;
            this.fileList(el.target);
            el.target.value = ''
        },
        fileList(fileList) {
            let files = fileList.files;
            if (this.imgList.length >= 1 || this.thumbnail != ''){
                this.$emit('informPopup','一個しかアップロードできない。')
            } else {
                if (files.length > 1){
                    this.$emit('informPopup','一個しかアップロードできない。')
                } else {
                    for (let i = 0; i < files.length; i++) {
                        //判断是否为文件夹
                        if (files[i].type !== '') {
                            this.fileAdd(files[i]);
                        } else {
                            //文件夹处理
                            this.folders(fileList.items[i]);
                        }
                    }
                }
            }
        },
        //文件夹处理
        folders(files) {
            let _this = this;
            //判断是否为原生file
            if (files.kind) {
                files = files.webkitGetAsEntry();
            }
            files.createReader().readEntries(function (file) {
                if (file.length > 1){
                    _this.$emit('informPopup','一個しかアップロードできない。')
                } else {
                    for (let i = 0; i < file.length; i++) {
                        if (file[i].isFile) {
                            _this.foldersAdd(file[i]);
                        } else {
                            _this.folders(file[i]);
                        }
                    }
                }
            })
        },
        foldersAdd(entry) {
            let _this = this;
            entry.file(function (file) {
                _this.fileAdd(file)
            })
        },
        fileAdd(file) {
            this.restrictFilesType(file);
            if (this.upload_state === true){
                //总大小
                this.size = this.size + file.size;
                //判断是否为图片文件
                var _this = this;
                if (file.type.indexOf('image') === -1) {
                    let reader = new FileReader();
                    reader.vue = this;
                    reader.readAsDataURL(file);
                    reader.onload = function () {
                        file.src = this.result;
                        file.thumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAMAAACahl6sAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAABUUExURQAAAIqKioqKioqKiouLi4qKio+Pj4uLi4qKiomJiYmJiYqKioeHh4mJiYmJiYmJiYqKiomJiYmJiYmJiYqKiomJiYqKioqKioqKioqKiouLi4qKiq75E2wAAAAbdFJOUwCfYM9/vxBAwICgMCDwcNDvUN+vsF+QP+BvT3TrUR4AAALFSURBVHja7d3dcpswEIZh2a4l8Wsc201S3f99ttOTYkhCRmhXgr7fMQM8I0ALaCRjCCGEEEIIIf/SvJ9PQSgHPcbjJUhGS+JkGWqS9yAfDckhhF1IdBzykmMIu5C0IexDUod9SFwI+5D8nB/MJ0qnKpn2hPU12a69Zps0z0e5uIT79ppX1/Oz926NPERGUj21R9p9e807fgzprBJEQjKG9EYMUotLRpDOyEGMuGQEqSUh4pIR5CgKkZaMIE4WIixRhMhKNCGiElXI7B3usFWIoEQZIifRhohJ1CFSEn2IkCQDZCa52Y1CZpKT3ShEQpIHIiDJBEkvyQVJLskGSS3JB0ksWYK0PxbSRkPSSpYgi9+43bcgVlyiBHFGWqIE+eyTWTqJEuRkpCVKkM83SyXRgrxYYYkkpP7eW0caiSSkCooSScjkBE+i94kkxM5+i/yyYpIliHUL+eqQ97l7OH+YYbJZVVTRuGJwSGGQL36+bQzi9gKZPIE3DIkdslMexN53Avlw1M42Ica97QTypz+57ARiTNt7/5YVsqbWWtHrAAECBAgQIEBEIM3SuPFmIxDNAAECBAgQIECkIYtDOOLSllc0xsUBAQIECBAgQHJAFodwxMVSNAIBAgQIECBAqLWAAAECBAgQIH/TeJE01FpAgAABAgQIkDUlylkk+iUK1S8QIECAAAGyJwhFIxAgQIAAAQIECBAgQID8h5A+N6RKBPG5IadEkNDmdYzX/FwHqfNC6mSQcM3puIZ0kKHJ52iGdZDnD3BDtja5Divnv5nOyXvIcse308WWI2Yuns08eHu8ql5hzevjNj2HmJ6gDwUmpm+2XXmOuDU/C2ySyJUZL6U5YheTbQq7uLroB6crC7LimekKapNu1WtRW8x9clnbIR+LaJQuwUqytve5Gb5PtGa0dVVV+yypq8pZQwghhBBCCBnlN01YLvZFk4ENAAAAAElFTkSuQmCC';
                        this.vue.imgList.push({
                            file
                        });
                        _this.uploadFiles(file)
                    }
                } else {
                    let reader = new FileReader();
                    reader.vue = this;
                    reader.readAsDataURL(file);
                    reader.onload = function () {
                        file.src = this.result;
                        file.thumbnail = this.result;
                        this.vue.imgList.push({
                            file
                        });
                        _this.uploadFiles(file)
                    }
                }
            } else {
                this.$emit('informPopup','拡張子が「mp4,jpeg,png,gif」のファイルのみアップできます。')
            }
        },
        fileDel(index) {
            this.size = this.size - this.imgList[index].file.size;//总大小
            this.imgList.splice(index, 1);
            this.$parent.$parent.projectInfo.result_capture = {
                display_size:'',
                file_name:'',
                file_path:'',
                file_size:'',
                seq_no:null
            }
        },
        bytesToSize(bytes) {
            if (bytes === 0) return '0 B';
            let k = 1000, // or 1024
                sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        },
        dragenter(el) {
            el.stopPropagation();
            el.preventDefault();
        },
        dragover(el) {
            el.stopPropagation();
            el.preventDefault();
        },
        drop(el) {
            el.stopPropagation();
            el.preventDefault();
            this.fileList(el.dataTransfer);
        },
        //文件上传接口
        async uploadFiles(file){
            try {
                this.loadingDisplay(true);
                var _this = this;
                let formData = new FormData();
                formData.append('step_id', this.initData['request_info']['step_id']);
                formData.append('request_id', this.initData['request_info']['request_id']);
                formData.append('request_work_id', this.initData['request_info']['id']);
                formData.append('task_id', this.initData['task_info']['id']);
                formData.append('task_started_at', this.initData['task_started_at']);
                formData.append('task_result_content',JSON.stringify(this.$parent.$parent.$parent.getAllComponentData()));
                formData.append('file_name',file.name);
                formData.append('file_data',file.src);
                await  axios.post('/api/biz/b00007/s00013/uploadFile',formData).then((res)=>{
                    // this.calculateFileWH(file,function (data) {
                    //     res.data.data.display_size = data;
                    //     _this.$emit('receiveResultFile',res.data)
                    // });
                    if (res.data.result == 'success'){
                        _this.$emit('receiveResultFile',res.data)
                    } else {
                        _this.$parent.$parent.$parent.$refs.abbeyList.errInformPopup(JSON.stringify(res.data))
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //限制文件上传类型
        restrictFilesType(file){
            this.upload_state = null;
            var type = file.type.split('/')[1];
            this.restrict_file_type.indexOf(type) === -1 ? this.upload_state = false : this.upload_state = true;
        },
        /*//计算文件的宽高
        calculateFileWH(file,callback){
            var type = file.type.split('/')[1];
            if (this.restrict_file_type.indexOf(type) !== -1){
                if (type === 'mp4'){
                    var video_url = file.src;
                    var video = document.createElement('video');
                    video.src = video_url;
                    video.oncanplay = function () {
                        var w_h = video.videoWidth + '*' + video.videoHeight;
                        callback && callback(w_h);
                    }
                } else {
                    var img_url = file.src;
                    var img = new Image();
                    img.onload = function(){
                        var w_h = img.naturalWidth + '*' + img.naturalHeight;
                        callback && callback(w_h);
                    };
                    img.src = img_url;
                }
            }
        },*/
        //设置缩略图默认显示
        async thumbnailDefaultShow(src){
            try {
                var _this = this;
                await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path='+src).then((res)=>{
                    _this.thumbnail = res.data.url;
                });
            } catch (err) {
                console.log(err);
            }
        },
        //删除缩略图
        thumbnail_del(){
            this.thumbnail = '';
            this.size = 0;
            this.$emit('receiveResultFile',{data:{
                display_size:'',
                file_name:'',
                file_path:'',
                file_size:'',
                seq_no:null
            }})
        }
    }
}
</script>

<style scoped>
    /*清除默认样式*/
    p{margin: 0 !important;}
    .project-title{
        font-weight: bold;
        color: #555555;
        line-height: 50px;
    }
    .upload_warp_img_div_del {
        position: absolute;
        top: 6px;
        width: 16px;
        right: 4px;
    }
    .thumbnail_del{
        display: block;
        width: 24px;
        padding: 5px;
        border-radius: 50%;
        position: absolute;
        background: #acacac;
        top: -10px;
        right: -10px;
    }
    .upload_warp_img_div_top {
        position: absolute;
        top: 0;
        width: 100%;
        height: 30px;
        background-color: rgba(0, 0, 0, 0.4);
        line-height: 30px;
        text-align: left;
        color: #fff;
        font-size: 12px;
        text-indent: 4px;
    }
    .upload_warp_img_div_text {
        white-space: nowrap;
        width: 80%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .upload_warp_img_div img {
        max-width: 100%;
        max-height: 100%;
        vertical-align: middle;
    }
    .upload_warp_img_div {
        position: relative;
        height: 100px;
        width: 120px;
        border: 1px solid #ccc;
        margin: 0 30px 10px 0;
        float: left;
        line-height: 100px;
        display: table-cell;
        text-align: center;
        background-color: #eee;
        cursor: pointer;
    }
    .upload_warp_img {
        border-top: 1px solid #D2D2D2;
        padding: 14px 0 0 14px;
        overflow: hidden
    }
    .upload_warp_text {
        text-align: left;
        margin-bottom: 10px;
        padding-top: 10px;
        text-indent: 14px;
        border-top: 1px solid #ccc;
        font-size: 14px;
    }
    .upload_warp_right {
        float: left;
        width: 57%;
        margin-left: 2%;
        height: 100%;
        border: 1px dashed #999;
        border-radius: 4px;
        line-height: 130px;
        color: #999;
    }
    .upload_warp_left img {
        margin-top: 32px;
    }
    .upload_warp_left {
        float: left;
        width: 40%;
        height: 100%;
        border: 1px dashed #999;
        border-radius: 4px;
        cursor: pointer;
    }
    .upload_warp {
        margin: 14px;
        height: 130px;
    }
    .upload {
        border: 1px solid #ccc;
        background-color: #fff;
        width: 100%;
        box-shadow: 0 1px 0 #ccc;
        border-radius: 4px;
    }
    .hello {
        width: 100%;
        text-align: center;
    }
</style>
