<template>
    <!-- ユーザーの名前を連結して表示（見切れる場合は三点リーダーで省略する） -->
    <div class="text-cut" :style="{'max-width': itemWidth}">
        <span v-if="userIds.length > 0">
            {{ usersName }}
        </span>
    </div>
</template>

<script>
const isNumber = (value) => {
    return ((typeof value === 'number') && (isFinite(value)))
}
export default {
    props: {
        userIds: { type: Array, required: true },
        usersInfo: { type: Array, required: true },
        width: {type: [Number, String], required: false, default: 100}
    },
    data: () => ({
        usersName: ''
    }),
    computed: {
        itemWidth: function () {
            const width = this.width
            // 数値の場合
            if (isNumber(width)) return `${width}px`

            return width
        },
    },
    methods: {
        /**
        * ユーザー情報を返す
        * @returns {Object}
        */
        getUserInfo (userId) {
            const userInfo = this.usersInfo.filter(user => userId == user.id)
            return userInfo.length > 0 ? userInfo[0] : []
        },
        /**
        * ユーザーの名前を返す
        * @returns {String}
        */
        getUserName (userId) {
            return this.getUserInfo(userId).name
        },
        /**
         * 表示するためにユーザーの名前を連結してセット
         */
        setUsersName() {
            this.usersName = ''
            for (const index in this.userIds) {
                this.usersName += this.getUserName(this.userIds[index])
                if (this.userIds.length - 1 != index) {
                    this.usersName += ',　'
                }
            }
        }
    },
    created () {
        this.setUsersName()
    },
    beforeUpdate() {
        this.setUsersName()
    },
}
</script>

<style scoped>
.text-cut {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}
</style>