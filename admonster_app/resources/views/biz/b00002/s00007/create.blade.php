@extends('base')

@section('title')
{{ $step_name }}
@endsection

@section('content')
<biz-base-view
    :request-work-id="{{ $request_work_id }}"
    :task-id="{{ $task_id }}"
>
</biz-base-view>
@endsection

@section('script')
<script src=" {{ mix('js/biz.js') }} "></script>
@endsection
