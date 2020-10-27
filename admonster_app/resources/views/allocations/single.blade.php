@extends('app')

@section('title')
@lang('allocations.single_allocate')
@endsection

@section('content')
    <single-allocation-view :inputs={{ $inputs }}></single-allocation-view>
@endsection
