<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonMailSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id',
        'mail_to',
        'cc',
        'subject',
        'body',
        'mail_template',
        'sign_template',
        'file_attachment',
        'save_button',
        'check_list_button',
        'review',
        'use_time',
        'unknown',
        'is_deleted',
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

    public static function searchByTaskId($task_id)
    {
        $query = \DB::table('common_mail_settings')
            ->selectRaw(
                'common_mail_settings.id,'.
                'common_mail_settings.business_id,'.
                'common_mail_settings.mail_to,'.
                'common_mail_settings.cc,'.
                'common_mail_settings.subject,'.
                'common_mail_settings.body,'.
                'common_mail_settings.mail_template,'.
                'common_mail_settings.sign_template,'.
                'common_mail_settings.file_attachment,'.
                'common_mail_settings.save_button,'.
                'common_mail_settings.check_list_button,'.
                'common_mail_settings.review,'.
                'common_mail_settings.use_time,'.
                'common_mail_settings.unknown,'.
                'common_mail_settings.is_deleted,'.
                'common_mail_settings.created_at,'.
                'common_mail_settings.created_user_id,'.
                'common_mail_settings.updated_at,'.
                'common_mail_settings.updated_user_id'
            )->join('requests', 'common_mail_settings.business_id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->where('tasks.id', $task_id)
            ->where('common_mail_settings.is_deleted', 0)
            ->where('requests.is_deleted', 0)
            ->where('request_works.is_deleted', 0)
            ->where('tasks.is_active', 1)->first();
        return $query;
    }
}
