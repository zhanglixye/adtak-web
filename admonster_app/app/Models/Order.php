<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_active',
        'is_deleted',
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
        'is_active' => true,
        'is_deleted' => false,
    ];

    /* -------------------- relations ------------------------- */

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function administrators()
    {
        return $this->belongsToMany(User::class, 'orders_administrators');
    }

    public function sharers()
    {
        return $this->belongsToMany(User::class, 'orders_sharers');
    }

    public function orderFiles()
    {
        return $this->belongsToMany(OrderFile::class, 'orders_order_files');
    }

    public function customStatuses()
    {
        return $this->hasMany(CustomStatus::class);
    }
}
