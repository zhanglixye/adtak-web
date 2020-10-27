<template>
    <div>
        <template v-for="(user_id, index) in users.allocated_user_ids.slice(0, maxViews)">
            <v-badge v-model="completed[user_id]" left overlap color="grey lighten-1" :key="index">
                <v-icon slot="badge" dark>done</v-icon>
                <v-tooltip :disabled="hideTooltip" top>
                    <v-avatar slot="activator" :size="size" class="ma-1">
                        <img :src="user_image_path(user_id)">
                    </v-avatar>
                    <span>{{ user_name(user_id) }}</span>
                </v-tooltip>
                <v-tooltip :disabled="hideTooltip" top>
                    <v-avatar slot="activator" :size="size" class="ma-1" v-if="users.allocated_user_ids.length > maxViews && index == maxViews - 1">
                        <v-icon slot="activator">more_horiz</v-icon>
                    </v-avatar>
                    <span>{{ 'その他' + (users.allocated_user_ids.length - maxViews) + '人' }}</span>
                </v-tooltip>
            </v-badge>
        </template>
        <div v-if="users.allocated_user_ids.length === 0">
            <v-avatar :size="size" class="ma-1">
                <img src="/images/question.svg">
            </v-avatar>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        users: { type: Object, required: true },
        candidates: { type: Array, required: true },
        maxViews: {type: Number, required:false, default: undefined},
        size: {type: [Number, String], required:false, default: '32px'},
        hideTooltip: {type: Boolean, required: false, default: false}
    },
    data: () => ({
        completed: {},
    }),
    methods: {
        operator (user_id) {
            let operator = this.candidates.filter(user => user_id == user.id)
            return operator.length > 0 ? operator[0] : []
        },
        user_name (user_id) {
            return this.operator(user_id).name
        },
        user_image_path (user_id) {
            return this.operator(user_id).user_image_path
        },
        set_completed() {
            if (Object.keys(this.completed).length !== 0) {this.completed = {}}
            this.users.allocated_user_ids.forEach(allocated_user_id => {
                let operator = this.users.completed_user_ids.filter(completed_user_id => allocated_user_id == completed_user_id)
                this.completed[allocated_user_id] = operator.length > 0
            })
        }
    },
    created () {
        this.set_completed()
    },
    beforeUpdate() {
        this.set_completed()
    },
}
</script>
