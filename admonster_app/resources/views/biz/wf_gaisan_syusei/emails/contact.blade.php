@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
-----admonsterシステムより配信-----<br>
<br>
<br>
関係各位<br>
<br>
<br>
下記タスクが不明処理となりましたので確認をお願いします。<br>
<br>
@include('biz.wf_gaisan_syusei.emails._task_info')
<br>
<br>
@include('biz.wf_gaisan_syusei.emails._additional_text')
<br>
<br>
-----admonsterシステムより配信-----<br>
<br>
<br>
{{ datetime('Y/m/d H:i', $request_mail->recieved_at) }} &lt;{{ $request_mail->from }}&gt;<br>
<blockquote style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
{!! $request_mail->body !!}
</blockquote>

@else
-----admonsterシステムより配信-----

関係各位

下記タスクが不明処理となりましたので確認をお願いします。

@include('biz.wf_gaisan_syusei.emails._task_info')

@include('biz.wf_gaisan_syusei.emails._additional_text')

-----admonsterシステムより配信-----


{{ datetime('Y/m/d H:i', $request_mail->recieved_at) }} {!! $request_mail->from !!}:
{!! $request_mail->body !!}
@endif
