<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomStatusAttribute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'custom_status_id',
        'label_id',
        'sort',
        'is_deleted',
        'created_at',
        'created_user_id',
        'updated_at',
        'updated_user_id',
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
        'is_deleted' => false,
    ];

    /* -------------------- relations ------------------------- */

    public function orderDetails()
    {
        return $this->belongsToMany(OrderDetail::class, 'order_details_related_custom_status_attributes');
    }

    public function customStatus()
    {
        return $this->belongsTo(CustomStatus::class);
    }
}
