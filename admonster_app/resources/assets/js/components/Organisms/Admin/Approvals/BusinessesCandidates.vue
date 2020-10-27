<template>
    <div :class="['box', 'display-inline-block', 'border-default', 'ma-1', 'mr-2', bgColor]" @mouseover="canDoEvent = true" @mouseleave="canDoEvent = false">
        <v-avatar :size="26" class="ma-1">
            <img :src="imagePath">
        </v-avatar>
        <span class="pr-3">{{ name }}</span>
        <v-btn color="#009688" v-show="showIcon" class="button" dark fab @click="event">
            <v-icon :size="18">{{ icon }}</v-icon>
        </v-btn>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </div>
</template>

<script>
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import store from '../../../../stores/Admin/Approvals/Detail/store'

export default {
    props: {
        imagePath: {type: String, require: true},
        name: {type: String, require: true},
        id: {type: Number, require: true},
        hiddenUserIds: {type: Array, require: true},
        allocatedUserIds: {type: Array, require: true}
    },
    components: {
        AlertDialog,
        ConfirmDialog
    },
    data: () => ({
        canDoEvent: false
    }),
    computed: {
        bgColor() {
            // 未割り振り
            if (!this.allocatedUserIds.includes(this.id)) return 'bg-gray'

            // 表示
            if (!this.hiddenUserIds.includes(this.id)) return 'bg-primary'

            // それ以外
            return 'bg-white'
        },
        icon () {
            // 未割り振り
            if (!this.allocatedUserIds.includes(this.id)) return 'mdi-account-plus-outline'

            // 表示
            if (!this.hiddenUserIds.includes(this.id)) return 'mdi-eye-off-outline'

            // それ以外
            return 'mdi-eye-outline'
        },
        request_work_updated_at: {
            set(val) {
                store.commit('setProcessingData', {request_work_updated_at: val})
            },
            get() {
                return store.state.processingData.request_work_updated_at
            }
        },
        loading: {
            set(val) {
                store.commit('setProcessingData', {loading: val})
            },
            get() {
                return store.state.processingData.loading
            }
        },
        showIcon() {
            // 承認後は割振りアイコンを表示させない
            return this.canDoEvent && (this.allocatedUserIds.includes(this.id) || store.state.processingData.edit)
        }
    },
    methods: {
        event () {
            if (!this.allocatedUserIds.includes(this.id)) {
                // 割振り
                this.allocate(this.id)
                return
            }

            if (this.hiddenUserIds.includes(this.id)) {
                // 表示
                this.$emit('add', this.id)
            } else {
                // 非表示
                this.$emit('click-delete', {user_id: this.id})
            }
        },
        async allocate (user_id) {
            if (await this.$refs.confirm.show(`${this.name}${Vue.i18n.translate('approvals.dialog.allocate.confirm')}`)) {
                // 割振り処理
                try {
                    this.loading = true

                    let params = new FormData()
                    params.append('request_work_id', store.state.processingData.request.request_work_id)
                    params.append('add_user_id', user_id)
                    params.append('request_work_updated_at', this.request_work_updated_at)
                    const res = await axios.post('/api/approvals/allocate',  params)
                    if (res.data.status === _const.API_STATUS_CODE.SUCCESS) {
                        this.request_work_updated_at = res.data.request_work.updated_at
                        // 情報の最新化
                        await store.dispatch('tryRefreshApprovals', store.state.processingData.inputs)
                        // 完了アラート
                        this.$refs.alert.show(
                            Vue.i18n.translate('approvals.dialog.allocate.finish')
                        )
                    } else if (res.data.status === _const.API_STATUS_CODE.EXCLUSIVE) {
                        this.$refs.alert.show(
                            '<div>' + this.$t('common.message.updated_by_others') + '</div>'
                            + '<div>' + this.$t('common.message.reload_screen') + '</div>',
                            () => window.location.reload()
                        )
                    } else {
                        this.$refs.alert.show(
                            Vue.i18n.translate('common.message.internal_error')
                        )
                    }
                } catch (error) {
                    this.$refs.alert.show(
                        Vue.i18n.translate('common.message.internal_error')
                    )
                } finally {
                    this.loading = false
                }
            }
        },
    }
}
</script>

<style scoped>
.border-default {
    border-color: #636b6f;
    border-style: solid;
    border-width: 1px;
}

.bg-primary {
    background-color: #80CBC4;
    color: white;
}

.bg-gray {
    background-color: #9e9e9e;
    color: white;
}

.bg-white {
    background-color: white;
    color: #636b6f;
}

.display-inline-block {
    display: inline-block;
}

.width-fit-content {
    width: fit-content;
}

.box {
    position: relative;
}

.button {
    position: absolute;
    right: -12px;
    top: 4px;
    height: 25px;
    width: 25px;
    margin: 0;
}
</style>
