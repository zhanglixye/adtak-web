@extends('app')

@section('title')
@lang('education_allocations.multi_allocate')
@endsection

@section('content')
    <education-multiple-allocation-confirm-view :inputs={{ $inputs }}></education-multiple-allocation-confirm-view>
@endsection
