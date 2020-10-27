<template>
	<!-- 画像欄 -->
	<v-layout mb-4 class="list-item">
		<v-flex xs2 mr-2>
			<v-layout justify-space-between column fill-height>
				<v-progress-circular v-if="result.file_data == undefined || result.file_data == ''" indeterminate color="primary"></v-progress-circular>
				<v-img class="preview" v-else-if="RegExp('image/*').test(result.type)" ref="image" v-on:load="imgTagLoad" :src="result.file_data" max-height="200px" contain></v-img>
				<div v-else-if="RegExp('video/mp4').test(result.type)" class="text-xs-center">
					<video class="preview" style="max-width:80%;max-heigth:200px;" ref="video" v-on:loadedmetadata="videoMetaDataLoad" controls>
						<source :src="result.file_data" :type="result.type">
					</video>
				</div>
				<div>
					<v-btn class="is-success-btn" :color="result.is_success === true ? 'success':'gray' " @click="result.is_success = !isDoneTask ? true : result.is_success" >{{ $t('common.button.ok') }}</v-btn>
					<v-btn class="is-success-btn" :color="result.is_success === false ? 'error':'gray' " @click="result.is_success = !isDoneTask ? false : result.is_success" >{{ $t('common.button.ng') }}</v-btn>
				</div>
			</v-layout>
		</v-flex>
	<!-- 画像欄 -->
	<!-- 情報欄 -->
		<v-flex xs9>
			<v-layout>
				<v-flex :class="column_value">
					<v-text-field :label="$t('biz.abbey_check.abbey_check.item_label.file_name')" class="column-vlaue-input" disabled hide-details v-model="result.file_name" ></v-text-field>
				</v-flex>
				<v-tooltip top>
						<v-btn xs1 flat icon v-if="!isDoneTask"  @click="file_delete" color="red lighten-2" slot="activator">
							<v-icon>clear</v-icon>
						</v-btn>
					<span>{{ $t('common.button.delete') }}</span>
				</v-tooltip>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value">
					<v-text-field :label="$t('biz.abbey_check.abbey_check.item_label.check_file_name')" :placeholder="$t('biz.abbey_check.common.loading')" disabled hide-details v-model="result.check_file_name" ></v-text-field>
				</v-flex>
				<v-tooltip top>
						<v-btn xs1 flat icon v-if="!isDoneTask" :disabled="result.check_file_name == undefined || result.check_file_name == ''" @click="download" color="primary" slot="activator">
							<v-icon>cloud_download</v-icon>
						</v-btn>
					<span>{{ $t('biz.abbey_check.abbey_check.tooltip_message.check_file_download') }}</span>
				</v-tooltip>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value">
					<v-text-field :label="$t('biz.abbey_check.abbey_check.item_label.pixel_number')" :placeholder="$t('biz.abbey_check.common.loading')"  disabled hide-details v-model="result.aspect_ratio" ></v-text-field>
				</v-flex>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value">
					<v-text-field :label="$t('biz.abbey_check.abbey_check.item_label.file_size')" :placeholder="$t('biz.abbey_check.common.loading')" disabled hide-details v-model="getFileSize" ></v-text-field>
				</v-flex>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value">
					<v-textarea rows="1" :label="$t('biz.abbey_check.abbey_check.item_label.menu')" style="font-size:12px;" auto-grow :disabled="isDoneTask" hide-details v-model="result.menu_name"></v-textarea>
				</v-flex>
				<v-tooltip top>
						<v-btn xs1 flat icon v-if="!isDoneTask" @click="openGuide" color="primary" slot="activator">
							<v-icon>search</v-icon>
						</v-btn>
					<span>{{ $t('biz.abbey_check.abbey_check.tooltip_message.abbey_search') }}</span>
				</v-tooltip>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value">
					<v-text-field :label="$t('biz.abbey_check.abbey_check.item_label.abbey_id')" :disabled="isDoneTask" hide-details v-model="result.abbey_id" ></v-text-field>
				</v-flex>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value" class="abbey-text-area">
					<v-textarea :label="$t('biz.abbey_check.abbey_check.item_label.err_description')" style="font-size:12px;" auto-grow name="checkResult" hide-details :disabled="isDoneTask" v-model="result.err_description"></v-textarea>
				</v-flex>
			</v-layout>
			<v-layout>
				<v-flex :class="column_value" class="abbey-text-area">
					<v-textarea :label="$t('biz.abbey_check.abbey_check.item_label.err_detail')" style="font-size:12px;" auto-grow :disabled="isDoneTask" hide-details v-model="result.err_detail"></v-textarea>
				</v-flex>
			</v-layout>
		</v-flex>
	<!-- /情報欄 -->
	</v-layout>
