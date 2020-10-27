@extends('app')

@section('title')
@lang('requests.detail.title')
@endsection

@section('content')
    <request-detail-view
        :request-id="{{ $request_id }}"
        :request-work-id="{{ $request_work_id }}"
        :reference-mode="{{ $reference_mode }}"
    >
    </request-detail-view>
@endsection
