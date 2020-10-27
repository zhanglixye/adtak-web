{{-- TODO: Remove app.blade.php --}}
@extends('base')

@section('title')
@yield('title')
@endsection

@section('content')
@yield('content')
@endsection

@section('script')
<script src=" {{ mix('js/admin.js') }} "></script>
@endsection
