<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkTimeBusinessReport extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'actual_date',
        'business_id',
        'step_id',
        'process_type',
        'work_count',
        'worker_count',
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

    public static function getWorkTimeReportByBusinessIds(array $businessIds, array $steps, array $period)
    {
        $query = \DB::table('work_time_business_reports')
            ->select(
                'work_time_business_reports.actual_date',
                'businesses.name as business_name',
                'steps.name as step_name',
                'work_time_business_reports.process_type',
                'work_time_business_reports.work_count',
                'work_time_business_reports.worker_count',
                'work_time_business_reports.system_work_time',
                'work_time_business_reports.manual_work_time',
                'work_time_business_reports.education_flg'
            )
            ->join('businesses', 'work_time_business_reports.business_id', '=', 'businesses.id')
            ->leftJoin('steps', 'work_time_business_reports.step_id', '=', 'steps.id')
            ->orderBy('work_time_business_reports.actual_date', 'asc')
            ->orderBy('businesses.name', 'asc')
            ->orderBy('steps.name', 'asc')
            ->orderBy('work_time_business_reports.education_flg', 'asc')
            ->orderBy('work_time_business_reports.process_type', 'asc')
        ;
        if (!empty($businessIds)) {
            $query = $query->whereIn('work_time_business_reports.business_id', $businessIds);
        }
        if (!empty($steps)) {
            $query = $query->whereIn('work_time_business_reports.step_id', $steps);
        }

        if ($period['from'] && $period['to']) {
            $query = $query->whereBetween('work_time_business_reports.actual_date', [$period['from'], $period['to']]);
        } elseif ($period['from'] && !$period['to']) {
            $query = $query->where('work_time_business_reports.actual_date', '>=', $period['from']);
        } elseif (!$period['from'] && $period['to']) {
            $query = $query->where('work_time_business_reports.actual_date', '<=', $period['to']);
        }
        return $query->get()->toArray();
    }
}
