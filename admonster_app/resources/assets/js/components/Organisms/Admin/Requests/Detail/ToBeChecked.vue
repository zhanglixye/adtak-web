<template>
    <div id="to-be-checked">
        <v-card>
            <v-card-title class="pt-1 pb-1 subheading grey darken-1 white--text">
            {{ $t('requests.detail.to_be_checked.header') }}<span class="body-1 ml-3">{{ toBeCheckedData.length }}{{ $t('requests.count') }}</span>
            </v-card-title>
            <v-divider class="ma-0"></v-divider>
            <v-card-text class="to-be-checked-inner">
                <v-list v-if="toBeCheckedData.length > 0" two-line>
                    <template v-for="(item, index) in toBeCheckedData">
                        <v-list-tile
                            :key="item.id"
                            avatar
                            ripple
                            
                            target="_blank"
                            @click="openWindow(item.task_url)"
                        >
                            <v-list-tile-avatar>
                                <v-tooltip top>
                                    <v-avatar slot="activator" size="36px" class="ma-1">
                                        <img :src="user_image_path(item.operator_id)">
                                    </v-avatar>
                                    <span>{{ user_name(item.operator_id) }}</span>
                                </v-tooltip>
                            </v-list-tile-avatar>
                            <v-list-tile-content>
                                <v-list-tile-title>
                                   <span>{{ item.step_name }}</span>
                                </v-list-tile-title>
                                <v-list-tile-sub-title class="text--primary caption">{{ $t('requests.code') }}: {{ item.request_work_code }}</v-list-tile-sub-title>
                            </v-list-tile-content>
                            <v-list-tile-action>
                                <v-list-tile-action-text>{{ item.updated_at | formatDateYmdHm }}</v-list-tile-action-text>
                                <v-list-tile-action-text class="text--primary">
                                    {{ checkTypeText(item.type) }}
                                </v-list-tile-action-text>
                            </v-list-tile-action>
              
                        </v-list-tile>
                        <v-divider
                            v-if="index + 1 < toBeCheckedData.length"
                            :key="index"
                            class="ma-0"
                        ></v-divider>
                    </template>
                </v-list>
                <div v-else class="ma-3">
                    <span>{{ $t('requests.detail.to_be_checked.no_count') }}</span>
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
// import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
export default {
    name: 'ToBeChecked',
    props: {
        toBeCheckedData: { type: Array },
        candidates: { type: Array, require: true }
    },
    data: () => ({
        //loading
        loading: false
    }),
    components:{
        // ProgressCircular
    },
    created () {
        // let self = this
    },
    computed: {
        checkTypeText() {
            return function(type) {
                if (type == _const.TASK_RESULT_TYPE.CONTACT) {
                    return '不明あり'
                } else {
                    return ''
                }
            }
        }
    },
    methods: {
        // UsersOverviewコンポーネント内と共通。mixinにする
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
        openWindow (url) {
            window.open(url,  null, 'width=1000, height=700, resizable=1, top=0,left=200')
        }
    }
}
</script>

<style scoped>
    #to-be-checked {
        position: relative;
    }
    .to-be-checked-inner {
        overflow-x: hidden;
        overflow-y: auto;
    }
</style>