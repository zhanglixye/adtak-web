<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} | ゲストクライアントパスワード発行</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{ Html::style('AdminLTE/bower_components/bootstrap/dist/css/bootstrap.min.css') }}
    {{ Html::style('AdminLTE/bower_components/font-awesome/css/font-awesome.min.css') }}
    {{ Html::style('AdminLTE/bower_components/Ionicons/css/ionicons.min.css') }}
    {{ Html::style('AdminLTE/dist/css/AdminLTE.min.css') }}
    {{ Html::style('AdminLTE/plugins/iCheck/square/blue.css') }}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
        .auth-custom-alert-wrap p {
            font-size: 16px;
        }
        .auth-custom-alert-wrap.has-error p {
            color: #dd4b39;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>{{ config('app.name') }}<br>for GUEST</b></a>
        </div>
        <div class="login-box-body">
        @if ($errors->has('issue_password'))
            <div class="auth-custom-alert-wrap">
                <p><strong>{{ $errors->first('issue_password') }}</strong></p>
            </div>
        @else
            @if ($errors->has('password_expired'))
                <div class="has-error auth-custom-alert-wrap">
                    <p>
                        <strong>{{ $errors->first('password_expired') }}</strong><br>
                        <strong>下記ボタンより、再度ワンタイムパスワードを発行してください。</strong>
                    </p>
                </div>
            @else
            <div>
                下記ボタンより、ワンタイムパスワードを発行してください。
            </div>
            @endif
            <div>
                当画面URLを受信されたメールアドレスにワンタイムパスワードをお送りします。
            </div>
            <p class="login-box-msg"></p>
            <form  method="POST" action="{{ route('guest_client.issue_password') }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">ワンタイムパスワード発行</button>
                    </div>
                </div>
                <input type="hidden" name="token" value="{{ $token }}">
            </form>
        @endif
        </div>
    <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

<!-- jQuery 3 -->
{{ Html::script('AdminLTE/bower_components/jquery/dist/jquery.min.js') }}
{{ Html::script('AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js') }}
{{ Html::script('AdminLTE/plugins/iCheck/icheck.min.js') }}

<script>
$(function () {
    $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' /* optional */
    });
});
</script>
</body>
</html>
