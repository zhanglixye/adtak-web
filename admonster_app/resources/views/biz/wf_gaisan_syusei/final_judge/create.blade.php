@extends('base')

@section('content')
    <final-judge-view :request-work-id="{{ $request_work_id }}" :task-id="{{ $task_id }}"></final-judge-view>
@endsection

@section('style')
<style>
    input {
        background-color: #fbb0cc;
    }
</style>
<link rel="stylesheet" href="{{ mix('css/biz/common.css') }}">
<link rel="stylesheet" href="{{ mix('css/biz/wf_gaisan_syusei.css') }}">
@endsection

@section('script')
<script src=" {{ mix('js/biz.js') }} "></script>
@endsection
