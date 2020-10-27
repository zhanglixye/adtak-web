<?php


namespace App\Services\CommonMail\Replay\Biz\B00010;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CommonMailBodyTemplate;
use App\Models\TaskResultFile;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00016Impl extends GenericImpl
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
        $template_config_array = \Config::get("biz.b00010.MAIL_CONFIG.s00016.MAIL_REPLAY_BODY_TEMPLATE");
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
        //排他制御
        MailController::exclusiveTaskByUserId($task_id, $user_id, false);
        //最新の作業履歴
        $task_result_po = MailController::getTaskResult($task_id);
        // 反序列化作业履历的content，得到对象
        $db_content_array = json_decode($task_result_po->content, true);
        // PDF画面データ
        $G00000_1_array = self::arrayIfNull($db_content_array, ['G00000_1'], []);
        $seq_no_array = array();
        for ($i = 0; $i < count($G00000_1_array); $i++) {
            //pdf押印結果
            $C00800_3_seq_no = self::arrayIfNull($G00000_1_array[$i], ['C00800_3']);
            if ($C00800_3_seq_no != null) {
                array_push($seq_no_array, $C00800_3_seq_no);
            }
        }
        if (count($seq_no_array) > 0) {
            //ファイル情報検索
            return MailController::getTaskResultFile($task_result_po->id, $seq_no_array);
        } else {
            return [];
        }
    }

    /**
     * 复制邮件共通页面以外的其它页面文件的定制方法(在复杂业务场景时,邮件共通的复制其它页面附件的方法无法满足需求时,在业务层面重写该方法)
     * @param array $task_result_content task_result表的content对应的数组表示,直接修改此数组,邮件共通会将结果序列化为json保存到数据库中
     * @param array $task_result_file_array task_result_file的数组,将当前处理页面要保存的task_result_file放入此数组,邮件共通会统一保存到数据库中(邮件共通处理中已经放入了邮件附件文件)
     * @param int $task_id 当前操作的task的task_id
     * @param int $task_result_id 当前操作的ask_result_id
     * @param int $max_seq_no 当前最大的附件文件序号(邮件共通处理中已经处理了部分文件)
     * @return int 本处理结束后,附件文件的序号的最大值
     */
    public function otherPageAttachFile(array &$task_result_content, array &$task_result_file_array, int $task_id, int $task_result_id, int $max_seq_no): int
    {
        $pdf_file_array = MailController::arrayIfNull($task_result_content, ['G00000_1'], []);
        $G00000_1_array = [];
        foreach ($pdf_file_array as $pdf_file) {
            $C00800_2_seq_no = MailController::arrayIfNull($pdf_file, ['C00800_2']);
            if (!empty($C00800_2_seq_no)) {
                $task_result_file = MailController::getFileInfoByTaskResultIdAndSeqNO($task_result_id, $C00800_2_seq_no);
                $max_seq_no = $max_seq_no + 1;
                $task_result_file_po = new TaskResultFile;
                $task_result_file_po->name = $task_result_file['file_name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->size = $task_result_file['size'];
                $task_result_file_po->width = $task_result_file['width'];
                $task_result_file_po->height = $task_result_file['height'];
                array_push($task_result_file_array, $task_result_file_po);
                $pdf_file['C00800_2'] = $max_seq_no;
            }
            $C00800_3_seq_no = MailController::arrayIfNull($pdf_file, ['C00800_3']);
            if (!empty($C00800_3_seq_no)) {
                $task_result_file = MailController::getFileInfoByTaskResultIdAndSeqNO($task_result_id, $C00800_3_seq_no);
                $max_seq_no = $max_seq_no + 1;
                $task_result_file_po = new TaskResultFile;
                $task_result_file_po->name = $task_result_file['file_name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->size = $task_result_file['size'];
                $task_result_file_po->width = $task_result_file['width'];
                $task_result_file_po->height = $task_result_file['height'];
                array_push($task_result_file_array, $task_result_file_po);
                $pdf_file['C00800_3'] = $max_seq_no;
            }
            array_push($G00000_1_array, $pdf_file);
        }
        $task_result_content['G00000_1'] = $G00000_1_array;
        return $max_seq_no;
    }

    /**
     * メール送信者を取得する
     * @param int $task_id 作業ID
     * @return string 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailSendFrom(int $task_id): string
    {
        return 'yamasaki@adpro-inc.co.jp';
    }
}
