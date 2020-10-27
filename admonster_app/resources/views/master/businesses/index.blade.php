@extends('base')

@section('title')
@lang('master/businesses.list.title')
@endsection

@section('content')
<businesses-list-view></businesses-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/master.js') }} "></script>
@endsection
