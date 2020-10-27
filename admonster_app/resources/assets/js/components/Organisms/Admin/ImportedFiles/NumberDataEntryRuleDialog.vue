<template>
    <v-dialog v-model="dialog" persistent width="500">
        <v-card>
            <v-card-title class="pb-2">
                <span class="text-color headline">
                    {{ $t('imported_files.order_imported_setting.number_data_entry_rule.subject') }}
                </span>
            </v-card-title>
            <v-card-text style="text-align: left;">
                <div class="text-color">
                    {{ $t('imported_files.order_imported_setting.number_data_entry_rule.rule_preference') }}
                </div>
                <span class="indent-interval">
                    <div style="margin-top: 15px;" class="text-color">
                        {{ $t('imported_files.order_imported_setting.number_data_entry_rule.upper_limit') }}
                    </div>
                    <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                    <v-form v-model="upperValid" @submit.prevent style="width: 200px;">
                        <v-text-field
                            class="centered-input"
                            v-model="viewLimit"
                            :rules="[rules.numCheck, upperCheck]"
                            style="max-width: 200px;"
                            @click.stop=""
                            @focus="isUpperInputMode = true"
                            @blur="blurUpperLimit"
                            reverse
                            hint="999,999,999,999 ~ -999,999,999,999"
                            :disabled="readonly"
                        ></v-text-field>
                    </v-form>
                </span>
                <span class="indent-interval">
                    <div style="margin-top: 15px;" class="text-color">
                        {{ $t('imported_files.order_imported_setting.number_data_entry_rule.lower_limit') }}
                    </div>
                    <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                    <v-form v-model="lowerValid" @submit.prevent style="width: 200px;">
                        <v-text-field
                            class="centered-input"
                            v-model="viewLowerLimit"
                            :rules="[rules.numCheck, lowerCheck]"
                            style="max-width: 200px;"
                            @click.stop=""
                            @focus="isInputMode = true"
                            @blur="blurLowerLimit"
                            reverse
                            hint="999,999,999,999 ~ -999,999,999,999"
                            :disabled="readonly"
                        ></v-text-field>
                    </v-form>
                </span>
                <span class="indent-interval">
                    <div class="text-color" style="margin-top: auto; margin-bottom: auto; margin-right: 10px">
                        {{ $t('imported_files.order_imported_setting.number_data_entry_rule.required') }}
                    </div>
                    <v-switch v-model="isInputRequired" :disabled="readonly" color="primary"></v-switch>
                </span>
                <span>
                    <div style="margin-top: 15px;" class="text-color">
                        {{ $t('imported_files.order_imported_setting.number_data_entry_rule.display_format') }}
                    </div>
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
                            :disabled="readonly"
                        ></v-checkbox>
                    </span>
                </span>
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
            selectedCheckBoxItems: [],
            index: null,
            isInputRequired: false,
            upperValid: false,
            lowerValid: false,
            dialog: false,
            rules: {
                numCheck: v => {
                    v = v.toString().replace(/,/g, '')
                    const font = /[^\d-.]/.test(v)
                    if (font) {
                        // 文字
                        return Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.contain_text')
                    } else {
                        const minus = /-+/.test(v)
                        if (minus) {
                            const minusCount = v.match(/-/g).length
                            if (minusCount > 1) {
                                // 複数
                                return Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.minus_included_multiple')
                            } else {
                                const minusPosition = /^-/.test(v);
                                if (!minusPosition){
                                    // 位置
                                    return Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.minus_position_is_different')
                                }
                            }
                            if (v.length <= 1) return Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.not_input_number')
                            return Number(v) >= -999999999999 || Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.less_than_minimum_value')
                        }
                    }
                    return Number(v) <= 999999999999 || Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.exceeded_maximum_value')
                },
            },
            beforeSize: { upperLimit: '', lowerLimit: '' },
            size: { upperLimit: '', lowerLimit: '' },
            isUpperInputMode: false, // true: ####### false: ###,###,###
            beforeIsInputRequired: false,
            isInputMode: false, // true: ####### false: ###,###,###
        }
    },
    computed: {
        checkBoxItems () {
            const displayFormats = []
            for (const [displayFormatKey, displayFormatValue] of Object.entries(_const.DISPLAY_FORMAT)) {
                const end = displayFormatKey.indexOf('_')
                if (!['NUM'].includes(displayFormatKey.substr(0, end))) continue
                displayFormats.push(
                    {
                        value: displayFormatValue,
                        text: this.$t(`order.order_details.dialog.setting_display_format.item_type_${_const.ITEM_TYPE.NUM.ID}.${_const.PREFIX}${displayFormatValue}`)
                    }
                )
            }
            return displayFormats
        },
        lowerCheck () {
            return this.size.upperLimit === '' ||
                this.size.lowerLimit === '' ||
                isNaN(this.size.upperLimit) ||
                Number(this.size.lowerLimit.toString().replace(/,/g, '')) <= Number(this.size.upperLimit.toString().replace(/,/g, '')) ||
                Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.max_than_value_is_large')
        },
        upperCheck () {
            return this.size.lowerLimit === '' ||
                this.size.upperLimit === '' ||
                isNaN(this.size.lowerLimit) ||
                Number(this.size.lowerLimit.toString().replace(/,/g, '')) <= Number(this.size.upperLimit.toString().replace(/,/g, '')) ||
                Vue.i18n.translate('imported_files.order_imported_setting.number_data_entry_rule.error_message.lower_limit_than_value_is_small')
        },
        disabledButton () {
            return !this.lowerValid || !this.upperValid
        },
        dataType () {
            return _const.ITEM_TYPE.NUM.ID
        },
        viewLimit: {
            set (val) {
                this.size.upperLimit = val
            },
            get () {
                if (this.isUpperInputMode) return this.size.upperLimit
                return this.size.upperLimit.toString().replace(/(\d)(?=(\d{3})+$)/g , '$1,')
            }
        },
        viewLowerLimit: {
            set (val) {
                this.size.lowerLimit = val
            },
            get () {
                if (this.isInputMode) return this.size.lowerLimit
                return this.size.lowerLimit.toString().replace(/(\d)(?=(\d{3})+$)/g , '$1,')
            }
        },
    },
    methods: {
        blurUpperLimit () {
            this.isUpperInputMode = false
            if (this.size.upperLimit === '') {
                // 処理を行わない
            } else if (/^00+$/.test(this.size.upperLimit)) { // すべて0の場合
                this.size.upperLimit = '0'
            } else if (/^0\d+/.test(this.size.upperLimit)) { // 先頭に0がついている場合
                this.size.upperLimit = this.size.upperLimit.replace(/^0+/ , '')
            }
            this.$emit('input', Number(this.size.upperLimit))
        },
        blurLowerLimit () {
            this.isInputMode = false
            if (this.size.lowerLimit === '') {
                // 処理を行わない
            } else if (/^00+$/.test(this.size.lowerLimit)) { // すべて0の場合
                this.size.lowerLimit = '0'
            } else if (/^0\d+/.test(this.size.lowerLimit)) { // 先頭に0がついている場合
                this.size.lowerLimit = this.size.lowerLimit.replace(/^0+/ , '')
            }
            this.$emit('input', Number(this.size.lowerLimit))
        },
        show (index, form) {
            if (form !== undefined) {
                if (form['inputRule'] !== null && form['inputRule'].selectType === this.dataType) {
                    this.size = Object.assign({}, form['inputRule'].size)
                    this.isInputRequired = form['inputRule'].isInputRequired
                    this.selectedCheckBoxItems = form['displayRule']
                }
                if (form['inputRule'] === null) {
                    this.selectedCheckBoxItems = form['displayRule']
                }
            }
            this.index = index
            this.beforeSize = Object.assign({}, this.size)
            this.beforeIsInputRequired = this.isInputRequired
            this.dialog = true
        },
        cancel () {
            this.size = Object.assign({}, this.beforeSize)
            this.isInputRequired = this.beforeIsInputRequired
            this.selectedCheckBoxItems = []
            this.dialog = false
        },
        setting () {
            const form = {
                inputRule: {
                    size: this.size,
                    isInputRequired: this.isInputRequired,
                    selectType: this.dataType,
                },
                displayRule: this.selectedCheckBoxItems
            }
            this.$emit('setting', form, this.index)
            this.dialog = false
        }
    }
}
</script>

<style scoped>
.text-color {
    color: rgba(0, 0, 0, 0.54);
    font-size: 14px;
    font-weight: 700;
}
.indent-interval {
    display: flex;
    margin-left: 32px;
}
</style>