export default {
    props: {
        emit_message: String,
        // アップロードするファイルのホワイトリスト
        allow_file_types: Array,
        // アップロードするファイルのブラックリスト
        forbid_file_types: Array,
        // アップロードファイル数の上限
        maxFileCnt: Number,
        disabled: { type: Boolean, required: false, default: false }
    },
    data() {
        return {
            isDrag: null,
            file:undefined,
            totalId: 0
        }
    },
    methods: {
        checkDrag(event, key, status){
            if (status && event.dataTransfer.types == 'text/plain') {
                //ファイルではなく、html要素をドラッグしてきた時は処理を中止
                return false
            }
            this.isDrag = status ? key : null
        },
        //inputタグとドラッグ&ドロップから呼ばれる
        onDrop (event) {
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
            for (const file of specificFiles){
                const file_attribute = {
                    id: this.totalId,
                    name: file.name,
                    size: file.size,
                    data: URL.createObjectURL(file),
                    lastModified: file.lastModified,
                    type: file.type,
                }
                file_list.push(file_attribute)
                this.totalId += 1
            }
            eventHub.$emit(this.emit_message, {'file_list': file_list})
        },
    }
}
