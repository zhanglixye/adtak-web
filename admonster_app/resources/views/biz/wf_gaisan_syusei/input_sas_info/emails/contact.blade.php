@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
@foreach ($business_admin_name_array as $business_admin_name)
{{ $business_admin_name }} 様<br>
@endforeach
<br>
<br>
本メールに不明点がありましたので、ご確認をお願い致します。<br>
<br>
[不明点]<br>
{!! $unclear_text_set !!}
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

本メールに不明点がありましたので、ご確認をお願い致します。

[不明点]
{!! $unclear_text_set !!}

どうぞ宜しくお願い致します。


{{ datetime('Y年m月d日 H:i', $request_mail->recieved_at) }} {!! $request_mail->from !!}:
{!! $request_mail->body !!}
@endif
