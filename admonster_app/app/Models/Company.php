<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'name_kana',
        'formal_name',
        'company_type',
        'postal_code',
        'address',
        'tel',
        'billing_standard',
        'billing_closing_day',
        'deposit_criteria',
        'deposit_closing_day',
        'bank',
        'bank_branch',
        'account_type',
        'account_no',
        'account_name',
        'remarks',
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
    
    public function business()
    {
        return $this->hasMany(Business::class);
    }
}
