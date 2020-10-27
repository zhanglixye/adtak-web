@extends('app')

@section('title')
@lang('deliveries.detail.title')
@endsection

@section('content')
<delivery-detail-view
    :request-work-id="{{ $request_work_id }}"
    :delivery="{{ $delivery }}"
    :reference-mode="{{ $reference_mode }}"
></delivery-detail-view>
@endsection
