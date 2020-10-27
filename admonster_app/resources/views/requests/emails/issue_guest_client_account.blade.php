<div>{{ $send_to_email }} 様</div>
<div>進捗状況確認用URLをお送り致します。</div>
<div>◆依頼ID：{{ $request_id }}</div>
<br>
<div>作業の進捗は、下記URLよりご確認いただけます。</div>
<div>{{ $url }}</div>
<br>
<div>＜ご利用にあたって＞</div>
<div>・ワンタイムパスワードによるログインになります。</div>
<div>・URLにアクセスすると、自動的にパスワードが発行されます。</div>
<div>・最終アクセスから2時間経過した場合は、パスワードが再発行されます。</div>
<br>
<div style="overflow:hidden">
    <div style="float: left;">※</div>
    <div style="float: left;">
        <div>このメールは送信専用メールアドレスから配信されています。</div>
        <div>ご返信いただいてもお答えできませんのでご了承ください。</div>
    </div>
</div>
<br>
<div>
    <div>◆問い合わせ</div>
    <div>------------------------------------</div>
    <div>{{ config('app.name') }}事務局</div>
    <div>mailto: xxxx@xxx.xx</div>
    <div>phone: 000-0000-0000</div>
    <div>------------------------------------</div>
</div>
<br>
@if ($request_mail)
<hr>
<div style="border-left: 1px solid #ccc;margin-left: 8px;padding-left: 10px;">
    <div>差出人:　{{ $request_mail->from }}</div>
    <div>送信日時:　{{ datetime('Y/m/d H:i', $request_mail->recieved_at) }}</div>
    <div>宛先:　{{ $request_mail->to }}</div>
    <div>件名:　{{ $request_mail->subject }}</div>
    <br>
    <div>{!! $request_mail->body !!}</div>
</div>
@endif

