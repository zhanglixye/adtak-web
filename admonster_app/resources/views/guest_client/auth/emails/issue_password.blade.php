<div>{{ $send_to_email }} 様</div>
<div>依頼ID：{{ $request_id }}のワンタイムパスワードは以下になります。</div>
<br>
<div>【パスワード】</div>
<div>{{ $password }}</div>
<br>
<div>＜ご利用にあたって＞</div>
<div>・ワンタイムパスワード発行後、10分以内にログインをしてください。</div>
<div>・有効期限が過ぎた場合は、再度ログインURLにアクセスしワンタイムパスワードの再発行をお願いします。</div>
<div>・ログインができない場合、入力桁数や、最新のパスワードを入力しているかご確認ください。</div>
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
