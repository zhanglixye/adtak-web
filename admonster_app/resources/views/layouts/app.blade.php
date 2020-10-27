<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{ Html::style('AdminLTE/bower_components/bootstrap/dist/css/bootstrap.min.css') }}
    {{ Html::style('AdminLTE/bower_components/font-awesome/css/font-awesome.min.css') }}
    {{ Html::style('AdminLTE/bower_components/Ionicons/css/ionicons.min.css') }}
    {{ Html::style('AdminLTE/dist/css/AdminLTE.min.css') }}

    {{ Html::style('AdminLTE/dist/css/skins/_all-skins.min.css') }}
    {{ Html::style('AdminLTE/bower_components/morris.js/morris.css') }}
    {{ Html::style('AdminLTE/bower_components/jvectormap/jquery-jvectormap.css') }}
    {{ Html::style('AdminLTE/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}
    {{ Html::style('AdminLTE/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}


    <!--[if lt IE 9]>
    {{ Html::script('https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js') }}
    {{ Html::script('https://oss.maxcdn.com/respond/1.4.2/respond.min.js') }}
    <![endif]-->

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    {{-- <link rel="stylesheet" href="css/app.css" /> --}}
    {{-- <link rel="stylesheet" href="css/header.css" /> --}}

    {{ Html::style('css/header.css') }}

    @yield('style')

    <!-- 暫定的に直置き。カスタマイズの規模感により別ファイルで管理するようにする。 -->
    <style>
        a.disabled{
            pointer-events: none;
        }
        ul {
            list-style: none;
        }
  /*      .table-bordered>tbody>tr>td {
            border: none;
            border-bottom:1px solid #f4f4f4;
            border-top:1px solid #f4f4f4;
        }*/
        td {
            font-weight: normal;
        }
        .unselectable {
            user-select: none;
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            opacity: 0.5;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    @include('layouts.header')
    @include('layouts.side')


    <div class="content-wrapper">
        <section class="content-header">

            <!-- success通知 -->
            @if (session('success'))
                <div class="message">
                    <div class="alert alert-success">
                    {{ session('success') }}
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- エラー出力 -->
            @if (count($errors) > 0)
                <div class="message errors">
                    <div class="alert alert-danger">
                        <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content_header')

            @yield('breadcrumbs')

        </section>
        <section class="content">

            @yield('content')

        </section>
    </div>

    <footer class="main-footer">
        <div class="pull-right hidden-xs"><!-- 予備スペース --></div>
        <strong>Copyright &copy; <a href="#"> ADPRO. inc. </a></strong> All Rights Reserved.
    </footer>
</div>

@yield('modal')

<!-- jQuery 3 -->
{{ Html::script('AdminLTE/bower_components/jquery/dist/jquery.min.js') }}
<!-- jQuery UI 1.11.4 -->
{{ Html::script('AdminLTE/bower_components/jquery-ui/jquery-ui.min.js') }}
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
{{ Html::script('AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js') }}
{{ Html::script('AdminLTE/dist/js/adminlte.min.js') }}
{{ Html::script('js/common.js') }}
{{ Html::script('js/Ajax.js') }}
@yield('script')

</body>
</html>
