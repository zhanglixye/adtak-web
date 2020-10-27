<template>
    <div class="elevation-1">
        <v-list dense>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>compare</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content style="flex: none;">
                    <v-list-tile-title class="text-color-default">{{ $t('verification.verification') }}</v-list-tile-title>
                </v-list-tile-content>
                <v-spacer></v-spacer>
                <v-list-tile-action>
                    <v-btn icon ripple @click="showSettingDialog()">
                        <v-icon class="icon-color-default">settings</v-icon>
                    </v-btn>
                </v-list-tile-action>
            </v-list-tile>
        </v-list>
        <!-- 横表示 Revert Commit ID:7b8904dbbde1e8e4762fcbbf3862e45e53a232da -->
        <!-- 縦表示 -->
        <div :class="['compare-table', 'scroll-table']">
            <table>
                <thead>
                    <tr>
                        <!-- 固定 -->
                        <th class="text-right table-nomal-color"></th>
                        <!-- 固定 -->
                        <!-- 納品データ -->
                        <th
                            :class="['bg-data-nomal', 'separate-left']"
                            :style="{minWidth : deliveryWidth + 'px', maxWidth : deliveryWidth + 'px'}"
                        >
                            <span>{{ $t('verification.delivery_data') }}</span>
                        </th>
                        <th :class="['bg-data-nomal', 'separate']"></th>
                        <th
                            :class="['bg-data-nomal', 'col-work-result', 'separate-right']"
                            :style="{minWidth : myTaskWidth + 'px', maxWidth : myTaskWidth + 'px'}"
                        >
                            <v-tooltip top class="mouse-hover-pointer">
                                <template slot="activator">
                                    <div>
                                        <v-avatar :size="26" class="ma-1">
                                            <img :src="verificationTaskResult['user_image_path']" @click="openTaskResult(verificationTaskResult)">
                                        </v-avatar>
                                        <span class="pr-3" @click="openTaskResult(verificationTaskResult)">{{ verificationTaskResult['user_name'] }}</span>
                                    </div>
                                </template>
                                <span v-html="$t('verification.window_open.confirm_task_result')"></span>
                            </v-tooltip>
                        </th>
                        <th
                            :class="['bg-data-nomal', 'col-work-result']"
                            :style="{minWidth : noteWidth + 'px', maxWidth : noteWidth + 'px'}"
                        >
                            <template>
                                <v-avatar :size="26" class="ma-1">
                                    <v-icon color="grey">mdi-pencil</v-icon>
                                </v-avatar>
                                <span class="pr-3">{{ $t('verification.note') }}</span>
                            </template>
                        </th>
                        <!-- 担当者 -->
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(items, i) in itemConfigs">
                        <template v-for="idx in arrayMaxCount(items)">
                            <template v-for="(item, j) in removeItemsForGroupName(items)">
                                <tr
                                    :key="'operator-header-' + i + idx + j"
                                    :style="{backgroundColor: isNecessarilyDisplay(item['group'], item['key']) ? 'rgba(130,177,255, 0.25)' : ''}"
                                >
                                    <!-- 項目名 -->
                                    <td
                                        ref="column"
                                        v-if="j === 0"
                                        :rowspan="removeItemsForGroupName(items).length"
                                        :style="{minWidth: itemNameWidth, maxWidth: itemNameWidth}"
                                        class="text-right bg-head-nomal table-nomal-color"
                                    >
                                        <!-- 項目 -->
                                        <template v-if="itemNameWidth === ''">
                                            <span>{{ isNecessarilyDisplay(item['group'], item['key']) ? $t('verification.result_and_total_comment') : tryGetGroupName(items) }}</span>
                                        </template>
                                        <!-- 項目名が一定以上長い場合 -->
                                        <template v-else>
                                            <div style="text-align:right;">
                                                <span style="white-space:normal; text-align:left; display:inline-block">
                                                    {{ isNecessarilyDisplay(item['group'], item['key']) ? $t('verification.result_and_total_comment') : tryGetGroupName(items) }}
                                                </span>
                                            </div>
                                        </template>
                                    </td>
                                    <!-- 項目名 -->
                                    <td
                                        :class="['separate-left']"
                                        :style="{
                                            minWidth : deliveryWidth + 'px',
                                            maxWidth : deliveryWidth + 'px'
                                        }"
                                    >
                                        <!-- Note:
                                                checklist表示用の臨時対応
                                                作業内容JSONに合わせて表示しているので他とルールが異なる
                                        -->
                                        <item-config
                                            v-if="item['group'] === 'checklist'"
                                            :item="checklistItem(item)"
                                            :labelData="labelData"
                                            :content="checklistContent(deliveredTaskResult['content'], item['item_key'])"
                                            :path="stepPath"
                                            :commnet-width="170"
                                            :comparisonContents="getComparisonContent(item, idx)"
                                            :candidates="businessesCandidates"
                                            color="primary"
                                            :filePreviewWidth.sync="filePreviewWidth"
                                            :localStorageKey="localStorageKey"
                                            :stepId="verificationTaskResult['step_id']"
                                            :disabled="true"
                                        ></item-config>
                                        <item-config
                                            v-else
                                            :item="item"
                                            :labelData="labelData"
                                            :content="content(idx, deliveredTaskResult['content'], item)"
                                            :path="stepPath"
                                            :commnet-width="170"
                                            :comparisonContents="getComparisonContent(item, idx)"
                                            :candidates="businessesCandidates"
                                            color="primary"
                                            :filePreviewWidth.sync="filePreviewWidth"
                                            :localStorageKey="localStorageKey"
                                            :stepId="verificationTaskResult['step_id']"
                                            :disabled="true"
                                        ></item-config>
                                    </td>
                                    <td :class="['bg-white', 'separate']"></td>
                                    <td
                                        :class="[getClassNameWorkResult(item, idx), 'col-work-result', 'separate-right']"
                                        :style="{
                                            minWidth: myTaskWidth + 'px',
                                            maxWidth: myTaskWidth + 'px'
                                        }"
                                    >
                                        <!-- Note:
                                                checklist表示用の臨時対応
                                                作業内容JSONに合わせて表示しているので他とルールが異なる
                                        -->
                                        <item-config
                                            v-if="item['group'] === 'checklist'"
                                            :item="checklistItem(item)"
                                            :labelData="labelData"
                                            :content="checklistContent(deliveredTaskResult['content'], item['item_key'])"
                                            :path="stepPath"
                                            :commnet-width="170"
                                            :comparisonContents="getComparisonContent(item, idx)"
                                            :candidates="businessesCandidates"
                                            color="primary"
                                            :filePreviewWidth.sync="filePreviewWidth"
                                            :localStorageKey="localStorageKey"
                                            :stepId="verificationTaskResult['step_id']"
                                            :disabled="true"
                                        ></item-config>
                                        <item-config
                                            v-else
                                            :item="item"
                                            :labelData="labelData"
                                            :content="content(idx, verificationTaskResult['content'], item)"
                                            :path="stepPath"
                                            :commnet-width="170"
                                            :comparisonContents="getComparisonContent(item, idx)"
                                            :candidates="businessesCandidates"
                                            color="primary"
                                            :filePreviewWidth.sync="filePreviewWidth"
                                            :localStorageKey="localStorageKey"
                                            :stepId="verificationTaskResult['step_id']"
                                            :disabled="true"
                                        ></item-config>
                                    </td>
                                    <!-- 編集可能な状態出ないと編集ダイアログもアイコンも表示されない -->
                                    <td
                                        :style="{minWidth : noteWidth + 'px', maxWidth : noteWidth + 'px'}"
                                        :class="['text-xs-left', 'edit-note', edit ? 'cursor-pointer' : '', 'col-work-result']"
                                        @click="enterCommentIfNeeded(getComment(item, idx), item, idx)"
                                    >
                                        <!-- TODO: -webkit-line-clampに渡す値を可変にできるか？ -->
                                        <div :style="{display : '-webkit-box', '-webkit-box-orient': 'vertical', '-webkit-line-clamp': 3, overflow: 'hidden'}">
                                            <template>
                                                <span style="color: #757575;font-size: 12px;">
                                                    {{ getComment(item, idx) }}
                                                </span>
                                            </template>
                                        </div>
                                        <div v-if="edit" class="edit-note-icon">
                                            <v-tooltip right>
                                                <v-icon
                                                    slot="activator"
                                                    color="grey"
                                                >
                                                    mdi-pencil
                                                </v-icon>
                                                <span>{{ $t('verification.click_to_enter') }}</span>
                                            </v-tooltip>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>
        <!-- /縦表示 -->
        <setting-dialog ref="settingDialog"></setting-dialog>
        <editor-dialog
            :title="$t('verification.note')"
            :placeholder="$t('verification.enter_comment')"
            ref="editorDialog"
        ></editor-dialog>
    </div>
