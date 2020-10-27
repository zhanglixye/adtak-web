<?php


namespace App\Services\CommonMail\Replay\Biz\B00006;

use App\Models\CommonMailBodyTemplate;
use App\Models\ExpenseCarfare;
use App\Models\RequestWork;
use App\Models\Task;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;
use Illuminate\Support\Facades\DB;

class S00012Impl extends GenericImpl
{

    /** @var string 承认前的『★交通費(AP) メール』 シートFile List */
    const CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST = 'C00800_1';
    /** @var string 承认前的『★交通費(常駐)メール』シート File List */
    const CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST = 'C00800_2';
    /** @var string 承认后的『★交通費(AP) メール』 シートFile List */
    const CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST = 'C00800_3';
    /** @var string 承认后的『★交通費(常駐)メール』シート File List */
    const CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST = 'C00800_4';
    /** @var string ファイル名 */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_NAME = 'file_name';
    /** @var string ファイル内容 */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_PATH = 'file_path';
    /** @var string ファイルseq */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_SEQ_NO = 'file_seq_no';

    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id)
    {
        return \Config::get("biz.b00006.MAIL_CONFIG.s00012.MAIL_REPLAY_BODY_TEMPLATE.default_mail_to");
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
        $request_mail = parent::getEmailByTaskId($task_id);
        if ($request_mail === null) {
            $origin = '';
        } else {
            $origin = $request_mail->subject;
        }
        // 常駐先経費席級＋【金額Check済】＋元の件名
        return "常駐先経費請求【金額Check済】" . $origin;
    }

    /**
     * 获取默认的本文.
     * @param int $task_id 作業ID
     * @return mixed 默认的本文
     */
    public function getDefaultBody(int $task_id)
    {
//        ご担当者様<br/>
//        <br/>
//        お疲れ様です。#{step_name}の担当#{step_user}です。<br/>
//        <br/>
//        経費伝票のCheck業務が終了しましたので、<br/>
//        <br/>
//        社員番号： #{employee_id}<br/>
//        申請者名： #{employee_name}（#{employee_spell}）<br/>
//        合計金額： #{amount}<br/>
//        <br/>
//        #{signature}
//        <br/>
//        ------------------ Original ------------------
//        <br/>
//        >From: #{before_mail_from}
//        <br/>
//        >Date:#{before_mail_date}
//        <br/>
//        >Subject:#{before_mail_subject}
//        <br/>
//        >To:#{before_mail_to}
//        <br/>
//        >CC:#{before_mail_cc}
//        <br/>
//        #{before_mail_body}
        $template_config_array = \Config::get("biz.b00006.MAIL_CONFIG.s00012.MAIL_REPLAY_BODY_TEMPLATE");
        $template_step_id = $template_config_array['step_id'];
        $template_condition_cd = $template_config_array['condition_cd'];
        // 获取模板
        $task_ext_info_po = parent::getExtInfoById($task_id, \Auth::user()->id);
        $template = CommonMailBodyTemplate::selectBySelective(
            $task_ext_info_po->company_id,
            $task_ext_info_po->business_id,
            $template_step_id,
            $template_condition_cd
        );
        // 获取担当者
        $task_user = \DB::table('tasks')
            ->select('users.name')
            ->join('users', 'tasks.user_id', 'users.id')
            ->where('tasks.id', $task_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();


        //ADMonseor登録作業名
        $request_work = RequestWork::findOrFail($task_ext_info_po->request_work_id);
        $step_name = $request_work->step->name;
        // 通过前作业ID获取经费信息
        $before_task_mixed = DB::table('deliveries')->select('approval_tasks.task_id')
            ->join('approval_tasks', 'deliveries.approval_task_id', 'approval_tasks.id')
            ->join('approvals', 'approval_tasks.approval_id', 'approvals.id')
            ->join('request_works', 'approvals.request_work_id', 'request_works.id')
            ->where('request_works.id', $request_work->before_work_id)->first();
        if ($before_task_mixed === null || $before_task_mixed->task_id === null) {
            throw new \Exception('before task not exist or not approved yet!');
        }
        $before_task_id = $before_task_mixed->task_id;
        $expense_carfare = ExpenseCarfare::where("task_id", $before_task_id)->firstOrFail();
        $date = $expense_carfare->date;
        $name = $expense_carfare->name;
        $employees_id = $expense_carfare->employees_id;
        $spell = $expense_carfare->spell;
        $price = $expense_carfare->price;
        if ($employees_id === null || empty($employees_id)) {
            $employees_id = '';
            $spell = '';
            $price = '';
            $name = '';
            $date = '';
        } else {
            $price = number_format($expense_carfare->price, 0) . '円';
            $spell = '（'. $spell . '）';
        }
        // 原邮件
        $email_mixed = parent::getEmailByTaskId($task_id);
        //绑定模板
        $signTemplates = parent::getSignTemplates($task_id, \Auth::user()->id);
        return TemplateEngine::make($template[0]->content, [
            'step_name' => $step_name,
            'step_user' => $task_user->name,
            'date' => $date,
            'employee_id' => $employees_id,
            'employee_name' => $name,
            'employee_spell' => $spell,
            'amount' => $price,
            'before_mail_from' => $email_mixed->from,
            'before_mail_date' => $email_mixed->recieved_at,
            'before_mail_subject' => $email_mixed->subject,
            'before_mail_to' => $email_mixed->to,
            'before_mail_cc' => $email_mixed->cc,
            'before_mail_body' => parent::makeReplyMailBody($email_mixed->body),
            'signature' => $signTemplates === null ? '' : $signTemplates->content
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

        $query = Task::where('id', $task_id)
            ->where('user_id', $user_id)
            ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
            ->where('is_active', \Config::get('const.FLG.ACTIVE'));
        $task = $query->first();
        if ($task === null) {
            throw new \Exception("The task does not exist or is completed, task_id:$task_id user_id:$user_id");
        }

        // 获取Task result
        $task_result_po = TaskResult::with('taskResultFiles')
            ->where('task_id', $task_id)
            ->orderBy('id', 'desc')
            ->first();
        // 反序列化作业履历的content，得到对象
        $content_array = json_decode($task_result_po->content, true);
        //================================================获取『★交通費(常駐)メール』シート承认后的文件====================================================
        // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
        $approval_permanent_file_seq_no_array = parent::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST], []);
        // 通过seq_no获取文件的base64表示
        $approval_permanent_file_array = self::getTaskResultFile($task_result_po->id, $approval_permanent_file_seq_no_array);

        return $approval_permanent_file_array;
    }

    /**
     * 获取默认的CheckList选项.
     * @param int $task_id 作業ID
     * @return mixed 默认的CheckList选项.
     */
    public function getDefaultChecklistValues(int $task_id)
    {
//        $items = parent::getChecklistItems($task_id, \Auth::user()->id);
//        if (!empty($items)) {
//            // 默认选中第一项
//            return [$items[0]->id];
//        }
        return [];
    }

    /**
     * 通过作业履历ID获取指定的文件
     * @param int $task_result_id 作业履历ID
     * @param array $seq_no_array 文件序号的数组
     * @return array 文件数组
     */
    private function getTaskResultFile(int $task_result_id, array $seq_no_array): array
    {
        $file_array = [];
        foreach ($seq_no_array as $seqNo) {
            $taskResultFile = TaskResultFile::where('task_result_id', $task_result_id)
                ->where('seq_no', $seqNo)
                ->firstOrFail();
            $fileData = CommonDownloader::base64FileFromS3($taskResultFile['file_path'], $taskResultFile['name'])[0];
            $file[self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_NAME] = $taskResultFile['name'];
            $file[self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_PATH] = $taskResultFile['file_path'];
            $file[self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_SEQ_NO] = $seqNo;
            array_push($file_array, $file);
        }
        return $file_array;
    }

    /**
     * 获取默认的抄送人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailCc(int $task_id)
    {
        return null;
    }
}