</template>

<script>

export default {
    name: 'list-item',
    props: {
        result: Object,
        /*
			task_result_file_seq_no: Number,
			menu_name: String,
			file_name: String,
			check_file_name: String,
			aspect_ratio: String,
			file_path: String,
			file_size: Number,
			abbey_id: String,
			err_description: String,
			file_data: String,
			type: String,
			err_detail: String,
			is_success: Boolean
		*/
        isDoneTask: Boolean,
    },
    data() {
        return {
            column_value: 'xs11 mt-2',
            search_modal: false,
        }
    },
    computed: {
        getFileSize: function() {
            return this.result.file_size + 'Byte'
        }
    },
    created() {
        this.getFileData()
    },
    updated() {
        this.getFileData()
    },
    methods: {
        getFileData:async function () {
            // チェック名の取得
            if ( (this.result.check_file_name == undefined || this.result.check_file_name == '')
			&& (this.result.file_size != undefined && this.result.file_size != 0) ){
                const res = await axios.post('/api/biz/abbey_check/abbey_check/convert',  {'list': [{'name': this.result.file_name, 'size':this.result.file_size}]})
                this.result.check_file_name = res.data.file_list[0].check_file_name
            }

            // 画像データに関する情報の取得
            if (this.result.file_data == undefined || this.result.file_data == ''){
                const res = await axios.post('/api/biz/abbey_check/abbey_check/download_file', {file_path: this.result.file_path, file_name: this.result.file_name})
                this.result.file_data = res.data.data
                this.result.type = res.data.mime_type
                this.result.file_size = res.data.file_size.toLocaleString()
            }
        },
        getDownloadUrl: function(){
            let uri = '/utilities/download_file?file_path='
            uri =  uri + encodeURIComponent(this.result.file_path) + '&file_name=' + encodeURIComponent(this.result.file_name)
            return uri
        },
        imgTagLoad: function() {
            // 画像の読み込み後
            const img = this.$refs.image.image
            this.result.aspect_ratio = img.naturalWidth  + 'x' + img.naturalHeight
        },
        videoMetaDataLoad: function(){
            // metadata読み込み後
            const video = this.$refs.video
            this.result.aspect_ratio = video.videoWidth  + 'x' + video.videoHeight
        },
        openGuide: function() {
            if (this.isDoneTask) return
            const self = this
            eventHub.$emit('open-search-modal',{
                'menu_name': self.result.menu_name,
                'abbey_id': self.result.abbey_id,
                'task_result_file_seq_no': self.result.task_result_file_seq_no,
                'file_name': self.result.file_name,
                'file_size': self.result.file_size,
                'aspect_ratio': self.result.aspect_ratio
            })
        },
        file_delete: function() {
            if (this.result.task_result_file_seq_no < 0){
                // メモリから削除
                URL.revokeObjectURL(this.result.file_data)
            }
            eventHub.$emit('delete',{'task_result_file_seq_no':this.result.task_result_file_seq_no})
        },
        download: function() {

            if (this.isDoneTask) return

            if (this.result.task_result_file_seq_no < 0) {
                // 未アップロードファイル
                const a = document.createElement('a')
                a.download = this.result.check_file_name
                a.target   = '_blank'

                a.href = this.result.file_data
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)

            } else {
                // 既存のダウンロード機能でデータを取り、名前を変更
                const a = document.createElement('a')
                a.download = this.result.check_file_name
                a.target   = '_blank'
                let uri = '/utilities/download_file?file_path='
                uri =  uri + encodeURIComponent(this.result.file_path) + '&file_name=' + encodeURIComponent(this.result.check_file_name)
                a.href = uri
                document.body.appendChild(a)
                a.click()
                document.body.removeChild(a)

            }
        },
    }
}
</script>

<style scoped>
button.is-success-btn {
	width:100%;
	padding: 0px;
	margin: 0 auto 0 auto;
	min-width: 0;
}
.preview.v-responsive {
	flex: 0 auto;
}
</style>

