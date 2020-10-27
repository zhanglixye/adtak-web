<template>
    <div>
    <!-- toolbar -->
        <v-layout row wrap align-center>
            <v-tooltip top>
                <v-btn
                        :disabled ="BtnDisabledFlg"
                        icon
                        small
                        color="primary"
                        slot="activator"
                        @click="addWorkerCandidateDialogShow"
                >
                    <v-icon>person_add</v-icon>
                </v-btn>
                <span>{{ $t('list.add_user') }}</span>
            </v-tooltip>
            <v-tooltip top>
                <v-btn
                        :disabled ="BtnDisabledFlg"
                        icon
                        small
                        color="primary"
                        slot="activator"
                        @click="WorksUserCheckBoxFlg = !WorksUserCheckBoxFlg"
                >
                    <v-icon>settings</v-icon>
                </v-btn>
                <span>{{ $t('list.add_user') }}</span>
            </v-tooltip>
            <v-tooltip top>
                <v-btn
                        v-if="deleteBtnFlg"
                        icon
                        small
                        color="primary"
                        slot="activator"
                        disable
                        @click = "deleteEducationalWorksUser"
                >
                    <v-icon>delete_forever</v-icon>
                </v-btn>
                <span>{{ $t('list.add_user') }}</span>
            </v-tooltip>
        </v-layout>
    <!-- toolbar -->
    <!-- table -->
        <v-data-table
        :headers="headers"
        :items="steps"
        hide-actions
        class="elevation-1"
        >
            <template slot="headerCell" slot-scope="props">
                <v-tooltip top>
                    <span slot="activator">
                        {{ props.header.text }}
                    </span>
                    <span>
                        {{ props.header.text }}
                    </span>
                    <span v-if="props.header.detail">
                        {{ props.header.detail }}
                    </span>
                </v-tooltip>
            </template>
            <template slot="items" slot-scope="props">
                <tr :active="props.selected" style="cursor:pointer;">
                    <td>
                        <!--<input type="radio" name="checkedStep" v-model="checkedStep"  :value="props.item">-->
                        <v-radio-group v-model="checkedStep" :mandatory="false" style="width: 10px!important">
                            <v-radio  :value="props.item" style="margin-top: 15px; width: 10px!important"></v-radio>
                        </v-radio-group>
                        <!--<v-checkbox-->
                                <!--:input-value="props.selected"-->
                                <!--primary-->
                                <!--hide-details-->
                                <!--color="primary"-->
                                <!--@change="selectedItem(props.item)"-->
                        <!--&gt;</v-checkbox>-->
                    </td>
                    <td class="center">{{ props.item.step_name }}</td>
                    <td class="text-xs-left">
                        <!-- 名前も表示 -->
                        <!-- <div d-flex style="display: flex;">
                            <template v-for="user_id in users(props.item)">
                                <div :key="user_id" class="px-2">
                                    <v-avatar slot="activator" size="42px" class="ma-1">
                                        <img :src="user_image_path(user_id)">
                                    </v-avatar>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ user_name(user_id) }}</div>
                                </div>
                            </template>
                        </div> -->
                        <!-- 名前はツールチップで表示 -->
                        <div d-flex style="display: flex;">
                            <template v-for="user_id in users(props.item)">
                                <div :key="user_id" class="px-2">
                                    <v-checkbox
                                            v-show="WorksUserCheckBoxFlg && checkedStep===props.item"
                                            class="ma-0"
                                            hide-details style="justify-content: center;padding-left: 6px;"
                                            :input-value="user_id.Selected"
                                            @change="selectedItem(user_id)"
                                    ></v-checkbox>
                                    <v-tooltip top>
                                        <v-avatar slot="activator" size="32px" class="ma-2">
                                            <img :src="user_image_path(user_id)">
                                        </v-avatar>
                                        <span>{{ user_name(user_id) }}</span>
                                    </v-tooltip>
                                    <div class="caption text-xs-center" style="white-space: nowrap;">{{ user_name(user_id) }}</div>
                                </div>
                            </template>
                        </div>
                    </td>
                </tr>
            </template>
            <template slot="no-data">
                <div class="text-xs-center">{{ $t('common.pagination.no_data') }}</div>
            </template>
        </v-data-table>
    <!-- table -->
        <add-candidate-dialog
                ref="AddEducationalWorkerCandidateDialog"
                :candidate_users="educational_works_candidate_users"
                :candidateFlg="candidateFlg"
                @add-educational-worker-user="addEducationalWorksUsers"
        >
        </add-candidate-dialog>
    </div>
