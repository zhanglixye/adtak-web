@extends('app')

@section('title')
@lang('allocations.collective_assignment_confirm')
@endsection

@section('content')
    <multiple-allocation-confirm-view :inputs={{ $inputs }}></multiple-allocation-confirm-view>
@endsection
