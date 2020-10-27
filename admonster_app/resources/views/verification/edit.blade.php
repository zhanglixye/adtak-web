@extends('base')

@section('title')
@lang('verification.verification')
@endsection

@section('content')
<verification-view :inputs={{ $inputs }}></verification-view>
@endsection

@section('script')
<script src=" {{ mix('js/work.js') }} "></script>
@endsection
