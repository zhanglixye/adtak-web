@extends('base')

@section('title')
    @lang('biz/b00006.s00012.title')
@endsection

@section('content')
    <s00012
{{--            :request-work-id="{{ $request_work_id }}"--}}
{{--            :task-id="{{ $task_id }}"--}}
    >
    </s00012>
@endsection

@section('script')
<script src=" {{ mix('js/biz.js') }} "></script>
@endsection
