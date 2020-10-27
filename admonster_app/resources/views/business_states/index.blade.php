@extends('app')

@section('title')
@lang('business_states.list.title')
@endsection

@section('content')
    <business-state-list-view :inputs={{ $inputs }}></business-state-list-view>
@endsection
