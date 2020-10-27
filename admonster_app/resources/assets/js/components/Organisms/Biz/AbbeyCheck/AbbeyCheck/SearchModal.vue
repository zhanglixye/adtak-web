<template>
    <!-- 確認モーダル -->
    <div id="search-modal-block">
        <v-layout row justify-center >
            <v-dialog id="searchDialog" fullscreen  transition="dialog-bottom-transition" v-model="modal">
                <v-card>
                    <v-card-title>
                        <v-toolbar
                                :clipped-left="$vuetify.breakpoint.lgAndUp"
                                color="primary"
                                dark
                                app
                                fixed
                                flat
                                dense
                                id="modal_header"
                                class="teal"
                            >
                                <v-toolbar-title>
                                    <v-btn color="white" dark flat @click="closeModal"><v-icon>close</v-icon></v-btn>
                                    <span class="hidden-sm-and-down">{{$t('biz.abbey_check.abbey_search.h1')}}</span>
                                </v-toolbar-title>
                                <v-spacer></v-spacer>
                            </v-toolbar>
                    </v-card-title>
                    <v-card-text>
                        <progress-circular style="position: absolute;z-index: 9999;" v-if="loading"></progress-circular>
                        <v-layout wrap mt-3 mb-2>
                            <v-flex xs6 offset-xs3>
                                <v-text-field @keyup.enter="getData" :label="$t('biz.abbey_check.abbey_search.search')" v-model="menu_name" hide-details></v-text-field>
                                </v-flex>
                            <v-flex xs1><v-btn color="primary" dark @click="getData">{{$t('common.button.search')}}</v-btn></v-flex>
                        </v-layout>
                        <v-data-table
                            :headers="headers"
                            :items="abbey_list"
                            :pagination.sync="pagination"
                            hide-actions
                            fix-header
                            class="elevation-1 fixed-header v-table__overflow"
                            style="max-height:75vh;backface-visibility: hidden;"
                        >
                            <template slot="items" slot-scope="props">
                                <tr style="cursor: pointer;" class="abbey-tr" @click="setAbbeyId(props.item.abbey_id)">
                                    <!-- 画面の幅に依存しない幅はスタイル指定 -->
                                    <!-- 1行目の↓のtdのみcssが適応されない為、フォントサイズを直接指定 -->
                                    <td class="text-xs-center" style="font-size: 12px; min-width: 400px;"  v-html="props.item.specification+props.item.specification_2"></td>
                                    <td class="text-xs-center" style="min-width: 150px" v-html="getPurposeMessage(props.item.purpose)"></td>
                                    <td class="text-xs-center" v-html="props.item.abbey_id"></td>
                                    <td class="text-xs-center" v-html="props.item.pixel"></td>
                                    <td class="text-xs-center" v-if="props.item.file_size_unit == 1" v-html="props.item.file_size + $t('biz.abbey_check.abbey_search.item_data_unit.file_size.KB')"></td>
                                    <td class="text-xs-center" v-else-if="props.item.file_size_unit == 2" v-html="props.item.file_size + $t('biz.abbey_check.abbey_search.item_data_unit.file_size.MB')"></td>
                                    <td class="text-xs-center" v-else value=""></td>
                                    <td class="text-xs-center" v-html="getFileFormant(props.item.file_format)"></td>
                                    <td class="text-xs-center" v-html="props.item.total_bit_rate"></td>
                                    <td class="text-xs-center" v-html="props.item.animation"></td>
                                    <td class="text-xs-center" v-html="props.item.target_Loudness"></td>
                                    <td class="text-xs-center" v-html="props.item.alt_text"></td>
                                    <td class="text-xs-center" v-html="props.item.link"></td>
                                </tr>
                            </template>
                        <template slot="no-data">
                            <div class="text-xs-center">
                                {{ $t('tasks.no_data') }}
                            </div>
                        </template>
                        </v-data-table>
                    </v-card-text>
                    <v-card-actions>
                        <p>{{ material_info }}</p>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-layout>
    </div>
    <!-- / 確認モーダル -->
</template>

<script>

