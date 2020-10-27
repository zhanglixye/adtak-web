<template>
    <!-- テーブルカスタムモーダル -->
    <div id="table-custom-modal-block">
        <v-dialog v-model="isShowModal" persistent max-width="210px">
            <v-card>
                <v-card-title id="header" class="pb-2">
                    <span class="headline">{{ $t('approvals.table_custom.width_title') }}</span>
                    <v-spacer></v-spacer>
                    <v-icon @click="close" >close</v-icon>
                </v-card-title>
                <v-card-text class="mt-2">
                    <v-text-field
                        id="td-width"
                        outline
                        v-model="comWidth"
                        suffix="Px"
                        type="number"
                        hide-details
                        @keyup.enter="save"
                    ></v-text-field>
                </v-card-text>
                <v-card-actions class="justify-end" >
                    <v-btn color="grey" dark @click="close">{{ $t('common.button.cancel') }}</v-btn>
                    <v-btn color="primary" @click="save">{{ $t('common.button.save') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
    <!-- / 表頭カスタムモーダル -->
</template>

<script>
export default {
    components: {
    },
    props: {
        width: { type: Number, required: true, default: 200 },
        minWidth: { type: Number, required: false, default: 0 },
        maxWidth: { type: Number, required: false, default: null}
    },
    data: () => ({
        isShowModal: false,
        dataWidth: null
    }),
    computed: {
        comWidth: {
            set(val) {
                this.dataWidth = Number(val)
            },
            get () {
                return this.dataWidth === null? this.width: this.dataWidth
            }
        }
    },
    methods: {
        show() {
            this.isShowModal = true
        },
        close() {
            this.isShowModal = false
        },
        save() {

            // 最小幅と最大幅の設定を反映させる
            if (this.minWidth > this.comWidth) {
                this.comWidth = this.minWidth
            } else if (this.maxWidth !== null && this.comWidth > this.maxWidth) {
                this.comWidth = this.maxWidth
            }
            this.$emit('update:width', Number(this.comWidth))
            this.isShowModal = false
        }
    }
}
</script>

<style scoped>
.v-input >>> #td-width {
    margin-top: 0;
    min-height: inherit;
}

.v-card >>> .v-input__slot {
    min-height: auto;
}

#header {
    border-bottom: 1px solid rgba(0, 0, 0, .54);
}

.handle {
    float: left;
    padding-top: 8px;
    padding-bottom: 8px;
}

.headline {
    font-size: 16px !important;
    line-height: 1 !important;
}

.v-text-field {
    padding-top : 0px !important;
    margin-top : 0px !important;
}
input {
    display: inline-block;
    width: 70%;
}
.header-list {
    overflow-x: hidden;
    overflow-y: scroll;
}
.list-group-item {
    height: 40px;
    padding-top: 0px !important;
    padding-bottom: 0px !important;
}
.list-group {
    margin-bottom: 0px !important;
}
</style>
