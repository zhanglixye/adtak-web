<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyEmployee extends Model
{
    protected $fillable = [
        'company_id',
        'employees_id',
        'spell',
        'name',
        'created_user_id',
        'updated_user_id'
    ];
}
