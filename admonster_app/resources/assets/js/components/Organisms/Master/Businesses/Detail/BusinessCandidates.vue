<template>
    <div id="business-candidates" class="elevation-1">
        <v-list>
            <v-list-tile>
                <v-list-tile-avatar>
                    <v-icon>assignment_ind</v-icon>
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t('businesses.detail.candidates') }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
        <v-container grid-list-md pt-0 id="business-candidates-main">
            <v-layout column wrap>
                <v-flex xs12>
                    <v-card class="elevation-2">
                        <v-card-title>
                            {{ $t('businesses.admin') + '（' + admins.length + $t('businesses.person') + '）' }}
                            <v-btn
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="addAdminCandidateDialogShow"
                            >
                                <v-icon>person_add</v-icon>
                            </v-btn>
                            <v-btn
                                    icon
                                    small
                                    color="primary"
                                    slot="activator"
                                    @click="changeAdminCheckBox"
                            >
                                <v-icon>settings</v-icon>
                            </v-btn>
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
                        </v-card-title>
                        <v-card-actions style="overflow-x: auto;">
                            <template v-for="admin in admins">
                                <div :key="admin.id" class="px-2">
                                    <v-checkbox
                                            v-show="showAdminCheckBox"
                                            class="ma-0"
                                            style="justify-content: center;padding-left: 7px;"
                                            hide-details
                                            color="primary"
                                            :input-value="admin.adminSelected"
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
                            {{ $t('businesses.operator') + '（' + candidates.length + $t('businesses.person') + '）' }}
                            <v-btn
                                icon
                                small
                                color="primary"
                                slot="activator"
                                @click="addOperatorCandidateDialogShow"
                            >
                                <v-icon>person_add</v-icon>
                            </v-btn>
                            <v-btn
                                icon
                                small
                                color="primary"
                                slot="activator"
                                @click="changeCandidateCheckBox"
                            >
                                <v-icon>settings</v-icon>
                            </v-btn>
                            <v-btn
                                v-if="deleteBtnFlg"
                                icon
                                small
                                color="primary"
                                slot="activator"
                                disable
                                @click = "deleteCandidate"
                            >
                                <v-icon>delete_forever</v-icon>
                            </v-btn>
                        </v-card-title>

                        <v-card-actions style="overflow-x: auto;">
                            <template v-for="candidate in candidates">
                                <div :key="candidate.id" class="px-2" flex>
                                    <v-checkbox
                                        v-show="showCandidateCheckBox"
                                        class="ma-0"
                                        hide-details style="justify-content: center;padding-left: 7px;"
                                        :input-value="candidate.candidateSelected"
                                        @change="selectedCandidateItem(candidate)"
                                    ></v-checkbox>
                                    <v-avatar slot="activator" size="42px" class="ma-1">
                                        <img :src="candidate.user_image_path">
                                    </v-avatar>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ candidate.name }}</div>
                                </div>
                            </template>
                        </v-card-actions>
                        <progress-circular v-if="loading"></progress-circular>
                        <add-candidate-dialog
                            ref="AddAdminCandidateDialog"
                            :candidate_users="admin_candidate_users"
                            :candidateFlg="candidateFlg"
                            @add-administrators="addAdministrators"
                        ></add-candidate-dialog>
                        <add-candidate-dialog
                                ref="AddOperatorCandidateDialog"
                                :candidate_users="operator_candidate_users"
                                :candidateFlg="candidateFlg"
                                @add-candidates="addCandidates"
                        ></add-candidate-dialog>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
    </div>
</template>

