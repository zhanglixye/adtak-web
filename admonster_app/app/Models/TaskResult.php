<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'step_id',
        'started_at',
        'finished_at',
        'work_time',
        'content',
        'created_user_id',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * task_idをスコープ
     */
    public function scopeTaskId($query, string $task_id)
    {
        return $query->where('task_id', $task_id);
    }

    /**
     * step_idをスコープ
     */
    public function scopeStepId($query, string $step_id)
    {
        return $query->where('step_id', $step_id);
    }

    /**
     * statusをスコープ
     */
    public function scopeStatuses($query, array $status)
    {
        return $query->whereIn('status', $status);
    }

    /**
     * 作業終了したタスク実績リストのクエリを返す
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getFinishedTaskWorkTimeQuery()
    {
        return self::select(
            'task_results.task_id',
            'task_results.work_time'
        )
            ->joinSub(Task::getTaskListByApprovedQuery(), 'task', 'task.id', '=', 'task_results.task_id')
            ->joinSub(self::getLatestIdsGroupByTaskQuery(), 'task_result_latests', 'task_result_latests.id', '=', 'task_results.id');
    }

    /**
     * タスクごとに最新のタスク実績IDを取得返す
     */
    public static function getLatestIdsGroupByTaskQuery()
    {
        return self::select(
            'task_id',
            \DB::raw('MAX(id) as id')
        )
            ->whereNotNull('work_time')
            ->groupBy('task_id');
    }

    public static function latest($task_id)
    {
        return self::select('task_results.*')
            ->leftJoin('task_results as t2', function ($join) {
                $join->on('task_results.task_id', '=', 't2.task_id')
                    ->whereRaw('task_results.updated_at < t2.updated_at');
            })
            ->where('task_results.task_id', $task_id)
            ->whereNull('t2.updated_at')
            ->first();
    }

    /* -------------------- relations ------------------------- */

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function taskResultFiles()
    {
        return $this->hasMany(TaskResultFile::class);
    }

    public function steps()
    {
        return $this->belongsTo(Step::class);
    }
}
