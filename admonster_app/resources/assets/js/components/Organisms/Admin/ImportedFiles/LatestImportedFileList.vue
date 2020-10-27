<template>
    <!-- 最新n件 -->
    <div id="latest-imported-files" class="elevation-1 data-content">
        <v-card>
            <v-container fluid grid-list-md>
                <v-layout row wrap class="row-sm-blocks-3">
                    <v-flex xs12>
                        <div class="headline">{{ $t('imported_files.latest_imported_file') }}</div>
                    </v-flex>
                    <template v-for="(latest_request_file, index) in latest_request_files">
                        <div class="latest-imported-file" :key="index">
                            <v-card>
                                <a :href="requestUri(latest_request_file.request_file_id)" :id="createId(index)" class="name-and-date-wrap">
                                    <v-card-title>
                                        <div class="name-and-date-wrap-inner">
                                            <v-tooltip top>
                                                <div slot="activator" class="text-overflow-ellipsis">{{ latest_request_file.request_file_name }}</div>
                                                <div>{{ latest_request_file.request_file_name }}</div>
                                            </v-tooltip>
                                            <div class="text-xs-center caption grey--text">{{ latest_request_file.request_file_created_at | formatDateYmdHm }}</div>
                                        </div>
                                    </v-card-title>
                                </a>
                                <v-divider :class="colorGenerator(index) + ' colored-divider ma-0'"></v-divider>
                                <v-card-actions>
                                    <div class="caption">
                                        <div>
                                            <v-icon small>business</v-icon>
                                            {{ latest_request_file.business_name }}
                                        </div>
                                        <div class="grey--text">{{ latest_request_file.step_name }}</div>
                                    </div>
                                </v-card-actions>
                            </v-card>
                        </div>
                    </template>
                </v-layout>
            </v-container>
        </v-card>
        <progress-circular v-if="loading"></progress-circular>
    </div>
</template>

<script>
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        ProgressCircular
    },
    data: () => ({
        latest_request_files : [],
        loading: false
    }),
    computed: {
    },
    created() {
        this.latestImportedFileListAsync()

        let self = this
        eventHub.$on('updateLatestImportedFileList', function () {
            self.latestImportedFileListAsync()
        })
    },
    methods: {
        latestImportedFileListAsync () {
            this.loading = true
            axios.post('/api/imported_files/latest')
                .then((res) =>{
                // 検索結果を画面に反映
                    this.latest_request_files = res.data.latest_request_files
                }).catch((error) => {
                    console.log(error)
                }).finally(() => {
                    this.loading = false
                })
        },
        createId (index) {
            return 'index'+index
        },
        colorGenerator (index) {
            let color = ''
            switch ( index ) {
            case 0:
                color = 'red';
                break;
            case 1:
                color = 'orange';
                break;
            case 2:
                color = 'green';
                break;
            case 3:
                color = 'cyan';
                break;
            case 4:
                color = 'purple';
                break;
            default:
                color = 'grey';
                break;
            }
            return color
        },
        requestUri (request_file_id, status=null) {
            let uri = '/management/requests?request_file_id=' + encodeURIComponent(request_file_id)
            if (status){
                uri += '&status=' + encodeURIComponent(this.requestStatus(status))
            }
            return uri
        },
        requestStatus (status) {
            switch (status) {
            case 'all':
                return 0
            case 'wip':
                return 1
            case 'completed':
                return 2
            case 'excluded':
                return 3
            default:
                return 0
            }
        },
    }
}
</script>/

<style scoped>
.name-and-date-wrap {
    /* mock-upと同色 */
    color: #555555;
    text-decoration: none
}
.name-and-date-wrap:hover#index0 {
    /* red */
    color: #F44336;
    text-decoration: none
}
.name-and-date-wrap:hover#index1 {
    /* orange */
    color: #FF9800;
    text-decoration: none
}
.name-and-date-wrap:hover#index2 {
    /* green */
    color: #4CAF50;
    text-decoration: none
}
.name-and-date-wrap:hover#index3 {
    /* cyan */
    color: #00BCD4;
    text-decoration: none
}
.name-and-date-wrap:hover#index4 {
    /* purple */
    color: #9C27B0;
    text-decoration: none
}
.name-and-date-wrap-inner {
    overflow: hidden;
}
.text-overflow-ellipsis {
    /* オーバーした要素を非表示にする*/
    overflow: hidden;

    /* 改行を半角スペースに変換することで、1行にする */
    white-space: nowrap;

    /* オーバーしたテキストを3点リーダーにする */
    text-overflow: ellipsis;
}
.colored-divider {
    border-width: 2px;
}
</style>
