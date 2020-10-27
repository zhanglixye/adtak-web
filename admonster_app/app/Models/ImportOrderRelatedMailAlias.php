<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImportOrderRelatedMailAlias extends Model
{
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'order_detail_id',
        'alias',
        'is_open_to_client',
        'from',
        'created_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 属性に対するモデルのデフォルト値
     *
     * @var array
     */
    protected $attributes = [
        'is_open_to_client' => false,
    ];

    /**
     * エイリアスの作成
     * @param int $order_id 案件ID
     * @param int $order_detail_id 案件明細ID
     * @param int $second_time_limit 制限秒数
     * @return string
     * @throws \Exception
     */
    public static function tryCreateAlias(int $order_id, ?int $order_detail_id, int $second_time_limit = 30)
    {
        // エイリアスの元となるアカウントは、import_mail_accounts の使用中のアカウントを暫定的にハードコーディングで保持して使用している。
        $base_account = \Config::get('order.order_detail.main_account_of_import_mail');
        $position = strpos($base_account, '@');
        if (!$position) {
            return response()->json([
                'error' => 'エラーが発生しました。',
            ]);
        }
        $account_name = substr($base_account, 0, $position);
        $domain = strstr($base_account, '@');

        // 案件と案件詳細とでエイリアスを出し分ける
        $target_str = $order_detail_id ? 'orderdetail' : 'order';
        $target_id = $order_detail_id ? $order_detail_id : $order_id;

        // 念のため重複アカウント生成回避
        $startTime = new \DateTime('now');
        while (true) {
            /*
             * [エイリアスのフォーマット]
             *
             * ■案件用
             * __取込用アカウントの@以前__+order-___ランダム文字列___-__依頼ID__@__取込用アカウントの@以降__

            * ■案件明細用
             * __取込用アカウントの@以前__+orderdetail-___ランダム文字列___-__依頼作業ID__@__取込用アカウントの@以降__
            */
            $alias = $account_name.'+'.$target_str.'-'.Str::random(8).'-'.$target_id.$domain;
            $exist_cnt = self::where('alias', $alias)->count();
            if ($exist_cnt < 1) {
                return $alias;
            }
            if (date_diff($startTime, new \DateTime('now'))->s < $second_time_limit) {
                throw new \Exception('That\'s over the limit');
            }
        }
    }

    /* -------------------- relations ------------------------- */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
