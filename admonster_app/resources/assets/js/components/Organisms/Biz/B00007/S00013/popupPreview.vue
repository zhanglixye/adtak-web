<template>
    <v-card>
        <v-dialog ref="popupPreview" v-model="dialog" style="width: auto;" scrollable>
            <img v-if="fileFormatCode===1" :src="fileSrc" alt="">
            <video v-if="fileFormatCode===2" :src="fileSrc" controls="controls"></video>
            <div class="prompt-message" v-show="fileFormatCode===0">画像と動画は参照のみできます</div>
        </v-dialog>
    </v-card>
</template>

<script>
export default {
    name: 'popupPreview',
    props:{
        loadingDisplay: { type: Function, require: true }
    },
    data () {
        return {
            dialog: false,
            fileSrc:null,
            fileFormatCode:null
        }
    },
    watch:{
        dialog:function () {
            if (this.dialog == false){
                this.loadingDisplay(false);
            } else {
                this.$nextTick(function(){
                    this.$refs.popupPreview.$refs.dialog.classList.add('preview-popup');
                });
            }
        }
    },
    methods:{
        //计算文件格式：0=其他文件，1=image，2=video
        countFileFormatCode:function (msg) {
            if (msg.indexOf('image') !== -1){
                this.fileFormatCode = 1
            } else if (msg.indexOf('video') !== -1){
                this.fileFormatCode = 2
            } else {
                this.fileFormatCode = 0
            }
        }
    }
}
</script>

<style scoped>
    @import "../../../../../../sass/biz/b00007.scss";
    img{
        display: block;
        margin: 0 auto;
    }
    video{
        display: block;
        max-width: 100%;
        margin: 0 auto;
    }
    .prompt-message{
        width: 600px;
        height: 100px;
        line-height: 100px;
        font-size: 36px;
        color: #fff;
        text-align: center;
        background-color: #1a1a1a;
    }
</style>
