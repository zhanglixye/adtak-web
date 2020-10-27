@extends('base')

@section('title')
@lang('master/users.list.title')
@endsection

@section('content')
    <user-list-view></user-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/master.js') }} "></script>
@endsection
