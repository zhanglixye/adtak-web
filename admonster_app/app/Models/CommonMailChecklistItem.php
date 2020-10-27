<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $business_id
 * @property integer $company_id
 * @property integer $step_id
 * @property string $content
 * @property boolean $is_deleted
 * @property string $created_at
 * @property integer $created_user_id
 * @property string $updated_at
 * @property integer $updated_user_id
 * @property Business $business
 * @property Company $company
 * @property Step $step
 */
class CommonMailChecklistItem extends Model
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
    protected $fillable = ['business_id', 'company_id', 'step_id', 'content', 'is_deleted', 'created_at', 'created_user_id', 'updated_at', 'updated_user_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo('AppModels\Business');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('AppModels\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function step()
    {
        return $this->belongsTo('AppModels\Step');
    }
}
