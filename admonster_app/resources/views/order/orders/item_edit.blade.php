@extends('base')

@section('title')
@lang('order/orders.setting.title')
@endsection

@section('content')
<item-edit-view :inputs="{{ $inputs }}"></edit-view>
@endsection

@section('script')
<script src=" {{ mix('js/order.js') }} "></script>
@endsection
