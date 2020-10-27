<template>
    <div id="users-overview">
        <div v-if="title">
            <v-card-title>
                <span>{{ createTitle(title) }}</span>
            </v-card-title>
        </div>
        <v-layout row wrap>
            <template v-for="user_id in users">
                <!-- <v-tooltip top>
                    <v-avatar slot="activator" size="32px" class="ma-1">
                        <img :src="user_image_path(user_id)">
                    </v-avatar>
                    <span>{{ user_name(user_id) }}</span>
                </v-tooltip> -->
                        <v-avatar slot="activator" size="32px" class="ma-1" :key="user_id">
                            <img :src="user_image_path(user_id)">
                        </v-avatar>
                        <span style="font-size: 8px" :key="user_id">{{ user_name(user_id) }}</span>
            </template>
        </v-layout>
        <div v-if="users.length === 0">
            <v-avatar size="32px" class="ma-1">
                <img src="/images/question.svg">
            </v-avatar>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        title: { type: String },
        users: { type: Array, require: true},
        candidates: { type: Array, require: true }
    },
    methods: {
        createTitle (title) {
            let template = '$title($number$person)';

            return template
                .replace('$title',title)
                .replace('$number',this.users.length)
                .replace('$person',eventHub.$t('businesses.person'))
        },
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
    },
}
</script>
