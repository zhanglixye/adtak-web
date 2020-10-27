@extends('app')

@section('title')
@lang('works.list.title')
@endsection

@section('content')
    <work-list-view :inputs="function () { return {{ $inputs }} }"></work-list-view>
@endsection
