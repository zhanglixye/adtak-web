<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $business_id
 * @property integer $user_id
 * @property string $title
 * @property string $content
 * @property boolean $is_deleted
 * @property string $created_at
 * @property integer $created_user_id
 * @property string $updated_at
 * @property integer $updated_user_id
 * @property Business $business
 * @property User $user
 */
class CommonMailSignTemplate extends Model
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
    protected $fillable = ['business_id',
        'user_id',
        'title',
        'content',
        'is_deleted',
        'created_at',
        'created_user_id',
        'updated_at',
        'updated_user_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo('App\Model\Business');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User');
    }

    public static function selectBySelective($businessId, $userId)
    {
        $query = self::query()->where('is_deleted', 0);
        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        return $query->first();
    }
}
