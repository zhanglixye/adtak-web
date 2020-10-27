@extends('base')

@section('title')
@lang('biz/abbey_check.abbey_check.title')
@endsection

@section('content')
    <abbey-check-view :request-work-id="{{ $request_work_id }}" :task-id="{{ $task_id }}" :reference-mode="{{ $reference_mode }}"></abbey-check-view>
@endsection

@section('style')
<style>
    input {
        background-color: #fbb0cc;
    }
</style>
<link rel="stylesheet" href="{{ mix('css/biz/common.css') }}">
<link rel="stylesheet" href="{{ mix('css/biz/abbey_check.css') }}">
@endsection

@section('script')
<script src=" {{ mix('js/biz.js') }} "></script>
@endsection
