@extends('app')

@section('title')
@lang('approvals.file_preview.title')
@endsection

@section('content')
    <file-preview-view :inputs="{{ $inputs }}"></file-preview-view>
@endsection
