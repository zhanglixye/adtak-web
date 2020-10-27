@extends('base')

@section('title')
@lang('requests.detail.title')
@endsection

@section('content')
<client-request-detail-view
    :request-id="{{ $request_id }}"
    :request-work-id="{{ $request_work_id }}"
>
</client-request-detail-view>
@endsection

@section('style')
<link rel="stylesheet" href="{{ mix('css/client.css') }}">
@endsection

@section('script')
<script src=" {{ mix('js/client.js') }} "></script>
@endsection
