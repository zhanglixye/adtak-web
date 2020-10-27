@section('style')
    {!! Html::style('js/vender/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') !!}
    @parent
@endsection
@section('script')
    {!! Html::script('js/vender/moment/moment.js') !!}
    {!! Html::script('js/vender/moment/moment-with-locales.js') !!}
    {!! Html::script('js/vender/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') !!}
    @parent
@endsection