<?php


namespace App\Services\CommonMail\Replay\Biz\B00006;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CommonMailBodyTemplate;
use App\Models\ExpenseCarfare;
use App\Models\RequestWork;
use App\Models\Task;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00011Impl extends GenericImpl
{
    /** @var string メール作成画面 */
    const CONTENT_NODE_KEY_MAIL_MAIN = 'G00000_27';
    /** @var string MAIL TO */
    const CONTENT_NODE_KEY_MAIL_MAIL_TO = 'C00300_28';
    /** @var string MAIL CC */
    const CONTENT_NODE_KEY_MAIL_MAIL_CC = 'C00300_29';
    /** @var string 「不明あり」で処理します,担当者へのコメント */
    const CONTENT_NODE_KEY_MAIL_UNKNOWN = 'G00000_34';
    /** @var string 作業時間登録 */
    const CONTENT_NODE_KEY_MAIL_WORKTIME = 'G00000_35';
    /** @var string 開始時間 */
    const CONTENT_NODE_KEY_MAIL_WORKTIME_STARTED_AT = 'C00700_36';
    /** @var string 終了時間 */
    const CONTENT_NODE_KEY_MAIL_WORKTIME_FINISHED_AT = 'C00700_37';
    /** @var string 作業時間_Hour */
    const CONTENT_NODE_KEY_MAIL_WORKTIME_TOTAL = 'C00100_38';
    /** @var string ファイルID */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID = 'C00800_32';
    /** @var string ファイル添付 */
    const CONTENT_NODE_KEY_MAIL_ATTACH = 'uploadFiles';
    /** @var string ファイル名 */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_NAME = 'file_name';
    /** @var string ファイル内容 */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_CONTENT = 'file_data';
    /** @var string ファイルseq */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_SEQ_NO = 'file_seq_no';
    /** @var string 作業内容に問題が無いか確認してください */
    const CONTENT_NODE_KEY_MAIL_CHECK_LIST_VALUES = 'G00000_33';
    /** @var string  最後に表示していたページ */
    const CONTENT_NODE_KEY_LAST_DISPLAYED_PAGE = "lastDisplayedPage";

    /** @var string 経費処理画面 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN = 'G00000_1';
    /** @var string 『★交通費（AP）メール』のシートに記載がありますか？　はい：1, いいえ：2 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_EXPENSES_TYPE = 'C00500_2';
    /** @var string 社員番号 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_EMPLOYESS_ID = 'C00100_3';
    /** @var string フリガナ */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_SPELL = 'C00100_4';
    /** @var string 氏名 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_NAME = 'C00100_5';
    /** @var string 申請合計金額 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_PRICE = 'C00101_6';
    /** @var string 会計年月 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_DATE = 'C00100_7';
    /** @var string 金額明細の合計と合計金額欄（太枠欄）が合っていますか？　合致：1, 不一致：2 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_ACCORD = 'C00500_8';
    /** @var string 『★交通費(AP)メール』シート　不備・不明理由を選択して下さい。（複数可） */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_UNKNOWN = 'C00500_9';
    /** @var string 不備 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED = 'C00400_10';
    /** @var string 不備コメント */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING = 'C00200_11';
    /** @var string 不明理由 */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNKNOWN = 'C00400_12';
    /** @var string 不明コメント */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNKNOWN_STRING = 'C00200_13';
    /** @var string 『★交通費(常駐)メール』のシートに記載はありましたか？ はい：1, いいえ：2 */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_HAVE_STATION = 'C00500_14';
    /** @var string 『★交通費(常駐)メール』シートの金額明細の合計と合計金額欄（太枠欄）が合っていますか？ 合致：1, 不一致：2 */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_ACCORD = 'C00500_15';
    /** @var string 不備 */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNPREPARED = 'C00400_17';
    /** @var string 不備コメント */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNPREPARED_STRING = 'C00200_18';
    /** @var string 不明理由 */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNKNOWN = 'C00400_19';
    /** @var string 不明コメント */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNKNOWN_STRING = 'C00200_20';
    /** @var string 不備な点を記載してください */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_UNPREPARED = 'C00200_22';
    /** @var string 不明な点を記載してください */
//    const CONTENT_NODE_KEY_EP_ONE_MAIN_UNKNOWN = 'C00200_23';
    /** @var string 交通費PDF upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN = 'C00800_24';
    /** @var string AP file seq no */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE = 'C00800_25';
    /** @var string 常驻 file seq no */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO = 'C00800_26';
    /** @var string AP file upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE = 'C00800_25_uploadFiles';
    /** @var string 常驻 file upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO = 'C00800_26_uploadFiles';

    /** @var string 录入页面标识 */
    const LAST_DISPLAYED_PAGE_INPUT = '1';
    /** @var string pdf上传页面标识 */
    const LAST_DISPLAYED_UPLOAD_INPUT = '2';
    /** @var string 邮件编辑页面标识 */
    const LAST_DISPLAYED_MAIL_INPUT = '3';

    /*---------------------------------------s00012 attach file json key for mail save-------------------------------------------
    /** @var string 承认前的『★交通費(AP) メール』 シートFile List */
    const CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST = 'C00800_1';
    /** @var string 承认前的『★交通費(常駐)メール』シート File List */
    const CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST = 'C00800_2';
    /** @var string 承认后的『★交通費(AP) メール』 シートFile List */
    const CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST = 'C00800_3';
    /** @var string 承认后的『★交通費(常駐)メール』シート File List */
    const CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST = 'C00800_4';
    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id)
    {
        // 最新の作業履歴
        $task_result_info_po = TaskResult::with('taskResultFiles')
            ->where('task_id', $task_id)
            ->orderBy('id', 'desc')
            ->first();
        $data_array = json_decode($task_result_info_po->content, true);

        // 不備・不明 check
        $ap_unprepared_unknown = [
            // 不備check item （複数可）
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED], []),
            // 不備コメント
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING]),
            // 不明理由
