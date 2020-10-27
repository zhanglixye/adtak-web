<template>
    <div>
        <v-menu
            ref="dateMenu"
            v-model="dateMenu"
            :close-on-content-click="false"
            :disabled="disabled"
            full-width
            lazy
            max-width="290"
            :nudge-right="40"
            offset-y
            :readonly="readonly"
            transition="scale-transition"
        >
            <template slot="activator">
                <v-text-field
                    v-model="dateText"
                    :append-icon="appendIcon"
                    :disabled="disabled"
                    :hide-details="hideDetails"
                    :label="label"
                    :placeholder="dateFormat"
                    :readonly="readonly"
                    :rules="rules"
                    @change="updateByText()"
                    @keyup.enter="enterEvent()"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="dateValue"
                :day-format="date => new Date(date).getDate()"
                :locale="userLanguage"
                :month-format="date => new Date(date).getMonth() + 1 + $t('common.datetime.month')"
                no-title
                :type="pickerType"
                @change="$refs.dateMenu.save(dateValue)"
            >
            </v-date-picker>
        </v-menu>
    </div>

</template>

<script>
export default {
    props: {
        appendIcon: { type: String, required: false, default: '' },
        dateTo: { type: Boolean, required: false, default: false },
        disabled: { type: Boolean, required: false, default: false },
        enterEvent: { type: Function, required: false, default: () => {} },
        hideDetails: { type: Boolean, required: false, default: false },
        label: { type: String, required: false, default: '' },
        pickerType: { type: String, required: false, default: 'date', validator: prop => ['month', 'date'].indexOf(prop) !== -1 },
        readonly: { type: Boolean, required: false, default: false },
        rules: { type: Array, required: false, default: () => [] },
        value: { required: true, validator: prop => typeof prop === 'string' || prop === null },
    },
    data: () => ({
        // date
        dateMenu: false,  // メニューの開閉
        dateValue: null,  // 値
        dateText: '',  // テキスト
    }),
    computed: {
        dateFormat () {
            return this.pickerType === 'month' ? 'YYYY/MM' : 'YYYY/MM/DD'
        },
        dateValueFormat () {
            return this.pickerType === 'month' ? 'YYYY-MM' : 'YYYY-MM-DD'
        },
        userLanguage () {
            // TODO: ユーザの言語設定が可能になったら合わせて変更できるようにする
            return 'ja-jp'
        },
        userTimezone () {
            return moment().tz()
        }
    },
    watch: {
        value () {
            this.setDateValue()
        },
        dateValue () {
            const momentDate = moment.tz(this.dateValue, this.dateValueFormat, true, this.userTimezone)
            this.dateText = momentDate.isValid() ? momentDate.format(this.dateFormat) : ''
            this.$emit('input', this.formatUtcValue(this.dateText))
        },
    },
    created () {
        this.setDateValue()
    },
    methods: {
        formatUtcValue (date) {
            const momentDate = moment.tz(date, this.dateFormat, this.userTimezone)
            return this.dateTo ? momentDate.endOf('day').toISOString() : momentDate.startOf('day').toISOString()
        },
        setDateValue () {
            // 一律、ISOStringに変換する（v-modelで渡される日付はUTC前提）
            const ISOString = moment.utc(this.value).toISOString()
            const momentDate = moment(ISOString)
            this.dateValue = momentDate.isValid() ? momentDate.format(this.dateValueFormat) : null
        },
        // 手入力した場合
        updateByText () {
            this.dateMenu = false
            const momentDate = moment.tz(this.dateText, this.dateFormat, true, this.userTimezone)
            this.dateValue = momentDate.isValid() ? momentDate.format(this.dateValueFormat) : null
        },
    },
}
</script>
