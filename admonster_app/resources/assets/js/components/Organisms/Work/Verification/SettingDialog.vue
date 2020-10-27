<template>
    <v-dialog v-model="dialog" persistent max-width="500px">
        <v-card>
            <v-card-title
                class="setting-dialog-title"
                :style="{ backgroundColor: primaryColor, color: 'white' }"
            >
                <v-icon class="mr-3">settings</v-icon>
                <span>{{ $t('verification.setting_dialog.setting') }}</span>
                <v-spacer></v-spacer>
                <v-btn icon small @click="cancel()">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-divider class="my-0"></v-divider>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12 md4 style="padding: 0 16px;">
                        <v-text-field
                            hide-details
                            v-model="delivery"
                            :label="$t('verification.setting_dialog.delivery_data')"
                            suffix="Px"
                            type="number"
                        ></v-text-field>
                    </v-flex>
                    <v-flex xs12 md4 style="padding: 0 16px;">
                        <v-text-field
                            hide-details
                            v-model="myTask"
                            :label="$t('verification.setting_dialog.my_task_result')"
                            suffix="Px"
                            type="number"
                        ></v-text-field>
                    </v-flex>
                    <v-flex xs12 md4 style="padding: 0 16px;">
                        <v-text-field
                            hide-details
                            v-model="note"
                            :label="$t('verification.setting_dialog.note')"
                            suffix="Px"
                            type="number"
                        ></v-text-field>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click="ok()">{{ $t('common.button.ok') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import store from '../../../../stores/Work/Verification/store'

export default {
    props: {
        minWidth: { type: Number, required: false, default: 200 },
        maxWidth: { type: Number, required: false, default: null}
    },
    data: () => ({
        dialog: false,
        tableDataWidth: {}
    }),
    computed: {
        primaryColor () {
            return this.$vuetify.theme.primary
        },
        request() {
            return JSON.parse(JSON.stringify(store.state.processingData.request))
        },
        delivery: {
            set (val) {
                this.tableDataWidth.delivery = parseInt(val)
            },
            get () {
                return this.tableDataWidth.delivery
            }
        },
        myTask: {
            set (val) {
                this.tableDataWidth.myTask = parseInt(val)
            },
            get () {
                return this.tableDataWidth.myTask
            }
        },
        note: {
            set (val) {
                this.tableDataWidth.note = parseInt(val)
            },
            get () {
                return this.tableDataWidth.note
            }
        },
    },
    methods: {
        show () {
            this.tableDataWidth = Object.assign({}, store.state.processingWidth)
            this.dialog = true
        },
        ok () {
            // 入力された横幅の数値の制限
            for (const key in this.tableDataWidth) {
                if (key in this.tableDataWidth) {
                    this.tableDataWidth[key] = this.widthControl(this.tableDataWidth[key])
                }
            }
            store.dispatch('setAppearanceState',({stepId: this.request.step_id, val: this.tableDataWidth}))
            this.dialog = false
        },
        cancel () {
            this.dialog = false
        },
        widthControl (width) {
            if (this.minWidth > width) {
                return this.minWidth
            } else if (this.maxWidth !== null && width > this.maxWidth) {
                return this.maxWidth
            }
            return width
        },
    }
}
</script>

<style scoped>
.v-card__title {
    padding: 0 0 0 16px;
}
.setting-dialog-title .v-icon {
    color: #fff;
    caret-color: #fff;
}
</style>
