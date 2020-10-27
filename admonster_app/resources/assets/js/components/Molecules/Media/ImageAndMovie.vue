<template>
    <div class="image-and-movie text-xs-center">
        <div v-if="RegExp('image/*').test(mimeType)">
            <v-img
                :src="dataUri"
                :height="height"
                contain
                @click="previewImage()"
            ></v-img>
        </div>
        <div v-else-if="RegExp('video/mp4').test(mimeType)">
            <!-- controlsの表示をしていればプレビューもできるのでクリックイベントは登録しない -->
            <video
                controls
                preload="none"
                :src="dataUri"
                :height="height"
            ></video>
        </div>
        <v-progress-circular v-else indeterminate color="primary"></v-progress-circular>
        <div :class="[dark ? 'text-color-white' : '', 'image-name']">{{ imageName }}</div>
    </div>
</template>

<script>
export default {
    props: {
        filePath: { type: String, requeire: false, default: '' },
        file: { type: Object, requeire: false, default: ()=> {return {name: '', data: '', mimeType: ''}} },
        height: { type: Number, requeire: false, default: 100 },
        dark: { type: Boolean, required: false, default: false},
    },
    data: () => ({
        mimeType: null,
        dataUri: null,
        imageName: null,
    }),
    methods: {
        previewImage () {
            // 画像を別ウインドウ表示
            // var canvas = document.createElement('canvas');
            var w = window.open('about:blank');
            w.document.write(`<img src='${this.dataUri}' />`);
        },
        async getFileData () {
            // s3を直接参照はできないのでbase64化して埋め込むことで表示する
            let res = await axios.post('/api/utilities/fileInfoFromS3', {
                file_path: this.filePath,
                file_name: this.imageName
            })
            this.mimeType = res.data.mime_type
            this.dataUri = res.data.data
        },
    },
    created () {
        if (this.filePath != '') {
            // 外部から取得
            this.imageName = this.filePath.split('/').pop()
            this.getFileData()
        } else {
            this.imageName = this.file.name
            this.dataUri = this.file.data
            this.mimeType = this.file.mimeType
        }
    }
}
</script>

<style scoped>
.image-name {
    word-break: break-all;
}

.text-color-white {
    color: white !important;
}

.image-and-movie {
    font-size: 12px;
}
</style>
