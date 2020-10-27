<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileImportColumnConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_import_column_config_id',
        'label_id',
        'column_no',
        'item_type',
        'data_type',
        'min',
        'max',
        'is_required',
        'is_active',
        'sort',
        'subject_part_no',
        'request_info_type',
        'is_deleted',
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

    public function fileImportMainConfig()
    {
        return $this->belongsTo(FileImportMainConfig::class);
    }
}
