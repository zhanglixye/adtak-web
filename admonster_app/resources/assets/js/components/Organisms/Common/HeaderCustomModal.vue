<template>
    <!-- 表頭カスタムモーダル -->
    <div id="header-custom-modal-block">
        <v-dialog v-model="headerCustomModal" persistent max-width="900px">
            <v-card style="padding: 8px;">
                <div style="display: flex;">
                    <v-spacer></v-spacer>
                    <a class="caption" @click="resetHeaderCustomData()">{{ $t('list.header_custom_modal.default') }}</a>
                </div>
                <v-card-actions class="justify-center">
                    <v-layout row wrap>
                        <v-flex xs5>
                            <span class="grey--text caption">{{ $t('list.header_custom_modal.hidden') }}</span>
                            <v-container ref="header-custom-hidden-list" class="header-list" pa-0 mt-2>
                                <draggable
                                    class="dragArea list-group"
                                    style="height: 400px"
                                    :list="hiddenHeaderCustomData"
                                    :clone="clone"
                                    :group="{ name: 'headers', pull: pullFunction }"
                                    :animation=300
                                    @start="start"
                                    :move="onMove"
                                    @choose="onChoose"
                                    @end="onEnd"
                                    data-column-type="hidden"
                                >
                                    <div
                                        class="list-group-item"
                                        v-for="(element,i) in hiddenHeaderCustomData"
                                        :key="i"
                                        :style="hiddenListTileStyle(i)"
                                    >

                                        <v-layout row align-center mx-0>
                                            <v-flex xs12 my-2>
                                                <v-tooltip top>
                                                    <div class="text-cut" slot="activator">
                                                        <v-icon v-if="isIconShow" small flat :color="displayFormatHiddenEditIconColor(i)">
                                                            <template v-if="element.isEdit">mdi-file-excel</template>
                                                            <template v-else>mdi-wrench</template>
                                                        </v-icon>
                                                        {{ element.text }}
                                                    </div>
                                                    <span>{{ element.text }}</span>
                                                </v-tooltip>
                                            </v-flex>
                                        </v-layout>
                                    </div>
                                </draggable>
                            </v-container>
                        </v-flex>
                        <v-flex xs1>
                            <v-layout align-center justify-center column fill-height>
                                <v-btn
                                    flat icon
                                    color="primary"
                                    slot="activator"
                                    @click="leftRightHeaderColumn('show')"
                                    :disabled="hiddenHeaderCustomData.length <= 0 ? true : false"
                                >
                                    <v-icon>arrow_forward</v-icon>
                                </v-btn>
                                <v-btn
                                    flat icon
                                    color="primary"
                                    slot="activator"
                                    @click="leftRightHeaderColumn('hidden')"
                                    :disabled="showHeaderCustomData.length <= 0 ? true : false"
                                    class="ml-0"
                                >
                                    <v-icon>arrow_back</v-icon>
                                </v-btn>
                            </v-layout>
                        </v-flex>
                        <v-flex xs5>
                            <v-layout justify-space-between>
                                <span class="grey--text caption">{{ $t('list.header_custom_modal.show') }}</span>
                            </v-layout>
                            <div></div>
                            <v-container ref="header-custom-show-list" class="header-list" pa-0 mt-2>
                                <draggable
                                    class="dragArea list-group"
                                    style="height: 350px"
                                    :list="showHeaderCustomData"
                                    group="headers"
                                    :animation=300
                                    :move="onMove"
                                    @choose="onChoose"
                                    @end="onEnd"
                                    data-column-type="show"
                                >
                                    <div
                                        class="list-group-item"
                                        v-for="(element,i) in showHeaderCustomData"
                                        :key="i"
                                        :style="showListTileStyle(i)"
                                    >
                                        <v-layout row align-center mx-0>
                                            <v-flex xs8 style="overflow: hidden;">
                                                <v-tooltip top :style="showListTextStyle(element.isEdit)">
                                                    <div class="text-cut my-2" slot="activator">
                                                        <v-icon v-if="isIconShow" small flat :color="displayFormatEditIconColor(i)">
                                                            <template v-if="element.isEdit">mdi-file-excel</template>
                                                            <template v-else>mdi-wrench</template>
                                                        </v-icon>
                                                        {{ element.text }}
                                                    </div>
                                                    <span>{{ element.text }}</span>
                                                </v-tooltip>
                                                <v-tooltip top v-if="element.isEdit && isOrderAdmin" style="float: right; margin-top: 4px;">
                                                    <v-btn slot="activator" icon small @click.stop="openEdit(element)" flat :color="displayFormatEditIconColor(i)">
                                                        <v-icon>mdi-tune</v-icon>
                                                    </v-btn>
                                                    <span>{{ $t('order.order_details.dialog.setting_display_format.display_type_edit') }}</span>
                                                </v-tooltip>
                                            </v-flex>
                                            <v-flex xs4>
                                                <!-- <v-text-field style="text-align:right" v-model="element.width" suffix="px" hide-details/> -->
                                                <!-- TODO v-text-fieldを使用するとドラッグした際にpx値がバグる。v-modelがうまく動作しない模様-->
                                                <input type="text" class="form-control" style="text-align:right" v-model="element.width" />px
                                            </v-flex>
                                        </v-layout>
                                    </div>
                                </draggable>
                            </v-container>
                            <div :style="{'display': 'flex', 'position': 'absolute', 'bottom': '50px'}">
                                <span style="line-height: 50px;">{{ $t('common.pagination.display_count_per_one_page') }}</span>
                                <v-select
                                    v-model="copySelectedRowsPerPage"
                                    :style="{'max-width': '50px', 'margin-left': '140px'}"
                                    :items="rowsCandidatesPerPage"
                                    :menu-props="{ maxHeight: '300' }"
                                ></v-select>
                            </div>
                        </v-flex>
                        <v-flex xs1>
                            <v-layout align-center justify-center column fill-height>
                                <v-btn flat icon
                                    color="primary"
                                    slot="activator"
                                    @click="upDownShowHeader('up')"
                                    :disabled="selectedShowItem === 0 ? true : false"
                                >
                                    <v-icon>arrow_upward</v-icon>
                                </v-btn>
                                <v-btn flat icon
                                    color="primary"
                                    slot="activator"
                                    @click="upDownShowHeader('down')"
                                    :disabled="selectedShowItem >= showHeaderCustomData.length-1 ? true : false"
                                    class="ml-0"
                                >
                                    <v-icon>arrow_downward</v-icon>
                                </v-btn>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                </v-card-actions>
                <div style="display: flex;">
                    <template v-if="isAllocationList">
                        <a class="caption" style="padding: 12px;" @click="changeEducationMode()">{{ $t('allocations.list.switch_to_educational_mode') }}</a>
                    </template>
                    <template v-if="isOrder && isOrderAdmin">
                        <a class="caption" style="padding: 12px;" @click="openOrderItemEdit()">{{ $t('order.orders.setting.link_edit_item_name') }}</a>
                    </template>
                    <v-spacer></v-spacer>
                    <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn
                        color="primary"
                        :dark="!showHeaderCustomData.length <= 0 ? true : false"
                        :disabled="showHeaderCustomData.length <= 0 ? true : false"
                        @click="executionProcess()"
                    >
                        {{ $t('common.button.setting') }}
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </div>
    <!-- / 表頭カスタムモーダル -->
