@extends('base')

@section('title')
@lang('案件明細一覧')
@endsection

@section('content')
<order-detail-list-view :order_id={{ $order_id }}></order-detail-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/order.js') }} "></script>
@endsection
