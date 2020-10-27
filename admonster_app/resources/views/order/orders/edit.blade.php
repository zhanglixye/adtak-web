@extends('base')

@section('title')
@lang('order/orders.setting.title')
@endsection

@section('content')
<order-edit-view :inputs="{{ $inputs }}"></order-edit-view>
@endsection

@section('script')
<script src=" {{ mix('js/order.js') }} "></script>
@endsection
