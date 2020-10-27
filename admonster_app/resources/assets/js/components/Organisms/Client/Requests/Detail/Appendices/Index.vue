<template>
    <div id="appendices" :style="{ height: height }">
        <v-card style="max-height:608px;">
            <v-list dense ref="header">
                <v-list-tile>
                    <v-list-tile-content>
                        <v-list-tile-title>
                            <span
                                color="transparent"
                                depressed
                            >
                                <span class="subheading font-weight-bold">{{ $t('client.requests.appendices.block_title') }}</span>
                                <span>（{{ paginate.data_count_total }} {{ $t('list.case') }}）</span>
                            </span>
                        </v-list-tile-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list>
            <v-divider class="my-0"></v-divider>

            <div v-if="appendices.length > 0" style="overflow-x: hidden; overflow-y: auto;max-height:509px;">
                <v-container grid-list-md>
                    <v-layout row wrap>
                        <template v-for="(item, index) in appendices">
                            <v-flex md12 :key="index">

                                <!-- 関連メール -->
                                <v-card v-if=" 'cc' in item">
                                    <v-card-title class="pr-3 pt-1 pl-3 pb-1">
                                        <v-icon color="grey lighten-1" class="mr-2">mail_outline</v-icon>
                                        <span>{{ $t('client.requests.appendices.related_mail') }}</span>
                                    </v-card-title>
                                    <v-divider class="my-0"></v-divider>
                                    <v-card-text class="pt-2 pb-2" @click="showRelatedMailDetailDialog(item)">
                                        <div class="appendix-text-wrap">
                                            <span class="appendix-text">
                                            {{ item.subject }}<span class="ml-2 caption grey--text">{{ item.body }}</span>
                                            </span>
                                        </div>
                                    </v-card-text>
                                    <v-card-actions class="pl-3 pr-3">
                                        <span class="caption">{{ item.updated_at | formatDateYmdHm(false, true) }}</span>
                                        <v-spacer></v-spacer>
                                        <v-badge
                                            v-if="item.request_mail_attachments.length > 0"
                                            overlap
                                            color="cyan"
                                            right
                                            class="attachments"
                                        >
                                            <span slot="badge">{{ item.request_mail_attachments.length }}</span>
                                            <v-icon color="grey lighten-1" @click="showAttachmentList(item.mail_attachments, 1)">attachment</v-icon>
                                        </v-badge>
                                    </v-card-actions>
                                </v-card>
                                <!-- 関連メール -->

                                <!-- 補足情報 -->
                                <v-card v-else-if="'is_open_to_client' in item">
                                    <v-card-title class="pr-3 pt-1 pl-3 pb-1">
                                        <v-icon color="grey lighten-1" class="mr-2">info</v-icon>
                                        <span>{{ $t('client.requests.appendices.additional_info') }}</span>
                                    </v-card-title>
                                    <v-divider class="my-0"></v-divider>
                                    <v-card-text class="pt-2 pb-2" @click="showAdditionalInfoDetailDialog(item)">
                                        <div class="appendix-text-wrap">
                                            <span class="appendix-text">{{ item.content }}</span>
                                        </div>
                                    </v-card-text>
                                    <v-card-actions class="pl-3 pr-3">
                                        <span class="caption">{{ item.updated_at | formatDateYmdHm(false, true) }}</span>
                                        <v-spacer></v-spacer>
                                        <v-badge
                                            v-if="item.request_additional_info_attachments.length > 0"
                                            overlap
                                            color="cyan"
                                            right
                                            class="attachments"
                                        >
                                            <span slot="badge">{{ item.request_additional_info_attachments.length }}</span>
                                            <v-icon color="grey lighten-1" @click="showAttachmentList(item.request_additional_info_attachments, 2)">attachment</v-icon>
                                        </v-badge>
                                    </v-card-actions>
                                </v-card>
                                <!-- 補足情報 -->

                                <!-- 依頼ログ -->
                                <v-card v-else>
                                    <!-- 受付完了 -->
                                    <template v-if="requestLogTypeImported(item.type)">
                                        <v-card-title class="pr-3 pt-1 pl-3 pb-1">
                                            <v-icon color="grey lighten-1" class="mr-2">mdi-import</v-icon>
                                            <span>{{ $t('client.requests.request_log_types.' + item.type + '.title') }}</span>
                                        </v-card-title>
                                        <v-divider class="my-0"></v-divider>
                                        <v-card-text class="pt-2 pb-2">
                                            <div class="appendix-text-wrap">
                                                <span class="appendix-text">{{ $t('client.requests.request_log_types.' + item.type + '.content_text') }}</span>
                                            </div>
                                        </v-card-text>
                                        <v-card-actions class="pl-3 pr-3">
                                            <span class="caption">{{ item.updated_at | formatDateYmdHm(false, true) }}</span>
                                        </v-card-actions>
                                    </template>
                                    <!-- / 受付完了 -->

                                    <!-- 納品完了 -->
                                    <template v-else-if="requestLogTypeAllCompleted(item.type)">
                                        <v-card-title class="pr-3 pt-1 pl-3 pb-1">
                                            <v-icon color="grey lighten-1" class="mr-2">mdi-truck-delivery</v-icon>
                                            <span>{{ $t('client.requests.request_log_types.' + item.type + '.title') }}</span>
                                        </v-card-title>
                                        <v-divider class="my-0"></v-divider>
                                        <v-card-text class="pt-2 pb-2">
                                            <div class="appendix-text-wrap">
                                                <span class="appendix-text">{{ $t('client.requests.request_log_types.' + item.type + '.content_text') }}</span>
                                            </div>
                                        </v-card-text>
                                        <v-card-actions class="pl-3 pr-3">
                                            <span class="caption">{{ item.updated_at | formatDateYmdHm(false, true) }}</span>
                                        </v-card-actions>
                                    </template>
                                    <!-- / 納品完了 -->
                                </v-card>
                                <!-- / 依頼ログ -->

                            </v-flex>
                        </template>
                    </v-layout>
                </v-container>
            </div>

            <v-divider class="my-0"></v-divider>

            <!--pagination-->
            <v-card-actions class="pl-3 pr-3" v-if="appendices.length > 0">
                <v-layout row wrap align-center justify-center>
                    <v-pagination
                        v-model="page"
                        :length="paginate.page_count"
                        circle
                        :total-visible="5"
                        @input="changePage(page)"
                    ></v-pagination>
                </v-layout>
            </v-card-actions>
            <!--pagination-->
        </v-card>

        <additional-info-detail-dialog v-if="additionalInfoDialog" :additionalInfoDialog.sync="additionalInfoDialog" :data="selectedAdditionalInfo"></additional-info-detail-dialog>
        <related-mail-detail-dialog v-if="relatedMailDetailDialog" :relatedMailDetailDialog.sync="relatedMailDetailDialog" :data="selectedRelatedMail"></related-mail-detail-dialog>
        <request-mail-attachments-modal ref="attachmentListModal1" :attachments="attachments"></request-mail-attachments-modal>
        <additional-info-attachments-modal ref="attachmentListModal2" :attachments="attachments"></additional-info-attachments-modal>
        <progress-circular v-if="loading"></progress-circular>
    </div>
