<template>
    <v-dialog v-model="dialog" persistent width="800">
        <v-card>
            <v-card-title>
                <span class="headline">{{ $t('order.order_details.show.information_component_management.additional_info.title') }}</span>
            </v-card-title>
            <v-card-text v-if="additionalInfos.length > 0">
                <v-list two-line>
                    <div id="add-info-list-wrap">
                        <div v-for="(info, index) in additionalInfos" :key="info.id" class="add-info-list pa-1">
                            <div
                                class="add-info-panel-header text-left"
                                @click="togglePanel(info.id)"
                            >
                                <v-list-tile-avatar @click.stop>
                                    <v-checkbox
                                        :input-value="info.selected"
                                        class="pa-0 ma-0"
                                        color="primary"
                                        hide-details
                                        @click.native="selected(info.info_id)"
                                    >
                                    </v-checkbox>
                                </v-list-tile-avatar>
                                <!-- ユーザーアイコン -->
                                <v-avatar size="32px" class="ma-1">
                                    <img v-if="isHuman(info.updated_user_id)" :src="user_image_path(info.updated_user_id)">
                                    <v-icon v-else slot="activator">android</v-icon>
                                </v-avatar><!-- / ユーザーアイコン -->
                                <div class="header-summary ml-2">
                                    <span class="caption font-weight-bold">{{ user_name(info.updated_user_id) }}</span>
                                    <span v-if="isUpdate(info)" class="creation-info ml-1">
                                            ({{ $t('requests.detail.created_user') }} : {{ user_name(info.created_user_id) }}<span class="mx-1">|</span>{{ $t('requests.detail.created_at') }} : {{ info.created_at | formatDateYmdHm(false, true) }})
                                        </span>
                                    <div>
                                        <span class="caption">{{ info.updated_at | formatDateYmdHm(false, true) }}</span>
                                        <span v-show="!isOpenToggle(info.id)" class="ml-3 body-1" v-html="info.content"></span>
                                    </div>
                                </div>
                                <v-spacer></v-spacer>
                                <!-- 添付ファイルダウンロード用リスト -->
                                <v-menu
                                    origin="center center"
                                    transition="scale-transition"
                                    bottom open-on-hover
                                >
                                    <v-btn
                                        slot="activator"
                                        v-show="!isOpenToggle(info.id) && attachments(info.order_additional_attachments).length > 0"
                                        icon
                                        @click.prevent=""
                                    >
                                        <v-icon>attachment</v-icon>
                                    </v-btn>
                                    <v-list>
                                        <v-list-tile v-for="(file, i) in attachments(info.order_additional_attachments)" :key="i" :href="file.download_uri">
                                            <v-list-tile-title>{{ file.file_name }}<v-icon color="primary" class="ml-3">cloud_download</v-icon></v-list-tile-title>
                                        </v-list-tile>
                                    </v-list>
                                </v-menu><!-- / 添付ファイルダウンロード用リスト -->
                                <v-btn icon>
                                    <v-icon v-if="isOpenToggle(info.id)">keyboard_arrow_up</v-icon>
                                    <v-icon v-else>keyboard_arrow_down</v-icon>
                                </v-btn>
                            </div>
                            <!-- 内容（展開時） -->
                            <div v-show="isOpenToggle(info.id)" class="pa-2 pl-5 text-left">
                                <div v-html="info.content"></div>
                                <!-- 添付ファイル -->
                                <ul class="mt-2 mb-0 pl-0">
                                    <li v-for="(attachment, i) in attachments(info.order_additional_attachments)" :key="i">
                                        <div style="display:flex;align-items:flex-end;">
                                            <v-icon>attachment</v-icon>
                                            <a class="ml-1" :href="attachment.download_uri">{{ attachment.file_name }}</a>
                                        </div>
                                        <template v-if="getFileExtensionType(attachment.download_uri) == 'image'">
                                            <v-img :src="attachment.download_uri" width="300px"></v-img>
                                        </template>
                                        <template v-else-if="getFileExtensionType(attachment.download_uri) == 'video'">
                                            <video
                                                controls
                                                width="300px"
                                                :src="attachment.download_uri"
                                            ></video>
                                        </template>
                                    </li>
                                </ul><!-- / 添付ファイル -->
                            </div><!-- / 内容（展開時） -->
                            <v-divider v-if="index+1 < additionalInfos.length" class="ma-0"></v-divider>
                        </div>
                    </div>
                </v-list>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn color="grey" dark @click="close">{{ $t('common.button.back') }}</v-btn>
                <v-btn color="primary" light @click="reflect" :disabled="!valid">{{ $t('common.button.reflect') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
// Mixins
import requestDetailMixin from '../../../../mixins/Admin/requestDetailMixin'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'
import storeAdditionalInfo from '../../../../stores/Order/OrderDetails/Show/additional-info';

export default {
    mixins: [
        requestDetailMixin,
    ],
    data() {
        return {
            dialog: false,
            additionalInfos: [],
            // サムネイル判定用拡張子
            supportedExtensions: {
                image: ['jpg', 'jpeg', 'png', 'gif'],
                video: ['mp4'],
            },
            openedTogglePanels: storeAdditionalInfo.state.processingData.openedTogglePanels,
        }
    },
    computed: {
        valid() {
            return this.additionalInfos.filter(info => info.selected === true).length > 0
        },
        isOpenToggle () {
            return function (id) {
                return this.openedTogglePanels.indexOf(id) != -1 ? true : false
            }
        },
        userId () {
            return document.getElementById('login-user-id').value
        },
        operator () {
            return function (user_id) {
                let operator = store.state.processingData.candidates.filter(user => user_id == user.id)
                return operator.length > 0 ? operator[0] : []
            }
        },
        user_name () {
            return function (user_id) {
                return this.operator(user_id).name
            }
        },
        user_image_path () {
            return function (user_id) {
                return this.operator(user_id).user_image_path
            }
        },
        attachments () {
            return function (attachments) {
                let files = []
                if (attachments) {
                    attachments.forEach(item => {
                        let file_name = item.name
                        let file_path = item.file_path
                        let uri = '/utilities/download_file?file_path=' + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
                        files.push({
                            file_id: item.id,
                            file_name: file_name,
                            file_path: file_path,
                            download_uri: uri,
                        })
                    })
                }
                return files
            }
        },
        getFileExtensionType () {
            return function (file) {
                if (!file) return
                const extension = file.slice(file.lastIndexOf('.') + 1)
                return Object.keys(this.supportedExtensions).filter(type => (this.supportedExtensions[type].some(support => support == extension))).shift()
            }
        },
    },
    methods: {
        show() {
            const additionalInfos = JSON.parse(JSON.stringify(storeAdditionalInfo.state.processingData.additionalInfos.filter(info => info.order_additional_attachments.length > 0)))
            this.additionalInfos = Object.entries(additionalInfos).map(([key, info]) => Object.assign(info, { info_id: key, selected: false }))
            this.dialog = true
        },
        reflect() {
            let files = [];
            this.additionalInfos.filter(info => info.selected).forEach(info => files = files.concat(info.order_additional_attachments))
            eventHub.$emit('set-additional-file', {
                additionalInfos: files
            })
            this.close()
        },
        close() {
            this.additionalInfos = []
            this.dialog = false
        },
        selected(index) {
            this.additionalInfos.filter(info => info.info_id == index).forEach(info => info.selected = !info.selected)
        },
        togglePanel (id) {
            const openedTogglePanels = this.openedTogglePanels
            if (this.isOpenToggle(id)) {
                this.openedTogglePanels = openedTogglePanels.filter(togglePanel => togglePanel !== id)
            } else {
                this.openedTogglePanels.push(id)
            }
            storeAdditionalInfo.commit('setOpenedTogglePanels', this.openedTogglePanels)
        },
        isUpdate (info) {
            return info.created_user_id != info.updated_user_id || info.created_at != info.updated_at ? true : false
        },
    },
}
</script>

<style scoped>
.add-info-panel-header {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.add-info-panel-header .header-summary {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.add-info-panel-header .header-summary .creation-info {
    font-size: 10px;
}
.add-info-panel-header .content-detail {
    display:flex;
    align-items:center;
}
</style>
