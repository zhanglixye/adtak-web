<template>
    <div class="elevation-1">
        <v-list dense>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>people</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content style="flex: none;">
                    <v-list-tile-title class="text-color-default">{{ $t('approvals.operator') }}</v-list-tile-title>
                </v-list-tile-content>
                <template v-if="canUseBusinessesCandidates">
                    <v-tooltip top>
                        <template v-if="isCandidateListDisplay">
                        <v-btn flat icon @click="isCandidateListDisplay = false" slot="activator">
                            <v-icon class="icon-color-default">keyboard_arrow_up</v-icon>
                        </v-btn>
                        <span>{{ $t('common.button.close') }}</span>
                        </template>
                        <template v-if="!isCandidateListDisplay">
                        <v-btn flat icon lighten-2 @click="isCandidateListDisplay = true" slot="activator">
                            <v-icon class="icon-color-default">keyboard_arrow_down</v-icon>
                        </v-btn>
                        <span>{{ $t('approvals.button.open_candidates') }}</span>
                        </template>
                    </v-tooltip>
                    <users-overview :candidates="businessesCandidates" :users="{allocated_user_ids:allocatedCandidateIds, completed_user_ids:[]}" :max-views="countToShow" :size="22" />
                </template>
                <v-spacer></v-spacer>
                <v-list-tile-action>
                    <v-btn icon ripple @click="openTableCustomModal">
                        <v-icon class="icon-color-default">settings</v-icon>
                    </v-btn>
                </v-list-tile-action>
            </v-list-tile>
        </v-list>
        <v-divider class="my-0"></v-divider>
        <!-- 担当者一覧表示 -->
        <div class="bg-white" v-show="isCandidateListDisplay">
            <template v-for="(user) in businessesCandidates">
                <businesses-candidates
                    :name="user.name"
                    :image-path="user.user_image_path"
                    :id="user.id"
                    :hidden-user-ids="hiddenUserIds"
                    :allocatedUserIds="allocatedCandidateIds"
                    :key="user.id"
                    @add="showUser"
                    @click-delete="hideUser"
                />
            </template>
        </div>
        <v-divider class="my-0"></v-divider>
        <!-- 横表示 Revert Commit ID:7b8904dbbde1e8e4762fcbbf3862e45e53a232da -->
        <!-- 縦表示 -->
        <div :class="['compare-table', 'scroll-table']" v-show="!isItemHeaderDisplay">
            <table>
                <thead>
                    <tr>
                        <!-- 固定 -->
                        <th class="text-right table-nomal-color">
                            {{ $t('approvals.operator') }}
                            <v-btn class="ma-0" flat icon small color="#636b6f" @click="collapseOperatorArea = !collapseOperatorArea">
                                <v-icon>
                                    <template v-if="collapseOperatorArea">mdi-arrow-expand-vertical</template>
                                    <template v-else>mdi-arrow-collapse-vertical</template>
                                </v-icon>
                            </v-btn>
                        </th>
                        <!-- 固定 -->
                        <!-- ピン留めの担当者 -->
                        <template v-for="(task, i) in activeTaskResults">
                            <th
                                v-show="!hiddenUserIds.includes(task.user_id) && headTaskId === task.task_id"
                                :key="'operator-header-pin-' + i"
                                :class="['bg-data-nomal','pinning', isCheckDoneTask(task) ? '' : 'colorFilter-base']"
                                :style="{minWidth : tableDataWidth + 'px', maxWidth : tableDataWidth + 'px'}"
                            >
                                <template v-if="collapseOperatorArea">
                                    <v-avatar :size="26" class="ma-1">
                                        <img :src="task['user_image_path']">
                                    </v-avatar>
                                    <span class="pr-3">{{ task['user_name'] }}</span>
                                </template>
                                <template v-else>
                                    <worker
                                        :task="task"
                                        :step-path="step_path"
                                        :icon-size="100"
                                        @click-pin="switchFirstTask"
                                        @click-delete="hideUser"
                                        :is-horizontal-orientation="isItemHeaderDisplay"
                                    />
                                </template>
                            </th>
                        </template>
                        <!-- ピン留めの担当者 -->
                        <!-- 担当者 -->
                        <template v-for="(task, i) in activeTaskResults">
                            <th
                                v-show="!hiddenUserIds.includes(task.user_id) && headTaskId !== task.task_id"
                                :key="'operator-header-' + i"
                                :class="['bg-data-nomal', isCheckDoneTask(task) ? '' : 'colorFilter-base']"
                                :style="{minWidth : tableDataWidth + 'px', maxWidth : tableDataWidth + 'px'}"
                            >
                                <template v-if="collapseOperatorArea">
                                    <v-avatar :size="26" class="ma-1">
                                        <img :src="task['user_image_path']">
                                    </v-avatar>
                                    <span class="pr-3">{{ task['user_name'] }}</span>
                                </template>
                                <template v-else>
                                    <worker
                                        :task="task"
                                        :step-path="step_path"
                                        :icon-size="100"
                                        @click-pin="switchFirstTask"
                                        @click-delete="hideUser"
                                        :is-horizontal-orientation="isItemHeaderDisplay"
                                    />
                                </template>
                            </th>
                        </template>
                        <!-- 担当者 -->
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(items, i) in itemConfigs">
                        <template v-for="idx in arrayMaxCount(items)">
                            <template v-for="(item, j) in removeItemsForGroupName(items)">
                                <tr
                                    v-show="idx <= notHiddenArrayMaxCount(items[0]['group'],idx)"
                                    :key="'operator-header-' + i + idx + j"
                                    :class="[changedRowIndexs.includes(i + ':'+ idx + ':' + j) ? 'selected' : '']"
                                >
                                    <!-- 項目名 -->
                                    <td
                                        ref="column"
                                        v-if="j === 0"
                                        :rowspan="removeItemsForGroupName(items).length"
                                        :style="{minWidth: itemNameWidth, maxWidth: itemNameWidth}"
                                        class="text-right bg-head-nomal table-nomal-color"
                                    >
                                        <!-- 初期表示 -->
                                        <template v-if="itemNameWidth === ''">
                                            <span>{{tryGetGroupName(items)}}</span>
                                        </template>
                                        <!-- 項目名が一定以上長い場合 -->
                                        <template v-else>
                                            <div style="text-align:right;"><span style="white-space:normal; text-align:left; display:inline-block">{{tryGetGroupName(items)}}</span></div>
                                        </template>
                                    </td>
                                    <!-- 項目名 -->
                                    <!-- ピン留め表示の作業内容 -->
                                    <template v-for="(task, k) in activeTaskResults">
                                        <!-- Note:
                                                checklist表示用の臨時対応
                                                作業内容JSONに合わせて表示しているので他とルールが異なる
                                        -->
                                        <td
                                            v-if="item['group'] === 'checklist'"
                                            v-show="!hiddenUserIds.includes(task.user_id) && headTaskId === task.task_id"
                                            :key="'operator-header-pin-' + k"
                                            @click="changeColor(i + ':'+ idx + ':' + j)"
                                            :class="[isCheckDoneTask(task) ? '' : 'colorFilter-base', 'work-content']"
                                            :style="{
                                                borderLeft: hasErrorForChecklist(item) ? '3px solid red' : 'none',
                                                minWidth : tableDataWidth + 'px',
                                                maxWidth : tableDataWidth + 'px'
                                            }"
                                        >
                                            <item-config
                                                v-if="!isNotWorking(task['content'])"
                                                :item="checklistItem(item)"
                                                :labelData="labelData"
                                                :content="checklistContent(task['content'], item['item_key'])"
                                                :path="step_path"
                                                :commnet-width="170"
                                                :dark="changedRowIndexs.includes(i + ':'+ idx + ':' + j)"
                                                :comparisonContents="getComparisonContent(item, idx)"
                                                :candidates="businessesCandidates"
                                                :filePreviewWidth.sync="filePreviewWidth"
                                                :localStorageKey="localStorageKey"
                                                :stepId="task['step_id']"
                                            ></item-config>
                                        </td>
                                        <td
                                            v-else
                                            v-show="!hiddenUserIds.includes(task.user_id) && headTaskId === task.task_id"
                                            :key="'operator-header-pin-' + k"
                                            @click="changeColor(i + ':'+ idx + ':' + j)"
                                            :class="[isCheckDoneTask(task) ? '' : 'colorFilter-base', 'work-content']"
                                            :style="{
                                                borderLeft: hasError(item, idx) ? '3px solid red' : 'none',
                                                minWidth : tableDataWidth + 'px',
                                                maxWidth : tableDataWidth + 'px'
                                            }"
                                        >
                                            <item-config
                                                v-if="!isNotWorking(task['content']) || isNecessarilyDisplay(item['group'], item['key'])"
                                                :item="item"
                                                :labelData="labelData"
                                                :content="content(idx, task['content'], item)"
                                                :path="step_path"
                                                :commnet-width="170"
                                                :dark="changedRowIndexs.includes(i + ':'+ idx + ':' + j)"
                                                :comparisonContents="getComparisonContent(item, idx)"
                                                :candidates="businessesCandidates"
                                                :filePreviewWidth.sync="filePreviewWidth"
                                                :localStorageKey="localStorageKey"
                                                :stepId="task['step_id']"
                                            ></item-config>
                                        </td>
                                    </template>
                                    <!-- /ピン留め表示の作業内容 -->
                                    <!-- ピン留め表示以外の作業内容 -->
                                    <template v-for="(task, k) in activeTaskResults">
                                        <!-- Note:
                                                checklist表示用の臨時対応
                                                作業内容JSONに合わせて表示しているので他とルールが異なる
                                        -->
                                        <td
                                            v-if="item['group'] === 'checklist'"
                                            v-show="!hiddenUserIds.includes(task.user_id) && headTaskId !== task.task_id"
                                            :key="'operator-header-' + k"
                                            @click="changeColor(i + ':'+ idx + ':' + j)"
                                            :class="[isCheckDoneTask(task) ? '' : 'colorFilter-base', 'work-content']"
                                            :style="{
                                                borderLeft: (isHeadTaskResult(task) && hasErrorForChecklist(item)) ? '3px solid red' : 'none',
                                                minWidth : tableDataWidth + 'px',
                                                maxWidth : tableDataWidth + 'px'
                                            }"
                                        >
                                            <item-config
                                                v-if="!isNotWorking(task['content'])"
                                                :item="checklistItem(item)"
                                                :labelData="labelData"
                                                :content="checklistContent(task['content'], item['item_key'])"
                                                :path="step_path"
                                                :commnet-width="170"
                                                :dark="changedRowIndexs.includes(i + ':'+ idx + ':' + j)"
                                                :comparisonContents="getComparisonContent(item, idx)"
                                                :candidates="businessesCandidates"
                                                :filePreviewWidth.sync="filePreviewWidth"
                                                :localStorageKey="localStorageKey"
                                                :stepId="task['step_id']"
                                            ></item-config>
                                        </td>
                                        <td
                                            v-else
                                            v-show="!hiddenUserIds.includes(task.user_id) && headTaskId !== task.task_id"
                                            :key="'operator-header-' + k"
                                            @click="changeColor(i + ':'+ idx + ':' + j)"
                                            :class="[isCheckDoneTask(task) ? '' : 'colorFilter-base', 'work-content']"
                                            :style="{
                                                borderLeft: (isHeadTaskResult(task) && hasError(item, idx)) ? '3px solid red' : 'none',
                                                minWidth : tableDataWidth + 'px',
                                                maxWidth : tableDataWidth + 'px'
                                            }"
                                        >
                                            <item-config
                                                v-if="!isNotWorking(task['content']) || isNecessarilyDisplay(item['group'], item['key'])"
                                                :item="item"
                                                :labelData="labelData"
                                                :content="content(idx, task['content'], item)"
                                                :path="step_path"
                                                :commnet-width="170"
                                                :dark="changedRowIndexs.includes(i + ':'+ idx + ':' + j)"
                                                :comparisonContents="getComparisonContent(item, idx)"
                                                :candidates="businessesCandidates"
                                                :filePreviewWidth.sync="filePreviewWidth"
                                                :localStorageKey="localStorageKey"
                                                :stepId="task['step_id']"
                                            ></item-config>
                                        </td>
                                    </template>
                                    <!-- /ピン留め表示以外の作業内容 -->
                                </tr>
                            </template>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
        <!-- /縦表示 -->
        <TableCustomModal :width.sync="width" ref="tableCustomModal" />
        <mail-preview-dialog ref="mailPreviewDialog"></mail-preview-dialog>
    </div>
