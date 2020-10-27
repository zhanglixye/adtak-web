@php
$juchu_meisai_nums = [$sas_data['item14'], $sas_data['item15'], $sas_data['item16']];
$juchu_meisai_nums = implode(',', $juchu_meisai_nums);
@endphp
@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
関係各位<br>
<br>
<br>
下記案件につきましてワークフロー申請をお願いします。<br>
<br>
■JOBNO : {{ $request_work_code }}<br>
■件名 : {{ $request_mail->subject }}<br>
■画面URL : {{ $task_url }}<br>
<br>
*ワークフロー申請完了後、スプレッドシートの更新をお願いします。<br>
*証憑添付について<br>
・原価の証憑は本メールに添付のパワーポイントをダウンロードのうえ添付してください。<br>
・実施料金または代理店手数料の証憑は、上記の情報を受信メールから検索し、PDF保存して添付してください。<br>
<br>
<br>
【経路】<br>
--------------------------------------------------------------------------------------------------------<br>
受注枠の営業担当 : {{ $sas_data['item01'] }}<br>
計上部署マネージャー : DAC 関谷 憲史<br>
完了通知 : DAC 関谷 憲史、 申 琳、　{{ $sas_data['item01'] }}<br>
--------------------------------------------------------------------------------------------------------<br>
<br>
<br>
【入力フォーム】<br>
--------------------------------------------------------------------------------------------------------<br>
■ 案件概要 <br>
受注明細No---原価No* : {{ $juchu_meisai_nums }}<br>
代理店 : {{ $sas_data['item02'] }}<br>
広告主 : {{ $sas_data['item03'] }}<br>
媒体社名 : {{ isset($master_data['sheet_type']) ? $master_data['sheet_type'] : '' }}<br>
サイト : {{ $sas_data['item05'] }}<br>
メニュー : {{ $sas_data['item06'] }}<br>
掲載開始日 : {{ $sas_data['item07'] }}<br>
掲載終了日 : {{ $sas_data['item08'] }}<br>
<br>
■ 案件概要---変更の確認 <br>
@if ($judge_type == \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_WF_APPLY)
無し or 有り : 無し<br>
<br>
■ 案件概要---変更後　※案件概要変更「有り」の場合 <br>
変更後---代理店 : <br>
変更後---広告主 : <br>
変更後---媒体社名 : <br>
変更後---サイト : <br>
変更後---メニュー : <br>
変更後---掲載開始日 : <br>
変更後---掲載終了日 : <br>
@elseif ($judge_type == \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_WF_APPLY_W_CHANGE)
無し or 有り : 有り<br>
<br>
■ 案件概要---変更後　※案件概要変更「有り」の場合 <br>
変更後---代理店 : <br>
変更後---広告主 : <br>
変更後---媒体社名 : <br>
変更後---サイト : <br>
変更後---メニュー : <br>
変更後---掲載開始日 : {{ isset($master_data['from']) ? $master_data['from'] : '' }}<br>
変更後---掲載終了日 : {{ isset($master_data['to']) ? $master_data['to'] : '' }}<br>
@endif
<br>
■ 金額等---変更前 <br>
外貨確認* : 日本円<br>
実施料金* : {{ preg_replace('/¥|,/', '', $sas_data['item11']) }}<br>
広告会社手数料* : {{ preg_replace('/¥|,/', '', $sas_data['item12']) }}<br>
原価* : {{ preg_replace('/¥|,/', '', $sas_data['item13']) }}<br>
金額変更の有無 : 有り<br>
<br>
■ 金額等---変更後 <br>
変更後---実施料金* : {{ (isset($master_data['commit_amount_gross']) && !empty($master_data['commit_amount_gross'])) ? preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) : ''}}<br>
変更後---広告会社手数料* : {{ (isset($master_data['commit_amount_gross']) && !empty($master_data['commit_amount_gross'])) ? round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $sas_data['item09']) / 100) : '' }}<br>
変更後---原価* : {{ (isset($master_data['commit_amount_gross']) && !empty($master_data['commit_amount_gross'])) ? round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $sas_data['item10']) / 100) : ''}}<br>
<br>
■ 証憑<br>
証憑添付---実施料金または代理店手数料の変更について　　　　受信メールを検索し、PDF化のうえ添付<br>
証憑添付---原価の変更について　　　　　　　　　　　　　　　本メールに添付のパワーポイントを添付<br>
--------------------------------------------------------------------------------------------------------<br>
<br>
<br>
どうぞ宜しくお願い致します。<br>
<br>
<br>

