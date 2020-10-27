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
use App\Models\TaskResult;
use App\Models\Task;
use App\Models\Queue;
use App\Models\Approval;
use App\Models\Delivery;
use App\Models\SendMail;

class InputMailInfoController extends WfGaisanSyuseiController
{
    public function __construct()
    {
        parent::__construct();
    }

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

        // TODO::ユーザーがこの作業の担当者であるかのチェックを入れる(2018.09.03 hama)

        $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();
        $request_mail = $request_work->requestMails->first();

        if ($request_mail->content_type === \App\Models\RequestMail::CONTENT_TYPE_HTML) {
            $request_mail->body = strip_tags($request_mail->body, '<br>');
        } else {
            $request_mail->body = nl2br(e($request_mail->body));
        }

        $started_at = Carbon::now();

        // 前作業の納品データを取得
        $prev_step_delivery_content = json_decode(Delivery::getByRequestWorkId($request_work->before_work_id)->content);

        $task_result = TaskResult::where('task_id', $task_id)->where('step_id', $request_work->step_id)->orderBy('updated_at', 'desc')->first();
        $task_result_content = !is_null($task_result) ? json_decode($task_result->content) : '';

        return response()->json([
            'request_work' => $request_work,
            'prev_step_delivery_content' => $prev_step_delivery_content,
            'request_mail' => $request_mail,
            'task_id' => $task_id,
            'started_at' => $started_at,
            'task_result_content' => $task_result_content,
            'is_done_task' => $is_done_task,
            'is_inactive_task' => $is_inactive_task,
            'message' => $message,
        ]);
    }

    // 実作業データ登録
    public function store(Request $req, TaskResult $task_result)
    {
        $user = \Auth::user();

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
            // タスクのステータスを更新
            $task = Task::find($req->task_id);
            $task->status = Task::STATUS_DONE;
            $task->updated_user_id = $user->id;
            $task->save();

            // 実作業データ登録
            $task_result_data = [
                'item01' => $req->task_result['item01'],
                'item02' => $req->task_result['item02'],
                'item03' => $req->task_result['item03'],
                'item04' => $req->task_result['item04'],
                'item05' => $req->task_result['item05'],
                'item06' => $req->task_result['item06'],
                'item07' => $this->escapeYenMark($req->task_result['item07']),
                'item08' => $this->escapeYenMark($req->task_result['item08']),
            ];
            $task_result_data = array_merge($task_result_data, $req->task_result_unclear);
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

            // 処理キュー登録（自動承認）
            $queue = new Queue;
            $queue->process_type = Queue::TYPE_AUTO_APPROVE;
            $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
            $queue->queue_status = Queue::STATUS_NONE;
            $queue->created_user_id = $user->id;
            $queue->updated_user_id = $user->id;
            $queue->save();

            $has_unclear = $this->checkHasUnclear($req->task_result_unclear);

            if ($has_unclear) {
                // 不明点がある場合
                // 送信メール登録
                // 業務管理者のメールアドレスを取得
                $business_id = RequestModel::where('id', $req->request_id)->pluck('business_id')->first();
                $business_admin = Business::with('users')->where('id', $business_id)->first()->users;
                $business_admin_email_array = $this->extractValueBykey($business_admin, 'email');
                $business_admin_name_array = $this->extractValueBykey($business_admin, 'name');

                // 不明点チェックボックス
                $unclear_points = [
                    'item09' => $req->task_result_unclear['item09'],
                    'item11' => $req->task_result_unclear['item11'],
                    'item13' => $req->task_result_unclear['item13'],
                ];
                // 不明点コメント
                $unclear_point_comments = [
                    'item10' => $req->task_result_unclear['item10'],
                    'item12' => $req->task_result_unclear['item12'],
                    'item14' => $req->task_result_unclear['item14'],
                ];

                $request_mail = RequestMail::where('id', $req->request_mail_id)->first();
                $request_mail_subject = isset($request_mail->subject) ? $request_mail->subject : '';

                $mail_body_data = [
                    'business_admin_name_array' => $business_admin_name_array,
                    'unclear_points' => $unclear_points,
                    'unclear_point_comments' => $unclear_point_comments,
                    'request_mail' => $request_mail
                ];
                // 不明点ありの際に送信するメール内容を検討中のため、一旦コメントアウト
                // // 本文生成
                // $mail_body = $this->makeMailBody($mail_body_data);
                //
                // $send_mail = new SendMail;
                // $send_mail->request_work_id = $req->request_work_id;
                // $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                // $send_mail->to = implode(',', $business_admin_email_array);
                // // $send_mail->subject = 'Re:'. $request_mail_subject;
                // $send_mail->subject = $this->makeUnclearContactMailSubject($req->step_id, $unclear_points, $request_mail_subject, 'biz/wf_gaisan_syusei.input_mail_info');
                // $send_mail->content_type = $request_mail->content_type;
                // $send_mail->body = $mail_body;
                // $send_mail->created_user_id = $user->id;
                // $send_mail->updated_user_id = $user->id;
                // $send_mail->save();
                //
                // // 処理キュー登録（メール送信）
                // $queue = new Queue;
                // $queue->process_type = Queue::TYPE_REQUEST_SEND_MAIL;
                // $queue->argument = json_encode(['mail_id' => $send_mail->id]);
                // $queue->queue_status = Queue::STATUS_NONE;
                // $queue->created_user_id = $user->id;
                // $queue->updated_user_id = $user->id;
                // $queue->save();

                $this->storeRequestLog(\Config::get('const.TASK_RESULT_TYPE.CONTACT'), $req->request_id, $req->request_work_id, $req->task_id, $user->id);
            } else {
                $this->storeRequestLog(\Config::get('const.TASK_RESULT_TYPE.DONE'), $req->request_id, $req->request_work_id, $req->task_id, $user->id);
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

    public function checkHasUnclear($unclear)
    {
        return in_array(self::ANSWER_FOR_QUESTION_YES, $unclear) ? true : false;
    }

    public function makeMailBody($mail_body_data)
    {
        $line_break = ($mail_body_data['request_mail']->content_type === RequestMail::CONTENT_TYPE_HTML) ? '<br>'.PHP_EOL : PHP_EOL;

        $business_admin_name_array = $mail_body_data['business_admin_name_array'];
        $unclear_text_set = $this->getUnclearTextSet($mail_body_data['unclear_points'], $mail_body_data['unclear_point_comments'], $line_break);
        $mail_body = \View::make('biz.wf_gaisan_syusei.input_mail_info/emails/contact')
                        ->with([
                            'business_admin_name_array' => $business_admin_name_array,
                            'unclear_text_set' => $unclear_text_set,
                            'request_mail' => $mail_body_data['request_mail'],
                        ])
                        ->render();

        return $mail_body;
    }

    public function getUnclearTextSet($unclear_points, $unclear_point_comments, $line_break)
    {
        $unclear_text_set = [];

        foreach ($unclear_points as $key => $value) {
            if ($value == self::ANSWER_FOR_QUESTION_YES) {
                $unclear_comment_key = 'item'.sprintf('%02d', intval(str_replace('item', '', $key)) + 1);
                $unclear_text_set[] = __('biz/wf_gaisan_syusei.input_mail_info.'.$key.'.label').$line_break.'=> '.$unclear_point_comments[$unclear_comment_key].$line_break;
            }
        }

        return implode($line_break, $unclear_text_set);
    }
}
