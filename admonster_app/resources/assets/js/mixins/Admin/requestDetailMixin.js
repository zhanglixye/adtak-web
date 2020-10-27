export default {
    props: {
        businessName: { type: String }
    },
    data: () => ({
        // ファイルアップロード用
        // ※ 更新する際は allow_file_type_names も更新すること
        allow_file_types: [
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        allow_file_type_names: [
            '.jpeg',
            '.png',
            '.gif',
            '.mp4',
            '.pdf',
            '.pptx',
            '.xlsx',
            '.docx',
        ],
    }),
    created () {
        // console.log('loaded requestDetailMixin')
    },
    computed: {
        isHuman() {
            return function(userId) {
                return (userId == 0 || userId == 1) ? false : true;
            }
        },
        requestWorkUri () {
            let self = this
            return function (step_name, request_work_ids, process, total=false) {

                // 新しく作成した一覧のURLに遷移
                if (['allocation','approval','work'].indexOf(process) !== -1) {

                    let uri = '/'+ process + 's' + '?business_name=' + encodeURIComponent(this.businessName)
                    if (process == 'work'){
                        uri = '/management/'+ process + 's' + '?business_name=' + encodeURIComponent(this.businessName)
                    }
                    request_work_ids.forEach(id => {
                        if (id === '') return
                        uri = uri + '&' + encodeURIComponent('request_work_ids[]') + '=' + encodeURIComponent(id)
                    })
                    uri = uri + '&step_name=' + encodeURIComponent(step_name)
                    let statusList = self.statusList(process, total) // TODO:ここの記載はどうするんだっけ？
                    statusList.forEach(status => {
                        if (status === '42') return
                        uri = uri + '&' + encodeURIComponent('status[]') + '=' + encodeURIComponent(status)
                    })
                    // 未作業一覧の場合は承認ステータスも条件に追加
                    if (process === 'work' && statusList.length > 0) {
                        [_const.APPROVAL_STATUS.NONE, _const.APPROVAL_STATUS.ON].forEach(status => {
                            uri = uri + '&' + encodeURIComponent('approval_status[]') + '=' + encodeURIComponent(status)
                        })
                    }
                    return uri

                } else {

                    let uri = '/request_works?business_name=' + encodeURIComponent(this.businessName)
                    request_work_ids.forEach(id => {
                        if (id === '') return
                        uri = uri + '&' + encodeURIComponent('request_work_ids[]') + '=' + encodeURIComponent(id)
                    })
                    uri = uri + '&date_type='
                    uri = uri + '&from='
                    uri = uri + '&to='
                    uri = uri + '&step_name=' + encodeURIComponent(step_name)
                    uri = uri + '&inactive=' + self.booleanNumber(true)
                    uri = uri + '&excluded=' + self.booleanNumber(process === 'excluded')
                    let statusList = self.statusList(process, total)
                    uri = uri + '&completed=' + self.booleanNumber(total)
                    statusList.forEach(status => {
                        if (status === '42') return
                        uri = uri + '&' + encodeURIComponent('status[]') + '=' + encodeURIComponent(status)
                    })
                    uri = uri + '&title=' + encodeURIComponent(self.displayTitle(process))

                    return uri
                }
            }
        }
    },
    methods: {
        booleanNumber (bool) {
            // TRUE:  1
            // FALSE: 0
            return bool ? 1 : 0
        },
        displayTitle (process) {
            switch (process) {
            case 'imported':
                return '取込一覧'
            case 'excluded':
                return '除外一覧'
            case 'allocation':
                return '割振一覧'
            case 'task':
                return '依頼作業一覧'
            case 'approval':
                return '承認一覧'
            case 'delivery':
                return '納品一覧'
            default:
                return '依頼作業一覧'
            }
        },
        statusList (process, all) {
            switch (process) {
            case 'allocation':
                return all ? [] : [_const.ALLOCATION_STATUS.NONE]
            case 'task':
                return all ? ['21', '22'] : ['21']
            case 'work':
                return all ? [] : [_const.TASK_STATUS.NONE, _const.TASK_STATUS.ON]
            case 'approval':
                return all ? [] : [_const.APPROVAL_STATUS.NONE, _const.APPROVAL_STATUS.ON]
            case 'delivery':
                return all ? ['41', '42'] : ['41']
            default:
                return ['11', '12', '21', '22', '31', '32', '41', '42']
            }
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
        completed_count (item) {
            if (item.completed_count === item.wip_count) {
                return item.completed_count
            } else {
                return item.completed_count + '/' + item.wip_count
            }
        },
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {

                // base64データが入っている場合は処理しない
                if ('data' == file.file_data.substring(0, 4)) resolve(file)

                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob'
                xhr.onload = () => {
                    var reader = new window.FileReader()
                    reader.readAsDataURL(xhr.response)
                    reader.onloadend = () => {
                        // メモリから削除
                        URL.revokeObjectURL(file.file_data)
                        file.file_data = reader.result
                        resolve(file)
                    }
                    reader.onerror = (e) => reject(e)
                }
                xhr.onerror = (e) => reject(e)
                xhr.open('GET', file.file_data)
                xhr.send()
            })
        },
    }
}