</template>

<script>
import ItemConfig from '../../../Molecules/Tasks/ItemConfig'
import Worker from './Worker'
import TableCustomModal from './TableCustomModal'
import UsersOverview from '../../../Molecules/Users/UsersOverview'
import BusinessesCandidates from './BusinessesCandidates'
import MailPreviewDialog from '../../../Atoms/Dialogs/MailPreviewDialog'
import store from '../../../../stores/Admin/Approvals/Detail/store'

export default {
    components: {
        ItemConfig,
        Worker,
        TableCustomModal,
        UsersOverview,
        BusinessesCandidates,
        MailPreviewDialog,
    },
    props: {
        shortDisplay: {type: Boolean, require: false, default: false}
    },
    data: () => ({
        step_path: '',
        isItemHeaderDisplay: false,
        isCandidateListDisplay: false,
        headTaskId: null,
        hiddenUserIds: [],
        changedRowIndexs: [],
        width: store.state.defaultTableDataWidth,
        tableDataWidth: store.state.defaultTableDataWidth,
        filePreviewWidth: store.state.defaultFilePreviewWidth,
        collapseOperatorArea: false,
        itemNameWidth: ''// 項目名の横幅指定
    }),
    watch: {
        width() {
            this.tableDataWidth = this.width < store.state.minTableDataWidth ? store.state.minTableDataWidth : this.width
            store.dispatch('setAppearanceState',({stepId: this.request.step_id, val: {tableDataWidth: this.tableDataWidth}}))
        },
        filePreviewWidth (val) {
            store.dispatch('setAppearanceState',({stepId: this.request.step_id, val: {filePreviewWidth: val}}))
        }
    },
    computed: {
        activeTaskResults() {
            return this.taskResults.filter(task => this.isActiveTask(task))
        },
        countToShow() {
            return this.shortDisplay ? 8 : 24
        },
        edit() {
            return JSON.parse(JSON.stringify(store.state.processingData.edit))
        },
        request() {
            return JSON.parse(JSON.stringify(store.state.processingData.request))
        },
        taskResults() {
            return JSON.parse(JSON.stringify(store.state.processingData.taskResults))
        },
        itemConfigs() {
            return JSON.parse(JSON.stringify(store.state.processingData.itemConfigs))
        },
        businessesCandidates() {
            return JSON.parse(JSON.stringify(store.state.processingData.businessesCandidates))
        },
        canUseBusinessesCandidates() {
            return store.state.processingData.canUseBusinessesCandidates
        },
        labelData() {
            return JSON.parse(JSON.stringify(store.state.processingData.labelData))
        },
        allocatedCandidateIds() {

            // 現在割り振られているユーザIDの配列を生成
            return this.taskResults.map(task => task.user_id).filter((x, i, self) => self.indexOf(x) === i)
        },
        headTaskResultOrUndefined () {
            // 表示されている先頭のタスク（初期表示順）
            const taskOrUndefined = this.activeTaskResults.find(activeTask => !this.hiddenUserIds.includes(activeTask.user_id))
            if (taskOrUndefined === undefined) return undefined//画面にタスクが表示されていない

            // ピン止めが無い
            if (this.headTaskId === null) return taskOrUndefined

            // ピン止めタスクが非表示の場合で先頭のタスクと同じか比較
            const pinTask = this.activeTaskResults.find(activeTask => this.headTaskId === activeTask.task_id)
            const isHiddenPinTask = this.hiddenUserIds.includes(pinTask.user_id)
            return isHiddenPinTask ?  taskOrUndefined : pinTask
        },
        localStorageKey () {
            return store.state.localStorageKey
        },
    },
    created () {

        // 幅の取得
        this.width = Object.prototype.hasOwnProperty.call(store.getters.getAppearanceState(this.request.step_id), 'tableDataWidth')? store.getters.getAppearanceState(this.request.step_id)['tableDataWidth']: store.state.defaultTableDataWidth
        this.filePreviewWidth = Object.prototype.hasOwnProperty.call(store.getters.getAppearanceState(this.request.step_id), 'filePreviewWidth')? store.getters.getAppearanceState(this.request.step_id)['filePreviewWidth']: store.state.defaultFilePreviewWidth
        this.step_path = 'biz.' + this.request.step_url.replace('/', '.')

        let self = this
        // メールプレビュー
        eventHub.$on('showMailPreview', function(params) {
            const mail = self.taskResults.find(taskResult => taskResult.contact_mail && taskResult.contact_mail.id == params.mailId).contact_mail
            self.$refs.mailPreviewDialog.show(mail)
        })
    },
    mounted() {
        // 項目名が一定の幅を超えている場合の制御
        const limitWidth = store.state.itemNameWidth
        if (this.$refs.column[0].clientWidth > limitWidth) {
            this.itemNameWidth = `${limitWidth}px`
        }
    },
    methods: {
        removeItemsForGroupName(items) {
            return items.filter(item => item.item_type !== null)
        },
        tryGetGroupName(items) {
            if (items.length === 0) throw 'Array length is 0'
            const localLabelData = this.labelData[Vue.i18n.locale()]
            let item = items.length > 1 ? items.find(item => item.item_type === null) : items[0] // groupがない場合はその要素のラベルが項目名となる
            if (item === null) throw 'Item is null'
            return localLabelData[item.label_id]
        },
        isNecessarilyDisplay(itemGroup, itemKey) {
            return itemGroup === 'results' && itemKey === 'type'
        },
        isNotWorking(taskContent) {
            return taskContent === null
        },
        isActiveTask({is_active}) {
            return is_active === _const.FLG.ACTIVE
        },
        isCheckDoneTask({status, is_active}) {
            return status === _const.TASK_STATUS.DONE && is_active === _const.FLG.ACTIVE
        },
        openTableCustomModal() {
            this.$refs.tableCustomModal.show()
        },
        // おそらく二つの素材があるとダメ
        changeColor(val) {
            if (this.changedRowIndexs.includes(val)){ // ある場合
                this.changedRowIndexs = this.changedRowIndexs.filter(item => item !== val)
            } else { // 無い場合
                this.changedRowIndexs.push(val)
            }
        },
        switchFirstTask({task_id}) {
            // すでにセットされている場合は、nullの代入
            this.headTaskId = (this.headTaskId === task_id)? null : task_id
        },
        showUser(id) {
            this.hiddenUserIds = this.hiddenUserIds.filter(userId => userId !== id)
        },
        hideUser({user_id}){
            if (!this.hiddenUserIds.includes(user_id)) this.hiddenUserIds.push(user_id)
        },
        openTaskResult (result) {
            let uri = '/' + ['biz', this.request.step_url, result.request_work_id, result.task_id, 'create'].join('/')
            window.open(uri)
        },
        arrayMaxCount (items) {
            const item = items.find(item => item.item_type !== null)// グループではないitemを1つ選択
            if (item === undefined) return 0
            const itemGroup = item['group']
            const itemFullKey = item['item_key']

            // グループに所属していない場合
            if (itemGroup === itemFullKey) return 1

            // 表示対象の処理完了タスクの配列を生成
            const workingTaskResults = this.activeTaskResults.filter(taskResult => !this.isNotWorking(taskResult.content))
            if (workingTaskResults.length === 0) return 1

            if (!Array.isArray(workingTaskResults[0].content[itemGroup])) {
                return 1
            }

            let max = 1;
            workingTaskResults.forEach(function (taskResult) {
                if (max < taskResult.content[itemGroup].length) {
                    max = taskResult.content[itemGroup].length
                }
            })
            return max
        },
        notHiddenArrayMaxCount (groupName) {
            let max = 1;

            // 画面に表示されている、未処理タスク以外の配列を生成
            const workingTaskResults = this.activeTaskResults.filter(taskResult => !this.isNotWorking(taskResult.content) && !this.hiddenUserIds.includes(taskResult.user_id))
            if (workingTaskResults.length === 0) return max

            if (!Array.isArray(workingTaskResults[0].content[groupName])) {
                return max
            }
            workingTaskResults.forEach(function (taskResult) {
                if (max < taskResult.content[groupName].length) {
                    max = taskResult.content[groupName].length
                }
            })
            return max
        },
        hasError (item, groupIdx = 1) {
            if (!item) return false

            // 比較対象のtasks
            const taskResults = store.getters.comparisonActiveTaskResults.filter(task => this.isCheckDoneTask(task) && !this.hiddenUserIds.includes(task.user_id))

            // 比較対象がない為、終了
            if (taskResults.length === 0) return false

            // 比較基準となるtask
            const baseTask = taskResults[0]

            // 個人のチェック結果を取り出す
            let array = taskResults.filter((task) => {


                // 対象外のitem_typeはスキップ
                if (item.item_type === _const.ITEM_CONFIG_TYPE.IMAGE) return true

                // group と item_keyが同じ場合
                if (item.group == item.item_key) {
                    // 比較基準
                    const comparisonCriteria = baseTask.content[item.group]

                    // 比較対象
                    const compareTo = task.content[item.group]

                    // 比較
                    return JSON.stringify(compareTo) == JSON.stringify(comparisonCriteria)
                } else {

                    try {

                        // item.gourpで配列もしくはオブジェクトが取得されるのでitem.keyでプロパティを比較

                        // 比較基準のオブジェクト
                        let objectForComparisonCriteria = null

                        // 比較対象のオブジェクト
                        let objectBeingCompared = null

                        // 値のセットを行う
                        if (task.content[item.group] instanceof Array) {

                            // groupIdxを配列番号に変換
                            const indexToCompare = groupIdx - 1
                            objectForComparisonCriteria = baseTask.content[item.group][indexToCompare]
                            objectBeingCompared = task.content[item.group][indexToCompare]

                        } else if (typeof task.content[item.group] === 'object') {

                            objectForComparisonCriteria = baseTask.content[item.group]
                            objectBeingCompared = task.content[item.group]

                        } else {
                            throw '比較処理 : 対象はありません'
                        }

                        // 互いに対象オブジェクトがundefinedもしくはnullの場合
                        if ((objectForComparisonCriteria === null || objectForComparisonCriteria === undefined)
                            && objectForComparisonCriteria === objectBeingCompared) {
                            return true
                        } else {
                            // プロパティが両方無い場合は比較できないので処理を終了
                            if ( !(item.key in objectForComparisonCriteria) && !(item.key in objectBeingCompared) ) return true

                            // 比較
                            const comparisonCriteria = objectForComparisonCriteria[item.key]
                            const beingCompared = objectBeingCompared[item.key]
                            return JSON.stringify(comparisonCriteria) == JSON.stringify(beingCompared)
                        }
                    } catch (error) {
                        console.log('Method: hasError')
                        console.log(error)
                        return false
                    }
                }
            })
            return array.length !== taskResults.length
        },
        getComparisonContent (item, tableIndex=1) {
            let TaskResults = []
            const processTargetItemIds = [_const.ITEM_CONFIG_TYPE.FILE_INPUT, _const.ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT]
            if (!processTargetItemIds.includes(item.item_type)) return []

            // 画面にて非表示となっているコンテントは削除
            TaskResults = this.activeTaskResults.filter(activeTaskResult => !this.hiddenUserIds.includes(activeTaskResult.user_id))

            // ピン止めを先頭に格納
            for (const idx in TaskResults) {
                if (this.headTaskId === TaskResults[idx].task_id) {
                    const firstContent = TaskResults[idx]
                    TaskResults.splice(idx, 1)
                    TaskResults.unshift(firstContent)
                }
            }

            // 必要な情報を抽出
            const arrayIndex = tableIndex - 1// テーブルのインデックス

            const comparisonContents = []
            for (const content of TaskResults) {
                if (content['content'] === null || content['content'] === undefined) {
                    continue
                }
                const taskContent = content['content']
                if (item.item_type === _const.ITEM_CONFIG_TYPE.FILE_INPUT) {
                    if (Array.isArray(taskContent[item.group])) {
                        // 配列の場合
                        // グループに所属している
                        if (item.group !== item.key) {// 所属している
                            for (const gi in taskContent[item.group]) {
                                if (!(item.key in taskContent[item.group][gi])) continue
                                for (const ki in taskContent[item.group][gi][item.key]) {
                                    taskContent[item.group][gi][item.key][ki].user_id = content.user_id
                                    comparisonContents.push(taskContent[item.group][gi][item.key][ki])
                                }
                            }
                        } else {// 所属していない
                            for (const index in taskContent[item.group]) {
                                taskContent[item.group][index].user_id = content.user_id
                                comparisonContents.push(taskContent[item.group][index])
                            }
                        }
                    } else if  (typeof taskContent[item.group] === 'object') {
                        // オブジェクトの場合
                        if (taskContent[item.group][item.key] !== null && taskContent[item.group][item.key] !== undefined) {
                            for (const index in taskContent[item.group][item.key]) {
                                taskContent[item.group][item.key][index].user_id = content.user_id
                                comparisonContents.push(taskContent[item.group][item.key][index])
                            }
                        }
                    } else {
                        // オブジェクト以外の場合
                        comparisonContents.push([taskContent])
                    }
                } else if (item.item_type === _const.ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT) {
                    if (Array.isArray(taskContent[item.group])) {
                        // 配列の場合
                        if (arrayIndex in taskContent[item.group]) {
                            const value = taskContent[item.group][arrayIndex][item.key]
                            if (value !== null && value !== undefined) {
                                value.user_id = content.user_id
                                comparisonContents.push(value)
                            }
                        }
                    } else if  (typeof taskContent[item.group] === 'object') {
                        // オブジェクトの場合
                        if (taskContent[item.group] !== null) {
                            if (item.key === item.group) {
                                taskContent[item.group].user_id = content.user_id
                                comparisonContents.push(taskContent[item.group])
                            } else if (item.key in taskContent[item.group] && taskContent[item.group][item.key] !== null) {
                                taskContent[item.group][item.key].user_id = content.user_id
                                comparisonContents.push(taskContent[item.group][item.key])
                            }
                        }
                    }
                }
            }
            return comparisonContents
        },
        content (idx, taskContent, item) {
            const itemGroup = item['group']
            const itemKey = item['item_key']

            if (taskContent === null) {
                if (itemGroup === 'results') {
                    // 未作業でも処理結果を表示させるため
                    return {type: _const.TASK_RESULT_TYPE.NOT_WORKING}
                }
                return taskContent
            }

            if (Array.isArray(taskContent[itemGroup])) {
                // 配列の場合
                // removeItemsForGroupName メソッドでグループを表すitemは無いため、keyとgroupで比較している
                if (itemKey === itemGroup) return taskContent // グループに所属していない場合
                return taskContent[itemGroup][idx-1]
            } else if  (typeof taskContent[itemGroup] === 'object') {
                // オブジェクトの場合
                if (itemKey === itemGroup) {
                    if (item.item_type === _const.ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT) return taskContent
                }
                // [ADPORTER_PF-350] 新経費申請業務の不明点情報を格納(暫定対応)
                if (itemGroup === 'results' && this.request.step_id == 11) {
                    taskContent[itemGroup].contact = { C00400_10: taskContent.G00000_1.C00400_10, C00200_11: taskContent.G00000_1.C00200_11 }
                }
                return taskContent[itemGroup]
            } else {
                // オブジェクト以外の場合
                return taskContent
            }
        },
        checklistContent (taskContent, item_key) {
            let content = Object.assign({}, taskContent)
            let keys = item_key.split('/')
            for (let i = 1, len = keys.length; i < len; ++i) {
                if (keys[i] in content) {
                    content = content[keys[i]]
                }
            }
            return content
        },
        checklistItem (item) {
            let checklistItem = Object.assign({}, item)
            checklistItem['key'] = 'value'
            return checklistItem
        },
        hasErrorForChecklist (item) {
            let comparisonActiveTaskResults = store.getters.comparisonActiveTaskResults.filter(task => this.isCheckDoneTask(task) && !this.hiddenUserIds.includes(task.user_id))
            if (comparisonActiveTaskResults.length < 1) return false

            let base = this.checklistContent(comparisonActiveTaskResults[0]['content'], item['item_key'])

            for (let i = 1, len = comparisonActiveTaskResults.length; i < len; ++i) {
                let compare = this.checklistContent(comparisonActiveTaskResults[i]['content'], item['item_key'])
                if (base.value !== compare.value) {
                    return true
                }
            }
            return false
        },
        isHeadTaskResult (task) {
            return this.headTaskResultOrUndefined !== undefined && this.headTaskResultOrUndefined.task_id === task.task_id
        }
    }
}
</script>

