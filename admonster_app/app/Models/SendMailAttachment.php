<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendMailAttachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'send_mail_id',
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

    public function sendMail()
    {
        return $this->belongsTo(SendMail::class);
    }
}
