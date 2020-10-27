<template>
    <div class="elevation-1" id="order-administrator">
        <v-container grid-list-md pt-0>
            <v-list>
                <v-list-tile>
                    <v-list-tile-avatar>
                        <v-icon>assignment_ind</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-content>
                        <v-list-tile-title>
                            {{ $t('order.orders.order_operator') }}
                            <span :style="{ 'color': 'grey', 'padding-left': '6px' }">
                                {{ $t('order.orders.order_admin_delete_warning') }}
                            </span>
                        </v-list-tile-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list>
            <v-layout column wrap>
                <v-flex xs12>
                    <v-card class="elevation-2">
                        <v-card-title>
                            {{ $t('businesses.admin') + '（' + admins.length + $t('businesses.person') + '）' }}
                            <v-tooltip top>
                                <v-btn
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="getAdminCandidateUsers"
                                >
                                    <v-icon>person_add</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.add') }}</span>
                            </v-tooltip>
                            <v-tooltip top>
                                <v-btn
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="changeAdminCheckBox"
                                >
                                    <v-icon>settings</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.edit') }}</span>
                            </v-tooltip>
                            <v-tooltip top v-model="showDeleteTooltip">
                                <v-btn
                                    v-if="adminDeleteBtnFlg"
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click = "deleteAdministrators"
                                >
                                    <v-icon>delete_forever</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.delete') }}</span>
                            </v-tooltip>

                        </v-card-title>
                        <v-card-actions style="overflow-x: auto;">
                            <template v-for="admin in admins">
                                <div :key="admin.id" class="px-2">
                                    <v-checkbox
                                        v-if="showAdminCheckBox"
                                        class="ma-0"
                                        hide-details style="justify-content: center; padding-left: 7px;"
                                        :input-value="admin.selected"
                                        @change="selectedAdminCandidateItem(admin)"
                                    ></v-checkbox>
                                    <v-avatar slot="activator" size="42px" class="ma-1">
                                        <img :src="admin.user_image_path">
                                    </v-avatar>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ admin.name }}</div>
                                </div>
                            </template>
                        </v-card-actions>
                    </v-card>
                </v-flex>
                <v-flex xs12>
                    <v-card class="elevation-2">
                        <v-card-title>
                            {{ $t('order.orders.order_sharer') + '（' + sharers.length + $t('businesses.person') + '）' }}
                            <v-tooltip top>
                                <v-btn
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="getSharerCandidateUsers"
                                >
                                    <v-icon>person_add</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.add') }}</span>
                            </v-tooltip>
                            <v-tooltip top>
                                <v-btn
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="changeSharerCheckBox"
                                >
                                    <v-icon>settings</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.edit') }}</span>
                            </v-tooltip>
                            <v-tooltip top v-model="showSharerDeleteTooltip">
                                <v-btn
                                    v-if="sharerDeleteBtnFlg"
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="deleteSharers"
                                >
                                    <v-icon>delete_forever</v-icon>
                                </v-btn>
                                <span>{{ $t('common.button.delete') }}</span>
                            </v-tooltip>

                        </v-card-title>
                        <v-card-actions style="overflow-x: auto;">
                            <template v-for="sharer in sharers">
                                <div :key="sharer.id" class="px-2">
                                    <v-checkbox
                                        v-if="showSharerCheckBox"
                                        class="ma-0"
                                        hide-details
                                        style="justify-content: center; padding-left: 7px;"
                                        :input-value="sharer.selected"
                                        @change="selectedSharerCandidateItem(sharer)"
                                    ></v-checkbox>
                                    <v-avatar slot="activator" size="42px" class="ma-1">
                                        <img :src="sharer.user_image_path">
                                    </v-avatar>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ sharer.name }}</div>
                                </div>
                            </template>
                        </v-card-actions>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
        <alert-dialog ref="alert"></alert-dialog>
        <add-candidate-dialog
            ref="addCandidateDialog"
            :candidateUsers="candidateUsers"
            @add-administrators="addAdministrators"
            @add-sharers="addSharers"
        ></add-candidate-dialog>
    </div>
</template>

