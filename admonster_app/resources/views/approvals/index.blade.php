@extends('app')

@section('title')
@lang('approvals.list.title')
@endsection

@section('content')
    <approval-list-view :inputs={{ $inputs }}></approval-list-view>
@endsection
