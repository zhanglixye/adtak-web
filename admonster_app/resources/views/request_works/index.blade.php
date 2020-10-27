@extends('app')

@section('title')
@lang('request_works.list.title')
@endsection

@section('content')
    <request-work-list-view :inputs={{ $inputs }}></request-work-list-view>
@endsection