<script>
import AddCandidateDialog from './AddCandidateDialog'
import AlertDialog from '../../../../Atoms/Dialogs/AlertDialog'
export default {
    name: 'OrderOperator',
    components: {
        AddCandidateDialog,
        AlertDialog
    },
    props: {
        orderId: { type: String, required: true },
    },
    data: () =>({
        admins: [],
        sharers: [],
        orderCreateUserFlg: false,
        candidateUsers: [],
        showAdminCheckBox: false,
        showSharerCheckBox: false,
        selectedAdminCandidateIds: [],
        selectedSharerCandidateIds: [],
        adminDeleteBtnFlg: false,
        sharerDeleteBtnFlg: false,
        selected: [],
        sharerSelected: [],
        showDeleteTooltip: false,
        showSharerDeleteTooltip: false
    }),
    watch: {
        selectedAdminCandidateIds(){
            if ( this.selectedAdminCandidateIds.length > 0) {
                this.adminDeleteBtnFlg = true
            } else {
                this.adminDeleteBtnFlg =false
            }
        },
        selectedSharerCandidateIds(){
            if (this.selectedSharerCandidateIds.length > 0) {
                this.sharerDeleteBtnFlg = true
            } else {
                this.sharerDeleteBtnFlg = false
            }
        },
    },
    created(){
        this.getOrderCandidates()
    },
    methods:{
        changeAdminCheckBox(){
            this.selected = []
            this.selectedAdminCandidateIds = []
            this.showAdminCheckBox === false ? this.showAdminCheckBox = true : this.showAdminCheckBox = false
        },
        changeSharerCheckBox(){
            this.sharerSelected = []
            this.selectedSharerCandidateIds = []
            this.showSharerCheckBox === false ? this.showSharerCheckBox = true : this.showSharerCheckBox = false
        },
        selectedAdminCandidateItem(item){
            const candidateId = item.id

            if (!this.selectedAdminCandidateIds.includes(candidateId) && !this.selected.includes(item)) {
                this.selectedAdminCandidateIds.push(candidateId)
                this.selected.push(item)
            }
            else {
                this.selectedAdminCandidateIds = this.selectedAdminCandidateIds.filter(id => id !== candidateId)
                this.selected = this.selected.filter(item => item.id !== candidateId)
            }
        },
        selectedSharerCandidateItem(item){
            const candidateId = item.id

            if (!this.selectedSharerCandidateIds.includes(candidateId) && !this.sharerSelected.includes(item)) {
                this.selectedSharerCandidateIds.push(candidateId)
                this.sharerSelected.push(item)
            }
            else {
                this.selectedSharerCandidateIds = this.selectedSharerCandidateIds.filter(id => id !== candidateId)
                this.sharerSelected = this.sharerSelected.filter(item => item.id !== candidateId)
            }
        },
        getOrderCandidates(){
            axios.post('/api/order/orders/order_candidates', {
                order_id : this.orderId
            })
                .then((res) => {
                    if (res.data.status === 200) {
                        this.admins = res.data.order_admins
                        this.sharers = res.data.order_sharers
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        getAdminCandidateUsers(){
            axios.post('/api/order/orders/admin_candidate_users', {
                order_id : this.orderId
            })
                .then((res) => {
                    if (res.data.status === 200) {
                        this.candidateUsers = res.data.admin_candidate_users
                        this.$refs.addCandidateDialog.show(true)
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        getSharerCandidateUsers(){
            axios.post('/api/order/orders/sharer_candidate_users', {
                order_id : this.orderId
            })
                .then((res) => {
                    if (res.data.status === 200) {
                        this.candidateUsers = res.data.sharer_candidate_users
                        this.$refs.addCandidateDialog.show(false)
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        addAdministrators(users){
            this.loading = true
            axios.post('/api/order/orders/add_administrators', {
                order_admin_candidate_users : users,
                order_id : this.orderId
            })
                .then((res) => {
                    this.$refs.addCandidateDialog.close()
                    this.getOrderCandidates()
                    if (res.data.status !== 200) throw res.data.message
                })
                .catch((err) => {
                    console.log(err)
                    if (err === 'no_admin_permission') {
                        this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                })
        },
        addSharers(users){
            this.loading = true
            axios.post('/api/order/orders/add_sharers', {
                order_sharer_candidate_users : users,
                order_id : this.orderId
            })
                .then((res) => {
                    this.$refs.addCandidateDialog.close()
                    this.getOrderCandidates()
                    if (res.data.status !== 200) throw res.data.message
                })
                .catch((err) => {
                    console.log(err)
                    if (err === 'no_admin_permission') {
                        this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                })
        },
        deleteAdministrators(){
            axios.post('/api/order/orders/delete_administrators', {
                admin_candidate_ids : this.selectedAdminCandidateIds,
                order_id : this.orderId
            })
                .then((res) => {
                    if (res.data.status === 200) {
                        this.selected = []
                        this.selectedAdminCandidateIds = []
                        this.getOrderCandidates()
                        this.showAdminCheckBox = false
                        this.showDeleteTooltip = false
                    } else {
                        throw res.data.message
                    }
                })
                .catch((err) => {
                    console.log(err)
                    if (err === 'no_admin_permission') {
                        this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                })
        },
        deleteSharers(){
            axios.post('/api/order/orders/delete_sharers', {
                sharer_candidate_ids : this.selectedSharerCandidateIds,
                order_id : this.orderId
            })
                .then((res) => {
                    if (res.data.status === 200) {
                        this.sharerSelected = []
                        this.selectedSharerCandidateIds = []
                        this.getOrderCandidates()
                        this.showSharerCheckBox = false
                        this.showSharerDeleteTooltip = false
                    } else {
                        throw res.data.message
                    }
                })
                .catch((err) => {
                    console.log(err)
                    if (err === 'no_admin_permission') {
                        this.$refs.alert.show(this.$t('common.message.no_admin_permission'))
                    } else {
                        this.$refs.alert.show(this.$t('common.message.internal_error'))
                    }
                })
        },
    },
}
</script>

<style scoped>
#order-administrator{
    background-color: #ffffff;
}
</style>