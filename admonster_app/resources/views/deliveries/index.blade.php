@extends('app')

@section('title')
@lang('deliveries.list.title')
@endsection

@section('content')
    <deliveries-list-view :inputs={{ $inputs }}></deliveries-list-view>
@endsection