<script>
import ProgressCircular from '../../../../Atoms/Progress/ProgressCircular'
import AddCandidateDialog from './AddCandidateDialog'
export default {
    components: {
        AddCandidateDialog,
        ProgressCircular
    },
    props: {
        candidates: { type: Array, required: true },
        admins: { type: Array, required: true },
        admin_candidate_users: { type: Array, required: true },
        operator_candidate_users: { type: Array, required: true },
        business_id:{type: Number, required: true }
    },
    data: () => ({
        showAdminCheckBox: false,
        showCandidateCheckBox: false,
        selectedCandidateIds: [],
        selectedAdminCandidateIds: [],
        adminSelected: [],
        candidateSelected: [],
        loading: false,
        candidateFlg: 0,
        deleteBtnFlg: false,
        adminDeleteBtnFlg: false
    }),
    methods:{
        changeCandidateCheckBox(){
            this.candidateSelected = []
            this.selectedCandidateIds = []
            this.showCandidateCheckBox === false ? this.showCandidateCheckBox = true : this.showCandidateCheckBox = false
        },
        changeAdminCheckBox(){
            this.adminSelected = []
            this.selectedAdminCandidateIds = []
            this.showAdminCheckBox === false ? this.showAdminCheckBox = true : this.showAdminCheckBox = false
        },
        selectedCandidateItem(item){
            const candidateId = item.id
            if (!this.selectedCandidateIds.includes(candidateId) && !this.candidateSelected.includes(candidateId)) {
                this.selectedCandidateIds.push(candidateId)
                this.candidateSelected.push(item)
            }
            else {
                this.selectedCandidateIds = this.selectedCandidateIds.filter(id => id !== candidateId)
                this.candidateSelected = this.candidateSelected.filter(item => item.id !== candidateId)
            }
        },
        selectedAdminCandidateItem(item){
            const candidateId = item.id
            if (!this.selectedAdminCandidateIds.includes(candidateId) && !this.adminSelected.includes(candidateId)) {
                this.selectedAdminCandidateIds.push(candidateId)
                this.adminSelected.push(item)
            }
            else {
                this.selectedAdminCandidateIds = this.selectedAdminCandidateIds.filter(id => id !== candidateId)
                this.adminSelected = this.adminSelected.filter(item => item.id !== candidateId)
            }
        },
        deleteCandidate(){
            // this.loading = true
            console.log(this.selectedCandidateIds)
            axios.post('/api/master/businesses/deleteCandidates', {
                deleteCandidateIds : this.selectedCandidateIds,
                businessId : this.business_id
            })
                .then((res) => {
                    console.log(res)
                    if (res.data.status === 200){
                        this.candidateSelected = []
                        this.selectedCandidateIds = []
                        this.showCandidateCheckBox = false
                        this.$emit('update-detail')
                        // this.loading = false
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        deleteAdministrators(){
            // this.loading = true
            console.log(this.selectedCandidateIds)
            axios.post('/api/master/businesses/deleteAdministrators', {
                deleteCandidateIds : this.selectedAdminCandidateIds,
                businessId : this.business_id
            })
                .then((res) => {
                    console.log(res)
                    if (res.data.status === 200){
                        this.adminSelected = []
                        this.selectedAdminCandidateIds = []
                        this.showCandidateCheckBox = false
                        this.$emit('update-detail')
                        this.showAdminCheckBox = false
                        // this.loading = false
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        addOperatorCandidateDialogShow: async function () {
            this.candidateFlg = _const.ADD_USER.BUSINESS_OPERATOR
            this.$refs.AddOperatorCandidateDialog.show()
        },
        addAdminCandidateDialogShow: async function () {
            this.candidateFlg = _const.ADD_USER.BUSINESS_ADMIN
            this.$refs.AddAdminCandidateDialog.show()
        },
        addCandidates(users){
            // this.loading = true
            axios.post('/api/master/businesses/addCandidates', {
                operatorCandidateUsers : users,
                businessId : this.business_id
            })
                .then((res) => {
                    console.log(res)
                    this.$refs.AddOperatorCandidateDialog.close()
                    this.$emit('update-detail')
                    // this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        addAdministrators(users){
            // this.loading = true
            axios.post('/api/master/businesses/addAdministrators', {
                adminCandidateUsers : users,
                businessId : this.business_id
            })
                .then((res) => {
                    console.log(res)
                    this.$refs.AddAdminCandidateDialog.close()
                    this.$emit('update-detail')
                    // this.loading = false
                })
                .catch((err) => {
                    console.log(err)
                })
        }
    },
    watch: {
        selectedAdminCandidateIds(){
            if ( this.selectedAdminCandidateIds.length > 0){
                this.adminDeleteBtnFlg = true
            } else {
                this.adminDeleteBtnFlg =false
            }
        },
        selectedCandidateIds(){
            if ( this.selectedCandidateIds.length > 0){
                this.deleteBtnFlg = true
            } else {
                this.deleteBtnFlg = false
            }
        }
    }
}
</script>

<style scoped>
#business-candidates {
    background-color: #ffffff;
}
#business-candidates-main {
    height: 350px;
}
</style>
