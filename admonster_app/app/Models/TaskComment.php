<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class TaskComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'task_id',
        'step_id',
        'content',
        'global_comment',
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

    /* -------------------- relations ------------------------- */

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