</template>
<script>
import AddCandidateDialog from './AddCandidateDialog'
export default {
    data:()=>({
        selected: [],
        candidateFlg: 0,
        checkedStep: {},
        BtnDisabledFlg: true,
        WorksUserCheckBoxFlg: false,
        deleteBtnFlg: false
    }),
    components: {
        AddCandidateDialog
    },
    props: {
        steps: { type: Array, required: true  },
        candidates: { candidates: Array, required: true }
    },
    computed: {
        headers (){
            return  [
                { text: '', value: 'businesses.step_id', align: 'center', sortable: false },
                { text: this.$t('businesses.step_name'), value: 'step_name', align: 'center', sortable: false, width: '20%'},
                { text: this.$t('businesses.educational_works_user'), value: 'educational_works_user', align: 'center', sortable: false},
            ]
        },
        educational_works_candidate_users() {
            let educational_works_candidate_users = this.candidates
            let work_user_ids = this.users(this.checkedStep)
            work_user_ids.forEach(function (work_user_id) {
                educational_works_candidate_users = educational_works_candidate_users.filter(user => user.id != work_user_id)
            })
            return educational_works_candidate_users
        }
    },
    methods: {
        users (step) {
            if (step){
                let educational_work_user_ids = step.educational_work_user_ids ? step.educational_work_user_ids.split(',') : [];
                return educational_work_user_ids;
            }
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
        selectedItem(user_id){

            if (!this.selected.includes(user_id) ) {
                this.selected.push(user_id)
            }
            else {
                this.selected = this.selected.filter(id => id !== user_id)
            }
        },
        addWorkerCandidateDialogShow: async function () {
            this.candidateFlg = _const.ADD_USER.EDUCATIONAL_WORKS_USER
            this.$refs.AddEducationalWorkerCandidateDialog.show()
        },
        deleteEducationalWorksUser(){
            axios.post('/api/master/businesses/deleteEducationalWorksUser', {
                delete_educational_works_user_ids : this.selected,
                step_id : this.checkedStep.step_id
            })
                .then((res) => {
                    console.log(res)
                    if (res.data.status === 200){
                        this.selected = []
                        this.checkedStep = {}
                        this.WorksUserCheckBoxFlg = false
                        this.$emit('update-detail')
                    }
                })
                .catch((err) => {
                    console.log(err)
                })
        },
        addEducationalWorksUsers(users){
            axios.post('/api/master/businesses/addEducationalWorksUsers', {
                educational_work_candidate_users : users,
                step_id : this.checkedStep.step_id
            })
                .then((res) => {
                    console.log(res)
                    this.$refs.AddEducationalWorkerCandidateDialog.close()
                    this.checkedStep = {}
                    this.$emit('update-detail')
                })
                .catch((err) => {
                    console.log(err)
                })
        }
    },
    watch: {
        checkedStep(){
            if (!(JSON.stringify(this.checkedStep) == '{}')) {
                this.BtnDisabledFlg = false
            } else {
                this.BtnDisabledFlg = true
            }
        },
        selected(){
            if (this.selected.length > 0) {
                this.deleteBtnFlg = true
            } else {
                this.deleteBtnFlg = false

            }
        }

    }
}
</script>
