<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Request as RequestModel;

class RequestAdditionalInfo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_id',
        'content',
        'is_deleted',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getList($req)
    {
        $query = self::where('request_id', $req->request_id)
            ->where('is_deleted', 0)
            ->orderBy('updated_at', 'desc');

        if ($req->request_work_id) {
            // 依頼作業詳細用
            $query->where('request_work_id', $req->request_work_id);
        } else {
            $query->whereNull('request_work_id');
        }

        return $query;
    }

    /* -------------------- relations ------------------------- */

    public function request()
    {
        return $this->belongsTo(RequestModel::class);
    }

    public function requestAdditionalInfoAttachments()
    {
        return $this->hasMany(RequestAdditionalInfoAttachment::class);
    }
}
