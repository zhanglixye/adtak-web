<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ItemConfigValue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_config_id',
        'sort',
        'label_id',
        'is_deleted',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function itemConfig()
    {
        return $this->belongsTo(ItemConfig::class);
    }
}
