@extends('app')

@section('title')
@lang('businesses.detail.title')
@endsection

@section('content')
    <business-detail-view :business-id="{{ $business_id }}"></business-detail-view>
@endsection
