@extends('base')

@section('title')
{{ $step_name }}
@endsection

@section('content')
<biz-base-view
    :business-id="{{ $business_id }}"
    :step-id="{{ $step_id }}"
    :request-work-id="{{ $request_work_id }}"
    :task-id="{{ $task_id }}"
    :reference-mode="{{ $reference_mode }}"
></biz-base-view>
@endsection

@section('script')
<script src=" {{ mix('js/biz.js') }} "></script>
@endsection
