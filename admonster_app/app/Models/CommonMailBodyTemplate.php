<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonMailBodyTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id',
        'company_id',
        'condition_cd',
        'title',
        'content',
        'is_deleted',
        'created_at',
        'created_user_id',
        'updated_at',
        'updated_user_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function selectBySelective($companyId, $businessId, $stepId, $conditionCd)
    {
        $query = self::query()->where('is_deleted', 0);
        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }
        if ($stepId !== null) {
            $query->where('step_id', $stepId);
        }
        if ($conditionCd !== null) {
            $query->where('condition_cd', $conditionCd);
        }
        return $query->get();
    }
}
