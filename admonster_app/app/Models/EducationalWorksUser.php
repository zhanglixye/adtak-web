<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EducationalWorksUser extends Model
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

    public static function getEducationWorkUserByUserIdAndStepId(int $user_id, int $step_id)
    {
        $query = DB::table('educational_works_users')
            ->select('educational_works_users.*')
            ->where('educational_works_users.step_id', $step_id)
            ->where('educational_works_users.user_id', $user_id);
        $education_work_user = $query->get();
        return $education_work_user;
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
