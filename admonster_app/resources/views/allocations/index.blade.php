@extends('app')

@section('title')
@lang('allocations.list.title')
@endsection

@section('content')
    <allocation-list-view :inputs={{ $inputs }}></allocation-list-view>
@endsection
