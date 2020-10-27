@extends('app')

@section('title')
@lang('imported_files.index.title')
@endsection

@section('content')
    <imported-file-index-view :inputs="function () { return {{ $inputs }} }"></imported-file-index-view>
@endsection
