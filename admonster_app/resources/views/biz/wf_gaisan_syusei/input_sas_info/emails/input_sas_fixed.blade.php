@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
@foreach ($business_admin_name_array as $business_admin_name)
{{ $business_admin_name }} 様<br>
@endforeach
<br>
<br>
WF概算修正の確定情報です。<br>
ご確認をおねがい致します。<br>
<br>
<br>
【経路】<br>
--------------------------------------------------------------------------------------------------------<br>
受注枠の営業担当 : {{ $work['item12'] }}<br>
計上部署マネージャー : DAC 関谷 憲史、 {{ $work['item12'] }}<br>
完了通知 : DAC 関谷 憲史、 申 琳<br>
--------------------------------------------------------------------------------------------------------<br>
<br>
<br>
【入力フォーム】<br>
--------------------------------------------------------------------------------------------------------<br>
■ 案件概要 ■■■■■■■■■■■■■■■■■■■■<br>
受注明細No---原価No* : {{ $work['item35'] }}<br>
代理店 : {{ $work['item13'] }}<br>
広告主 : {{ $work['item14'] }}<br>
媒体社名 : {{ isset($master_data['sheet_type']) ? $master_data['sheet_type'] : ''}}<br>
サイト : {{ $work['item16'] }}<br>
メニュー : {{ $work['item17'] }}<br>
掲載開始日 : {{ $work['item18'] }}<br>
掲載終了日 : {{ $work['item19'] }}<br>
<br>
■ 案件概要---変更の確認 ■■■■■■■■■■■■■■■■■■■■ <br>
@if ($process_type == \App\Http\Controllers\Biz\WfGaisanSyusei\WfGaisanSyuseiController::PROCESS_TYPE_WF_APPLY)
無し or 有り : 無し<br>
<br>
■ 案件概要---変更後　※案件概要変更「有り」の場合 ■■■■■■■■■■■■■■■■■■■■ <br>
変更後---代理店 : <br>
変更後---広告主 : <br>
変更後---媒体社名 : <br>
変更後---サイト : <br>
変更後---メニュー : <br>
変更後---掲載開始日 : <br>
変更後---掲載終了日 : <br>
@elseif ($process_type == \App\Http\Controllers\Biz\WfGaisanSyusei\WfGaisanSyuseiController::PROCESS_TYPE_WF_APPLY_W_CHANGE)
無し or 有り : 有り<br>
<br>
■ 案件概要---変更後　※案件概要変更「有り」の場合 ■■■■■■■■■■■■■■■■■■■■ <br>
変更後---代理店 : <br>
変更後---広告主 : <br>
変更後---媒体社名 : <br>
変更後---サイト : <br>
変更後---メニュー : <br>
変更後---掲載開始日 : {{ isset($master_data['from']) ? $master_data['from'] : ''}}<br>
変更後---掲載終了日 : {{ isset($master_data['to']) ? $master_data['to'] : ''}}<br>
@endif
<br>
■ 金額等---変更前 ■■■■■■■■■■■■■■■■■■■■ <br>
外貨確認 (日本円 or 【外貨】実施料金・広告会社手数料　or 【外貨】原価) : 日本円<br>
実施料金* : {{ preg_replace('/¥|,/', '', $work['item32']) }}<br>
広告会社手数料* : {{ preg_replace('/¥|,/', '', $work['item33']) }}<br>
原価* : {{ preg_replace('/¥|,/', '', $work['item34']) }}<br>
金額変更の有無 : 有り<br>
<br>
■ 金額等---変更後 ■■■■■■■■■■■■■■■■■■■■ <br>
変更後---実施料金* : {{ isset($master_data['commit_amount_gross']) ? preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) : ''}}<br>
変更後---広告会社手数料* : {{ round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $work['item20']) / 100) }}<br>
変更後---原価* : {{ round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $work['item21']) / 100) }}<br>
--------------------------------------------------------------------------------------------------------<br>
<br>
<br>
どうぞ宜しくお願い致します。<br>
<br>
<br>
{{ datetime('Y年m月d日 H:i', $request_mail->recieved_at) }} &lt;{{ $request_mail->from }}&gt;<br>
<blockquote style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
{!! $request_mail->body !!}
</blockquote>

@else
@foreach ($business_admin_name_array as $business_admin_name)
{{ $business_admin_name }} 様
@endforeach

WF概算修正の確定情報です。
ご確認をおねがい致します。


【経路】
--------------------------------------------------------------------------------------------------------
受注枠の営業担当 : {{ $work['item12'] }}
計上部署マネージャー : DAC 関谷 憲史、 {{ $work['item12'] }}
完了通知 : DAC 関谷 憲史、 申 琳
--------------------------------------------------------------------------------------------------------


【入力フォーム】
--------------------------------------------------------------------------------------------------------
■ 案件概要 ■■■■■■■■■■■■■■■■■■■■
受注明細No---原価No* : {{ $work['item35'] }}
代理店 : {{ $work['item13'] }}
広告主 : {{ $work['item14'] }}
媒体社名 : {{ isset($master_data['sheet_type']) ? $master_data['sheet_type'] : ''}}
サイト : {{ $work['item16'] }}
メニュー : {{ $work['item17'] }}
掲載開始日 : {{ $work['item18'] }}
掲載終了日 : {{ $work['item19'] }}

■ 案件概要---変更の確認 ■■■■■■■■■■■■■■■■■■■■
@if ($process_type == \App\Http\Controllers\Biz\WfGaisanSyusei\WfGaisanSyuseiController::PROCESS_TYPE_WF_APPLY)
無し or 有り : 無し

■ 案件概要---変更後　※案件概要変更「有り」の場合 ■■■■■■■■■■■■■■■■■■■■
変更後---代理店 :
変更後---広告主 :
変更後---媒体社名 :
変更後---サイト :
変更後---メニュー :
変更後---掲載開始日 :
変更後---掲載終了日 :
@elseif ($process_type == \App\Http\Controllers\Biz\WfGaisanSyusei\WfGaisanSyuseiController::PROCESS_TYPE_WF_APPLY_W_CHANGE)
無し or 有り : 有り

■ 案件概要---変更後　※案件概要変更「有り」の場合 ■■■■■■■■■■■■■■■■■■■■
変更後---代理店 :
変更後---広告主 :
変更後---媒体社名 :
変更後---サイト :
変更後---メニュー :
変更後---掲載開始日 : {{ isset($master_data['from']) ? $master_data['from'] : ''}}
変更後---掲載終了日 : {{ isset($master_data['to']) ? $master_data['to'] : ''}}
@endif

■ 金額等---変更前 ■■■■■■■■■■■■■■■■■■■■
外貨確認 (日本円 or 【外貨】実施料金・広告会社手数料　or 【外貨】原価) : 日本円
実施料金* : {{ preg_replace('/¥|,/', '', $work['item32']) }}
広告会社手数料* : {{ preg_replace('/¥|,/', '', $work['item33']) }}
原価* : {{ preg_replace('/¥|,/', '', $work['item34']) }}
金額変更の有無 : 有り

■ 金額等---変更後 ■■■■■■■■■■■■■■■■■■■■
変更後---実施料金* : {{ isset($master_data['commit_amount_gross']) ? preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) : ''}}
変更後---広告会社手数料* : {{ round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $work['item20']) / 100) }}
変更後---原価* : {{ round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $work['item21']) / 100) }}
--------------------------------------------------------------------------------------------------------


どうぞ宜しくお願い致します。


{{ datetime('Y年m月d日 H:i', $request_mail->recieved_at) }} {!! $request_mail->from !!}:
{!! $request_mail->body !!}
@endif
