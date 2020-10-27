<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class TaskResultFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'task_result_id',
        'seq_no',
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

    public static function getFile($task_result_id = null, $seq_no = null, $name = null)
    {
        $query = DB::table('task_result_files')->select('*');

        if ($task_result_id != null) {
            $query->whereIn('task_result_id', $task_result_id);
        }

        if ($seq_no != null) {
            $query->whereIn('seq_no', $seq_no);
        }

        if ($name != null) {
            $query->whereIn('name', $name);
        }
        return $query->get();
    }

    /* -------------------- relations ------------------------- */

    public function taskResult()
    {
        return $this->belongsTo(TaskResult::class);
    }
}
