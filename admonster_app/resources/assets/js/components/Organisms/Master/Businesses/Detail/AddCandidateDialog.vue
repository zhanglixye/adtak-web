<template>
    <v-dialog
            v-model="dialog"
            width="1000"
    >
        <v-card>
            <v-card-title
                    class="headline grey lighten-2"
                    primary-title
            >
                {{title}}
            </v-card-title>

            <v-card-text>
                <v-data-table
                    v-model="users"
                    :headers="headers"
                    :items="candidate_users"
                    item-key="id"
                    hide-actions
                    must-sort
                    :pagination.sync="pagination"
                    style="height: 66vh;overflow-y: auto;"
                >
                    <template slot="headerCell" slot-scope="props">
                        <v-tooltip top v-if="props.header.detail">
                            <span slot="activator">{{ props.header.text }}</span>
                            <span>{{ props.header.detail }}</span>
                        </v-tooltip>
                        <span v-else>{{ props.header.text }}</span>
                    </template>
                    <template slot="items" slot-scope="props">
                        <tr :active="props.selected">
                            <td class="text-xs-center pa-0">
                                <v-checkbox
                                    :input-value="props.selected"
                                    primary
                                    hide-details
                                    color="primary"
                                    @click="props.selected = !props.selected"
                                ></v-checkbox>
                            </td>
                            <td class="text-xs-center pa-0">{{ props.item.id }}</td>
                            <td class="text-xs-right">{{ props.item.name }}</td>
                            <td class="text-xs-right">{{ props.item.email }}</td>
                            <td class="text-xs-center">
                                <template>
                                    <v-avatar size="32px" :tile="false">
                                        <img :src="userImageSrc(props.item.user_image_path)">
                                    </v-avatar>

                                </template>
                            </td>
                            <td class="text-xs-right">{{ props.item.nickname }}</td>
                            <td class="text-xs-center pa-0">{{ sexString(props.item.sex)}}</td>


                        </tr>
                    </template>
                </v-data-table>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                        color="primary"
                        text
                        @click="dialog = false"
                >
                    {{ $t('common.button.cancel') }}
                </v-btn>
                <v-btn
                        color="primary"
                        text
                        @click="addOperator"
                >
                    {{ $t('common.button.add') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    data: () => ({
        dialog: false,
        selected: [],
        users: [],
        title: '',

        pagination: {
            sortBy: 'average',
            descending: false,
            rowsPerPage: -1, // ALL
        },
    }),
    methods:{
        show(){
            this.users = []
            this.select = []
            this.dialog = true
        },
        close(){
            this.dialog = false
        },
        addOperator(){
            if (this.candidateFlg === _const.ADD_USER.BUSINESS_OPERATOR){
                this.$emit('add-candidates', this.users)
            } else if (this.candidateFlg === _const.ADD_USER.BUSINESS_ADMIN) {
                this.$emit('add-administrators', this.users)
            } else if (this.candidateFlg === _const.ADD_USER.WORKS_USER) {
                this.$emit('add-worker',this.users)
            } else if (this.candidateFlg === _const.ADD_USER.EDUCATIONAL_WORKS_USER) {
                this.$emit('add-educational-worker-user',this.users)
            }
        },
        sexString(sex){
            if (sex === _const.SEX.MALE) {
                return this.$t('list.sex_category.male')
            } else if (sex === _const.SEX.FEMALE){
                return this.$t('list.sex_category.female')
            } else {
                return ''
            }
        }
    },
    props:{
        candidate_users: { type: Array, required: true },
        candidateFlg: { type: Number, required: true },
    },
    computed:{
        headers () {
            return [
                { text: '', value: 'master.users.headers.id', align: 'center', sortable: false },
                { text: this.$t('master.users.headers.id'), value: 'id', align: 'center' },
                { text: this.$t('master.users.headers.user_name'), value: 'user_name', align: 'center' },
                { text: this.$t('master.users.headers.email'), value: 'email', align: 'center' },
                { text: this.$t('master.users.headers.user_image_path'), value: 'user_image_path', align: 'center' },
                { text: this.$t('master.users.headers.nickname'), value: 'nickname', align: 'center' },
                { text: this.$t('master.users.headers.sex'), value: 'sex', align: 'center', },

            ]
        },
        userImageSrc () {
            return function (user_image_path) {
                if (user_image_path) {
                    return user_image_path
                } else {
                    return location.origin + '/images/dummy_icon.png'
                }
            }
        }
    },
    watch: {
        users () {
            // this.$emit('update:selected', this.users)
        },
        selected () {
            this.users = this.selected
        },
        candidateFlg () {
            if (this.candidateFlg=== _const.ADD_USER.BUSINESS_OPERATOR){
                this.title = this.$t('master.businesses.dialog.add_business_operator')
            } else if (this.candidateFlg=== _const.ADD_USER.BUSINESS_ADMIN){
                this.title = this.$t('master.businesses.dialog.add_business_admin')
            } else if (this.candidateFlg=== _const.ADD_USER.WORKS_USER){
                this.title = this.$t('master.businesses.dialog.add_works_user')
            } else if (this.candidateFlg=== _const.ADD_USER.EDUCATIONAL_WORKS_USER){
                this.title = this.$t('master.businesses.dialog.add_educational_works_user')
            }
        }
    },
    created () {
        this.users = this.selected
    }
}
</script>

<style scoped>

</style>