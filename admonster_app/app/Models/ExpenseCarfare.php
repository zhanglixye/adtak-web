<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $task_id
 * @property integer $step_id
 * @property integer $request_work_id
 * @property boolean $expenses_type
 * @property string $employees_id
 * @property string $spell
 * @property string $name
 * @property float $price
 * @property string $date
 * @property boolean $ap_accord
 * @property boolean $have_station
 * @property boolean $station_accord
 * @property string $ap_unprepared_unknown
 * @property string $station_unprepared_unknown
 * @property string $unprepared_unknown
 * @property string $created_at
 * @property integer $created_user_id
 * @property string $updated_at
 * @property integer $updated_user_id
 * @property Task $task
 */
class ExpenseCarfare extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['task_id', 'step_id', 'request_work_id', 'expenses_type', 'employees_id','spell','name', 'price', 'date', 'ap_accord', 'have_station', 'station_accord', 'ap_unprepared_unknown', 'station_unprepared_unknown', 'unprepared_unknown', 'created_at', 'created_user_id', 'updated_at', 'updated_user_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo('App\Models\Task');
    }
}
