@if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML)
*CCC案件、キリン案件は別フローで対応してください。<br>
*JOBNOの記載がない場合は、Xoneで調べるもしくは日本に戻してください。営業へ確認します。<br>
*対応不要クライアントはスプレッドシートの確定ステータスに「営業対応」と記載してください。<br>
*作業を再開する場合は、対象メールを「gaisanshusei-social@adpro-inc.co.jp」に転送してください。<br>
<br>

@else
*CCC案件、キリン案件は別フローで対応してください。
*JOBNOの記載がない場合は、Xoneで調べるもしくは日本に戻してください。営業へ確認します。
*対応不要クライアントはスプレッドシートの確定ステータスに「営業対応」と記載してください。
*作業を再開する場合は、対象メールを「gaisanshusei-social@adpro-inc.co.jp」に転送してください。

@endif