<style scoped>
.text-color-default {
    color: #757575;
}

.bg-white {
    background-color: white;
}

/* ピン留め時 */
.pinning >>> .v-icon.pin-icon {
    color: #4DB6AC;
}

i.icon-color-default.v-icon.icon-color-default.material-icons {
    color: rgba(0,0,0,.54);
}
/* 同項目を選択後 */
.compare-table table tr.selected{
    background-color: #9e9e9e;
}

/* /同項目を選択後 */

.item-name {
    font-size: 11px;
    color: rgba(0,0,0,.54);
}

/* compare-table */
.compare-table table {
    /* 各グループのleft or rightの縦線が
    グループ内の下の要素のborderに影響を与えるので
    以下の値にしている
    */
    border-collapse: separate;
    border-spacing: 0px;
}

/* teble font color */
.table-nomal-color{
    color: #757575;
}

/* usercomponent */
.compare-table >>> .head-border {
    border-color: #757575;
}

.compare-table table td,
.compare-table table th {
    padding: 10px;
    text-align: center;
    border: solid 0.5px #f2f2f2;
    font-weight: normal;
}

.compare-table table tr {
    background-color: #ffffff;
}

.compare-table table td.text-right,
.compare-table table th.text-right{
    text-align: right;
}

.compare-table table th {
    background-color: #fafafa;
    border: solid 0.5px #d7d7d7;
}

