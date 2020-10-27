<template>
    <div class="material-box">
        <div class="material">
            <div>
                <img :src="materialMsg.url" alt="">
            </div>
        </div>
        <div></div>
        <div class="info-box">
            <ul>
                <li :title="'ファイル名：'+materialMsg.accessoryName">{{materialMsg.accessoryName}}</li>
                <li :title="'ピクセル(WxH)：'+materialMsg.widthHight">{{materialMsg.widthHight}}</li>
                <li :title="'ファイルサイズ：'+materialMsg.size+' Byte'">{{materialMsg.size}}&nbsp;Byte</li>
                <li :title="'チェックファイル名：'+materialMsg.checkFileName">{{materialMsg.checkFileName}}</li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AbbeyInfoMaterial',
    props:{
        materialInfo:Object
    },
    data(){
        return {
            materialMsg:{
                url:'',
                accessoryName:'',
                widthHight:'',
                size:'',
                checkFileName:''
            }
        }
    },
    watch:{
        materialInfo:function () {
            this.getMaterialInfo();
        }
    },
    methods:{
        async getMaterialInfo() {
            try {
                var _this = this;
                await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path='+this.materialInfo.material.file_path).then((res)=>{
                    _this.materialMsg.url = res.data.url;
                    _this.materialMsg.accessoryName = this.materialInfo.material.file_name;
                    _this.materialMsg.widthHight = this.materialInfo.material.display_size;
                    _this.materialMsg.size = this.materialInfo.material.file_size;
                    _this.materialMsg.checkFileName = this.materialInfo.material.check_file_name;
                })
            } catch (err) {
                console.log(err);
            }
        }
    }
}
</script>

<style scoped>
    /*清除默认样式*/
    li{list-style: none}
    ul{padding: 0 !important;}
    img{display: block}
    .material-box{
        height: 200px;
        padding: 15px 0;
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    .material-box div{
        float: left;
        width: 49.9%;
    }
    .material-box div:nth-last-child(2){
        width: 1px;
    }
    .material-box div:nth-last-child(2):after{
        height: 121px;
        display: block;
        content: "";
        float: right;
        margin-top: 24px;
        border-right: dotted 1px #aaaaaa;
    }
    .material-box .material{
        height: 169px;
        padding: 0 5%;
    }
    .material-box .material div{
        height: 100%;
        width: 100%;
        overflow: auto;
    }
    .material-box .material div img{
        height: 100%;
        margin: 0 auto;
    }
    .material-box .info-box ul{
        width: 78%;
        margin: 0 auto;
        background-color: rgba(250, 250, 250, 1);
        border: solid 1px rgba(215, 215, 215, 1);
    }
    .material-box .info-box ul li{
        font-size: 14px;
        color: #555555;
        height: 42px;
        line-height: 42px;
        text-align: center;
        border-bottom: solid 1px rgba(215, 215, 215, 1);
        white-space: nowrap;
        overflow: hidden;
    }
    .material-box .info-box ul li:nth-last-child(1){
        border-bottom: none;
    }
</style>
