<?php


namespace App\Services\CommonMail\Replay\Biz\B00011;

use App\Models\CommonMailBodyTemplate;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00017Impl extends GenericImpl
{

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
        $template_config_array = \Config::get("biz.b00011.MAIL_CONFIG.s00017.MAIL_REPLAY_BODY_TEMPLATE");
        $template_step_id = $template_config_array['step_id'];
        $template_condition_cd = $template_config_array['condition_cd'];
        $task_ext_info_po = parent::getExtInfoById($task_id, \Auth::user()->id);
        $template = CommonMailBodyTemplate::selectBySelective(
            $task_ext_info_po->company_id,
            $task_ext_info_po->business_id,
            $template_step_id,
            $template_condition_cd
        );
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
            'before_mail_body' => parent::makeReplyMailBody($email_mixed->body)
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
}