//            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNKNOWN], false),
            // 不明コメント
//            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNKNOWN_STRING])
        ];
        /*$station_unprepared_unknown = [
            // 不備
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNPREPARED], []),
            // 不備コメント
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNPREPARED_STRING]),
            // 不明理由
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNKNOWN], false),
            // 不明コメント
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_STATION_UNKNOWN_STRING])
        ];
        $unprepared_unknown = [
            // 不備な点を記載してください
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_UNPREPARED]),
            // 不明な点を記載してください
            parent::arrayIfNull($data_array, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_UNKNOWN])
        ];*/

        $not_exist_unknown = parent::jsonArrayAllItemEmpty([$ap_unprepared_unknown/*,$station_unprepared_unknown,$unprepared_unknown*/], true);
        if ($not_exist_unknown) {
            // 不備・不明なしの場合：?????@admonstor.jp （経費伝票承認業務工程へ）
            return \Config::get("biz.b00006.MAIL_CONFIG.s00011.MAIL_REPLAY_BODY_TEMPLATE.default_mail_to");
        }

        $all_item_unknown = parent::jsonArrayAllItemNotEmpty([$ap_unprepared_unknown/*,$station_unprepared_unknown,$unprepared_unknown*/], true);
        if ($all_item_unknown) {
            //全体項目に不明あり：管理者
            $ext_info_mixed = parent::getExtInfoById($task_id, \Auth::user()->id);
            $businesses_admin_mixed = \DB::table('businesses_admin')
                ->select('users.email')
                ->join('users', 'businesses_admin.user_id', 'users.id')
                ->where('business_id', $ext_info_mixed->business_id)
                ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                ->first();
            if ($businesses_admin_mixed !== null) {
                return $businesses_admin_mixed->email;
            }
        } else {
            //個別項目に不明あり：送信者（最初は管理者）
            $request_mail = parent::getEmailByTaskId($task_id);
            if ($request_mail === null) {
                return '';
            } else {
                return $request_mail->from;
            }
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
        // 依頼メール
        $request_mail = parent::getEmailByTaskId($task_id);
        if ($request_mail === null) {
            $origin = '';
        } else {
            $origin = $request_mail->subject;
        }
        // 不備・不明なしの場合：【金額Check済】＋元の件名
        return "【金額Check済】" . $origin;
        // 不備・不明ありの場合：【ご確認依頼】＋元の件名
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
        $template_config_array = \Config::get("biz.b00006.MAIL_CONFIG.s00011.MAIL_REPLAY_BODY_TEMPLATE");
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
        //经费信息
        $expense_carfare = ExpenseCarfare::where("task_id", $task_id)->firstOrFail();
        $name = $expense_carfare->name;
        $date = $expense_carfare->date;
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
        //邮件
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
        $attachments_array = [];
        // 反序列化作业履历的content，得到对象
        $content_array = json_decode($task_result_po->content, true);
        // 获取前页面的pdf文件
        if (parent::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN,self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE])) {
            // 1-3-1取得『★交通費(AP) メール』 シート 的文件Seq_no集合
            $ap_file_seq_no_array = self::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN, self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE]);
            // 1-3-2通过seq_no获取文件的信息
            $ap_file_info_array = MailController::getTaskResultFile($task_result_po->id, $ap_file_seq_no_array);
            // 1-3-3添加文件到结果数组
            foreach ($ap_file_info_array as $ap_file) {
                array_push($attachments_array, $ap_file);
            }
        }
        if (parent::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN,self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO])) {
            // 1-3-4取得『★交通費(常駐)メール』シート 的文件Seq_no集合
            $permanent_file_seq_no_array = self::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN, self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO]);
            // 1-3-5通过seq_no获取文件的信息
            $permanent_file_info_array = MailController::getTaskResultFile($task_result_po->id, $permanent_file_seq_no_array);
            // 1-3-6添加文件到结果数组
            foreach ($permanent_file_info_array as $permanent_file) {
                array_push($attachments_array, $permanent_file);
            }
        }
        return $attachments_array;
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

    /**
     * 不明あり
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return mixed 実作業内容
     */
    public function doUnknown(int $task_id, int $user_id, array &$content)
    {
        //不明处理已经在S00011Controller.saveExpenseData方法处理完,定义该方法避免再执行共通的不明处理
    }
}
