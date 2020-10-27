@extends('base')

@section('title')
@lang('biz/development.improvement.title')
@endsection

@section('content')
    <improvement-view
        :request-work-id="{{ $request_work_id }}"
        :task-id="{{ $task_id }}"
        :reference-mode="{{ $reference_mode }}"
    >
    </improvement-view>
@endsection

@section('script')
<script src=" {{ mix('js/biz.js') }} "></script>
@endsection
