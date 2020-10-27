@extends('app')

@section('title')
@lang('allocations.collective_assignment')
@endsection

@section('content')
    <multiple-allocation-view :inputs={{ $inputs }}></multiple-allocation-view>
@endsection
