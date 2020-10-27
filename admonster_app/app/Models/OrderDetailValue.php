<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailValue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_detail_id',
        'value',
        'created_user_id',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /* -------------------- relations ------------------------- */

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function orderFileImportColumnConfigs()
    {
        return $this->belongsToMany(OrderFileImportColumnConfig::class, 'order_file_import_column_configs_order_detail_values');
    }
}
