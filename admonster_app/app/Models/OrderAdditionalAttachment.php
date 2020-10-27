<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAdditionalAttachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_additional_info_id',
        'name',
        'file_path',
        'size',
        'width',
        'height',
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

    /* -------------------- relations ------------------------- */
    
    public function orderAdditionalInfo()
    {
        return $this->belongsTo(OrderAdditionalInfo::class);
    }
}
