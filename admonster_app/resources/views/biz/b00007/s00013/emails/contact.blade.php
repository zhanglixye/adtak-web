@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
ご担当者様<br>
<br>
ご依頼の素材の{{ $business_name }}につきまして、<br>
下記の不明点があり実施できませんでした。<br>
ご確認をお願い致します。<br>
<br>
[不明点]<br>
{{ $comment }}<br>
<br>
<br>
{{ datetime('Y/m/d H:i', $request_mail->recieved_at) }} &lt;{{ $request_mail->from }}&gt;<br>
<blockquote style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
{!! $request_mail->body !!}
</blockquote>
@else
ご担当者様

ご依頼の素材の{{ $business_name }}につきまして、
下記の不明点があり実施できませんでした。
ご確認をお願い致します。

[不明点]
{{ $comment }}


{{ datetime('Y/m/d H:i', $request_mail->recieved_at) }} {!! $request_mail->from !!}:
{!! $request_mail->body !!}
@endif
