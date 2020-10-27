<?php

namespace App\Http\Controllers\Api\Biz\Development;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\DownloadFileManager\Downloader;
use App\Services\Traits\RequestLogStoreTrait;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestMail;
use App\Services\Traits\RequestMailTrait;
use App\Models\RequestMailAttachment;
use App\Models\Queue;
use App\Models\TaskResult;
use App\Models\Task;

class ImprovementController extends Controller
{
    use RequestLogStoreTrait;
    use RequestMailTrait;

    public function create(Request $req)
    {
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
        $is_on_task = ($task_status == \Config::get('const.TASK_STATUS.ON')) ? true : false;
        $is_inactive_task = ($task->is_active == \Config::get('const.FLG.INACTIVE')) ? true : false;
        $message = $task->message;

        // TODO クエリ減らせそう
        $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();
        $request_mail_id = $request_work->requestMails->first()->id;

        $request_mail_template_info = $this->tryCreateMailTemplateData($request_mail_id);

        $started_at = Carbon::now();

        $task_result = TaskResult::where('task_id', $task_id)
            ->where('step_id', $request_work->step_id)
            ->orderBy('updated_at', 'desc')
            ->first();
        $task_result_content = !is_null($task_result) ? json_decode($task_result->content) : '';

        return response()->json([
            'request_id' => $request_work->request_id,
            'request_work_id' => $req->request_work_id,
            'request_mail_id' => $request_mail_id,
            'request_mail' => $request_mail_template_info,
            'task_id' => $task_id,
            'step_id' => $request_work->step_id,
            'started_at' => $started_at,
            'task_result_content' => $task_result_content,
            'is_done_task' => $is_done_task,
            'is_on_task' => $is_on_task,
            'is_inactive_task' => $is_inactive_task,
            'message' => $message,
        ]);
    }

    // 登録
    public function store(Request $req)
    {
        $user = \Auth::user();

        // DB登録
        \DB::beginTransaction();

        try {
            // 排他チェック
            $inactivated = Task::checkIsInactivatedById($req->task_id);
            if ($inactivated) {
                return response()->json([
                    'result' => 'inactivated',
                    'request_work_id' => $req->request_work_id,
                    'task_id' => $req->task_id,
                ]);
            }

            if (!$req->is_on_task) {
                /*
                *  未対応→対応中（保留）へ更新
                */

                // タスク実績
                $task_result_data = [];
                $task_result_data = array_merge(
                    $task_result_data,
                    [
                        "results" => [
                            // TODO 値の検討(一旦、保留と同様の扱いとする)
                            "type" => config('const.TASK_RESULT_TYPE.HOLD'),
                        ],
                        "development_started_at" => Carbon::now(),
                    ]
                );

                $task_result = new TaskResult;
                $task_result->task_id = $req->task_id;
                $task_result->step_id = $req->step_id;
                $task_result->started_at = $req->started_at['date'];
                $task_result->finished_at = Carbon::now();
                $task_result->content = json_encode($task_result_data, JSON_UNESCAPED_UNICODE);
                $task_result->created_user_id = $user->id;
                $task_result->updated_user_id = $user->id;
                $task_result->save();

                // タスクのステータスを処理中(保留)に更新
                $task = Task::find($req->task_id);
                $task->status = config('const.TASK_STATUS.ON');
                $task->updated_user_id = $user->id;
                $task->save();

                // ログ登録
                // TODO 保留の場合、ログは不必要らしいが、対応中の場合は必要？
                // $request_log_attributes = [
                //     'request_id' => $req->request_id,
                //     'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_HOLDED_WITH_SOME_REASON'),
                //     'request_work_id' => $req->request_work_id,
                //     'task_id' => $req->task_id,
                //     'created_user_id' => $user->id,
                //     'updated_user_id' => $user->id,
                // ];
                // $this->storeRequestLog($request_log_attributes);
            } else {
                /*
                *  対応中（保留）→完了へ更新
                */

                // タスク実績
                $task_result_data = [];
                $task_result_data = array_merge(
                    $task_result_data,
                    [
                        "results" => [
                            "type" => config('const.TASK_RESULT_TYPE.DONE'),
                        ],
                        "development_started_at" => $req->task_result_content['development_started_at'],
                        "development_finished_at" => Carbon::now(),
                    ]
                );

                $task_result = new TaskResult;
                $task_result->task_id = $req->task_id;
                $task_result->step_id = $req->step_id;
                $task_result->started_at = $req->started_at['date'];
                $task_result->finished_at = Carbon::now();
                $task_result->content = json_encode($task_result_data, JSON_UNESCAPED_UNICODE);
                $task_result->created_user_id = $user->id;
                $task_result->updated_user_id = $user->id;
                $task_result->save();

                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = config('const.TASK_STATUS.DONE');
                $task->updated_user_id = $user->id;
                $task->save();

                // 処理キュー登録（承認）
                $queue = new Queue;
                $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                // ログ登録
                $request_log_attributes = [
                    'request_id' => $req->request_id,
                    'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
                    'request_work_id' => $req->request_work_id,
                    'task_id' => $req->task_id,
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id,
                ];
                $this->storeRequestLog($request_log_attributes);
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
}
