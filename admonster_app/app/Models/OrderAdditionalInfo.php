<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAdditionalInfo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'order_detail_id',
        'content',
        'is_open_to_client',
        'is_deleted',
        'created_at',
        'create_user_id',
        'updated_at',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 指定の案件IDから案件補足情報リストを取得
     *
     * @param int $order_id 案件ID
     * @param array $form 検索条件
     * @return OrderAdditionalInfo
     */
    public static function getSearchListByOrderId($order_id, $form = [])
    {
        $query = self::where('order_id', $order_id)
            ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->orderBy('updated_at', 'desc');

        $order_detail_id = array_get($form, 'order_detail_id');
        if ($order_detail_id) {
            $query->where('order_detail_id', $order_detail_id);
        } else {
            $query->whereNull('order_detail_id');
        }

        return $query;
    }

    /* -------------------- relations ------------------------- */

    public function orderAdditionalAttachments()
    {
        return $this->hasMany(OrderAdditionalAttachment::class);
    }
}
