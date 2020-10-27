<template>
    <v-flex xs12>
        <v-card>
            <v-container fluid grid-list-lg>
                <v-layout row align-center>
                    <v-flex xs2 ml-4 mr-4>
                        <div>
                            <v-card-media
                                :src="userData.user_image_path"
                                contain
                                style="border-radius: 50%;"
                            ></v-card-media>
                        </div>
                    </v-flex>
                    <v-flex xs10 style="align-items: center; display: flex;">
                        <div style="width:100%;">
                            <div class="headline font-weight-bold mb-3"><span>{{ userData.name }}</span></div>
                            <div class="text-xs-left">
                                <span class="">{{ $t('workers.task_count') }}</span>
                                <span class="caption">（※{{ $t('workers.captions.is_only_admin_business') }}）</span>
                            </div>
                            <v-divider class="ma-1"></v-divider>
                            <v-layout row wrap>
                                <v-flex xs4>
                                    <v-card class="allocation-condition text-xs-center">
                                        <v-card-text class="px-0">{{ $t('workers.whole') }}</v-card-text>
                                        <v-divider class="ma-0"></v-divider>
                                        <v-card-text class="px-0">
                                            <template>
                                                <v-layout pr-2 pl-2 align-center>
                                                    <v-flex>
                                                        <a :href="requestWorkUri('', '', userData.name, [])"><span class="display-1 font-weight-bold mr-1">{{ userData.total_task_count }}</span></a>{{ $t('list.case') }}
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                                <v-flex xs4>
                                    <v-card class="allocation-condition text-xs-center">
                                        <v-card-text class="px-0">{{ $t('workers.completed') }}</v-card-text>
                                        <v-divider class="ma-0"></v-divider>
                                        <v-card-text class="px-0">
                                            <template>
                                                <v-layout pr-2 pl-2 align-center>
                                                    <v-flex>
                                                        <a :href="requestWorkUri('', '', userData.name, [taskStatuses.done])"><span class="display-1 font-weight-bold mr-1">{{ userData.completed_task_count }}</span></a>{{ $t('list.case') }}
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                                <v-flex xs4>
                                    <v-card class="allocation-condition text-xs-center">
                                        <v-card-text class="px-0">{{ $t('workers.uncompleted_tasks') }}</v-card-text>
                                        <v-divider class="ma-0"></v-divider>
                                        <v-card-text class="px-0">
                                            <template>
                                                <v-layout pr-2 pl-2 align-center>
                                                    <v-flex>
                                                        <a :href="requestWorkUri('', '', userData.name, [taskStatuses.none, taskStatuses.on])"><span class="display-1 font-weight-bold mr-1">{{ userData.uncompleted_task_count }}</span></a>{{ $t('list.case') }}
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                        </v-card-text>
                                    </v-card>
                                    <div class="text-xs-right"><span class="caption">{{ $t('workers.estimated_time_is') }} - {{ $t('common.datetime.minute') }}（{{ $t('workers.prospect') }}）</span></div>
                                </v-flex>
                            </v-layout>
                        </div>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card>
    </v-flex>
</template>

<script>
const TASK_STATUSES = {
    'none': _const.TASK_STATUS.NONE,
    'on': _const.TASK_STATUS.ON,
    'done': _const.TASK_STATUS.DONE
}

export default {
    props: {
        workerId: { type: Number }
    },
    data: () => ({
        taskStatuses: TASK_STATUSES,
        userData: [],
    }),
    computed: {
        requestWorkUri () {
            return function (business_name, step_name, worker, statuses) {
                let uri = '/management/works' + '?business_name=' + encodeURIComponent(business_name)
                uri = uri + '&step_name=' + encodeURIComponent(step_name)
                uri = uri + '&worker=' + encodeURIComponent(worker)
                statuses.forEach(status => {
                    if (status === '42') return
                    uri = uri + '&' + encodeURIComponent('status[]') + '=' + encodeURIComponent(status)
                })
                return uri
            }
        }
    },
    created() {
        this.getWorkerDetails()
    },
    methods: {
        getWorkerDetails () {
            this.loading = true
            axios.post('/api/workers/show',{
                worker_id: this.workerId
            })
                .then((res) => {
                    this.userData = res.data.user_data
                })
                .catch((err) => {
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        }
    }
}
</script>
