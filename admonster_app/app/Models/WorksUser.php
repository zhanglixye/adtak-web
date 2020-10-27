<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorksUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id',
        'user_id',
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

    public static function getWorksUserByUserIdAndStepId(int $user_id, int $step_id)
    {
        $query = DB::table('works_users')
            ->select('works_users.*')
            ->where('works_users.step_id', $step_id)
            ->where('works_users.user_id', $user_id);
        $works_user = $query->get();
        return $works_user;
    }

    /* -------------------- relations ------------------------- */

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
