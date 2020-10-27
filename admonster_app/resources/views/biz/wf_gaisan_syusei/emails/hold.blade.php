@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
-----admonsterシステムより配信-----<br>
<br>
<br>
関係各位<br>
<br>
<br>
下記案件につきまして、{{ $step_name }}で保留処理がされましたのでご連絡します。<br>
<br>
@include('biz.wf_gaisan_syusei.emails._task_info')
<br>
<br>
*保留案件は作業終了まで案件管理のうえ対応をお願いします。<br>
<br>
<br>
-----admonsterシステムより配信-----<br>
<br>
<br>

@else
-----admonsterシステムより配信-----

関係各位

下記案件につきまして、{{ $step_name }}で保留処理がされましたのでご連絡します。

@include('biz.wf_gaisan_syusei.emails._task_info')

*保留案件は作業終了まで案件管理のうえ対応をお願いします。

-----admonsterシステムより配信-----


@endif
