<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $business_id
 * @property integer $company_id
 * @property integer $step_id
 * @property string $name
 * @property string $mail_account
 * @property integer $to_times
 * @property integer $cc_times
 * @property boolean $is_deleted
 * @property string $created_at
 * @property integer $created_user_id
 * @property string $updated_at
 * @property integer $updated_user_id
 * @property Business $business
 * @property Company $company
 * @property Step $step
 */
class CommonMailFrequency extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'common_mail_to_frequencies';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['business_id', 'company_id', 'step_id', 'name', 'mail_account', 'to_times', 'cc_times', 'is_deleted', 'created_at', 'created_user_id', 'updated_at', 'updated_user_id'];

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

    public static function searchMailToFrequencyList($businessId, $companyId, $stepId, $keyword = null)
    {
        $query = \DB::table('common_mail_frequencies')
            ->selectRaw(
                '`name`,' .
                'mail_account,' .
                'ROUND(to_times/(select sum(to_times) from common_mail_frequencies)*100,2) frequency'
            );
        $query->where('is_deleted', 0)
            ->where('business_id', $businessId)
            ->where('company_id', $companyId)
            ->where('step_id', $stepId);
        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('mail_account', 'like', "%{$keyword}%");
            });
        }
        $query->OrderBy('to_times', 'desc');
        return $query->get();
    }

    public static function searchMailCcFrequencyList($businessId, $companyId, $stepId, $keyword = null)
    {
        $query = \DB::table('common_mail_frequencies')
            ->selectRaw(
                '`name`,' .
                'mail_account,' .
                'ROUND(cc_times/(select sum(cc_times) from common_mail_frequencies)*100,2) frequency'
            );
        $query->where('is_deleted', 0)
            ->where('business_id', $businessId)
            ->where('company_id', $companyId)
            ->where('step_id', $stepId);
        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('mail_account', 'like', "%{$keyword}%");
            });
        }
        $query->OrderBy('cc_times', 'desc');
        return $query->get();
    }

    /**
     * 创建或更新使用频率
     * @param int $business_id 業務ID
     * @param int $company_id 企業ID
     * @param int $step_id 作業ID
     * @param string $mail_account メールアカウント
     * @param string|null $name 名前
     * @param int $to_times mail to回数(1:增加， 0：不变)
     * @param int $cc_times mail cc回数(1:增加， 0：不变)
     * @param int $user_id login user
     */
    public static function upTimes(int $business_id, int $company_id, int $step_id, string $mail_account, string $name = null, int $to_times, int $cc_times, int $user_id)
    {
        \DB::insert(
            'INSERT INTO common_mail_frequencies (business_id, company_id, step_id, mail_account, `name`, to_times, cc_times,' .
            ' created_user_id, updated_user_id, created_at, updated_at )' .
            ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now())' .
            ' ON DUPLICATE KEY UPDATE to_times=to_times + ?,' .
            '                         cc_times=cc_times + ?,' .
            '                         updated_at=now(),' .
            '                         updated_user_id =?',
            [$business_id, $company_id, $step_id, $mail_account, $name, $to_times, $cc_times, $user_id, $user_id, $to_times, $cc_times, $user_id]
        );
    }
}
