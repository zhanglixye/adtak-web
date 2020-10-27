<template>
    <div>
        <v-layout row wrap :class="[reverseClass]">
            <v-flex
                v-for="(content) in contents"
                :key="`item-${content.id}`"
                v-show="[left, right].includes(content.id)"
                :class="[
                    left === content.id ? leftClass : '',
                    right === content.id ? rightClass : '',
                ]"
            >
                <editing
                    v-if="needsShowEditing(content.id)"
                    :arrow-reverse="isArrowReverse(content.id)"
                    :height="'500px'"
                    @grow="reset"
                >
                </editing>
                <component
                    v-show="!needsShowEditing(content.id)"
                    class="items"
                    :ref="content.component"
                    :is="content.component"
                    :mode="mode(content.id)"
                    :hide-left-grow-button="hideLeftGrowButton(content.id)"
                    :hide-right-grow-button="hideRightGrowButton(content.id)"
                    :full-width="expansion === content.id"
                    @previous="prev(content.id)"
                    @next="next(content.id)"
                    @shrink-right="shrinkToRight(content.id)"
                    @shrink-left="shrinkToLeft(content.id)"
                    @grow="grow(content.id)"
                    @to-edit-mode="toEditMode(content.id)"
                    @to-read-mode="toReadMode(content.id)"
                    @open-create-mail="openComponent(content.id, 5)"
                    @close-create-mail="closeComponent(5)"
                    @open-create-request="openComponent(content.id, 6)"
                    @close-create-request="closeComponent(6)"
                ></component>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
import OrderDetailInfo from './OrderDetailInfo'
import AdvertisementMaterial from './AdvertisementMaterial'
import AdditionalInfo from './AdditionalInfo'
import RelatedMail from './RelatedMail'
import RequestInfo from './RequestInfo'
import Editing from './Editing'
import CreateMail from './CreateMail'
import CreateRequest from './CreateRequest'
import store from '../../../../stores/Order/OrderDetails/Show/store'

const STATE = {
    NONE: 0,
    BASIC: 1,
    WIDE: 2,
    HIDDEN: 3,
    GROW: 4,
    SHRINK: 5,
}

