<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDestination extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'connection_information',
        'name',
        'path',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /* -------------------- relations ------------------------- */

    public function steps()
    {
        return $this->belongsToMany(Step::class);
    }
}
