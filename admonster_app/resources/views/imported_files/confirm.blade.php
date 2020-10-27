@extends('app')

@section('title')
@lang('imported_files.index.title')
@endsection

@section('content')
    <imported-file-confirm-view :inputs="{{ $inputs }}"></imported-file-confirm-view>
@endsection
