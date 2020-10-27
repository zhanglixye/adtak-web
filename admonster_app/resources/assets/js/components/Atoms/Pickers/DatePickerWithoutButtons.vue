<template>
    <div>
        <v-menu
            v-model="menu"
            lazy
            transition="scale-transition"
            full-width
            offset-y
            :nudge-right="40"
            :close-on-content-click="false"
            :disabled="!isActive"
            max-width=290
        >
            <template slot="activator">
                <v-text-field
                    v-model="text"
                    :label="label"
                    :placeholder="placeholder"
                    :disabled="!isActive"
                    :rules="rules"
                    :hide-details="from || to ? false : true"
                    @change="updateByText"
                    @keyup.enter="enterEvent"
                    :append-icon="isShowIcon ? 'event' : ''"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="date"
                no-title
                scrollable
                @change="updateByPicker"
            >
            </v-date-picker>
        </v-menu>
    </div>

</template>

<script>
export default {
    data: () => ({
        // メニューの開閉
        menu: false,
        // 日付
        date: null,
        // テキスト日付
        text: '',
    }),
    props: {
        dateValue: { type: String, required: true, default: '' },
        comparisonDate: { type: String, required: false, default: '' },
        label: { type: String, required: true, default: '' },
        placeholder: { type: String, required: false, default: 'YYYY/MM/DD' },
        dateFormat: { type: String, required: false, default: 'YYYY/MM/DD' },
        isActive: { type: Boolean, required: false, default: true },
        from: { type: Boolean, required: false, default: false },
        to: { type: Boolean, required: false, default: false },
        enterEvent: { type: Function, required: false, default: function () {} },
        isShowIcon: { type: Boolean, required: false, default: false },
    },
    computed: {
        rules () {
            if (this.from) {
                return [this.fromCheck]
            } else if (this.to) {
                return [this.toCheck]
            }
            return []
        },
        isComparison () {
            return this.comparisonDate !== ''
        },
        fromCheck () {
            return this.dateValue === '' ||
                this.comparisonDate === '' ||
                this.comparisonDate >= this.dateValue ||
                Vue.i18n.translate('common.datetime.to_than_also_after')
        },
        toCheck () {
            return this.dateValue === '' ||
                this.comparisonDate === '' ||
                this.comparisonDate <= this.dateValue ||
                Vue.i18n.translate('common.datetime.from_than_also_date_is_ago')
        },
    },
    methods: {
        // 日付バリデーションチェック
        isDate (strDate) {
            // 空文字
            if (strDate === '') {
                return false
            }
            // 年/月/日の形式のみ許容する
            if (!strDate.match(/^\d{4}\/\d{1,2}\/\d{1,2}$/)) {
                return false
            }
            // 存在する日付であることを確認
            var date = new Date(strDate)
            var splitDate = strDate.split('/')
            if (date.getFullYear() !== Number(splitDate[0])
                || date.getMonth() + 1 !== Number(splitDate[1]) // getMonthは 0 から始まる
                || date.getDate() !== Number(splitDate[2])
            ) {
                return false
            }

            return true
        },
        // 手入力した場合
        updateByText () {
            // カレンダーを閉じる
            this.menu = false

            // フォーマットが正しくない場合
            if (!this.isDate(this.text)) {
                // 値をリセット
                this.text = ''
                this.date = null
                return
            }
            this.date = moment(this.text, 'YYYY-MM-DD').format('YYYY-MM-DD')
        },
        // カレンダーから選択した場合
        updateByPicker () {
            // カレンダーを閉じる
            this.menu = false
            // フォーマットして表示
            this.text = moment(this.date).format(this.dateFormat)
        },
        // dateValue更新時の連動処理
        updateByDateValue () {
            this.text = this.dateValue
            if (!this.isDate(this.text)) {
                this.date = null
            } else {
                this.date = moment(this.text, 'YYYY-MM-DD').format('YYYY-MM-DD')
            }
        }
    },
    watch: {
        text () {
            this.$emit('change', this.text)
        },
        dateValue () {
            this.updateByDateValue()
        }
    },
    created () {
        this.updateByDateValue()
    }
}
</script>
