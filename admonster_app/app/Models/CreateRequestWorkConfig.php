<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreateRequestWorkConfig extends Model
{
    protected $primaryKey = 'step_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /* -------------------- relations ------------------------- */

    public function step()
    {
        return $this->hasOne(Step::class);
    }
}
