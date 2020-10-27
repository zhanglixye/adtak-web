<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'identifier',
        'is_deleted',
        'created_at',
        'created_user_id',
        'updated_at',
        'updated_user_id',
    ];

    /* -------------------- relations ------------------------- */
}