</template>

<script>
import ItemConfig from '../../../Molecules/Tasks/ItemConfig'
import SettingDialog from './SettingDialog'
import store from '../../../../stores/Work/Verification/store'
import EditorDialog from '../../../Atoms/Dialogs/EditorDialog'

export default {
    components: {
        ItemConfig,
        SettingDialog,
        EditorDialog,
    },
    props: {
        shortDisplay: {type: Boolean, require: false, default: false}
    },
    data: () => ({
        stepPath: '',
        itemNameWidth: ''// 項目名の横幅指定
    }),
    computed: {
        deliveredTaskResult() {
            return this.activeTaskResults.find(task => task.user_id === this.request.approved_user_id)
        },
        verificationTaskResult() {
            return this.activeTaskResults.find(task => task.task_id == store.state.processingData.inputs.task_id)// inputsはstringの値のため、==
        },
        activeTaskResults() {
            return this.taskResults.filter(task => this.isActiveTask(task))
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
        labelData() {
            return JSON.parse(JSON.stringify(store.state.processingData.labelData))
        },
        taskComment : {
            set (val) {
                store.commit('setProcessingData', {taskComment: val})
            },
            get () {
                return JSON.parse(JSON.stringify(store.state.processingData.taskComment))
            }
        },
        deliveryWidth() {
            return store.state.processingWidth.delivery
        },
        myTaskWidth() {
            return store.state.processingWidth.myTask
        },
        noteWidth() {
            return store.state.processingWidth.note
        },
        filePreviewWidth: {
            set (width) {
                store.dispatch('setAppearanceState',({stepId: this.request.step_id, val: {filePreviewWidth: width}}))
            },
            get () {
                return store.state.processingWidth.filePreviewWidth
            }
        },
        localStorageKey () {
            return store.state.localStorageKey
        },
    },
    created () {

        this.stepPath = 'biz.' + this.request.step_url.replace('/', '.')
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
            return items.filter(item => (item.item_type !== null && item.item_type !== _const.ITEM_CONFIG_TYPE.TASK_RESULT))
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
        isActiveTask({is_active}) {
            return is_active === _const.FLG.ACTIVE
        },
        showSettingDialog () {
            this.$refs.settingDialog.show()
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
            const taskResults = [this.deliveredTaskResult, this.verificationTaskResult]

            if (!Array.isArray(taskResults[0].content[itemGroup])) {
                return 1
            }

            let max = 1;
            taskResults.forEach(function (taskResult) {
                if (max < taskResult.content[itemGroup].length) {
                    max = taskResult.content[itemGroup].length
                }
            })
            return max
        },
        hasError (item, groupIdx = 1) {
            // 比較用に成形した納品データと対象タスクとの比較
            if (!item) return false

            // 比較対象のtasks
            const task = store.getters.comparisonVerificationTaskResult

            // 比較基準となるtask
            const baseTask = store.getters.comparisonDeliveredTaskResult

            // 対象外のitem_typeはスキップ
            if (item.item_type === _const.ITEM_CONFIG_TYPE.IMAGE) return false

            // group と item_keyが同じ場合
            if (item.group == item.item_key) {
                // 比較基準
                const comparisonCriteria = baseTask.content[item.group]

                // 比較対象
                const compareTo = task.content[item.group]

                // 比較
                return JSON.stringify(compareTo) != JSON.stringify(comparisonCriteria)
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
                        return false
                    } else {
                        // プロパティが両方無い場合は比較できないので処理を終了
                        if ( !(item.key in objectForComparisonCriteria) && !(item.key in objectBeingCompared) ) return false

                        // 比較
                        const comparisonCriteria = objectForComparisonCriteria[item.key]
                        const beingCompared = objectBeingCompared[item.key]
                        return JSON.stringify(comparisonCriteria) != JSON.stringify(beingCompared)
                    }

                } catch (error) {
                    console.log('Method: hasError')
                    console.log(error)
                    return true

                }
            }
        },
        getComparisonContent (item, tableIndex=1) {

            const taskResults = [this.deliveredTaskResult, this.verificationTaskResult]

            const processTargetItemIds = [_const.ITEM_CONFIG_TYPE.FILE_INPUT, _const.ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT]
            if (!processTargetItemIds.includes(item.item_type)) return []

            // 必要な情報を抽出
            const arrayIndex = tableIndex - 1// テーブルのインデックス

            const comparisonContents = []
            for (const content of taskResults) {
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
                return taskContent[itemGroup]
            } else {
                // オブジェクト以外の場合
                return taskContent
            }
        },
        async enterCommentIfNeeded(comment, item, idx) {
            if (!this.edit) return// 編集不可能の場合は処理しない
            const {value, isEnter} = await this.$refs.editorDialog.show(comment)
            this.setComment(isEnter ? value : comment, item, idx)
        },
        setComment (comment, item, index) {

            const itemKey = item['item_key']
            const group = item['group']
            const key = item['key']
            const taskComment = this.taskComment

            // 処理結果
            if (this.isNecessarilyDisplay(group, key)) {
                taskComment['global_comment'] = comment
                this.taskComment = taskComment
                return
            }

            // 各画面固有のitem
            const content = taskComment['content']
            if (itemKey === group) {
                content[key] = comment
            } else if (Array.isArray(content[group])) {
                const arrayIndex = index - 1 // 添え字番号にするため-1
                content[group][arrayIndex][key] = comment
            } else {
                content[group][key] = comment
            }
            this.taskComment = taskComment

        },
        getComment (item, idx) {
            // processing result / overall comment
            if (this.isNecessarilyDisplay(item['group'], item['key'])) return this.taskComment['global_comment']
            const comment = this.getCommentFormContent(item, idx)
            return comment
        },
        getCommentFormContent(item, index) {
            const itemKey = item['item_key']
            const group = item['group']
            const key = item['key']

            // error回避処理
            const content = this.taskComment['content']
            if (content === null) return ''

            // グループに所属していない
            if (itemKey === group) return content[key]

            // グループに所属している
            if (group in content) {
                // groupの中身が配列の場合があるので、判断
                if (Array.isArray(content[group])) {
                    const arrayIndex = index - 1 // 添え字番号にするため-1
                    if (key in content[group][arrayIndex]) return content[group][arrayIndex][key]
                } else {
                    return content[group][key]
                }
            }
            return ''
        },
        getClassNameWorkResult (item, groupIdx = 1) {
            /* Note:
             * checklist表示用の臨時対応
             * 作業内容JSONに合わせて表示しているので他とルールが異なる
             */
            if (item['group'] === 'checklist') {
                return this.getClassNameWorkResultForChecklist(item)
            }

            let className = ''
            switch (item.diff_check_level) {
            // 項目のエラーチェック
            case _const.DIFF_CHECK_LEVEL.ERROR:
                if (this.hasError(item, groupIdx)) {
                    className = 'diff-check-err'
                }
                break
            // 基本的に合致しない項目のチェック
            case _const.DIFF_CHECK_LEVEL.WARNING:
                if (this.hasError(item, groupIdx)) {
                    className = 'diff-check-warn'
                }
                break
            }
            return className
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
            let base = this.checklistContent(store.getters.comparisonDeliveredTaskResult['content'], item['item_key'])
            let compare = this.checklistContent(store.getters.comparisonVerificationTaskResult['content'], item['item_key'])
            if (base.value !== compare.value) {
                return true
            }
            return false
        },
        getClassNameWorkResultForChecklist (item) {
            let className = ''
            switch (item.diff_check_level) {
            // 項目のエラーチェック
            case _const.DIFF_CHECK_LEVEL.ERROR:
                if (this.hasErrorForChecklist(item)) {
                    className = 'diff-check-err'
                }
                break
            // 基本的に合致しない項目のチェック
            case _const.DIFF_CHECK_LEVEL.WARNING:
                if (this.hasErrorForChecklist(item)) {
                    className = 'diff-check-warn'
                }
                break
            }
            return className
        },
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

i.icon-color-default.v-icon.icon-color-default.material-icons {
    color: rgba(0,0,0,.54);
}

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
/* テーブルセパレート */
.compare-table table th.separate,
.compare-table table td.separate {
    min-width: 20px;
    border: none;
}
.compare-table table th.separate-left,
.compare-table table td.separate-left {
    border-right: solid 0.5px #d7d7d7;
}
.compare-table table th.separate-right,
.compare-table table td.separate-right {
    border-left: solid 3px #d7d7d7;
}
/* 作業結果・メモのカラム */
.compare-table table th.col-work-result {
    border-top: solid 3px #d7d7d7;
}
.compare-table table th.col-work-result:last-child,
.compare-table table td.col-work-result:last-child {
    border-right: solid 3px #d7d7d7;
}
.compare-table table tr:last-child td.col-work-result {
    border-bottom: solid 3px #d7d7d7;
}
.compare-table table th.col-work-result span {
    font-weight: 700;
}
/* 結果の差分デザイン */
.compare-table table td.diff-check-err {
    background-color: #feb9c3;
}
.compare-table table td.diff-check-warn {
    border-left: 3px solid red;
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

.edit-note {
    position: relative;
    vertical-align: top;
}
.cursor-pointer {
    cursor: pointer;
}
.edit-note-icon {
    position: absolute;
    display: none;
    bottom: 0px;
    right: 0px;
    padding: 8px;
    /* background-color: rgba(255,255,255,0.8); */
}
.edit-note-icon:after {
    position: absolute;
}
.edit-note:hover .edit-note-icon {
    display: inline;
}
.edit-note {
    white-space: normal;
}
.mouse-hover-pointer:hover {
    cursor: pointer;
}
</style>
<style>
.compare-table table td.diff-check-err input,
.compare-table table td.diff-check-err .file-info span {
    color: #890009 !important;
}
</style>
