@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
ご担当者様<br>
<br>
ご依頼の素材の{{ $business_name }}結果をご報告致します。<br>
<br>
【対応表】<br>
結果ファイル：{{ $excel_file['file_name'] }}<br>
URL：{!! $excel_file['download_url'] !!}<br>
<br>
@if(isset($check_file_zip))
【リネーム後ファイルzip】<br>
素材URL：{!! $check_file_zip['download_url'] !!}<br>
<br>
@else
@foreach ($check_files as $file)
@if ($file['is_success'] === false)
【NG】【リネーム後ファイル】<br>
@else
【リネーム後ファイル】<br>
@endif
元ファイル名：{{ $file['file_name'] }}<br>
チェックファイル名：{{ $file['check_file_name'] }}<br>
素材URL：{!! $file['download_url'] !!}<br>
<br>
@endforeach
@endif
【結果画像】<br>
@if( count($result_files) == 0)
なし<br>
@else
@foreach ($result_files as $task_result_file)
ファイル名：{{ $task_result_file->name }}<br>
URL：{!! $task_result_file->download_url !!}<br>
<br>
@endforeach
@endif
<br>
{{ datetime('Y年m月d日 H:i', $request_mail->recieved_at) }} &lt;{{ $request_mail->from }}&gt;<br>
<blockquote style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
{!! $request_mail->body !!}
</blockquote>

@else
ご担当者様

ご依頼の素材の{{ $business_name }}結果をご報告致します。

【対応表】
結果ファイル：{{ $excel_file['file_name'] }}
URL：{!! $excel_file['download_url'] !!}

@if(isset($check_file_zip))
【リネーム後ファイルzip】
素材URL：{!! $check_file_zip['download_url'] !!}

@else
@foreach ($check_files as $file)
@if ($file['is_success'] === false)
【NG】【リネーム後ファイル】
@else
【リネーム後ファイル】
@endif
元ファイル名：{{ $file['file_name'] }}
チェックファイル名：{{ $file['check_file_name'] }}
素材URL：{!! $file['download_url'] !!}

@endforeach
@endif
【結果画像】
@if( count($result_files) == 0)
なし
@else
@foreach ($result_files as $task_result_file)
ファイル名：{{ $task_result_file->name }}
URL：{!! $task_result_file->download_url !!}

@endforeach
@endif

{{ datetime('Y年m月d日 H:i', $request_mail->recieved_at) }} {!! $request_mail->from !!}:
{!! $request_mail->body !!}
@endif
