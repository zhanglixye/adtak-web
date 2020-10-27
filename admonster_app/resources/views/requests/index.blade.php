@extends('app')

@section('title')
@lang('requests.list.title')
@endsection

@section('content')
    <request-list-view :inputs={{ $inputs }}></request-list-view>
@endsection
