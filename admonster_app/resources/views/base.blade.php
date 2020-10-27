<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') | {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href='https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons' rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    @yield('style')
</head>
<body>
    <div id="app">
        @yield('content')
        <div id="hidden-params">
            {{ Form::hidden('loaded-utc-datetime', utc_string_datetime_now(), ['id' => 'loaded-utc-datetime']) }}
            {{ Form::hidden('login-user-id', Auth::user()->id, ['id' => 'login-user-id']) }}
            {{ Form::hidden('login-user-name', Auth::user()->name, ['id' => 'login-user-name']) }}
            {{ Form::hidden('login-user-image', Auth::user()->user_image_path, ['id' => 'login-user-image']) }}
            {{ Form::hidden('login-user-email', Auth::user()->email, ['id' => 'login-user-email']) }}
            {{ Form::hidden('login-user-timezone', Auth::user()->timezone, ['id' => 'login-user-timezone']) }}
            {{ Form::hidden('login-guest-user', !empty(Auth::guard('guest_client')->user()) ? 'true' : 'false', ['id' => 'login-guest-user']) }}
        </div>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
    <script src=" {{ mix('js/vendor.js') }} "></script>
    @yield('script')
</body>
</html>