import ProgressCircular from '../../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        ProgressCircular,
    },
    props: {
    },
    data: () => ({
        modal: false,
        material_info: '',
        pagination: {
            sortBy:'abbey_id',// default abbey_id
            descending: false,// default asc
            rowsPerPage: -1,
        },
        menu_name: '',
        task_result_file_seq_no: '',
        abbey_list: [],
        loading: false,
        purposeMessageList: {
            1:'画像',
            2:'動画（音声あり）',
            3:'動画（音声なし）',
            4:'代替画像',
            5:'静止/代替画像',
            6:'動画（音声必須）',
            7:'画像（右パネル）',
            8:'画像（左パネル）',
        },
        file_format_list: {
            1:'MP4',
            2:'PNG',
            4:'JPEG',
            8:'GIF89a',
        }
    }),
    created: function () {
        let self = this;

        eventHub.$on('open-search-modal', function(data) {
            self.modal = true
            self.menu_name = data.menu_name
            self.task_result_file_seq_no = data.task_result_file_seq_no

            // 素材情報追加
            const byteSize = data.file_size
            var splitSise = byteSize.split(',')
            const sizeUnits = ['Byte', 'KB', 'MB', 'GB']
            const sizeUnit = sizeUnits[splitSise.length - 1]
            const size = splitSise[0]
            const delimiter_1 = self.$t('biz.abbey_check.abbey_search.material_info.delimiter_1')
            const delimiter_2 = self.$t('biz.abbey_check.abbey_search.material_info.delimiter_2')
            self.material_info ='[' + self.$t('biz.abbey_check.abbey_search.material_info.title')+ ']'
            + self.$t('biz.abbey_check.abbey_search.material_info.name') + delimiter_2 + data.file_name
            + delimiter_1 + self.$t('biz.abbey_check.abbey_search.material_info.aspect_ratio')+ delimiter_2 + data.aspect_ratio
            + delimiter_1 + self.$t('biz.abbey_check.abbey_search.material_info.size') + delimiter_2 + size + sizeUnit + '(' + byteSize + 'Byte)'

            self.getData({wait:500})
        })

    },
    mounted() {
        this.search_menu_name = this.menu_name
    },
    computed: {
        headers: function () {
            return [
                { text: this.$t('biz.abbey_check.abbey_search.item_label.specification'), value: 'specification', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.purpose'), value: 'purpose', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.abbey_id'), value: 'abbey_id', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.pixel'), value: 'pixel', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.file_size'), value: 'file_size', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.file_format'), value: 'file_format', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.total_bit_rate'), value: 'total_bit_rate', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.animation'), value: 'animation', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.target_Loudness'), value: 'target_Loudness', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.alt_text_content'), value: 'alt_text', align: 'center'},
                { text: this.$t('biz.abbey_check.abbey_search.item_label.link'), value: 'link', align: 'center'},
            ]
        }
    },
    methods: {
        getData: async function({wait = 0}) {
            const sleep = msec => new Promise(resolve => setTimeout(resolve, msec))
            await sleep(wait)
            this.loading = true
            axios.post('/api/biz/abbey_check/abbey_search/index',{'search_word' : this.menu_name})
                .then((res) => {
                    this.loading = false
                    const data = JSON.parse(JSON.stringify(res.data.abbey_list))

                    // ソート用にデータを作成
                    data.forEach(abbey => {
                        abbey.pixel = abbey.width+'x'+abbey.hight
                    })

                    this.abbey_list = data
                })
                .catch((err) => {
                    this.loading = false
                    console.log(err)
                    eventHub.$emit('open-notify-modal', {message: this.$t('biz.abbey_check.common.messages.failed_to_retrieve_data')})
                });
        },
        getPurposeMessage: function(index) {
            return this.purposeMessageList[index]
        },
        setAbbeyId: function(abbey_id) {
            const self = this
            this.closeModal()
            eventHub.$emit('set-abbey-id', {
                'abbey_id': abbey_id,
                'task_result_file_seq_no': self.task_result_file_seq_no,
            })
        },
        getFileFormant: function(format_bit){
            let format = ''
            for (const key of Object.keys(this.file_format_list)) {
                if ( (parseInt(format_bit, 2) & key ) == key) {
                    if (format == '') {
                        format += this.file_format_list[key];
                    } else {
                        format += '<br />'+this.file_format_list[key];
                    }
                }
            }
            return format
        },
        closeModal: function(){
            this.modal = false
            this.material_info = ''
            this.abbey_list = []
        }
    }
}
</script>

<style>
#abbey-check table.v-table tbody tr.abbey-tr td {
    font-size: 12px;
    white-space: normal;
}

#abbey-check .theme--dark.v-table thead th {
    background-color: #424242;
}
#abbey-check .theme--light.v-table thead th {
    background-color: #fff;
}
/* Theme */
#abbey-check .fixed-header {
    display: flex;
    flex-direction: column;
    height: 100%;
}
#abbey-check .fixed-header table {
    /* データの表示幅を画面サイズに依存させない */
    /* table-layout: fixed; */
}
#abbey-check .fixed-header th {
    position: sticky;
    top: 0;
    z-index: 5;
}
#abbey-check .fixed-header th:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
}
#abbey-check .fixed-header tr.v-datatable__progress th {
    height: 1px;
}
#abbey-check .fixed-header .v-table__overflow {
    flex-grow: 1;
    flex-shrink: 1;
    overflow-x: auto;
    overflow-y: auto;
}
#abbey-check .fixed-header .v-datatable.v-table {
    flex-grow: 0;
    flex-shrink: 1;
}
#abbey-check .fixed-header .v-datatable.v-table .v-datatable__actions {
    flex-wrap: nowrap;
}
#abbey-check .fixed-header .v-datatable.v-table .v-datatable__actions .v-datatable__actions__pagination {
    white-space: nowrap;
}

</style>
