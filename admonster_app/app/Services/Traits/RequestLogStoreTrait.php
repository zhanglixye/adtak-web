<?php

namespace App\Services\Traits;

use App\Models\RequestLog;

/**
 * RequestLog登録用のトレイト
 */
trait RequestLogStoreTrait
{
    public function storeRequestLog(array $attributes)
    {
        $request_log = new RequestLog;
        $request_log->request_id = $attributes['request_id'];
        $request_log->type = $attributes['type'];
        $request_log->request_work_id = isset($attributes['request_work_id']) ? $attributes['request_work_id'] : null;
        $request_log->task_id = isset($attributes['task_id']) ? $attributes['task_id'] : null;
        $request_log->created_user_id = $attributes['created_user_id'];
        $request_log->updated_user_id = $attributes['updated_user_id'];
        $request_log->save();

        return $request_log;
    }
}
