<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'file_path',
        'size',
        'width',
        'height',
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

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'orders_order_files');
    }

    public function orderFileImportMainConfigs()
    {
        return $this->belongsToMany(OrderFileImportMainConfig::class, 'order_files_order_file_import_main_configs');
    }
}
