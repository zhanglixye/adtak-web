<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTimeOperatorReport extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'actual_date',
        'user_id',
        'business_id',
        'step_id',
        'process_type',
        'work_count',
        'ok_count',
        'ng_count',
        'system_work_time',
        'manual_work_time',
        'education_flg',
        'created_user_id',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getWorkTimeReportByUserIds(array $user_ids, array $period)
    {
        $query = \DB::table('work_time_operator_reports')
            ->select(
                'work_time_operator_reports.actual_date',
                'users.name as user_name',
                'businesses.name as business_name',
                'steps.name as step_name',
                'work_time_operator_reports.process_type',
                'work_time_operator_reports.work_count',
                'work_time_operator_reports.ok_count',
                'work_time_operator_reports.ng_count',
                'work_time_operator_reports.system_work_time',
                'work_time_operator_reports.manual_work_time',
                'work_time_operator_reports.education_flg'
            )
            ->join('users', 'work_time_operator_reports.user_id', '=', 'users.id')
            ->leftJoin('businesses', 'work_time_operator_reports.business_id', '=', 'businesses.id')
            ->leftJoin('steps', 'work_time_operator_reports.step_id', '=', 'steps.id')
            ->orderBy('work_time_operator_reports.actual_date', 'asc')
            ->orderBy('users.name', 'asc')
            ->orderBy('businesses.name', 'asc')
            ->orderBy('steps.name', 'asc')
            ->orderBy('work_time_operator_reports.education_flg', 'asc')
            ->orderBy('work_time_operator_reports.process_type', 'asc');
        if (!empty($user_ids)) {
            $query = $query->whereIn('work_time_operator_reports.user_id', $user_ids);
        }

        if ($period['from'] && $period['to']) {
            $query = $query->whereBetween('work_time_operator_reports.actual_date', [$period['from'], $period['to']]);
        } elseif ($period['from'] && !$period['to']) {
            $query = $query->where('work_time_operator_reports.actual_date', '>=', $period['from']);
        } elseif (!$period['from'] && $period['to']) {
            $query = $query->where('work_time_operator_reports.actual_date', '<=', $period['to']);
        }
        return $query->get()->toArray();
    }
}