export default {
    components: {
        OrderDetailInfo,
        AdvertisementMaterial,
        AdditionalInfo,
        RelatedMail,
        RequestInfo,
        Editing,
        CreateMail,
        CreateRequest,
    },
    props: {
        orderDetailId: { type: Number, required: false, default: null }
    },
    data() {
        return {
            left: 0,
            leftWidth: STATE.BASIC,
            right: 1,
            rightWidth: STATE.BASIC,
            expansion: null,
            startedExpansionNumber: null,
            startedHiddenNumber: null,
            isReverse: false,
            editContentId: null,
            contents: [
                {
                    id: 0,
                    component: 'orderDetailInfo',
                    show: true,
                },
                {
                    id: 1,
                    component: 'additionalInfo',
                    show: true,
                },
                {
                    id: 2,
                    component: 'relatedMail',
                    show: true,
                },
                {
                    id: 3,
                    component: 'advertisementMaterial',
                    show: false,
                },
                {
                    id: 4,
                    component: 'requestInfo',
                    show: true,
                },
                {
                    id: 5,
                    component: 'createMail',
                    show: false,
                },
                {
                    id: 6,
                    component: 'createRequest',
                    show: false,
                },
            ],
        }
    },
    computed: {
        leftClass() {
            if (this.leftWidth === STATE.NONE) return ''
            if (this.leftWidth === STATE.SHRINK) return 'shrink'
            if (this.leftWidth === STATE.BASIC) return 'xs6'
            if (this.leftWidth === STATE.WIDE) return 'xs12'
            if (this.leftWidth === STATE.GROW) return 'grow'
            if (this.leftWidth === STATE.HIDDEN) return 'display-none'
            return ''
        },
        rightClass() {
            if (this.rightWidth === STATE.NONE) return ''
            if (this.rightWidth === STATE.SHRINK) return 'shrink'
            if (this.rightWidth === STATE.BASIC) return 'xs6'
            if (this.rightWidth === STATE.WIDE) return 'xs12'
            if (this.rightWidth === STATE.GROW) return 'grow'
            if (this.rightWidth === STATE.HIDDEN) return 'display-none'
            return ''
        },
        reverseClass() {
            if ((!this.isReverse && this.left > this.right)
                || (this.isReverse && this.left < this.right)) return 'reverse'
            return ''
        },
        showMaxId() {
            return Math.max(...this.contents.map(content => content.show ? content.id : null))
        },
        userEmail () {
            return document.getElementById('login-user-email').value
        },
        isAdmin() {
            return store.state.processingData.isAdmin
        },
    },
    async created() {
        if (![undefined, null].includes(this.orderDetailId)) {
            const displayOrderDetail = await store.dispatch('getDisplayOrderDetail', this.orderDetailId);
            Object.assign(this.$data, displayOrderDetail)
        }
        // 前回画面を閉じた際に編集中だった場合はリセット
        this.allReadMode()
        if ([this.rightWidth, this.leftWidth].includes(STATE.SHRINK)) this.reset()

        // 常時表示しないコンポーネントが指定されていた場合は他のコンポーネントを表示する
        const hiddenComponentIds = this.contents.flatMap(content => !content.show ? content.id : [])
        if (hiddenComponentIds.includes(this.left)) this.prev(this.left)
        if (hiddenComponentIds.includes(this.right)) this.prev(this.right)
    },
    methods: {
        isArrowReverse(id) {
            return this.hideLeftGrowButton(id)
        },
        needsShowEditing(id) {
            const state = this.left === id ? this.leftWidth : this.right === id ? this.rightWidth : null
            return state === STATE.SHRINK
        },
        mode: function(id) {
            if (!this.isAdmin) return _const.DISPLAY_MODE.READ_ONLY
            if (this.editContentId === null) return _const.DISPLAY_MODE.EDIT_OFF
            return this.editContentId === id ? _const.DISPLAY_MODE.EDIT_ON : _const.DISPLAY_MODE.READ_ONLY
        },
        hideLeftGrowButton: function(id) {
            return (!this.isReverse && this.left === id) || (this.isReverse && this.right === id)
        },
        hideRightGrowButton: function(id) {
            return (!this.isReverse && this.right === id) || (this.isReverse && this.left === id)
        },
        grow: function(id) {
            this.expansion = id
            if (id === this.left) {
                this.leftGrow(id)
            } else if (id === this.right) {
                this.rightGrow(id)
            }
            this.saveDisplay()
        },
        leftGrow: function(id) {
            if (this.editContentId !== null){
                if (id !== this.editContentId) {
                    this.leftWidth = STATE.GROW
                    this.rightWidth = STATE.SHRINK
                    return
                }
            }
            this.leftWidth = STATE.WIDE
            this.startedExpansionNumber = this.left
            this.rightWidth = STATE.HIDDEN
            this.startedHiddenNumber = this.right
        },
        rightGrow: function(id) {
            if (this.editContentId !== null) {
                if (id !== this.editContentId) {
                    this.leftWidth = STATE.SHRINK
                    this.rightWidth = STATE.GROW
                    return
                }
            }
            this.leftWidth = STATE.HIDDEN
            this.startedHiddenNumber = this.left
            this.rightWidth = STATE.WIDE
            this.startedExpansionNumber = this.right
        },
        shrinkToLeft: function(id) {
            this.reset()
            if (!this.isReverse && this.left !== id) this.reverse()
            else if (this.isReverse && this.left === id) this.reverse()
            this.saveDisplay()
        },
        shrinkToRight: function(id) {
            this.reset()
            if (!this.isReverse && this.right !== id) this.reverse()
            else if (this.isReverse && this.right === id) this.reverse()
            this.saveDisplay()
        },
        reset: function() {
            this.expansion = null
            this.startedExpansionNumber = null
            this.startedHiddenNumber = null
            this.leftWidth = STATE.BASIC
            this.rightWidth = STATE.BASIC
        },
        reverse: function() {
            this.isReverse = !this.isReverse
        },
        next: function(id) {
            if (id === this.left) this.nextLeft()
            else if (id === this.right) this.nextRight()
            this.saveDisplay()
        },
        prev: function(id) {
            if (id === this.left) this.prevLeft()
            else if (id === this.right) this.prevRight()
            this.saveDisplay()
        },
        nextLeft: function() {
            let left = this.left + 1
            while (!this.canShow(left)) {// 表示出来るIDまでループ
                left++
                if (left > this.showMaxId) left = 0
            }
            if (this.expansion === this.left) {
                if (this.right === this.editContentId) {
                    if (left === this.right) {
                        left++
                        while (!this.canShow(left)) {// 表示出来るIDまでループ
                            left++
                            if (left > this.showMaxId) left = 0
                        }
                    }
                } else if (left === this.startedHiddenNumber) {
                    this.right = this.startedExpansionNumber
                } else {
                    if (this.startedHiddenNumber !== this.right) {
                        this.right = this.startedHiddenNumber
                    }
                }
                this.left = left
                this.expansion = left
                return
            } else {
                if (left === this.right) {
                    left++
                    while (!this.canShow(left)) {// 表示出来るIDまでループ
                        left++
                        if (left > this.showMaxId) left = 0
                    }
                }
                this.left = left
            }
        },
        prevLeft: function() {
            let left = this.left - 1
            while (!this.canShow(left)) {// 表示出来るIDまでループ
                left--
                if (left < 0) left = this.showMaxId
            }
            if (this.expansion === this.left) {
                if (this.right === this.editContentId) {
                    if (left === this.right) {
                        left--
                        while (!this.canShow(left)) {// 表示出来るIDまでループ
                            left--
                            if (left < 0) left = this.showMaxId
                        }
                    }
                } else if (left === this.startedHiddenNumber) {
                    this.right = this.startedExpansionNumber
                } else {
                    if (this.startedHiddenNumber !== this.right) {
                        this.right = this.startedHiddenNumber
                    }
                }
                this.left = left
                this.expansion = left
                return
            } else {
                if (left === this.right) {
                    left--
                    while (!this.canShow(left)) {// 表示出来るIDまでループ
                        left--
                        if (left < 0) left = this.showMaxId
                    }
                }
                this.left = left
            }
        },
        nextRight: function() {
            let right = this.right + 1
            while (!this.canShow(right)) {// 表示出来るIDまでループ
                right++
                if (right > this.showMaxId) right = 0
            }
            if (this.expansion === this.right) {
                if (this.left === this.editContentId) {
                    if (right === this.left) {
                        right++
                        while (!this.canShow(right)) {// 表示出来るIDまでループ
                            right++
                            if (right > this.showMaxId) right = 0
                        }
                    }
                } else if (right === this.startedHiddenNumber) {
                    this.left = this.startedExpansionNumber
                } else {
                    if (this.startedHiddenNumber !== this.left) {
                        this.left = this.startedHiddenNumber
                    }
                }
                this.right = right
                this.expansion = right
                return
            } else {
                if (right === this.left) {
                    right++
                    while (!this.canShow(right)) {// 表示出来るIDまでループ
                        right++
                        if (right > this.showMaxId) right = 0
                    }
                }
                this.right = right
            }
        },
        prevRight: function() {
            let right = this.right - 1
            while (!this.canShow(right)) {// 表示出来るIDまでループ
                right--
                if (right < 0) right = this.showMaxId
            }
            if (this.expansion === this.right) {
                if (this.left === this.editContentId) {
                    if (right === this.left) {
                        right--
                        if (right < 0) right = this.showMaxId
                        while (!this.canShow(right)) {// 表示出来るIDまでループ
                            right--
                            if (right < 0) right = this.showMaxId
                        }
                    }
                } else if (right === this.startedHiddenNumber) {
                    this.left = this.startedExpansionNumber
                } else {
                    if (this.startedHiddenNumber !== this.left) {
                        this.left = this.startedHiddenNumber
                    }
                }
                this.right = right
                this.expansion = right
                return
            } else {
                if (right === this.left) {
                    right--
                    if (right < 0) right = this.showMaxId
                    while (!this.canShow(right)) {// 表示出来るIDまでループ
                        right--
                        if (right < 0) right = this.showMaxId
                    }
                }
                this.right = right
            }
        },
        saveDisplay: function() {
            if ([undefined, null].includes(this.orderDetailId)) return //保存しない
            const displayOrderDetail = JSON.parse(JSON.stringify(this.$data))
            delete displayOrderDetail.contents // 不要なkeyを削除
            delete displayOrderDetail.editContentId // 不要なkeyを削除
            const obj = {}
            obj[this.orderDetailId] = displayOrderDetail
            store.commit('setDisplayFormDetails', obj)
        },
        toEditMode: function(id) {
            this.editContentId = id
        },
        toReadMode: function(id) {
            if (this.editContentId === id) this.editContentId = null
        },
        allReadMode: function() {
            this.editContentId = null
        },
        openComponent: function(operationContentId, contentId) {
            this.reset()

            // コンポーネントを切り替える
            this.showSwitching(contentId)
            if (contentId === 5) this.setMailInfo()
            if (operationContentId === this.left) {
                while (this.right !== contentId) {
                    this.next(this.right)
                }
            } else if (operationContentId === this.right) {
                while (this.left !== contentId) {
                    this.next(this.left)
                }
            }
            this.grow(contentId)
            this.toEditMode(contentId)
        },
        closeComponent: function(id) {
            this.reset()
            this.showSwitching(id)
            if (this.left === id) {
                this.prev(this.left)
            } else if (this.right === id) {
                this.prev(this.right)
            }
        },
        canShow: function(id) {
            const content = this.contents.find(content => content.id === id)
            if (content === undefined) return false
            return content.show
        },
        showSwitching: function(id) {
            this.contents = this.contents.map(content => {
                if (content.id === id) {
                    content.show = !content.show
                }
                return content
            })
        },
        setMailInfo: function() {
            // 一旦、案件情報の内容をメール作成コンポーネントに入れるように設定
            const obj = {
                cc: [this.userEmail], //画面を操作しているユーザのメールアドレスを追加
                subject: store.state.processingData.subjectData,
            }
            // メールに初期表示させるデータの作成
            this.$refs.createMail[0].set(obj)
        },
    },
}
</script>

<style scoped>

.display-none {
    display: none;
}
</style>
