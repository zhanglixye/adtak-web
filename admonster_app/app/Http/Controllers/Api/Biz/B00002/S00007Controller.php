<?php

namespace App\Http\Controllers\Api\Biz\B00002;

use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\Biz\BaseController;

use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\SendMailAttachment;

class S00007Controller extends BaseController
{
    public function __construct(Request $req)
    {
        parent::__construct($req);
    }

    public function create(Request $req)
    {
        // ----- option -----
        $specific_info = [
            'clerk_email' => config('biz.b00002.clerk_email'),
        ];
        // ----- option -----

        $base_info = parent::create($req)->original;

        return response()->json($specific_info + $base_info);
    }

    // 登録
    public function store(Request $req)
    {
        $this->user = \Auth::user();
        \DB::beginTransaction();

        try {
            // ========================================
            // 排他チェック
            // ========================================

            // ----- option -----
            // ※今回は無し
            // ----- option -----

            parent::exclusive();

            // ========================================
            // 保存処理
            // ========================================

            // ----- option -----
            $task_result_content = json_decode($req->task_result_content, true);  // 作業内容

            $request_work = RequestWork::where('id', $this->request_work_id)->first();
            $request_mail = $request_work->requestMails[0];
            $request_mail_attachments = $request_mail->requestMailAttachments;

            // 送信メール登録
            $mail_body_data = [
                'approval_date' => Carbon::now(),
                'approver_name' => $this->user->name,
                'request_mail' => $request_mail
            ];

            $send_mail = new SendMail;
            $send_mail->request_work_id = $this->request_work_id;
            $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
            $send_mail->to = $task_result_content['clerk_email'];
            $send_mail->subject = '【承認】Re: '.$request_mail->subject;
            $send_mail->content_type = $request_mail->content_type;
            $send_mail->body = $this->makeMailBody($mail_body_data);
            $send_mail->created_user_id = $this->user->id;
            $send_mail->updated_user_id = $this->user->id;
            $send_mail->save();

            // 添付ファイル
            foreach ($request_mail_attachments as $attachment) {
                $send_mail_attachment = new SendMailAttachment;
                $send_mail_attachment->send_mail_id = $send_mail->id;
                $send_mail_attachment->name = $attachment->name;
                $send_mail_attachment->file_path = $attachment->file_path;
                $send_mail_attachment->created_user_id = $this->user->id;
                $send_mail_attachment->updated_user_id = $this->user->id;
                $send_mail_attachment->save();
            }

            // 納品後にメール送付したいのでメールIDを指定パスに追加
            $task_result_content['results']['mail_id'] = array($send_mail['id']);

            $work_time = null;  // 作業時間（当作業では画面での手入力無し）
            // ----- option -----

            if ($task_result_content['results']['type'] === \Config::get('const.TASK_RESULT_TYPE.DONE')) {
                // 完了
                parent::save($task_result_content, $work_time);
            } elseif ($task_result_content['results']['type'] === \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                // 問い合わせ（不明あり）
                parent::contact($task_result_content, $work_time);
            } elseif ($task_result_content['results']['type'] === \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                // 一時保存（対応中）
                parent::temporarySave($task_result_content, $work_time);
            }

            $this->result = 'success';
            \DB::commit();
        } catch (\Exception $e) {
            $this->result = 'error';
            $this->err_message = $e->getMessage();
            \DB::rollback();
            report($e);
        }

        return response()->json([
            'result' => $this->result,
            'err_message' => $this->err_message,
        ]);
    }

    public function makeMailBody($mail_body_data)
    {
        return \View::make('biz.b00002.emails/approval')
            ->with($mail_body_data)
            ->render();
    }
}
