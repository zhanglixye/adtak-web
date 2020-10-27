@extends('base')

@section('title')
@lang('order/orders.list.title')
@endsection

@section('content')
<order-list-view></order-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/order.js') }} "></script>
@endsection
