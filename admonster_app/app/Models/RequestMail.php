<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Request as RequestModel;

class RequestMail extends Model
{
    const CONTENT_TYPE_TEXT = 1;

    const CONTENT_TYPE_HTML = 2;

    /* -------------------- relations ------------------------- */

    public function requestWorks()
    {
        return $this->belongsToMany(RequestWork::class, 'request_work_mails')->withTimestamps();
    }

    public function requestMailAttachments()
    {
        return $this->hasMany(RequestMailAttachment::class);
    }

    public function requests()
    {
        return $this->belongsToMany(RequestModel::class);
    }
}
