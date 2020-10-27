<?php
/*
経費申請の言語テキスト

[呼び出し方法]

bladeでの使用例
<button>@lang('biz/b00002.common.link_to_index')</button>

blade内のjsでの使用例
<?php echo __('biz/b00002.common.link_to_index'); ?>

Vue.jsのテンプレート内での使用例
<span>{{ $t('biz.b00002.common.link_to_index') }}</span>

Vueインスタンス内での使用例
this.$t('biz.b00002.common.link_to_index')
*/

return [
    'common' => [
        'link_to_index' => '',
    ],
    's00012' => [
        'title' => '経費処理',
        'send_mail_to' => '',
        '_modal' => [
            'p1' => '',
            'notify' => '',
        ],
    ],
];
