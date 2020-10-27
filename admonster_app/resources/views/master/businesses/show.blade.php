@extends('base')

@section('title')
@lang('businesses.detail.title')
@endsection

@section('content')
<master-business-detail-view :business-id="{{ $business_id }}"></master-business-detail-view>
@endsection

@section('script')
<script src=" {{ mix('js/master.js') }} "></script>
@endsection
