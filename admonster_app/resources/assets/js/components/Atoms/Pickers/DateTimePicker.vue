<template>
    <div class="d-flex">
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
                    :disabled="disabled"
                    hide-details
                    :label="label"
                    :placeholder="dateFormat"
                    prepend-icon="event"
                    @change="updateByText('date')"
                    @keyup.enter="enterEvent()"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="dateValue"
                :day-format="date => new Date(date).getDate()"
                :locale="userLanguage"
                :month-format="date => new Date(date).getMonth() + 1 + $t('common.datetime.month')"
                no-title
                :readonly="readonly"
                @change="$refs.dateMenu.save(dateValue)"
            ></v-date-picker>
        </v-menu>
        <v-menu
            ref="timeMenu"
            v-model="timeMenu"
            :close-on-content-click="false"
            :disabled="disabledTimePicker"
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
                    v-model="timeText"
                    :disabled="disabledTimePicker"
                    hide-details
                    :placeholder="timeFormat"
                    prepend-icon="access_time"
                    :readonly="readonly"
                    @change="updateByText('time')"
                    @keyup.enter="enterEvent()"
                ></v-text-field>
            </template>
            <v-time-picker
                v-if="timeMenu"
                v-model="timeValue"
                format="24hr"
                @change="$refs.timeMenu.save(timeValue)"
            ></v-time-picker>
        </v-menu>
    </div>
</template>

<script>
export default {
    props: {
        dateTo: { type: Boolean, required: false, default: false },
        disabled: { type: Boolean, required: false, default: false },
        enterEvent: { type: Function, required: false, default: function () {} },
        label: { type: String, required: false, default: '' },
        readonly: { type: Boolean, required: false, default: false },
        value: { required: true, validator: prop => typeof prop === 'string' || prop === null },
    },
    data: () =>({
        // date
        dateMenu: false,  // メニューの開閉
        dateValue: null,  // 値
        dateText: '',  // テキスト
        // time
        timeMenu: false,  // メニューの開閉
        timeValue: null,  // 値
        timeText: '',  // テキスト
    }),
    computed: {
        dateFormat () {
            return 'YYYY/MM/DD'
        },
        dateValueFormat () {
            return 'YYYY-MM-DD'
        },
        timeFormat () {
            return 'HH:mm'
        },
        datetimeValue () {
            let date = ''
            let time = ''
            if (this.dateValue) {
                date = this.dateValue
            }
            if (this.timeValue) {
                time = this.timeValue
            }
            return date + ' ' + time
            // TODO: eslintで許容
            // return (this.dateValue ?? '') + ' ' + (this.timeValue ?? '')
        },
        disabledTimePicker () {
            return this.disabled || !this.dateValue
        },
        userLanguage () {
            // TODO: ユーザの言語設定が可能になったら合わせて変更できるようにする
            return 'ja-jp'
        },
        userTimezone () {
            return moment().tz()
        },
    },
    created () {
        this.setDateValue()
    },
    watch: {
        value () {
            this.setDateValue()
        },
        dateValue () {
            const momentDate = moment.tz(this.dateValue, this.dateValueFormat, true, this.userTimezone)
            this.dateText = momentDate.isValid() ? momentDate.format(this.dateFormat) : ''
            // 未入力であれば時間を自動補完
            if (this.dateValue && !this.timeValue) {
                const momentDate = moment.tz(this.dateValue, this.dateValueFormat, true, this.userTimezone)
                this.timeValue = this.dateTo ? momentDate.endOf('day').format(this.timeFormat) : momentDate.startOf('day').format(this.timeFormat)
            }
            this.$emit('input', this.formatUtcValue(this.datetimeValue))
        },
        timeValue () {
            const momentTime = moment.tz(this.timeValue, this.timeFormat, true, this.userTimezone)
            this.timeText = momentTime.isValid() ? momentTime.format(this.timeFormat) : ''
            this.$emit('input', this.formatUtcValue(this.datetimeValue))
        },
    },
    methods: {
        formatUtcValue (datetime) {
            const momentDate = moment.tz(datetime, (this.dateValueFormat + ' ' + this.timeFormat), this.userTimezone)
            return momentDate.isValid() ? momentDate.toISOString() : ''
        },
        setDateValue () {
            // 一律、ISOStringに変換する（v-modelで渡される日付はUTC前提）
            const ISOString = moment.utc(this.value).toISOString()
            const momentDate = moment(ISOString)
            this.dateValue = momentDate.isValid() ? momentDate.format(this.dateValueFormat) : null
            this.timeValue = momentDate.isValid() ? momentDate.format(this.timeFormat) : null
        },
        // 手入力した場合
        updateByText (type) {
            if (type === 'date') {
                this.dateMenu = false
                const momentDate = moment.tz(this.dateText, this.dateFormat, true, this.userTimezone)
                this.dateValue = momentDate.isValid() ? momentDate.format(this.dateValueFormat) : null

                if (!this.dateValue) {
                    // 日付を未設定にした場合は時間も未設定
                    this.timeValue = null
                } else if (!this.timeValue) {
                    // 未入力であれば時間を自動補完
                    const momentDate = moment.tz(this.dateValue, this.dateValueFormat, true, this.userTimezone)
                    this.timeValue = this.dateTo ? momentDate.endOf('day').format(this.timeFormat) : momentDate.startOf('day').format(this.timeFormat)
                }
            }
            if (type === 'time') {
                this.timeMenu = false
                const momentTime = moment.tz(this.timeText, this.timeFormat, true, this.userTimezone)
                this.timeValue = momentTime.isValid() ? momentTime.format(this.timeFormat) : null
            }
        },
    },
}
</script>
