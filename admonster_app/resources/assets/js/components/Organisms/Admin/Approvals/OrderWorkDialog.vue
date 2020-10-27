<template>
    <v-dialog v-model="dialog" persistent max-width="600">
        <v-card>
            <v-container>
                <v-card-text>
                    <v-layout>
                        <v-flex xs6>
                            <v-text-field
                                :label="$t('approvals.dialog.order_work.worker.label')"
                                :value="name"
                                readonly
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-textarea
                        v-model="comment"
                        :label="$t('approvals.dialog.order_work.reason.label')"
                        hide-details
                        no-resize
                    ></v-textarea>
                </v-card-text>
                <v-card-actions class="btn-center-block">
                    <v-btn dark color="grey" @click.native="cancel">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn color="primary" @click.native="ok">{{ $t('common.button.ok') }}</v-btn>
                </v-card-actions>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<script>

export default {
    props: {
        propComment: { type: String, require: true }
    },
    data() {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            comment: '',
            name: null,
            items: []
        }
    },
    methods: {
        show (name, comment='') {
            this.name = name
            this.comment = comment
            this.dialog = true
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        ok () {
            // 親要素に選択した値とコメントを返す
            this.resolve({result: true, comment: this.comment})
            this.dialog = false
        },
        cancel () {
            this.resolve({result: false})
            this.resolve({isChecked: false})
            this.dialog = false
        }
    },
}
</script>
