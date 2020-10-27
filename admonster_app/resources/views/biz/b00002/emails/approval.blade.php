@if ($request_mail['content_type'] === \App\Models\RequestMail::CONTENT_TYPE_HTML)
ご担当者様<br>
<br>
下記経費申請依頼が、<br>
　日時：{{ datetime('Y年m月d日 H:i', $approval_date) }}<br>
承認者：{{ $approver_name }}<br>
<br>
によって承認されました。<br>
<br>
よろしくご手配のほどお願い申し上げます。<br>
<br>
==========================================================================<br>
{{ datetime('Y年m月d日 H:i', $request_mail['recieved_at']) }} &lt;{{ $request_mail['from'] }}&gt;<br>
<blockquote style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
{!! $request_mail['body'] !!}
</blockquote>

@else
ご担当者様

下記経費申請依頼が、
　日時：{{ datetime('Y年m月d日 H:i', $approval_date) }}
承認者：{{ $approver_name }}

によって承認されました。

よろしくご手配のほどお願い申し上げます。

==========================================================================
{{ datetime('Y年m月d日 H:i', $request_mail['recieved_at']) }} {!! $request_mail['from'] !!}:
{!! $request_mail['body'] !!}
@endif
