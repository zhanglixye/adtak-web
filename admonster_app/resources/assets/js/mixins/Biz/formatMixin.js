import moment from 'moment'
export default {
    created () {
        console.log('loaded formatMixin')
    },
    methods: {
        // 日付
        formatDate (date) {
            if (!date) {
                return null
            }
            date = moment(date).format('YYYY-MM-DD')
            const [year, month, day] = date.split('-')
            return `${year}/${month}/${day}`
        },
        // datepicker用
        parseDate (date) {
            if (!date) {
                return null
            }
            date = moment(date).format('YYYY/MM/DD')
            const [year, month, day] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
        },
        formatYen: function(val) {
            if (!val) {
                return null
            }
            let formatedVal = this.numberFormat(val);
            formatedVal = this.addYenMark(formatedVal);

            return formatedVal
        },
        addYenMark: function(str) {
            if (!str.match(/\\xA5/)) {
                str = '\xA5' + str;
            }
            return str
        },
        addPerMark: function(str) {
            if (!str.match(/%/)) {
                str = str + '%';
            }
            return str;
        },
        // 数字の3桁区切り
        numberFormat: function(str) {
            str = this.hankaku(str);
            str = this.removeExceptForNumber(str);
            str = str.replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');

            return str;
        },
        hankaku: function(str) {
            str = str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
                return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
            });

            return str;
        },
        // 数字のみ抽出
        removeExceptForNumber: function(str) {
            str = str.replace(/[^0-9]/g, '');

            return str;
        }
    }
}
