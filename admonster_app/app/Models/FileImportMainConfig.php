<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileImportMainConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id',
        'start_row_no',
        'subject_delimiter',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /* -------------------- relations ------------------------- */

    public function fileImportColumnConfigs()
    {
        return $this->hasMany(FileImportColumnConfig::class);
    }
}
