<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestMailAttachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'request_mail_id',
        'name',
        'file_path',
        'size',
        'width',
        'height',
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
    
    public function requestMail()
    {
        return $this->belongsTo(RequestMail::class);
    }
}
