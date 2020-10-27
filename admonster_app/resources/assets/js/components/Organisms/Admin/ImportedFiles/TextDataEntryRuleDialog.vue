<template>
    <v-dialog v-model="dialog" persistent width="500">
        <v-card>
            <v-card-title class="pb-2">
                <div class="text-color headline">
                    {{ $t('imported_files.order_imported_setting.text_data_entry_rule.subject') }}
                </div>
            </v-card-title>
            <v-card-text style="text-align: left;">
                <table>
                    <tr>
                        <th style="width:150px">
                            <div class="text-color">
                                {{ $t('imported_files.order_imported_setting.text_data_entry_rule.upper_limit') }}
                            </div>
                        </th>
                        <th style="display: flex;">
                            <v-text-field
                                class="centered-input"
                                v-model="fontSize"
                                :mask="mask"
                                :rules="[rules.check]"
                                @click.stop=""
                                :hint="$t('imported_files.order_imported_setting.text_data_entry_rule.hint', {'max_length': maxLength})"
                                @focus="isInputMode=true"
                                @blur="blur"
                                :disabled="readonly"
                                reverse
                            ></v-text-field>
                            <div style="margin-top: 30px; margin-left: 10px;" class="text-color">
                                {{ $t('imported_files.order_imported_setting.text_data_entry_rule.text') }}
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th style="vertical-align:top;">
                            <div class="text-color">
                                {{ $t('imported_files.order_imported_setting.text_data_entry_rule.input_limitation') }}
                            </div>
                        </th>
                        <th>
                            <v-radio-group @click="clear()" v-model="isUseInputLimit" :disabled="readonly" style="max-height: 20px;">
                                <v-radio
                                    color="primary"
                                    :label="$t('imported_files.order_imported_setting.text_data_entry_rule.no_setting')"
                                    :value="inactive"
                                ></v-radio>
                            </v-radio-group>
                            <v-radio-group v-model="isUseInputLimit" :disabled="readonly" style="max-height: 20px;">
                                <v-radio
                                    color="primary"
                                    :label="$t('imported_files.order_imported_setting.text_data_entry_rule.setting')"
                                    :value="active"
                                ></v-radio>
                            </v-radio-group>
                            <span
                                v-for="checkBoxItem in checkBoxItems"
                                :style="{ 'display': 'flex', 'margin-top': '16px', 'margin-left': '32px' }"
                                :key="checkBoxItem.value"
                            >
                                <v-checkbox
                                    color="primary"
                                    :style="{ height: '30px', 'margin-top': '0px' }"
                                    :label="checkBoxItem.text"
                                    v-model="selectedCheckBoxItems"
                                    :value="checkBoxItem.value"
                                    :key="checkBoxItem.value"
                                    :disabled="readonly || isUseInputLimit === inactive"
                                ></v-checkbox>
                            </span>
                        </th>
                    </tr>
                    <tr>
                        <span style="display: flex;">
                            <div class="text-color" style="margin-top: auto; margin-bottom: auto; margin-right: 10px">
                                {{ $t('imported_files.order_imported_setting.text_data_entry_rule.required') }}
                            </div>
                            <v-switch v-model="isInputRequired" :disabled="readonly" color="primary"></v-switch>
                        </span>
                    </tr>
                </table>
            </v-card-text>
            <div style="display: flex;">
                <v-spacer></v-spacer>
                <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn
                    v-if="!readonly"
                    color="primary"
                    @click="setting()"
                    :disabled="disabledButton"
                >
                    {{ $t('common.button.setting') }}
                </v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        readonly: { type: Boolean, required: false, default: false },
    },
    data () {
        return {
            index: null,
            isInputRequired: false,
            beforeIsInputRequired: false,
            rules: {
                check: v => v <= this.maxLength || Vue.i18n.translate('imported_files.order_imported_setting.text_data_entry_rule.hint', {'max_length': this.maxLength}),
            },
            dialog: false,
            isUseInputLimit: _const.FLG.INACTIVE,
            beforeIsUseInputLimit: _const.FLG.INACTIVE,
            selectedCheckBoxItems: [],
            isInputMode: false, // true: ####### false: ###,###,###,
            fontSize: '',
            beforeFontSize: ''
        }
    },
    computed: {
        checkBoxItems () {
            return [
                {
                    text: Vue.i18n.translate('imported_files.order_imported_setting.text_data_entry_rule.input_text_format.' + _const.PREFIX + _const.INPUT_RULE.TEXT.HALFWIDTH_ALPHANUMERIC_SYMBOL),
                    value: _const.INPUT_RULE.TEXT.HALFWIDTH_ALPHANUMERIC_SYMBOL
                },
                {
                    text: Vue.i18n.translate('imported_files.order_imported_setting.text_data_entry_rule.input_text_format.' + _const.PREFIX + _const.INPUT_RULE.TEXT.FULLWIDTH_ALPHANUMERIC_SYMBOL),
                    value: _const.INPUT_RULE.TEXT.FULLWIDTH_ALPHANUMERIC_SYMBOL
                },
                {
                    text: Vue.i18n.translate('imported_files.order_imported_setting.text_data_entry_rule.input_text_format.' + _const.PREFIX + _const.INPUT_RULE.TEXT.FULLWIDTH_HIRAGANA_KATAKANA),
                    value: _const.INPUT_RULE.TEXT.FULLWIDTH_HIRAGANA_KATAKANA
                }
            ]
        },
        active () {
            return _const.FLG.ACTIVE
        },
        inactive () {
            return _const.FLG.INACTIVE
        },
        disabledButton () {
            return this.fontSize > this.maxLength
        },
        mask () {
            if (this.isInputMode) return '########'
            return this.fontSize.toString().replace(/(\d)(?=(\d{3})+$)/g , '$1,')
        },
        dataType () {
            return _const.ITEM_TYPE.STRING.ID
        },
        maxLength () {
            return 2000
        }
    },
    methods: {
        clear () {
            this.selectedCheckBoxItems = []
        },
        blur () {
            this.isInputMode = false
            if (this.fontSize === '') {
                // 処理を行わない
            } else if (/^00+$/.test(this.fontSize)) { // すべて0の場合
                this.fontSize = '0'
            } else if (/^0\d+/.test(this.fontSize)) { // 先頭に0がついている場合
                this.fontSize = this.fontSize.replace(/^0+/ , '')
            }
            this.$emit('input', Number(this.fontSize))
        },
        show (index, form) {
            if (form !== undefined) {
                if (form['inputRule'] !== null && form['inputRule'].selectType === this.dataType) {
                    this.isUseInputLimit = form['inputRule'].isUseInputLimit
                    this.selectedCheckBoxItems = form['inputRule'].selectedCheckBoxItems
                    this.fontSize = form['inputRule'].fontSize
                    this.isInputRequired = form['inputRule'].isInputRequired
                }
            }
            this.index = index
            this.beforeIsUseInputLimit = this.isUseInputLimit
            this.beforeSelectedCheckBoxItems = this.selectedCheckBoxItems
            this.beforeFontSize = this.fontSize
            this.beforeIsInputRequired = this.isInputRequired
            this.dialog = true
        },
        cancel () {
            this.isUseInputLimit = this.beforeIsUseInputLimit
            this.selectedCheckBoxItems = this.beforeSelectedCheckBoxItems
            this.fontSize = this.beforeFontSize
            this.isInputRequired = this.beforeIsInputRequired
            this.dialog = false
        },
        setting () {
            const form = {
                inputRule: {
                    isUseInputLimit: this.isUseInputLimit,
                    selectedCheckBoxItems: this.selectedCheckBoxItems,
                    fontSize: this.fontSize,
                    isInputRequired: this.isInputRequired,
                    selectType: this.dataType
                }
            }
            this.$emit('setting', form, this.index)
            this.dialog = false
        },
    }
}
</script>

<style scoped>
.text-color {
    color: rgba(0, 0, 0, 0.54);
    font-size: 14px;
    font-weight: 700;
}
</style>