@extends('app')

@section('title')
@lang('education_allocations.single_allocate')
@endsection

@section('content')
    <education-single-allocation-view :inputs={{ $inputs }}></education-single-allocation-view>
@endsection