</template>

<script>
// 公式サイトにVue.useを使用する記法は[For Vue.js 1.0 only]と記載があるためここでimport
import draggable from 'vuedraggable'
import ConfirmDialog from '../../Atoms/Dialogs/ConfirmDialog'

export default {
    components: {
        draggable,
        ConfirmDialog,
    },
    props: {
        headers: { type: Array },
        showHeaders: { type: Array },
        hiddenHeaders: { type: Array },
        initialHeaders: { type: Object },
        selectedRowsPerPage: { type: Number, required: true },
        orderId: { type: Number, required: false, default: null },
        isOrderAdmin: { type: Boolean, required: false, default: false },
        prependFunctionToCancel: { type: Function, required: false, default: () => {} },
        isIconShow: {type: Boolean, required: false, default: false },
    },
    data: () => ({
        // propsの値を直接書き換えないため
        copySelectedRowsPerPage: null,

        headerCustomModal: false,
        selectedShowItem:0,
        selectedHiddenItem:0,

        //変更内容の保存用配列
        showHeaderCustomData: [],
        hiddenHeaderCustomData: [],

        beforeSelectedShowItem : null,
        beforeSelectedHiddenItem :  null,

        tempSelectedShowItem : null,
        tempSelectedHiddenItem : null,

        controlOnStart: true
    }),
    computed: {
        isOrder () {
            return this.orderId !== null
        },
        hiddenListTileStyle () {
            return (idx) => {
                if (idx === this.selectedHiddenItem || idx === this.tempSelectedHiddenItem) {
                    return {
                        color: 'white',
                        backgroundColor: this.$vuetify.theme.primary,
                    }
                }
                return {
                    // color: 'rgba(0,0,0,.87)',
                    backgroundColor: '',
                }
            }
        },
        showListTileStyle () {
            return (idx) => {
                if (idx === this.selectedShowItem || idx === this.tempSelectedShowItem) {
                    return {
                        color: 'white',
                        backgroundColor: this.$vuetify.theme.primary,
                    }
                }
                return {
                    // color: 'rgba(0,0,0,.87)',
                    backgroundColor: '',
                }
            }
        },
        showListTextStyle () {
            return (isEdit) => {
                if (isEdit && this.isOrderAdmin) {
                    return {
                        float: 'left',
                        'max-width': '80%'
                    }
                }
            }
        },
        displayFormatEditIconColor () {
            return (idx) => {
                if (idx === this.selectedShowItem || idx === this.tempSelectedShowItem) {
                    return 'white'
                }
                return 'primary'
            }
        },
        displayFormatHiddenEditIconColor () {
            return (idx) => {
                if (idx === this.selectedHiddenItem || idx === this.tempSelectedHiddenItem) {
                    return 'white'
                }
                return 'primary'
            }
        },
        isAllocationList () {
            return '/allocations' === location.pathname
        },
        rowsCandidatesPerPage () {
            return [20, 50, 100]
        }
    },
    created: function () {
        let self = this;
        eventHub.$on('open-header-custom-modal', function() {

            // 初期化
            self.showHeaderCustomData = [];
            self.hiddenHeaderCustomData = [];
            self.selectedHiddenItem = 0;
            self.selectedShowItem = 0;
            // propsの値を直接書き換えないため
            self.copySelectedRowsPerPage = self.selectedRowsPerPage

            // そのまま使用するとv-modelがstoreを上書きしてしまうため変更用変数を用意する
            for (let i = 0; i < self.showHeaders.length; i++) {
                self.showHeaderCustomData[i] = {
                    text: self.showHeaders[i]['text'],
                    value: self.showHeaders[i]['value'],
                    width: self.showHeaders[i]['width'],
                    align: self.showHeaders[i]['align'],
                    textForSetting: self.showHeaders[i]['textForSetting'],
                    isEdit: 'isEdit' in self.showHeaders[i] ? self.showHeaders[i]['isEdit'] : false,
                    labelId: self.showHeaders[i]['labelId'],
                }
            }
            for (let i = 0; i < self.hiddenHeaders.length; i++) {
                self.hiddenHeaderCustomData[i] = {
                    text: self.hiddenHeaders[i]['text'],
                    value: self.hiddenHeaders[i]['value'],
                    width: self.hiddenHeaders[i]['width'],
                    align: self.hiddenHeaders[i]['align'],
                    textForSetting: self.hiddenHeaders[i]['textForSetting'],
                    isEdit: 'isEdit' in self.hiddenHeaders[i] ? self.hiddenHeaders[i]['isEdit'] : false,
                    labelId: self.hiddenHeaders[i]['labelId'],
                }
            }
            self.scrollToTop()
            self.headerCustomModal = true;
        })
    },
    methods: {
        executionProcess () {

            eventHub.$emit('commitHeaderCustomData', {
                'showHeaders': this.showHeaderCustomData,
                'hiddenHeaders': this.hiddenHeaderCustomData
            });
            eventHub.$emit('changeRowsPerPage', {
                'rowsPerPage': this.copySelectedRowsPerPage
            });
            // eventHub.$emit('resetHeaderCustomData');
            this.headerCustomModal = false
        },
        clone({ text, width, value, align, textForSetting, isEdit, labelId }) {
            return { text, width, value, align, textForSetting, isEdit, labelId };
        },
        resetHeaderCustomData() {

            // 初期化
            this.showHeaderCustomData = [];
            this.hiddenHeaderCustomData = [];
            this.selectedHiddenItem = 0;
            this.selectedShowItem = 0;
            this.copySelectedRowsPerPage = 20

            //初期値
            let initialShowHeaders = this.initialHeaders.showHeaders
            let initialHiddenHeaders = this.initialHeaders.hiddenHeaders

            // そのまま使用するとv-modelがstoreを上書きしてしまうため変更用変数を用意する
            for (let i = 0; i < initialShowHeaders.length; i++) {
                this.showHeaderCustomData[i] = {
                    text: initialShowHeaders[i]['text'],
                    value: initialShowHeaders[i]['value'],
                    width: initialShowHeaders[i]['width'],
                    align: initialShowHeaders[i]['align'],
                    textForSetting: initialShowHeaders[i]['textForSetting'],
                    isEdit: 'isEdit' in initialShowHeaders[i] ? initialShowHeaders[i]['isEdit'] : false,
                    labelId: initialShowHeaders[i]['labelId'],
                }
            }
            for (let i = 0; i < initialHiddenHeaders.length; i++) {
                this.hiddenHeaderCustomData[i] = {
                    text: initialHiddenHeaders[i]['text'],
                    value: initialHiddenHeaders[i]['value'],
                    width: initialHiddenHeaders[i]['width'],
                    align: initialHiddenHeaders[i]['align'],
                    textForSetting: initialHiddenHeaders[i]['textForSetting'],
                    isEdit: 'isEdit' in initialHiddenHeaders[i] ? initialHiddenHeaders[i]['isEdit'] : false,
                    labelId: initialHiddenHeaders[i]['labelId'],
                }
            }
            this.scrollToTop()
        },
        // リストのitemをクリックした際に呼ばれるイベント
        onChoose(e) {
            // Vue.Draggableのイベントでは取得元カラム情報が存在しないため、data-column-typeを追加し判別
            let from_column_type = e.from.getAttribute('data-column-type');

            // 選択中itemを更新
            if (from_column_type === 'hidden'){
                this.selectedHiddenItem = e.oldIndex;
            } else {
                this.selectedShowItem = e.oldIndex;
            }
        },
        // リストのitemをドラッグし入れ替えた際に呼ばれるイベント
        onMove(e) {
            let from_column_type = e.from.getAttribute('data-column-type');
            let to_column_type = e.to.getAttribute('data-column-type');

            if (this.beforeSelectedShowItem === null && this.beforeSelectedHiddenItem === null){
                this.beforeSelectedShowItem = this.selectedShowItem;
                this.beforeSelectedHiddenItem = this.selectedHiddenItem;
            }

            if (from_column_type === to_column_type){
                this.selectedShowItem = this.beforeSelectedShowItem;
                this.selectedHiddenItem = this.beforeSelectedHiddenItem;
                this.tempSelectedShowItem = null;
                this.tempSelectedHiddenItem = null;
                return
            }

            // 別カラムへのドラッグ中もドロップした際の結果と同じ表示になるよう調整
            if (to_column_type === 'hidden'){

                this.selectedHiddenItem = null;

                if (this.showHeaderCustomData.length-1 <= this.selectedShowItem && this.showHeaderCustomData.length != 1){
                    this.tempSelectedShowItem = this.selectedShowItem-1
                } else {
                    this.tempSelectedShowItem = this.selectedShowItem+1;
                }
            } else {
                this.selectedShowItem = null;

                if (this.hiddenHeaderCustomData.length-1 <= this.selectedHiddenItem && this.hiddenHeaderCustomData.length != 1){
                    this.tempSelectedHiddenItem = this.selectedHiddenItem-1
                } else {
                    this.tempSelectedHiddenItem = this.selectedHiddenItem+1;
                }
            }

        },
        // リストのitemをドロップしたときに呼ばれるイベント
        onEnd(e) {

            this.beforeSelectedShowItem = null;
            this.beforeSelectedHiddenItem = null;
            this.tempSelectedShowItem = null;
            this.tempSelectedHiddenItem = null;

            let from_column_type = e.from.getAttribute('data-column-type');
            let to_column_type = e.to.getAttribute('data-column-type');

            // 同一カラム間の移動
            if (from_column_type === to_column_type){
                if (to_column_type === 'hidden'){
                    this.selectedHiddenItem = e.newIndex;
                } else {
                    this.selectedShowItem = e.newIndex;
                }
                return
            }

            // 別カラムへの移動
            if (to_column_type === 'hidden'){
                this.selectedHiddenItem = e.newIndex;

                if (this.showHeaderCustomData.length <= this.selectedShowItem && this.showHeaderCustomData.length != 0){
                    this.selectedShowItem = this.showHeaderCustomData.length-1
                }
            } else {
                this.selectedShowItem = e.newIndex;

                if (this.hiddenHeaderCustomData.length <= this.selectedHiddenItem && this.hiddenHeaderCustomData.length != 0){
                    this.selectedHiddenItem = this.hiddenHeaderCustomData.length-1
                }
            }
        },
        pullFunction() {
            return this.controlOnStart ? 'clone' : true;
        },
        start({ originalEvent }) {
            this.controlOnStart = originalEvent.ctrlKey;
        },
        cancel() {
            this.prependFunctionToCancel()
            this.headerCustomModal = false;
        },
        leftRightHeaderColumn(type) {
            if (type === 'hidden'){
                // showからhiddenの末尾へコピー
                this.hiddenHeaderCustomData.push(this.showHeaderCustomData[this.selectedShowItem]);
                // showから削除
                this.showHeaderCustomData.splice(this.selectedShowItem, 1);
                // hiddenの参照項目の変更
                this.selectedHiddenItem = this.hiddenHeaderCustomData.length-1;
                // showの参照項目の変更
                if (this.showHeaderCustomData.length <= this.selectedShowItem && this.showHeaderCustomData.length != 0){
                    this.selectedShowItem = this.showHeaderCustomData.length-1
                }
            } else {
                // hiddenからshowの末尾へコピー
                this.showHeaderCustomData.push(this.hiddenHeaderCustomData[this.selectedHiddenItem]);
                // hiddenから削除
                this.hiddenHeaderCustomData.splice(this.selectedHiddenItem, 1);
                // showの参照項目の変更
                this.selectedShowItem = this.showHeaderCustomData.length-1;
                // hiddenの参照項目変更
                if (this.hiddenHeaderCustomData.length <= this.selectedHiddenItem && this.hiddenHeaderCustomData.length != 0){
                    this.selectedHiddenItem = this.hiddenHeaderCustomData.length-1
                }
            }
            this.scrollToEnd(type);
        },
        scrollToTop: function() {
            let self = this
            self.$nextTick(function () {
                var hidden_container = this.$refs['header-custom-hidden-list'];
                var show_container = this.$refs['header-custom-show-list'];
                hidden_container.scrollTop = 0;
                show_container.scrollTop = 0;
            });
        },
        scrollToEnd: function(type) {
            let self = this
            var container = null
            self.$nextTick(function () {
                if (type === 'hidden'){
                    container = this.$refs['header-custom-hidden-list'];
                } else {
                    container = this.$refs['header-custom-show-list'];
                }
                container.scrollTop = container.scrollHeight;
            });
        },
        upDownShowHeader(type) {
            let index = this.selectedShowItem;

            let array = this.showHeaderCustomData;

            if (type === 'up'){
                array.splice(index-1, 2, array[index], array[index-1]);
                this.selectedShowItem = index-1;
            } else {
                array.splice(index, 2, array[index+1], array[index]);
                this.selectedShowItem = index+1;
            }

            this.showHeaderCustomData = array;

        },
        async changeEducationMode () {
            // 通常モード ⇒ 教育割振りモードへの切替え
            const message = '<h4>' + this.$t('allocations.list.confirm_switch_to_educational_mode') + '</h4>'
                            + '<div style="color: red;">' + this.$t('allocations.list.note_switch_to_educational_mode_1') +'</div>'
                            + '<div style="color: red;">' + this.$t('allocations.list.note_switch_to_educational_mode_2') + '</div>'
            if (await this.$refs.confirm.show(message)) {
                /**
                 * OKの場合
                 */
                // 割振一覧（教育）画面へ遷移
                window.location.href = '/education/allocations'
            }
        },
        openEdit(element) {
            eventHub.$emit('openEdit', element)
        },
        openOrderItemEdit () {
            window.location.href = '/order/orders/' + this.orderId + '/item/edit/'
        }
    }
}
</script>
<style scoped>
.handle {
  float: left;
  padding-top: 8px;
  padding-bottom: 8px;
}
.v-text-field {
  padding-top : 0px !important;
  margin-top : 0px !important;
}
input {
  display: inline-block;
  width: 70%;
}
.header-list {
    overflow-x: hidden;
    overflow-y: scroll;
}
.list-group-item {
    height: 40px;
    padding-top: 0px !important;
    padding-bottom: 0px !important;
}
.list-group {
    margin-bottom: 0px !important;
}
.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
</style>
