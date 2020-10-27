<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessFlow extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id',
        'business_id',
        'next_step_id',
        'seq_no',
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

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
