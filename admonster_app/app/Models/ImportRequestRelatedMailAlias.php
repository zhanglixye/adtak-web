<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRequestRelatedMailAlias extends Model
{
    const UPDATED_AT = null;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_id',
        'alias',
        'create_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
