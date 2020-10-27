@extends('base')

@section('title')
@lang('master/steps.list.title')
@endsection

@section('content')
<steps-list-view></steps-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/master.js') }} "></script>
@endsection
