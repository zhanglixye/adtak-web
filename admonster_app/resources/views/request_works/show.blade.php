@extends('app')

@section('title')
@lang('request_works.detail.title')
@endsection

@section('content')
    <request-work-detail-view :request-work-id={{ $request_work_id }}></request-work-detail-view>
@endsection
