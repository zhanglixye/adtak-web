@extends('base')

@section('title')
@lang('user.list.title')
@endsection

@section('content')
<user-setting-view></user-setting-view>
@endsection

@section('script')
<script src=" {{ mix('js/user.js') }} "></script>
@endsection
