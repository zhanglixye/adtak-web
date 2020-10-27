@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
-----admonsterシステムより配信-----<br>
<br>
<br>
関係各位<br>
<br>
<br>
下記案件につきまして、最終判定でワークフロー申請対象外でしたので確認をお願いします。<br>
<br>
<br>
■業務名：{{ $business_name }}<br>
■作業名：{{ $step_name }}<br>
■作業者：{{ $work_user_name }}<br>
■判定結果：{{ $judge_type_text }}<br>
■JOBNO：{{ $request_work_code }}<br>
■案件名：{{ $request_mail_subject }}<br>
■画面URL：<a href="{{ $task_url }}" target="_blank">{{ $task_url }}</a><br>
<br>
<br>
*スプレッドシートの確定ステータスに「{{ $judge_type_text }}」と記載してください。<br>
<br>
<br>
-----admonsterシステムより配信-----<br>
<br>
<br>

@else
-----admonsterシステムより配信-----

関係各位

下記案件につきまして、最終判定でワークフロー申請対象外でしたので確認をお願いします。

■業務名：{{ $business_name }}
■作業名：{{ $step_name }}
■作業者：{{ $work_user_name }}
■判定結果：{{ $judge_type_text }}
■JOBNO：{{ $request_work_code }}
■案件名：{{ $request_mail_subject }}
■画面URL：{{ $task_url }}

*スプレッドシートの確定ステータスに「{{ $judge_type_text }}」と記載してください。

-----admonsterシステムより配信-----


@endif
