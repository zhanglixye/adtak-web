<?php

namespace App\Services\CommonMail\Replay\Biz\B00020;

use App\Models\CommonMailBodyTemplate;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00026Impl extends GenericImpl
{
    /** @var string key of the business name. */
    const REQEUST_WORK_FILE_CONTENT_INFO_KEY_BUSINESS_NAME = 'BUSINESS_NAME';
    /** @var string key of the mail to */
    const REQEUST_WORK_FILE_CONTENT_INFO_KEY_TO = 'TO';
    /** @var string key of the mail cc */
    const REQEUST_WORK_FILE_CONTENT_INFO_KEY_CC = 'CC';


    /**
     * 获取默认的本文.
     * @param int $task_id 作業ID
     * @return mixed 默认的本文
     */
    public function getDefaultBody(int $task_id)
    {
//        ------------------ Original ------------------
//
//        >From: #{before_mail_from}
//
//        >Date:#{before_mail_date}
//
//        >Subject:#{before_mail_subject}
//
//        >To:#{before_mail_to}
//
//        >CC:#{before_mail_cc}
//        #{before_mail_body}

        // 获取模板
        $template_config_array = \Config::get("biz.b00020.MAIL_CONFIG.s00026.MAIL_REPLAY_BODY_TEMPLATE");
        $template_step_id = $template_config_array['step_id'];
        $template_condition_cd = $template_config_array['condition_cd'];
        $task_ext_info_po = parent::getExtInfoById($task_id, \Auth::user()->id);
        $template = CommonMailBodyTemplate::selectBySelective(
            $task_ext_info_po->company_id,
            $task_ext_info_po->business_id,
            $template_step_id,
            $template_condition_cd
        );
        //获取担当者
        $task_user = \DB::table('tasks')
            ->select('users.name')
            ->join('users', 'tasks.user_id', 'users.id')
            ->where('tasks.id', $task_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();
        //获取指定纳期
        $request_works_deadline = \DB::table('tasks')
            ->select('request_works.deadline')
            ->join('request_works', 'tasks.request_work_id', 'request_works.id')
            ->where('tasks.id', $task_id)
            ->first();
        $deadline_timestamp = strtotime($request_works_deadline->deadline) + (9 * 3600);
        $deadline_str = $time_str = date('Y-m-d H:i:s', (int)$deadline_timestamp);
        //邮件
        $email_mixed = parent::getEmailByTaskId($task_id);
        //绑定模板
        $signTemplates = parent::getSignTemplates($task_id, \Auth::user()->id);
        return TemplateEngine::make($template[0]->content, [
            'signature' => $signTemplates === null ? '' : $signTemplates->content,
            'before_mail_from' => $email_mixed->from,
            'before_mail_date' => $email_mixed->recieved_at,
            'before_mail_subject' => $email_mixed->subject,
            'before_mail_to' => $email_mixed->to,
            'before_mail_cc' => $email_mixed->cc,
            'before_mail_body' => parent::makeReplyMailBody($email_mixed->body),
            'step_user' => $task_user->name,
            'request_works_deadline' => $deadline_str
        ]);
    }


    /**
     * 获取ファイル添付.
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed ファイル添付
     */
    public function getDefaultAttachments(int $task_id, int $user_id)
    {
        return [];
    }

    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id)
    {
        return 'rakuten@huihai-info.com';
    }

    /**
     * 获取默认的抄送人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailCc(int $task_id)
    {
        return 'ad-operation@dac.co.jp';
    }

    /**
     * 获取默认的标题.
     * @param int $task_id 作業ID
     * @param float $time_zone 客户端的时区
     * @return mixed 默认的标题
     */
    public function getDefaultSubject(int $task_id, float $time_zone = null)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);
        if ($request_mail_po === null) {
            return '';
        } else {
            return '＜ご対応依頼＞' . $request_mail_po->subject;
        }
    }

    /**
     * メール送信者を取得する
     * @param int $task_id 作業ID
     * @return string 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailSendFrom(int $task_id): string
    {
        return 'ad-operation@dac.co.jp';
    }
}
