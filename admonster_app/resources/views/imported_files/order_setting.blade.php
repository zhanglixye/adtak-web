@extends('app')

@section('title')
@lang('imported_files.index.title')
@endsection

@section('content')
    <imported-file-preference-view :inputs="{{ $inputs }}"></imported-file-preference-view>
@endsection
