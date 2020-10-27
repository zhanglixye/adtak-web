## 画面追加

#### 画面の分類が追加になる場合は以下のファイル作成が必要

1. `cp ./app/order.js ./app/{{ YOUR_ADD_DIRECTORY }}.js`
2. `vi ./app/{{ YOUR_ADD_DIRECTORY }}.js`

Line:7 
```
let requireComponent = require.context('../components/Templates/{{ YOUR_ADD_DIRECTORY }}', true, /\w+\.vue$/);
```

3. `vi {{ 任意のディレクトリ }}/{{ 任意 }}.blade.php`
4. ex) を参考に編集する

ex) ../views/order/orders/index.blade.php
```
@extends('base')

@section('title')
@lang('order/orders.list.title')
@endsection

@section('content')
<order-list-view></order-list-view>
@endsection

@section('script')
<script src=" {{ mix('js/order.js') }} "></script>
@endsection
```
