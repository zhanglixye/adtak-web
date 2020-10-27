<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class RequestMailAttachmentExtra extends Model
{
    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'request_mail_attachment_extra';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id',
        'mail_attachment_id',
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

    public static function getFile($id = null, $attachment_id = null, $file_name = null)
    {
        $query = DB::table('request_mail_attachment_extra')->select('*');

        if ($id != null) {
            $query->whereIn('id', $id);
        }
        if ($attachment_id != null) {
            $query->whereIn('mail_attachment_id', $attachment_id);
        }
        if ($file_name != null) {
            $query->whereIn('name', $file_name);
        }
        return $query->get();
    }


    /* -------------------- relations ------------------------- */

    public function requestWork()
    {
        return $this->belongsTo(RequestWork::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}
