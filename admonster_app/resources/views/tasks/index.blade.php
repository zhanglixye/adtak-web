@extends('base')

@section('title')
@lang('tasks.index.h1')
@endsection

@section('content')
<task-list-view :inputs={{ $inputs }}></task-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/work.js') }} "></script>
@endsection
