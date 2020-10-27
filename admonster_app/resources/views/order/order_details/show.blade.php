@extends('base')

@section('title')
@lang('order/order_details.show.title')
@endsection

@section('content')
<order-detail-show-view :order_id={{ $order_id }} :order_detail_id={{ $order_detail_id }}></order-detail-show-view>
@endsection

@section('script')
<script src=" {{ mix('js/order.js') }} "></script>
@endsection
