@extends('app')

@section('title')
@lang('education_allocations.list.title')
@endsection

@section('content')
    <education-allocation-list-view :inputs={{ $inputs }}></education-allocation-list-view>
@endsection
