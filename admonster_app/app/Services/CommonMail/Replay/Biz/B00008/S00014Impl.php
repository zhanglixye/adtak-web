<?php


namespace App\Services\CommonMail\Replay\Biz\B00008;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CommonMailBodyTemplate;
use App\Models\ExpenseCarfare;
use App\Models\Queue;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00014Impl extends GenericImpl
{

    /** @var string key of the business name. */
    const REQEUST_WORK_FILE_CONTENT_INFO_KEY_BUSINESS_NAME = 'BUSINESS_NAME';
    /** @var string key of the mail to */
    const REQEUST_WORK_FILE_CONTENT_INFO_KEY_TO = 'TO';
    /** @var string key of the mail cc */
    const REQEUST_WORK_FILE_CONTENT_INFO_KEY_CC = 'CC';

    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id)
    {
        $task_ext_info = $this->getExtInfoById($task_id, \Auth::user()->id);

        $request_work_file_content_info_array = $this->getRequestWorkFileContentInfo($task_ext_info->request_work_id);
        $mail_to_str = $request_work_file_content_info_array[self::REQEUST_WORK_FILE_CONTENT_INFO_KEY_TO];
        if (empty($mail_to_str)) {
            return [];
        } else if (strpos($mail_to_str, ',')) {
            return explode(',', $mail_to_str);
        } else {
            return [$mail_to_str];
        }
    }

    /**
     * 获取默认的抄送人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailCc(int $task_id)
    {
        $task_ext_info = $this->getExtInfoById($task_id, \Auth::user()->id);
        $request_work_file_content_info_array = $this->getRequestWorkFileContentInfo($task_ext_info->request_work_id);
        $mail_cc_str = $request_work_file_content_info_array[self::REQEUST_WORK_FILE_CONTENT_INFO_KEY_CC];
        if (empty($mail_cc_str)) {
            return [];
        } else if (strpos($mail_cc_str, ',')) {
            return explode(',', $mail_cc_str);
        } else {
            return [$mail_cc_str];
        }
    }

    /**
     * 获取默认的标题.
     * @param int $task_id 作業ID
     * @param float $time_zone 客户端的时区
     * @return mixed 默认的标题
     */
    public function getDefaultSubject(int $task_id, float $time_zone = null)
    {
        $task_ext_info = $this->getExtInfoById($task_id, \Auth::user()->id);
        $request_work_file_content_info_array = $this->getRequestWorkFileContentInfo($task_ext_info->request_work_id);
        $timestamp = time() + $time_zone * 3600;
        $time_str = date('md', (int)$timestamp);
        $business_name =  $request_work_file_content_info_array[self::REQEUST_WORK_FILE_CONTENT_INFO_KEY_BUSINESS_NAME];

        return "${time_str}${business_name}";
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
//        経費伝票のCheck業務が終了しましたので、承認をお願いします。<br/>
//        <br/>
//        社員番号： #{employee_id}<br/>
//        申請者名： #{employee_name}（#{employee_spell}）<br/>
//        合計金額： #{amount}<br/>
//        <br/>
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
        $template_config_array = \Config::get("biz.b00006.MAIL_CONFIG.s00014.MAIL_REPLAY_BODY_TEMPLATE");
        $template_step_id = $template_config_array['step_id'];
        $template_condition_cd = $template_config_array['condition_cd'];
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

        //绑定模板
        $signTemplates = parent::getSignTemplates($task_id, \Auth::user()->id);
        return TemplateEngine::make($template[0]->content, [
            'step_name' => $step_name,
            'step_user' => $task_user->name,
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
        return [];
    }




    /**
     * 不明あり
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return mixed 実作業内容
     */
    public function doUnknown(int $task_id, int $user_id, array &$content)
    {
        $task_ext_info = $this->getExtInfoById($task_id, $user_id);

        $mail_to = '';
        $query= \DB::table('businesses_candidates')
            ->select('businesses_candidates.mail_address')
            ->join('businesses_admin', function ($join) {
                $join->on('businesses_admin.business_id', 'businesses_candidates.business_id')
                    ->on('businesses_admin.user_id', 'businesses_candidates.user_id');
            })
            ->where('businesses_admin.business_id', 8);
        $admin_mail_address_collection = $query->get();
        if (!$admin_mail_address_collection->isEmpty()) {
            foreach ($admin_mail_address_collection as $admin_mail_address) {
                if ($mail_to !== '') {
                    $mail_to = $mail_to . ',';
                }
                $mail_to = $mail_to . $admin_mail_address->mail_address;
            }
        }


        // 获取担当者
        $task_user = \DB::table('tasks')
            ->select('users.name')
            ->join('users', 'tasks.user_id', 'users.id')
            ->where('tasks.id', $task_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();

        // 获取担当者email
        $task_user_mail_address = \DB::table('tasks')
            ->select('users.email')
            ->join('users', 'tasks.user_id', 'users.id')
            ->where('tasks.id', $task_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();

        $comment = MailController::arrayIfNull($content, ['G00000_27','C00200_34']);
        $mail_body_data = [
            'comment' => $comment,
            'mail_address' => $task_user_mail_address->email,
            'task_user' => $task_user->name,
        ];

        // 本文生成
        $mail_body = \View::make('biz.b00008.s00014.emails')->with($mail_body_data);

        $send_mail = new SendMail;
        $send_mail->cc = null;
        $send_mail->request_work_id = $task_ext_info->request_work_id;
        $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
        $send_mail->subject = '【登録作業_不備・不明】';
        $send_mail->body = $mail_body;
        $send_mail->created_user_id = \Auth::user()->id;
        $send_mail->updated_user_id = \Auth::user()->id;
        $send_mail->content_type = 1;
        $send_mail->to = $mail_to;
        $send_mail->save();

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => (int) $task_ext_info->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = \Auth::user()->id;
        $queue->updated_user_id = \Auth::user()->id;
        $queue->save();
        // 処理キュー登録（send mail）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
        $queue->argument = json_encode(['mail_id' => (int)$send_mail->id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = \Auth::user()->id;
        $queue->updated_user_id = \Auth::user()->id;
        $queue->save();
    }

    /**
     * 依頼作業のファイル内のコンテンツを取得する.
     * @param int $request_work_id 依頼作業ID
     * @return array 依頼作業のファイル内のコンテンツ
     */
    private function getRequestWorkFileContentInfo(int $request_work_id) : array
    {
        $content_array = [];
        $subject_json_key = 'mail_subject1';
        $to_json_key = 'mail_to';
        $cc_json_key = 'mail_cc';

        $result = \DB::table('request_work_files')
            ->where('request_work_id', $request_work_id)
            ->first();
        if ($result !== null && !empty($result->content)) {
            $content_array = json_decode($result->content, true);
        }

        return [
            self::REQEUST_WORK_FILE_CONTENT_INFO_KEY_BUSINESS_NAME => MailController::arrayIfNull($content_array, [$subject_json_key], ''),
            self::REQEUST_WORK_FILE_CONTENT_INFO_KEY_TO => MailController::arrayIfNull($content_array, [$to_json_key], ''),
            self::REQEUST_WORK_FILE_CONTENT_INFO_KEY_CC => MailController::arrayIfNull($content_array, [$cc_json_key], '')
        ];
    }
}
