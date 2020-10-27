<?php

namespace App\Http\Controllers\Api\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Business;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestMail;
use App\Models\RequestLog;
use App\Models\TaskResult;
use App\Models\Task;
use App\Models\Queue;
use App\Models\Approval;
use App\Models\Delivery;
use App\Models\SendMail;

class AssortMailController extends WfGaisanSyuseiController
{
    public function __construct()
    {
        parent::__construct();
    }

    // メール仕訳画面
    public function create(Request $req)
    {
        $user = \Auth::user();

        $task_id = $req->task_id;

        $task = \DB::table('tasks')
            ->select(
                'status',
                'is_active',
                'message'
            )
            ->where('id', $task_id)
            ->first();

        $task_status = $task->status;
        $is_done_task = ($task_status == \Config::get('const.TASK_STATUS.DONE')) ? true : false;
        $is_inactive_task = ($task->is_active == \Config::get('const.FLG.INACTIVE')) ? true : false;
        $message = $task->message;

        $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();
        $request_mail = $request_work->requestMails->first();
        $started_at = Carbon::now();

        if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML) {
            $request_mail->body = strip_tags($request_mail->body, '<br>');
        } else {
            $request_mail->body = nl2br(e($request_mail->body));
        }

        $task_result = TaskResult::where('task_id', $task_id)->where('step_id', $request_work->step_id)->orderBy('updated_at', 'desc')->first();
        $task_result_content = !is_null($task_result) ? json_decode($task_result->content) : '';

        return response()->json([
            'request_work' => $request_work,
            'request_mail' => $request_mail,
            'task_id' => $task_id,
            'started_at' => $started_at,
            'task_result_content' => $task_result_content,
            'is_done_task' => $is_done_task,
            'is_inactive_task' => $is_inactive_task,
            'message' => $message,
        ]);
    }

    // データ登録
    public function store(Request $req, TaskResult $task_result)
    {
        $user = \Auth::user();
        $step_name = 'メール仕訳';

        // 排他チェック
        $inactivated = Task::checkIsInactivatedById($req->task_id);
        if ($inactivated) {
            return response()->json([
                'result' => 'inactivated',
                'request_work_id' => $req->request_work_id,
                'task_id' => $req->task_id,
            ]);
        }

        // DB登録
        \DB::beginTransaction();

        try {
            // 実作業データ登録
            $task_result_data = [
                'item01' => $req->task_result['item01'],
                'item02' => $req->task_result['item02'],
                'item03' => $req->task_result['item03'],
                'item04' => $req->task_result['item04'],
                'item05' => array_values(array_filter($req->task_result['item05'])) // nullを削除し配列の値のみを取り出す
            ];
            $task_result_data = array_merge($task_result_data, $req->task_result_type_info);
            $task_result_data = array_merge($task_result_data, $req->process_info);

            $task_result = new TaskResult;
            $task_result->task_id = $req->task_id;
            $task_result->step_id = $req->step_id;
            $task_result->started_at = $req->started_at;
            $task_result->finished_at = Carbon::now();
            $task_result->content = json_encode($task_result_data, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = $user->id;
            $task_result->updated_user_id = $user->id;
            $task_result->save();

            $task_result_type = $req->task_result_type_info['result_type'];
            if ($task_result_type == \Config::get('const.TASK_RESULT_TYPE.CANCEL') || $task_result_type == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                /*
                *  対応不要 or 不明あり
                */
                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = Task::STATUS_DONE;
                $task->updated_user_id = $user->id;
                $task->save();

                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_AUTO_APPROVE;
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                // 送信メール登録
                // 業務管理者のメールアドレスを取得
                $business_id = RequestModel::where('id', $req->request_id)->pluck('business_id')->first();
                $business = Business::with('users')->where('id', $business_id)->first();
                $business_admin = $business->users;
                $business_admin_email_array = $this->extractValueBykey($business_admin, 'email');

                $request_mail = RequestMail::where('id', $req->request_mail_id)->first();
                $request_mail_subject = isset($request_mail->subject) ? $request_mail->subject : '';

                $mail_body_data = [
                    'mail_type' => $task_result_type,
                    'business_name' => $business->name,
                    'step_name' => $step_name,
                    'work_user_name' => $user->name,
                    'comment' => $req->task_result_type_info['irregular_comment'],
                    'task_url' => url('biz/wf_gaisan_syusei/assort_mail/'.$req->request_work_id.'/'.$req->task_id.'/create'),
                    'request_mail' => $request_mail,
                ];
                $mail_subject_data = [
                    'business_name' => $business->name,
                    'step_name' => $step_name,
                    'mail_type' => __('biz/wf_gaisan_syusei.common.task_result_type_text.prefix'.$task_result_type),
                    'request_name' => $request_mail_subject,
                ];

                // 本文生成
                $mail_body = $this->makeMailBody($mail_body_data);

                $send_mail = new SendMail;
                $send_mail->request_work_id = $req->request_work_id;
                $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                $send_mail->to = implode(',', $business_admin_email_array);
                $send_mail->subject = $this->generateSendMailSubject($mail_subject_data);
                $send_mail->content_type = $request_mail->content_type;
                $send_mail->body = $mail_body;
                $send_mail->created_user_id = $user->id;
                $send_mail->updated_user_id = $user->id;
                $send_mail->save();

                // 処理キュー登録（メール送信）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_REQUEST_SEND_MAIL;
                $queue->argument = json_encode(['mail_id' => $send_mail->id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                $this->storeRequestLog($task_result_type, $req->request_id, $req->request_work_id, $req->task_id, $user->id);
            } elseif ($task_result_type == \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                /*
                *  保留
                */
                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = Task::STATUS_ON;
                $task->updated_user_id = $user->id;
                $task->save();

                $this->storeRequestLog($task_result_type, $req->request_id, $req->request_work_id, $req->task_id, $user->id);
            } else {
                /*
                *  通常
                */
                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = Task::STATUS_DONE;
                $task->updated_user_id = $user->id;
                $task->save();

                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_AUTO_APPROVE;
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                $this->storeRequestLog($task_result_type, $req->request_id, $req->request_work_id, $req->task_id, $user->id);
            }

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_work_id' => $req->request_work_id,
                'task_id' => $req->task_id
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_work_id' => $req->request_work_id,
                'task_id' => $req->task_id
            ]);
        }
    }

    public function makeMailBody($mail_body_data)
    {
        $mail_body = '';
        if ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
            $mail_body = \View::make('biz.wf_gaisan_syusei.emails/contact')
                            ->with($mail_body_data)
                            ->render();
        } elseif ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.CANCEL')) {
            $mail_body = \View::make('biz.wf_gaisan_syusei.emails/abort')
                            ->with($mail_body_data)
                            ->render();
        }

        return $mail_body;
    }
}
