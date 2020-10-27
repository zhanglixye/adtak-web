import lang from './vue-i18n-locales.generated.js';

Vue.filter('parseUtcStringToUserTimezoneDate', (utcDate, dateFormat='YYYY/MM/DD HH:mm:ss') => {
    const ISOString = moment.utc(utcDate).toISOString()
    return moment(ISOString).format(dateFormat)
})

// 日付フォーマット('YYYY/MM/DD HH:MM')
Vue.filter('formatDateYmdHm', function(date, diffDemand = false, fullLength = false){

    let formatDate = '';

    // DBにはUTC時間が登録されているためユーザのタイムゾーンに合わせ変換
    let utcISOString = moment.utc(date).toISOString();
    let localDate = moment(utcISOString);

    let isToday = localDate.format('YYYY/MM/DD') == moment().format('YYYY/MM/DD');
    let isThisMonth = localDate.format('YYYY/MM') == moment().format('YYYY/MM');
    let isThisYear = localDate.format('YYYY') == moment().format('YYYY');

    if (fullLength) {
        // 区切り文字のみの変換
        formatDate =  localDate.format('YYYY/MM/DD HH:mm');
    } else {
        if (diffDemand) {
            if (isToday) {
                /*
                 * 当日の場合は現在時刻からの差分時間を表示
                */
                const diff = moment().diff(localDate);
                // ミリ秒からdurationオブジェクトを生成
                const duration = moment.duration(diff);
                // 時・分・秒を取得
                // const days    = Math.floor(duration.asDays());
                const hours   = duration.hours();
                const minutes = duration.minutes();
                const seconds = duration.seconds();
                // 1つの単位のみ出力
                let formatDiff = '';

                const locale = Vue.i18n.locale();

                if (hours > 0) {
                    formatDiff = hours + lang[locale].common.datetime.hour.interval + minutes + lang[locale].common.datetime.minute;
                } else if (hours == 0 &&  minutes > 0) {
                    formatDiff = minutes + lang[locale].common.datetime.minute;
                } else if (hours == 0 &&  minutes == 0 && seconds > 0) {
                    formatDiff = seconds + lang[locale].common.datetime.second;
                }
                formatDate = formatDiff + lang[locale].common.datetime.before;

            } else if ((!isToday && isThisMonth) || (!isToday && !isThisMonth && isThisYear))  {
                // 当月 or 当年
                formatDate = localDate.format('MM/DD HH:mm');
            } else {
                // 当年以外
                formatDate =  localDate.format('YYYY/MM/DD HH:mm');
            }
        } else {
            if (isToday) {
                formatDate = localDate.format('HH:mm');
            } else if ((!isToday && isThisMonth) || (!isToday && !isThisMonth && isThisYear))  {
                // 当月 or 当年
                formatDate = localDate.format('MM/DD HH:mm');
            } else {
                // 当年以外
                formatDate =  localDate.format('YYYY/MM/DD HH:mm');
            }
        }
    }

    return formatDate;
});
