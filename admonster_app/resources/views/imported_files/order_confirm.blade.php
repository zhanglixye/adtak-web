@extends('app')

@section('title')
@lang('imported_files.index.title')
@endsection

@section('content')
    <imported-order-file-confirm-view :inputs="{{ $inputs }}"></imported-order-file-confirm-view>
@endsection
