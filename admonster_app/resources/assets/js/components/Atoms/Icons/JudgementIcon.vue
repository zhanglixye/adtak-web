<template>
    <v-tooltip top v-if="isMatching()">
        <v-icon slot="activator" flat icon color="info">thumb_up</v-icon>
        <span>全員一致</span>
    </v-tooltip>
    <v-tooltip top v-else>
        <v-icon slot="activator" flat icon color="warning">warning</v-icon>
        <span>不一致あり</span>
    </v-tooltip>
</template>

<script>
export default {
    props: {
        item_no: { type: String },
        taskResults: { type: Array }
    },
    methods: {
        isMatching () {
            // 全員の回答が一致しているかどうかを判定
            // TODO: 曖昧検索への対応方法検討
            let taskResult = this.taskResults.filter(taskResult => {
                return this.taskResults[0].content[this.item_no] === taskResult.content[this.item_no]
            }, this)

            return taskResult.length === this.taskResults.length
        }
    }
}
</script>
