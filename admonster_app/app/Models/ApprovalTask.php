<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalTask extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'approval_id',
        'task_id',
        'approval_result',
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

    public function approval()
    {
        return $this->belongsTo(Approval::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}
