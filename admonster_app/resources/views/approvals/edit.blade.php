@extends('app')

@section('title')
@lang('approvals.title')
@endsection

@section('content')
    <approval-view :inputs={{ $inputs }}></approval-view>
@endsection
