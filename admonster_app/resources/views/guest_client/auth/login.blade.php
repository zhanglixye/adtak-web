<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} | ゲストクライアントログイン</title>
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
    </style>
</head>
<body class="hold-transition login-page">
    <!-- .login-box -->
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>{{ config('app.name') }}<br>for GUEST</b></a>
        </div>
        <div class="login-box-body">
        @if (count($errors) < 1)
            @if (isset($is_reissue) && $is_reissue)
                <div class="auth-custom-alert-wrap text-primary">
                    <p><strong>ワンタイムパスワードを再発行しました。</strong></p>
                </div>
            @else
                <div class="auth-custom-alert-wrap text-primary">
                    <p><strong>ワンタイムパスワードを発行しました。</strong></p>
                </div>
            @endif
        @endif
            <div>
                当画面URLが届いたメールアドレスにワンタイムパスワードをお送りしました。<br>
                メール本文に記載されているワンタイムパスワードを入力してください。
            </div>
            <p class="login-box-msg"></p>
            <form  method="POST" action="{{ route('guest_client.login') }}">
                {{ csrf_field() }}
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} has-feedback">
                    <input id="password" class="form-control" name="password" value="{{ old('password') }}" placeholder="ワンタイムパスワード" required autofocus>
                    <span class="form-control-feedback"></span>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="row">
                    <div class="col-xs-8"></div>
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">ログイン</button>
                    </div>
                </div>
                <input type="hidden" name="token" value="{{ $token }}">
            </form>
        </div>
        <hr style="margin: 0px;">
        <div class="login-box-body">
            <div>
                <span>※ パスワードが届かない場合は下記ボタンより再発行をお試しください。</span>
            </div>
            <p class="login-box-msg"></p>
            <form  method="POST" action="{{ route('guest_client.issue_password') }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">ワンタイムパスワード再発行</button>
                    </div>
                </div>
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="is_reissue" value="1">
            </form>
        </div>

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
