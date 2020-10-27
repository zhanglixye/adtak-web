<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'name',
        'is_active',
        'is_deleted',
        'created_user_id',
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
        'is_active' => true,
        'is_deleted' => false,
    ];

    /* -------------------- relations ------------------------- */

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function orderDetailValues()
    {
        return $this->hasMany(OrderDetailValue::class);
    }

    public function customStatuses()
    {
        return $this->belongsToMany(CustomStatus::class, 'order_details_related_custom_status_attributes');
    }

    public function customStatusAttributes()
    {
        return $this->belongsToMany(CustomStatusAttribute::class, 'order_details_related_custom_status_attributes');
    }

    public function requests()
    {
        return $this->belongsToMany(Request::class, 'order_details_requests')
            ->withTimestamps();
    }
}
