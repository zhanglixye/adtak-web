@extends('app')

@section('title')
@lang('workers.detail.title')
@endsection

@section('content')
    <worker-detail-view :worker-id={{ $user_id }}></worker-detail-view>
@endsection