@else
関係各位

下記案件につきましてワークフロー申請をお願いします。

■JOBNO : {{ $request_work_code }}
■件名 : {{ $request_mail->subject }}
■画面URL : {{ $task_url }}

*ワークフロー申請完了後、スプレッドシートの更新をお願いします。
*証憑添付について
・原価の証憑は本メールに添付のパワーポイントをダウンロードのうえ添付してください。
・実施料金または代理店手数料の証憑は、上記の情報を受信メールから検索し、PDF保存して添付してください。



【経路】
--------------------------------------------------------------------------------------------------------
受注枠の営業担当 : {{ $sas_data['item01'] }}
計上部署マネージャー : DAC 関谷 憲史
完了通知 : DAC 関谷 憲史、 申 琳、 {{ $sas_data['item01'] }}
--------------------------------------------------------------------------------------------------------


【入力フォーム】
--------------------------------------------------------------------------------------------------------
■ 案件概要
受注明細No---原価No* : {{ $juchu_meisai_nums }}
代理店 : {{ $sas_data['item02'] }}
広告主 : {{ $sas_data['item03'] }}
媒体社名 : {{ isset($master_data['sheet_type']) ? $master_data['sheet_type'] : ''}}
サイト : {{ $sas_data['item05'] }}
メニュー : {{ $sas_data['item06'] }}
掲載開始日 : {{ $sas_data['item07'] }}
掲載終了日 : {{ $sas_data['item08'] }}

■ 案件概要---変更の確認
@if ($judge_type == \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_WF_APPLY)
無し or 有り : 無し

■ 案件概要---変更後　※案件概要変更「有り」の場合
変更後---代理店 :
変更後---広告主 :
変更後---媒体社名 :
変更後---サイト :
変更後---メニュー :
変更後---掲載開始日 :
変更後---掲載終了日 :
@elseif ($judge_type == \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_WF_APPLY_W_CHANGE)
無し or 有り : 有り

■ 案件概要---変更後　※案件概要変更「有り」の場合
変更後---代理店 :
変更後---広告主 :
変更後---媒体社名 :
変更後---サイト :
変更後---メニュー :
変更後---掲載開始日 : {{ isset($master_data['from']) ? $master_data['from'] : ''}}
変更後---掲載終了日 : {{ isset($master_data['to']) ? $master_data['to'] : ''}}
@endif

■ 金額等---変更前
外貨確認* : 日本円
実施料金* : {{ preg_replace('/¥|,/', '', $sas_data['item11']) }}
広告会社手数料* : {{ preg_replace('/¥|,/', '', $sas_data['item12']) }}
原価* : {{ preg_replace('/¥|,/', '', $sas_data['item13']) }}
金額変更の有無 : 有り

■ 金額等---変更後
変更後---実施料金* : {{ (isset($master_data['commit_amount_gross']) && !empty($master_data['commit_amount_gross'])) ? preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) : ''}}
変更後---広告会社手数料* : {{ (isset($master_data['commit_amount_gross']) && !empty($master_data['commit_amount_gross'])) ? round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $sas_data['item09']) / 100) : ''}}
変更後---原価* : {{ (isset($master_data['commit_amount_gross']) && !empty($master_data['commit_amount_gross'])) ? round(preg_replace('/¥|,/', '', $master_data['commit_amount_gross']) * str_replace('%', '', $sas_data['item10']) / 100) : '' }}

■ 証憑
証憑添付---実施料金または代理店手数料の変更について　　　　受信メールを検索し、PDF化のうえ添付
証憑添付---原価の変更について　　　　　　　　　　　　　　　本メールに添付のパワーポイントを添付
--------------------------------------------------------------------------------------------------------


@endif
