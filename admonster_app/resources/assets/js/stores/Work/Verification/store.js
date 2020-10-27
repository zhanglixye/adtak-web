import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const localStorageKey = 'verification'

const initialState = {
    eachStepStates: {},
    defaultTableDataWidth: {
        delivery: 300,
        myTask: 300,
        note: 300,
    },
    defaultFilePreviewWidth: 600,
    processingWidth: {
        delivery: 300,
        myTask: 300,
        note: 300,
        filePreviewWidth: 600,
    },
    itemNameWidth: 180,// 日本語の$t('verification.result_and_total_comment')にあわせて設定
    processingData: {
        edit: true,
        request: null,
        taskResults: [],
        comparisonTaskResults: [],// 比較用に処理したTaskResults
        itemConfigs: [],
        inputs: {},
        businessesCandidates: [],
        startedAt: null,
        labelData: {},
        loading: false,
        taskComment: {},
    },
    checkData: {
        taskComment: {},
    },

    localStorageKey: localStorageKey,
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setAppearanceState(state, eachStepStates) {
            Object.assign(state.eachStepStates, eachStepStates)
        },
        setProcessingWidth(state, processingWidth) {
            Object.assign(state.processingWidth, processingWidth)
        },
        setProcessingData(state, val) {
            Object.assign(state.processingData, val)
        },
        setCheckData(state, val) {
            state.checkData = val
        }
    },
    getters: {

        isNonUpdatedData: (state) => {
            if (!state.processingData.edit) {
                return true
            } else {
                return JSON.stringify(state.processingData.taskComment) == JSON.stringify(state.checkData.taskComment)
            }
        },
        comparisonActiveTaskResults: (state) => {
            return state.processingData.comparisonTaskResults.filter(task => task.is_active === _const.FLG.ACTIVE)
        },
        comparisonDeliveredTaskResult: (state, getters) => {
            return getters.comparisonActiveTaskResults.find(task => task.user_id === state.processingData.request.approved_user_id)
        },
        comparisonVerificationTaskResult: (state, getters) => {
            return getters.comparisonActiveTaskResults.find(task => task.task_id == state.processingData.inputs.task_id)// inputsはstringの値のため、==
        },
    },
    actions: {
        setAppearanceState(store, { stepId, val }) {
            const stepState = (stepId in store.state.eachStepStates) ? store.state.eachStepStates[stepId] : {}
            let eachStepStates = {}
            eachStepStates[stepId] = Object.assign(stepState, val)
            store.commit('setAppearanceState', eachStepStates)

            const obj = Object.assign({}, store.state.processingWidth)
            for (const key in obj) {
                if (key in val) obj[key] = val[key]
            }
            store.commit('setProcessingWidth', obj)
        },
        async tryRefreshVerification(store, param) {

            const res = await axios.post('/api/verification/edit', param)

            if (res.data['result'] !== 'success') throw 'Error processing /api/verification/edit date: ' + new Date()
            // stateに追加するデータ
            const setData = {}
            setData.itemConfigs = res.data['item_configs']
            const taskResults = res.data['task_results']
            setData.request = res.data['request']
            setData.businessesCandidates = res.data['businesses_candidates']
            setData.startedAt = res.data['started_at']
            setData.labelData = res.data['label_data']
            const taskComment = res.data['task_comment'] === null ? {} : res.data['task_comment']

            let tableDataWidth = Object.prototype.hasOwnProperty.call(store.state.eachStepStates, setData.request.step_id) ? store.state.eachStepStates[setData.request.step_id] : store.state.defaultTableDataWidth
            const filePreviewWidth = ('filePreviewWidth' in tableDataWidth) ? tableDataWidth['filePreviewWidth'] : store.state.defaultFilePreviewWidth
            tableDataWidth = Object.assign({ filePreviewWidth: filePreviewWidth }, tableDataWidth)
            store.commit('setProcessingWidth', tableDataWidth)// 画面に反映

            // 初めての登録の際は、task_comment.contentをitem_configを基準に空文字列を代入
            if (!('created_at' in taskComment)) {// DB未登録の場合（created_atが無い）

                const activeTaskResults = taskResults.filter(task => task.is_active === _const.FLG.ACTIVE)
                const deliveredTaskResult = activeTaskResults.find(task => task.user_id === setData.request.approved_user_id)
                const myTaskResult = activeTaskResults.find(task => task.task_id == store.state.processingData.inputs['task_id'])// inputsはstringの値のため、==

                // item_configの対象のkeyのvalueを空文字列に変更
                const content = taskComment['content']
                setData.itemConfigs.forEach(items => {
                    items.forEach(item => {
                        if (item.item_type !== null) {// groupを表すitemは処理しない
                            const itemKey = item['item_key']
                            const group = item['group']
                            const key = item['key']

                            // グループに所属していない
                            if (itemKey === group) {
                                return content[key] = ''
                            }

                            // グループに所属している
                            if (group in content) {
                                // groupの中身が配列の場合があるので、判断
                                if (Array.isArray(content[group])) {
                                    const dLen = JSON.parse(deliveredTaskResult['content'])[group].length
                                    const mLen = JSON.parse(myTaskResult['content'])[group].length
                                    const maxLength = dLen > mLen ? dLen : mLen
                                    // 配列の長さを追加
                                    if (content[group].length < maxLength) {// 追加
                                        for (let i = content[group].length; i < maxLength; i++) {
                                            content[group][i] = content[group][0]// コピー
                                        }
                                    }
                                    for (const i in content[group]) {
                                        if (key in content[group][i]) {
                                            content[group][i][key] = ''
                                        }
                                    }
                                } else {
                                    // Object
                                    content[group][key] = ''
                                }
                            }
                        }
                    })
                })
            }
            setData.taskComment = taskComment
            // 変更を検知するためのデータをセット
            store.commit('setCheckData', { taskComment: JSON.parse(JSON.stringify(taskComment)) })

            // 文字列 -> JSONに変換する
            taskResults.forEach(taskResult => taskResult.content = JSON.parse(taskResult.content))
            setData.taskResults = taskResults

            // 対象タスクの作業者とログインユーザが同じ場合は編集可とする
            const userId = document.getElementById('login-user-id').value  // ログインしているユーザのIDを取得
            const taskResult = setData.taskResults.find( taskResult => taskResult['task_id'] === parseInt(store.state.processingData.inputs['task_id']) )// 対象タスクの取得
            setData.edit = userId === taskResult['user_id']

            setData.comparisonTaskResults = JSON.parse(JSON.stringify(setData.taskResults))

            store.commit('setProcessingData', setData)

            // 比較用taskResultを作成
            await store.dispatch('createComparisonTaskResults')
        },
        async createComparisonTaskResults({ dispatch, getters, state, commit }) {

            // 比較対象のtasks
            let taskResults = JSON.parse(JSON.stringify([getters.comparisonDeliveredTaskResult, getters.comparisonVerificationTaskResult]))

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