/* 縦表示のユーザ表示行 */
.compare-table table th.bg-data-nomal {
    background-color: #ffffff;
    border-left: none;
    border-right: solid 0.5px #f2f2f2;
    border-bottom: solid 0.5px #f2f2f2;
}

/* 縦表示の項目 */
.compare-table table td.bg-head-nomal {
    background-color: #fafafa;
    border-top: none;
    border-left: solid 0.5px #d7d7d7;
    border-right: solid 0.5px #d7d7d7;
    border-bottom: solid 0.5px #d7d7d7;
}

/* 未作業時の色 */
.compare-table table .colorFilter-base,
.compare-table table th.colorFilter-base{
    background-color: hsla(0, 0%, 0%, 0.2)
}

.compare-table table .colorFilter-base >>> .user-image,
.compare-table table .colorFilter-base >>> .user-name {
    opacity: 0.5;    /* カラーフィルタ効果の度合いを指定 */
}

/* scroll-table */
.scroll-table table {
    display: block;
    overflow-x: auto;
    overflow-y: auto;
    white-space: nowrap;
}

/* item-header-table */

/* operator-header-table */
.operator-header-table table {
    display: flex;
}
.operator-header-table table thead {
    display: block;
    float: left;
    width: 30%;
}
.operator-header-table table tbody {
    display: block;
    float: left;
    width: 70%;
}
.operator-header-table table tr {
    display: block;
    float: left;
}
.operator-header-table table thead tr {
    width: 100%;
}
.operator-header-table table tbody tr {
    width: 30%;
}
.operator-header-table table tr td,
.operator-header-table table tr th {
    display: block;
}
</style>
