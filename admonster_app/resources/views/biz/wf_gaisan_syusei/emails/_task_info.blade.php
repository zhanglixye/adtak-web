@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
<br>
<br>
■業務名：{{ $business_name }}<br>
■作業名：{{ $step_name }}<br>
■作業者：{{ $work_user_name }}<br>
@if (isset($request_work_code))
■JOBNO：{{ $request_work_code }}<br>
@endif
@if (isset($request_mail_subject))
■案件名：{{ $request_mail_subject }}<br>
@endif
@if (isset($comment))
■コメント内容：<br>
======================================<br>
{{ $comment }}
======================================<br>
@endif
■画面URL：<a href="{{ $task_url }}" target="_blank">{{ $task_url }}</a><br>
<br>
<br>

@else

■業務名：{{ $business_name }}
■作業名：{{ $step_name }}
■作業者：{{ $work_user_name }}
@if (isset($request_work_code))
■JOBNO：{{ $request_work_code }}
@endif
@if (isset($request_mail_subject))
■案件名：{{ $request_mail_subject }}
@endif
@if (isset($comment))
■コメント内容：
======================================
{{ $comment }}
======================================
@endif
■画面URL：{{ $task_url }}

@endif
