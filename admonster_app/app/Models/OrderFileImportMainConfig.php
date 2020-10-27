<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFileImportMainConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sheet_name',
        'header_row_no',
        'data_start_row_no',
        'start_column',
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

    public function orderFiles()
    {
        return $this->belongsToMany(OrderFile::class, 'orders_order_files');
    }

    public function orderFileImportColumnConfigs()
    {
        return $this->hasMany(OrderFileImportColumnConfig::class);
    }
}
