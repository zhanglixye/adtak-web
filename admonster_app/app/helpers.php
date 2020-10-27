<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

if (!function_exists('parse_utc_string_to_user_timezone_date')) {
    /**
     * UTC -> user timezone date
     *
     * @param String $utc_string
     * @return Carbon
     */
    function parse_utc_string_to_user_timezone_date(String $utc_string)
    {
        $user = Auth::user();
        return Carbon::parse($utc_string)->setTimezone($user->timezone);
    }
}

if (!function_exists('utc_string_datetime_now')) {
    /**
     * current UTC datetime(YYYY-MM-DD HH:mm:ss)
     *
     * @return String
     */
    function utc_string_datetime_now()
    {
        return Carbon::now('UTC');
    }
}

if (!function_exists('build_flash_error_message_with_reason')) {
    function build_flash_error_message_with_reason($cause, $action)
    {
        $message = $cause.__('flash.parts_of_text.for').$action.__('flash.parts_of_text.failed');

        return $message;
    }
}

// DateTimeクラスを使用して日付フォーマット(2038年問題への対応)
if (!function_exists('datetime')) {
    function datetime($format, $datetime)
    {
        $datetime = new DateTime($datetime);
        // TODO 一旦は日本時間に変換
        $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $formatted_datetime = $datetime->format($format);

        return $formatted_datetime;
    }
}

// 金額フォーマット [¥1,111,111]
if (!function_exists('money_format_ja')) {
    function money_format_ja($price)
    {
        // 半角に変換 -> 数字のみに変換 -> ¥1,111,111の形で返却
        $price_hankaku = mb_convert_kana($price, 'n');
        $price_num = preg_replace('/[^0-9]/', '', $price_hankaku);
        return '¥'.number_format(floatval($price_num));
    }
}
