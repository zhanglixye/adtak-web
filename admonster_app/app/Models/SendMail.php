<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendMail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_work_id',
        'references',
        'in_reply_to',
        'reply_to',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'sended_at',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function searchSendMails(array $ids = [])
    {
        $query = self::query()
            ->select(
                'send_mails.id as mail_id',
                'send_mails.references as references',
                'send_mails.in_reply_to as in_reply_to',
                'send_mails.from as mail_from',
                'send_mails.to as mail_to',
                'send_mails.cc as mail_cc',
                'send_mails.bcc as mail_bcc',
                'send_mails.subject as mail_subject',
                'send_mails.content_type as mail_content_type',
                'send_mails.body as mail_body',
                'send_mails.sended_at as mail_sended_at',
                \DB::raw('(select group_concat(concat(name, ":", file_path)) from send_mail_attachments where send_mail_attachments.send_mail_id = send_mails.id) as mail_attachments')
            )
            ->where('is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'));

        if ($ids) {
            $query = $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    public function requestWork()
    {
        return $this->belongsTo(RequestWork::class);
    }

    public function sendMailAttachments()
    {
        return $this->hasMany(SendMailAttachment::class);
    }
}