</template>

<script>
import store from '../../../../../../stores/Client/Requests/Detail/store'
import requestDetailMixin from '../../../../../../mixins/Admin/requestDetailMixin'
import ProgressCircular from '../../../../../Atoms/Progress/ProgressCircular'
import AdditionalInfoDetailDialog from  './AdditionalInfoDetailDialog'
import RelatedMailDetailDialog from  './RelatedMailDetailDialog'
import RequestMailAttachmentsModal from '../../../../../Molecules/Requests/RequestMailAttachmentsModal'
import AdditionalInfoAttachmentsModal from '../../../../../Molecules/Client/Requests/AdditionalInfoAttachmentsModal'

export default {
    name: 'appendices',
    mixins: [requestDetailMixin],
    props: {
        requestId: { type: Number, required: true },
        candidates: { type: Array },
        existAppendices: { type: Boolean },
        height: { type: String, required: false, default: '615px' },
        useStore: { type: Boolean, required: false, default: true },
        addSearchParams: { type: Object, required: false, default: () => ({}) },
    },
    data: () => ({
        dialog: false,
        additionalInfoDialog: false,
        relatedMailDetailDialog: false,
        //loading
        loading: false,
        appendices: [],
        selectedAdditionalInfo: null,
        selectedRelatedMail: null,

        page: store.state.appendicesSearchParams.page,
        pagination: {
            sortBy: store.state.appendicesSearchParams.sort_by,
            descending: store.state.appendicesSearchParams.descending,
            rowsPerPage: store.state.appendicesSearchParams.rows_per_page,
        },
        paginate: {
            data_count_total: 0,
            page_count: 0,
            page_from: 0,
            page_to: 0,
        },
        attachments: []
    }),
    components:{
        ProgressCircular,
        AdditionalInfoDetailDialog,
        RelatedMailDetailDialog,
        RequestMailAttachmentsModal,
        AdditionalInfoAttachmentsModal
    },
    computed: {
        openedAddInfo () {
            return function (id) {
                if (this.openedAddInfoIds.indexOf(id) != -1) {
                    return true
                } else {
                    return false
                }
            }
        },
        attachmentFiles () {
            return function (attachments) {
                let array = [];
                if (attachments.length > 0) {
                    attachments.forEach(item => {
                        let uri = '/utilities/download_file?file_path='
                        let file_name = item.name
                        let file_path = item.file_path
                        uri = uri + encodeURIComponent(file_path) + '&file_name=' + encodeURIComponent(file_name)
                        array.push({
                            name: file_name,
                            path: file_path,
                            downloadUri: uri,
                            id: item.id,
                            size: item.file_size
                        });
                    });
                }
                return array
            }
        },
        searchParams () {
            return Object.assign(store.state.appendicesSearchParams, this.addSearchParams)
        },
        requestLogTypeImported() {
            return function (requestLogType) {
                if (requestLogType === _const.REQUEST_LOG_TYPE.IMPORT_COMPLETED) {
                    return true
                } else {
                    return false
                }
            }
        },
        requestLogTypeAllCompleted() {
            return function (requestLogType) {
                if (requestLogType === _const.REQUEST_LOG_TYPE.ALL_COMPLETED) {
                    return true
                } else {
                    return false
                }
            }
        },
        appendicesUri() {
            switch (this.addSearchParams.open_page) {
            case 'work':
                return '/api/biz/base/getAppendices'
            default:
                return '/api/client/requests/appendices'
            }
        },
    },
    created () {
        this.getAppendices(this.searchParams)
    },
    methods: {
        changePage (page) {
            let params = this.searchParams
            params.page = page
            this.getAppendices(params)
        },
        getAppendices (params) {
            this.loading = true
            let searchParams = Vue.util.extend({}, params)

            axios.post(this.appendicesUri,{
                request_id: this.requestId,
                search_params: searchParams,
            })
                .then((res) => {
                // 検索条件をstoreに保存
                    if (this.useStore) {
                        store.commit('setAppendicesSearchParams', { params: params })
                    }
                    // 検索結果を画面に反映
                    this.appendices = res.data.appendices.data
                    if (res.data.appendices.data.length > 0) {
                        this.$emit('update:existAppendices', true)
                    }
                    this.paginate.data_count_total = res.data.appendices.total
                    this.paginate.page_count = res.data.appendices.last_page
                    this.paginate.page_from = res.data.appendices.from ? res.data.appendices.from : 0
                    this.paginate.page_to = res.data.appendices.to ? res.data.appendices.to : 0
                })
                .catch((err) => {
                // TODO エラー時処理
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.read_failed') });
                })
                .finally(() => {
                    this.loading = false
                });
        },
        showAdditionalInfoDetailDialog (item) {
            this.selectedAdditionalInfo = item
            this.additionalInfoDialog = true
        },
        showRelatedMailDetailDialog (item) {
            this.selectedRelatedMail = item
            this.relatedMailDetailDialog = true
        },
        showAttachmentList: function (attachments, type) {
            let files = JSON.parse(JSON.stringify(attachments));
            this.attachments = this.attachmentFiles(files)

            if (type == 1) {
                this.$refs.attachmentListModal1.show()
            } else if (type == 2)
                this.$refs.attachmentListModal2.show()
        }
    }
}
</script>
<style scoped>
/*2行の3点リーダー*/
.appendix-text-wrap {
    overflow: hidden;
    width: 100%;
}
.appendix-text-wrap .appendix-text {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>
