<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Request as RequestModel;

class RequestLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_id',
        'type',
        'request_work_id',
        'task_id',
        'request_additional_info_id',
        'comment',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getList($id, array $form = [])
    {
        $query = self::select(
            'request_logs.*',
            'steps.id as step_id',
            'steps.name as step_name',
            'request_works.code as request_work_code'
        )
            ->where('request_logs.request_id', $id)
            ->leftJoin('request_works', function ($join) {
                $join->on('request_works.id', '=', 'request_logs.request_work_id')
                    ->whereNotNull('request_logs.request_work_id');
            })
            ->leftJoin('steps', function ($join) {
                $join->on('request_works.step_id', '=', 'steps.id')
                    ->whereNotNull('request_works.step_id');
            })
            ->orderBy('request_logs.created_at', 'desc')
            ->orderBy('id', 'desc');

        if (isset($form['request_work_id']) && $form['request_work_id']) {
            $query->where('request_logs.request_work_id', $form['request_work_id']);
        }

        $list = $query->get();

        return $list;
    }

    /* -------------------- relations ------------------------- */

    public function request()
    {
        return $this->belongsTo(RequestModel::class);
    }
}
