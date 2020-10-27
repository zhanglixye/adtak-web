import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const localStorageKey = 'appearanceStateOfApprovalScreen'

const initialState = {
    eachStepStates: {},
    defaultTableDataWidth: 350,
    minTableDataWidth: 200,
    itemNameWidth: 200,
    defaultFilePreviewWidth: 600,
    processingData: {
        edit: true,
        selected: [],
        request: null,
        taskResults: [],
        comparisonTaskResults: [],// 比較用に処理したTaskResults
        itemConfigs: [],
        approvalTasks: [],
        inputs: {},
        businessesCandidates: [],
        canUseBusinessesCandidates: false,
        labelData: {},
        loading: false,
        requestWorkUpdatedAt: null,
    },
    checkData: {
        taskResults: []
    },
    localStorageKey: localStorageKey
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setAppearanceState(state, val) {
            Object.assign(state.eachStepStates, val)
        },
        setProcessingData(state, val) {
            Object.assign(state.processingData, val)
        },
        setCheckData(state, val) {
            state.checkData = val
        }
    },
    getters: {

        getAppearanceState:state => stepId => Object.prototype.hasOwnProperty.call(state.eachStepStates,  stepId) ? state.eachStepStates[stepId] : {},
        isNonUpdatedData: (state) => {
            if (!state.processingData.edit) {
                return true
            } else {
                return state.processingData.selected.length === 0 && JSON.stringify(state.processingData.approvalTasks) == JSON.stringify(state.checkData.approvalTasks)
            }
        },
        comparisonActiveTaskResults: (state) => {
            return state.processingData.comparisonTaskResults.filter(task => task.is_active === _const.FLG.ACTIVE)
        },
    },
    actions: {
        setAppearanceState(store, {stepId, val}) {
            const stepState = store.getters.getAppearanceState(stepId)
            let eachStepStates = {}
            eachStepStates[stepId] = Object.assign(stepState, val)
            store.commit('setAppearanceState', eachStepStates)
        },
        async tryRefreshApprovals(store, param) {

            const res = await axios.post('/api/approvals', param)

            // stateに追加するデータ
            const setData = {}
            setData.itemConfigs = res.data['item_configs']
            const taskResults = res.data['task_results']
            setData.request = res.data['request']
            setData.businessesCandidates = res.data['businesses_candidates']
            setData.canUseBusinessesCandidates = true
            setData.labelData = res.data['label_data']
            setData.requestWorkUpdatedAt = res.data['request_work_updated_at']

            // 文字列 -> JSONに変換する
            taskResults.forEach(taskResult => taskResult.content = JSON.parse(taskResult.content))
            setData.taskResults = taskResults

            // 個別承認データ
            const appTasks = res.data['approval_tasks']
            if (appTasks.length === setData.taskResults.length) {
                setData.approvalTasks = appTasks
            } else if (appTasks.length > 0) {

                const approvalTasks = appTasks

                // 足りない分は追加
                const addedTaskIds = appTasks.map(appTask => appTask.task_id)
                for (const task of setData.taskResults) {
                    if (!addedTaskIds.includes(task.task_id)) {
                        const approvalTask = {
                            // snake case for server side
                            task_id: task.task_id,
                            approval_result: null
                        }
                        approvalTasks.push(approvalTask)
                    }
                }

                setData.approvalTasks = approvalTasks

            } else {
                // すべて追加
                const approvalTasks = []
                for (const task of setData.taskResults) {
                    const approvalTask = {
                        // snake case for server side
                        task_id: task.task_id,
                        approval_result: null
                    }
                    approvalTasks.push(approvalTask)
                }

                setData.approvalTasks = approvalTasks
            }

            // 変更を検知するためのデータをセット
            store.commit('setCheckData', {approvalTasks: JSON.parse(JSON.stringify(setData.approvalTasks))})

            // 画面表示制御
            if (setData.request.process === _const.APPROVAL_STATUS.DONE
                || setData.request.request_work_is_active === _const.FLG.INACTIVE
                || setData.request.request_is_deleted === _const.FLG.ACTIVE) {
                setData.edit = false

                // 承認済み
                if (setData.request.approved_user_id) {

                    // 納品者
                    setData.selected = [setData.request.approved_user_id]

                    // 個人の最新タスクを管理するオブジェクト
                    const currentTaskEachUser = {}

                    // 未作業者の最後のタスクを表示する為に作業者の最新タスクのis_activeを有効にする
                    for (const task of setData.taskResults) {
                        if (currentTaskEachUser[task.user_id] === undefined) {
                            currentTaskEachUser[task.user_id] = task.task_id
                        } else if (task.task_id > currentTaskEachUser[task.user_id]) {
                            currentTaskEachUser[task.user_id] = task.task_id
                        }
                    }
                    const latestTaskIds = Object.values(currentTaskEachUser)
                    for (const task of setData.taskResults) {
                        if (latestTaskIds.includes(task.task_id) && task.is_active === _const.FLG.INACTIVE) task.is_active = _const.FLG.ACTIVE
                    }
                }
            } else {

                // 画面内で入力した内容の反映
                // OK/NG
                const activeTaskIds = setData.taskResults.filter(task => task.is_active === _const.FLG.ACTIVE).map(task => task.task_id)
                const stateApprovalTasks = JSON.parse(JSON.stringify(store.state.processingData.approvalTasks))
                for (const stateApprovalTask of stateApprovalTasks) {
                    for (const approvalTask of setData.approvalTasks) {
                        if (stateApprovalTask.task_id === approvalTask.task_id && activeTaskIds.includes(stateApprovalTask.task_id)) approvalTask.approval_result = stateApprovalTask.approval_result
                    }
                }

                // 納品対象チェック
                const activeSelectUserIds = setData.taskResults.filter(task => task.is_active === _const.FLG.ACTIVE && task.status === _const.TASK_STATUS.DONE).map(task => task.user_id)
                const stateSelected = JSON.parse(JSON.stringify(store.state.processingData.selected))
                setData.selected = stateSelected.filter(id => activeSelectUserIds.includes(id))
            }

            setData.comparisonTaskResults = JSON.parse(JSON.stringify(setData.taskResults))

            store.commit('setProcessingData', setData)

            // 比較用taskResultを作成
            await store.dispatch('createComparisonTaskResults')
        },
        async createComparisonTaskResults({ dispatch, getters, state, commit }) {

            // 比較対象のtasks
            let taskResults = JSON.parse(JSON.stringify(getters.comparisonActiveTaskResults))

            // 比較対象がない為、終了
            if (taskResults.length === 0) return

            // データの成形
            for (const itemConfig of state.processingData.itemConfigs) {
                // グループは検査対象から外す
                const items = itemConfig.filter(item => item.item_type !== null)

                if (items.length === 0) continue// 変換対象がない
                for (const item of items) {
                    // 対象外のitem_typeはスキップ
                    if (item.item_type === _const.ITEM_CONFIG_TYPE.IMAGE) return

                    // group と item_keyが同じ場合
                    if (item.group == item.item_key) {
                        // 置き換え
                        taskResults = await Promise.all(taskResults.map(async taskResult => {
                            const task = JSON.parse(JSON.stringify(taskResult))

                            // 変換処理が行えないのでreturn
                            if (task.content === null || task.content === undefined) return task
                            task.content[item.group] = await dispatch('mightConvertComparisonData',
                                {
                                    itemType: item.item_type,
                                    data: task.content[item.group]
                                })
                            return task
                        }))
                    } else {
                        taskResults = await Promise.all(taskResults.map(async taskResult => {
                            const task = JSON.parse(JSON.stringify(taskResult))

                            // 変換処理が行えないのでreturn
                            if (task.content === null || task.content === undefined) return task
                            if (task.content[item.group] === null || task.content[item.group] === undefined) return task

                            if (task.content[item.group] instanceof Array) {
                                for (const i in task.content[item.group]) {
                                    task.content[item.group][i][item.key] = await dispatch('mightConvertComparisonData',
                                        {
                                            itemType: item.item_type,
                                            data: task.content[item.group][i][item.key]
                                        }
                                    )
                                }
                            }

                            // 変換処理が行えないのでreturn
                            if (!(item.key in task.content[item.group])) return task

                            task.content[item.group][item.key] = await dispatch('mightConvertComparisonData',
                                {
                                    itemType: item.item_type,
                                    data: task.content[item.group][item.key]
                                }
                            )
                            return task
                        }))
                    }
                }
            }

            // 変更した内容を代入
            const comparisonTaskResults = JSON.parse(JSON.stringify(getters.comparisonActiveTaskResults))
            for (const i in comparisonTaskResults) {
                const taskOrUndefined = taskResults.find(task => comparisonTaskResults[i].task_id === task.task_id)
                if (taskOrUndefined !== undefined) comparisonTaskResults[i] = taskOrUndefined

            }
            commit('setProcessingData', { comparisonTaskResults: comparisonTaskResults })
        },
        mightConvertComparisonData({ dispatch }, { itemType, data }) {
            const copyData = JSON.parse(JSON.stringify(data))
            switch (itemType) {
            case 300:
                return dispatch('mightConvertItemType300Data', copyData)
            case 800:
                return dispatch('mightConvertItemType800Data', copyData)
            case _const.ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT:
                return dispatch('mightConvertItemType801Data', copyData)
            default:
                return copyData
            }
        },
        mightConvertItemType300Data(context, data) {
            if (data instanceof Array) {
                const array = JSON.parse(JSON.stringify(data))// sortで元の要素に影響を与えないようにする
                // Unicodeコードポイントの昇順
                array.sort()
                return array
            }
            return data
        },
        mightConvertItemType800Data(context, data) {
            if (data instanceof Array) {
                const array = data.map(file => file.hash)// 比較用配列の作成
                array.sort()
                return array
            }
            return data
        },
        mightConvertItemType801Data(context, data) {
            if (data === null) return null
            if (typeof data === 'object') {
                return 'hash' in data ? data.hash : null
            }
            return data
        },
    },
    plugins: [
        createPersistedState({
            key: localStorageKey,
            paths: ['eachStepStates'],
            storage: window.localStorage,
        })
    ]
})
export default store
