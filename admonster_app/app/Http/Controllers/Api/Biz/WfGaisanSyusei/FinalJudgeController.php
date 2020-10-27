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
use App\Models\RequestMailAttachment;
use App\Models\TaskResult;
use App\Models\Task;
use App\Models\Step;
use App\Models\Queue;
use App\Models\Approval;
use App\Models\Delivery;
use App\Models\SendMail;
use App\Models\SendMailAttachment;

class FinalJudgeController extends WfGaisanSyuseiController
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

        $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();
        $request_mail_id = $request_work->requestMails->first()->id;
        $started_at = Carbon::now();

        // 同じ依頼から発生した他のJOBNO
        $related_active_jobnos = RequestWork::getOtherCodesInSameRequest($request_work->request_id, $request_work->code);

        // spreadsheetのデータを取得
        $master_data = $this->getMasterDataOnSpreadSheet($req, $task_id, $request_work->code);

        // 当該作業以外の全作業の納品データを取得
        $deliveries = \DB::table('deliveries')
            ->select(
                'OD.id as request_work_id',
                'OD.step_id',
                'deliveries.content',
                'S.url as step_url',
                'T.id as task_id'
            )
            ->leftJoin('approval_tasks as AT', 'deliveries.approval_task_id', '=', 'AT.id')
            ->leftJoin('approvals as AP', 'AT.approval_id', '=', 'AP.id')
            ->leftJoin('request_works as OD', 'AP.request_work_id', '=', 'OD.id')
            ->leftJoin('steps as S', 'OD.step_id', '=', 'S.id')
            ->join('tasks as T', 'T.request_work_id', '=', 'OD.id')
            ->whereIn('OD.id', function ($query) use ($request_work) {
                $query->select('id')
                     ->from('request_works')
                     ->where('request_id', $request_work->request_id)
                     ->where('code', $request_work->code);
            })
            ->get();

        $steps = \DB::table('steps')
            ->select(
                'steps.id',
                'steps.name'
            )
            ->join('business_flows as BF', 'BF.step_id', 'steps.id')
            ->join('requests as R', 'BF.business_id', 'R.business_id')
            ->where('R.id', $request_work->request_id)
            ->groupBy('steps.id')
            ->orderBy('steps.id', 'asc')
            ->get();

        $works_users = \DB::table('works_users')
            ->select(
                'works_users.step_id',
                'works_users.user_id',
                'users.name as user_name'
            )
            ->join('users', 'works_users.user_id', 'users.id')
            ->join('steps', 'works_users.step_id', 'steps.id')
            ->whereIn('steps.id', array_pluck($steps->toArray(), 'id'))
            ->get();

        // 作業担当者情報を作業IDをキーとした配列にする
        $operators = [];
        foreach ($works_users as $works_user) {
            if (array_key_exists($works_user->step_id, $operators)) {
                array_push($operators[$works_user->step_id], array('user_id' => $works_user->user_id, 'user_name' => $works_user->user_name));
            } else {
                $operators[$works_user->step_id] = [array('user_id' => $works_user->user_id, 'user_name' => $works_user->user_name)];
            }
        }

        // 作業毎にまとめ
        $agency_mail_data = [];
        $sas_data = [];
        $media_mail_data = [];
        $ppt_data = [];
        foreach ($deliveries as $delivery) {
            switch ($delivery->step_id) {
                case self::STEP_ID_INPUT_MAIL_INFO:
                    $agency_mail_data = json_decode($delivery->content, true);
                    $unclear_point_keys = ['item09', 'item11', 'item13'];
                    $agency_mail_data['unclear_points_cnt'] = $this->getUnclearPointsCnt($agency_mail_data, $unclear_point_keys);
                    $agency_mail_data['task_url'] = $this->generateTaskUrl($delivery->step_url, $delivery->request_work_id, $delivery->task_id);
                    break;
                case self::STEP_ID_INPUT_SAS_INFO:
                    $sas_data = json_decode($delivery->content, true);
                    $unclear_point_keys = ['item17', 'item19', 'item21'];
                    $sas_data['unclear_points_cnt'] = $this->getUnclearPointsCnt($sas_data, $unclear_point_keys);
                    $sas_data['task_url'] = $this->generateTaskUrl($delivery->step_url, $delivery->request_work_id, $delivery->task_id);
                    break;
                case self::STEP_ID_IDENTIFY_PPT:
                    $media_mail_data = json_decode($delivery->content, true);
                    $unclear_point_keys = ['item07'];
                    $media_mail_data['unclear_points_cnt'] = $this->getUnclearPointsCnt($media_mail_data, $unclear_point_keys);
                    $media_mail_data['task_url'] = $this->generateTaskUrl($delivery->step_url, $delivery->request_work_id, $delivery->task_id);
                    break;
                case self::STEP_ID_INPUT_PPT_INFO:
                    $ppt_data = json_decode($delivery->content, true);
                    $unclear_point_keys = ['item04', 'item06', 'item08'];
                    $ppt_data['unclear_points_cnt'] = $this->getUnclearPointsCnt($ppt_data, $unclear_point_keys);
                    $ppt_data['task_url'] = $this->generateTaskUrl($delivery->step_url, $delivery->request_work_id, $delivery->task_id);
                    break;
            }
        }

        $delivery_data_set = [
            'agency_mail_data' => $agency_mail_data,
            'sas_data' => $sas_data,
            'media_mail_data' => $media_mail_data,
            'ppt_data' => $ppt_data,
        ];

        $task_result = TaskResult::where('task_id', $task_id)->where('step_id', $request_work->step_id)->orderBy('updated_at', 'desc')->first();
        $task_result_content = !is_null($task_result) ? json_decode($task_result->content) : '';

        return response()->json([
            'request_work' => $request_work,
            'master_data' => $master_data,
            'request_mail_id' => $request_mail_id,
            'task_id' => $task_id,
            'started_at' => $started_at,
            'task_result_content' => $task_result_content,
            'step_ids' => self::$step_ids,
            'judge_types' => self::$judge_types,
            'is_done_task' => $is_done_task,
            'is_inactive_task' => $is_inactive_task,
            'steps' => $steps,
            'operators' => $operators,
            'delivery_data_set' => $delivery_data_set,
            'related_active_jobnos' => $related_active_jobnos,
        ]);
    }

    // 実作業データ登録
    public function store(Request $req, TaskResult $task_result)
    {
        $user = \Auth::user();
        $step_name = '最終判定';

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
            $judge_type = $req->judge_type;
            $task_url = url('biz/wf_gaisan_syusei/final_judge/'.$req->request_work_id.'/'.$req->task_id.'/create');

            // タスクのステータスを更新
            $task = Task::find($req->task_id);
            $task->status = ($judge_type == self::JUDGE_TYPE_HOLD) ? Task::STATUS_ON : Task::STATUS_DONE;
            $task->updated_user_id = $user->id;
            $task->save();

            // 実作業データ登録
            $task_result_data = [
                'item01' => $req->task_result['item01']
            ];

            $process_info = $req->process_info;
            $next_steps = $process_info['results']['next_step'];
            foreach ($next_steps as $key => $value) {
                $user_ids = [];
                if (!$value['step_id']) {
                    unset($next_steps[$key]);
                }
            }

            $process_info['results']['next_step'] = array_merge($next_steps);

            $task_result_data = array_merge($task_result_data, $process_info);

            $task_result = new TaskResult;
            $task_result->task_id = $req->task_id;
            $task_result->step_id = $req->step_id;
            $task_result->started_at = $req->started_at;
            $task_result->finished_at = Carbon::now();
            $task_result->content = json_encode($task_result_data, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = $user->id;
            $task_result->updated_user_id = $user->id;
            $task_result->save();

            // 業務管理者のメールアドレスを取得
            $business_id = RequestModel::where('id', $req->request_id)->pluck('business_id')->first();
            $business = Business::with('users')->where('id', $business_id)->first();
            $business_admin = $business->users;
            $business_admin_email_array = $this->extractValueBykey($business_admin, 'email');
            $business_admin_name_array = $this->extractValueBykey($business_admin, 'name');

            // 依頼メール
            $request_mail = RequestMail::where('id', $req->request_mail_id)->first();
            $request_mail_subject = isset($request_mail->subject) ? $request_mail->subject : '';

            // 送信メール本文生成に必要なデータをセット
            $mail_body_data = [
                'business_name' => $business->name,
                'step_name' => $step_name,
                'work_user_name' => $user->name,
                'sas_data' => $req->sas_data,
                'judge_type' => $judge_type,
                'judge_type_text' => self::$judge_types_text_map[$judge_type],
                'master_data' => $req->master_data,
                'request_mail' => $request_mail,
                'request_mail_subject' => $request_mail_subject,
                'request_work_code' => $req->request_work_code,
                'task_url' => $task_url,
            ];

            if ($judge_type == self::JUDGE_TYPE_WF_APPLY || $judge_type == self::JUDGE_TYPE_WF_APPLY_W_CHANGE) {
                /*
                * 「WF申請」or「WF申請(案件概要変更あり)」
                */

                // 送信メール登録
                // 件名
                $send_mail_subject = '';
                if ($judge_type == self::JUDGE_TYPE_WF_APPLY) {
                    $send_mail_subject = '【WF申請依頼】'.$request_mail_subject;
                } elseif ($judge_type == self::JUDGE_TYPE_WF_APPLY_W_CHANGE) {
                    $send_mail_subject = '【WF申請（案件概要変更あり）依頼】'.$request_mail_subject;
                }

                // 本文生成
                $mail_body = $this->makeMailBody($mail_body_data);

                $send_mail = new SendMail;
                $send_mail->request_work_id = $req->request_work_id;
                $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                $send_mail->to = config('biz.wf_gaisan_syusei.changchun_biz_email');
                $send_mail->subject = $send_mail_subject;
                $send_mail->content_type = $request_mail->content_type;
                $send_mail->body = $mail_body;
                $send_mail->created_user_id = $user->id;
                $send_mail->updated_user_id = $user->id;
                $send_mail->save();

                // 添付ファイル
                $evidence_attachment_file = RequestMailAttachment::where('id', $req->evidence_attachment_file_id)->first(['name', 'file_path']);
                $send_mail_attachment = new SendMailAttachment;
                $send_mail_attachment->send_mail_id = $send_mail->id;
                $send_mail_attachment->name = $evidence_attachment_file->name;
                $send_mail_attachment->file_path = $evidence_attachment_file->file_path;
                $send_mail_attachment->created_user_id = $user->id;
                $send_mail_attachment->updated_user_id = $user->id;
                $send_mail_attachment->save();

                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_AUTO_APPROVE;
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                // 処理キュー登録（メール送信）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_REQUEST_SEND_MAIL;
                $queue->argument = json_encode(['mail_id' => $send_mail->id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                $this->storeRequestLog(\Config::get('const.TASK_RESULT_TYPE.DONE'), $req->request_id, $req->request_work_id, $req->task_id, $user->id);
            } elseif ($judge_type == self::JUDGE_TYPE_FULL_CHARGE || $judge_type == self::JUDGE_TYPE_FULL_DIGESTION || $judge_type == self::JUDGE_TYPE_SAS_CORRECTED) {
                /*
                * 「満額消化」or「満額請求」or「SAS修正済み」
                */

                // 送信メール登録
                // 件名
                $send_mail_subject = '';
                if ($judge_type == self::JUDGE_TYPE_FULL_CHARGE) {
                    $send_mail_subject = '【'.$business->name.'＿満額請求】 '.$request_mail_subject;
                } elseif ($judge_type == self::JUDGE_TYPE_FULL_DIGESTION) {
                    $send_mail_subject = '【'.$business->name.'＿満額消化】 '.$request_mail_subject;
                } elseif ($judge_type == self::JUDGE_TYPE_SAS_CORRECTED) {
                    $send_mail_subject = '【'.$business->name.'＿SAS修正済】 '.$request_mail_subject;
                }

                // 本文生成
                $mail_body = $this->makeMailBody($mail_body_data);

                $send_mail = new SendMail;
                $send_mail->request_work_id = $req->request_work_id;
                $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                $send_mail->to = config('biz.wf_gaisan_syusei.changchun_biz_email');
                $send_mail->subject = $send_mail_subject;
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

                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_AUTO_APPROVE;
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                $this->storeRequestLog(\Config::get('const.TASK_RESULT_TYPE.DONE'), $req->request_id, $req->request_work_id, $req->task_id, $user->id);
            } elseif ($judge_type == self::JUDGE_TYPE_HOLD) {
                /*
                *  保留
                */
                // 本文生成
                $mail_body = $this->makeMailBody($mail_body_data);

                // 件名
                $send_mail_subject = '【'.$business->name.'＿'.$step_name.'＿保留処理'.'】'.$request_mail_subject;

                $send_mail = new SendMail;
                $send_mail->request_work_id = $req->request_work_id;
                $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                $send_mail->to = implode(',', $business_admin_email_array);
                $send_mail->subject = $send_mail_subject;
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
            } elseif ($judge_type == self::JUDGE_TYPE_RETURN_STEPS) {
                /*
                *  作業を戻す
                */
                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = Queue::TYPE_AUTO_APPROVE;
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = Queue::STATUS_NONE;
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

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

    public function makeMailBody($mail_body_data)
    {
        $judge_type = $mail_body_data['judge_type'];
        $mail_body = '';
        if ($judge_type == self::JUDGE_TYPE_WF_APPLY || $judge_type == self::JUDGE_TYPE_WF_APPLY_W_CHANGE) {
            $mail_body = \View::make('biz.wf_gaisan_syusei.final_judge/emails/wf_apply')
                            ->with($mail_body_data)
                            ->render();
        } elseif ($judge_type == self::JUDGE_TYPE_FULL_CHARGE || $judge_type == self::JUDGE_TYPE_FULL_DIGESTION || $judge_type == self::JUDGE_TYPE_SAS_CORRECTED) {
            $mail_body = \View::make('biz.wf_gaisan_syusei.final_judge/emails/wf_excluded')
                            ->with($mail_body_data)
                            ->render();
        } elseif ($judge_type == self::JUDGE_TYPE_HOLD) {
            $mail_body = \View::make('biz.wf_gaisan_syusei.emails/hold')
                            ->with($mail_body_data)
                            ->render();
        }

        return $mail_body;
    }

    public function getUnclearPointsCnt($data, $unclear_point_keys)
    {
        $unclear_point_cnt = 0;
        foreach ($unclear_point_keys as $unclear_point_key) {
            if ($data[$unclear_point_key]) {
                $unclear_point_cnt += 1;
            }
        }

        return $unclear_point_cnt;
    }

    public function generateTaskUrl($step_url, $request_work_id, $task_id)
    {
        return url('/biz/'.$step_url.'/'.$request_work_id.'/'.$task_id.'/create');
    }
}
