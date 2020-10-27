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
    's00007' => [
        'title' => '経費承認',
        'send_mail_to' => 'メール送信先',
        '_modal' => [
            'p1' => '経費申請を承認し、指定宛先へメールを送信します。よろしいですか？',
        ],
    ],
];
