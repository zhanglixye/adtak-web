<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    const TYPE_AUTO_APPROVE = 2;

    const TYPE_REQUEST_SEND_MAIL = 4;

    const STATUS_ERROR = -1;

    const STATUS_NONE = 0;

    const STATUS_DONE = 1;

    const STATUS_ON = 99;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'process_type',
        'argument',
        'queue_status',
        'err_description',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
