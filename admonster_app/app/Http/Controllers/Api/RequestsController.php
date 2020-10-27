<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Business;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestMail;
use App\Models\RequestFile;
use App\Models\RequestAdditionalInfo;
use App\Models\RequestAdditionalInfoAttachment;
use App\Models\RequestLog;
use App\Models\Task;
use App\Models\Queue;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Step;
use App\Models\ImportRequestRelatedMailAlias;
use App\Models\FileImportMainConfig;
use App\Models\Label;
use App\Models\SendMail;
use App\Models\SendMailAttachment;
use App\Models\GuestClient;
use Carbon\Carbon;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\Traits\RequestMailTrait;
use App\Services\Traits\RequestFileTrait;

class RequestsController extends Controller
{
    use RequestLogStoreTrait;
    use RequestMailTrait;
    use RequestFileTrait;

    public function index(Request $request)
    {
        $user = \Auth::user();

        //検索条件の取得
        $form = [
            //'request_file_name' => $request->input('request_file_name'),
            'request_file_id' => $request->input('request_file_id'),
            'business_name' => $request->input('business_name'),
            'client_name' => $request->input('client_name'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'date_type' => $request->input('date_type'),
            'request_name' => $request->input('request_name'),
            'page' => $request->input('page'),
            'sort_by' => $request->get('sort_by'),
            'descending' => $request->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $request->get('rows_per_page'),
            'status' => $request->get('status')
        ];

        $requests = RequestModel::getRelatedRequestsList($user->id, $form);
        return response()->json([
            'requests' => $requests,
        ]);
    }

    public function show(Request $req)
    {
        $user = \Auth::user();

        // 依頼ログ取得
        $request_logs = RequestLog::getList($req->request_id);

        // 件数表示バージョンデータセット用
        $pre_count_summary = Step::getWorkInProcessCountList([
            'request_id' => $req->request_id
        ]);

        $sub = RequestModel::getRequestProcesses();

        $query = RequestModel::query();
        $request = $query->select()
            ->where('id', $req->request_id)
            ->with([
                'business',
                'requestWork' => function ($query) use ($sub) {
                    $query->join(\DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id');
                    // $query->where('is_deleted', 0);
                },
                'requestWork.step',
                'requestWork.task',
                'requestWork.task.taskResults',
                'requestWork.approval',
                'requestWork.approval.approvalTask.delivery',
                'relatedMails' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'relatedMails.requestMailAttachments',
            ])
            ->first();

        $request_works = $request->requestWork;

        // step毎に仕訳
        $steps = $request_works->sortBy('step.id')->groupBy('step.id');

        /*
         * 依頼データを取得
         * 現時点で依頼テーブルには依頼データが直接紐づいていないため、暫定的に最初の依頼作業に紐づくデータから取得する。
         */
        $target_request_work = $request_works->sortBy('created_at')[0];
        $target_request_work_id = $target_request_work->id;
        // 依頼メール
        $request_mail = RequestWork::find($target_request_work_id)->requestMails()->select('id')->first();
        $request_mail_template_info = null;
        if (!is_null($request_mail)) {
            $request_mail_template_info = $this->tryCreateMailTemplateData($request_mail->id);
        }

        // 依頼ファイル
        $request_file = RequestFile::getWithPivotByRequestWorkId($target_request_work_id);
        $label_data = new \stdClass();
        if (!is_null($request_file)) {
            $file_import_configs = $this->getFileImportConfigs($target_request_work->step_id);
            $column_configs = $file_import_configs['column_configs'];
            // 依頼内容情報
            if ($request_file->content) {
                $request_file->content = $this->generateRequestFileItemData($column_configs, $request_file->content);
            }
            // ラベルデータ
            $label_ids = [];
            foreach ($column_configs as $column_config) {
                $label_ids[] = $column_config->label_id;
            }
            $label_data = Label::getLangKeySetByIds($label_ids);
        }

        $pre_step_details = [];
        $code_divided_request_works = [];
        $to_be_checked_data = [];
        $loop_num = 0;

        foreach ($steps as $step_id => $request_works) {
            // グループ化とソート
            $code_divided_request_works = $request_works->sortBy('created_at')->groupBy('code')->toArray();

            $step_name = '';
            $step_url = '';
            foreach ($code_divided_request_works as $key => $request_works) {
                foreach ($request_works as $key2 => $request_work) {
                    $import_info = [];
                    $tasks_info = [];

                    // 取込情報
                    $import_info['operator_id'] = $request_work['created_user_id'];
                    $import_info['executed_at'] = $request_work['created_at'];

                    // タスク情報
                    $tasks = $request_work['task'];
                    $allocation = [];
                    foreach ($tasks as $task) {
                        // 無効は除外 -> ではなく、作業一覧の件数に合わせたいので除外タスクも表示する
                        // if ($task['is_active'] === \Config::get('const.FLG.INACTIVE')) {
                        //     continue;
                        // }
                        // 割振情報
                        if (empty($allocation['executed_at']) || $allocation['executed_at'] < $task['created_at']) {
                            $allocation['operator_id'] = $task['created_user_id'];
                            $allocation['executed_at'] = $task['created_at'];
                        }
                        $tasks_info[] = $task;

                        // 確認事項用 => 内容は暫定的
                        if ($request_work['is_active'] && $task['task_results']) {
                            if (json_decode($task['task_results'][0]['content'])->results->type == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                                $to_be_checked_data[] = $this->makeToBeCheckedTaskInfo($request_work, $task);
                            }
                        }
                    }

                    $approval = isset($request_work['approval']) ? $request_work['approval'] : null;

                    $delivery = null;
                    if ($approval) {
                        foreach ($approval['approval_task'] as $approval_task) {
                            if ($approval_task['delivery']) {
                                $delivery = $approval_task['delivery'];
                            }
                        }
                    }

                    // ステップ情報
                    $step_url = $request_work['step']['url'];
                    $step_name = $request_work['step']['name'];

                    // 基本情報
                    $code_divided_request_works[$key][$key2]['deadline'] = $request_work['deadline'];
                    $code_divided_request_works[$key][$key2]['system_deadline'] = $request_work['system_deadline'];
                    $code_divided_request_works[$key][$key2]['process'] = $request_work['process'];
                    $code_divided_request_works[$key][$key2]['completed_process'] = $request_work['completed_process'];
                    $code_divided_request_works[$key][$key2]['status'] = $request_work['status'];

                    // プロセス情報
                    $code_divided_request_works[$key][$key2]['import_info'] = $import_info;
                    $code_divided_request_works[$key][$key2]['allocation_info'] = $allocation;
                    $code_divided_request_works[$key][$key2]['tasks_info'] = $tasks_info;
                    $code_divided_request_works[$key][$key2]['approval_info'] = $approval;
                    $code_divided_request_works[$key][$key2]['delivery_info'] = $delivery;
                }
            }

            foreach ($code_divided_request_works as $code => $code_divided_request_work) {
                $code_divided_request_works[$code] = collect($code_divided_request_work)->groupBy(function ($item, $key) {
                    if ($item['is_active'] == 1) {
                        return 'is_active';
                    } elseif ($item['is_active'] == 0) {
                        return 'is_inactive';
                    }
                });

                $code_divided_request_works[$code] = $code_divided_request_works[$code]->toArray();
            }

            $pre_step_details[$loop_num]['step_id'] = $step_id;
            $pre_step_details[$loop_num]['step_name'] = $step_name;
            $pre_step_details[$loop_num]['step_url'] = $step_url;
            $pre_step_details[$loop_num]['code_divided_request_works'] = $code_divided_request_works;

            $loop_num += 1;
        }

        // 雛形用のstep情報とマージ。stepに紐づくrequest_worksが存在しない場合も予定としてのrequest_works枠を表示。
        $step_list = Step::getListByBusinessId($request->business->id);
        $count_summary = [];
        $step_details = [];
        $codes = [];
        foreach ($step_list as $key => $step) {
            $count_summary[$key] = $step;
            $step_details[$key] = (array) $step;

            foreach ($pre_count_summary as $count) {
                if ($step->step_id === $count->step_id) {
                    $count_summary[$key] = (object) array_merge((array) $step_list[$key], (array) $count);
                }
            }

            foreach ($pre_step_details as $step_detail) {
                $codes = array_keys($step_detail['code_divided_request_works']);
                if ($step->step_id === $step_detail['step_id']) {
                    $step_details[$key] = array_merge((array) $step_list[$key], $step_detail);
                }
            }

            if (!isset($step_details[$key]['code_divided_request_works'])) {
                foreach ($codes as $key2 => $code) {
                    $empty_set = [
                        'import_info' => [],
                        'allocation_info' => [],
                        'tasks_info' => [],
                        'approval_info' => [],
                        'delivery_info' => [],
                    ];
                    $step_details[$key]['code_divided_request_works'][$code]['is_active'][] = $empty_set;
                }
            }
        }

        $base = $count_summary[0];
        // 【依頼】を先頭に追加
        array_unshift($count_summary, self::requestAll($base));
        // 【完了】を末尾に追加
        array_push($count_summary, self::requestCompleted($base));

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::get();

        $base_info = [
            'business_name' => $request->business->name,
            'deadline' => $request->deadline,
            'system_deadline' => $request->system_deadline,
            'status' => $request->status,
            'is_deleted' => $request->is_deleted,
        ];

        foreach ($request->relatedMails as &$related_mail) {
            $related_mail->original_body = $related_mail->body;
            if ($related_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                $related_mail->body = strip_tags($related_mail->body, '<br>');
            } else {
                $related_mail->body = nl2br(e($related_mail->body));
            }
        }

        // 依頼全体と作業単位の進捗パーセンテージを計測
        $process_cnt = 5;  // 取込、割振、タスク、承認、納品
        $rw_cnt = 0; // activeなrequest_worksの数（依頼単位）
        $competed_processes = [];
        foreach ($step_details as $key => $step_detail) {
            $rw_cnt_in_step = 0; // activeなrequest_worksの数（step単位）
            $competed_processes_in_step = [];
            foreach ($step_detail['code_divided_request_works'] as $request_work) {
                if (isset($request_work['is_active']) && $request_work['is_active']) {
                    $rw_cnt += count($request_work['is_active']);
                    $rw_cnt_in_step += count($request_work['is_active']);
                    foreach ($request_work['is_active'] as $active_rw) {
                        if (isset($active_rw['process'])) {
                            $competed_processes[] = $active_rw['completed_process'];
                            $competed_processes_in_step[] = $active_rw['completed_process'];
                        } else {
                            $competed_processes[] = 0;  // 発生予定のrequest_work分
                            $competed_processes_in_step[] = 0;
                        }
                    }
                }
            }
            $total_process_cnt_in_step = $rw_cnt_in_step * $process_cnt;
            $completed_percentage_in_step = ($total_process_cnt_in_step > 0) ? round(array_sum($competed_processes_in_step) / $total_process_cnt_in_step * 100) : 0;
            $step_details[$key]['completed_percentage'] = $completed_percentage_in_step;
        }
        $total_process_cnt = $rw_cnt * $process_cnt;
        $completed_percentage = ($total_process_cnt > 0) ? round(array_sum($competed_processes) / $total_process_cnt * 100) : 0;

        return response()->json([
            'base_info' => $base_info,
            'business_name' => $request->business->name,
            'request_mail' => $request_mail_template_info,
            'request_file' => $request_file,
            'to_be_checked_data' => $to_be_checked_data,
            'status_summary' => [],
            'step_details' => $step_details,
            'count_summary' => $count_summary,
            'candidates' => $candidates,
            'request_logs' => $request_logs,
            'related_mails' => $request->relatedMails,
            'label_data' => $label_data,
            'completed_percentage' => $completed_percentage,
        ]);
    }

    public function update(Request $req)
    {
        $user = \Auth::user();

        $request_id = $req->request_id;
        // DB登録
        \DB::beginTransaction();
        try {
            $request = RequestModel::find($request_id);
            $request->deadline = $req->deadline;
            $request->save();

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_id' => $request_id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_id' => $request_id,
            ]);
        }
    }

    public function delete(Request $req)
    {
        $user = \Auth::user();

        // 排他チェック
        // ほかのユーザにより更新されているデータを取得 => あればエラー
        $updated_request = RequestModel::where('id', $req->request_id)
            ->where('updated_at', '>=', $req->started_at)
            ->count();

        if ($updated_request) {
            return response()->json([
                'result' => 'warning',
                'request_id' => $req->request_id,
            ]);
        }

        // DB登録
        \DB::beginTransaction();
        try {
            // 関連する「依頼」を除外（ステータスを「３：除外」に更新）
            // 関連する「依頼作業」を無効化（有効フラグを「0：無効」に更新）
            // 「0：未対応」または「1：保留」の「タスク」を無効化（有効フラグを「0：無効」に更新）
            $request = RequestModel::find($req->request_id);
            $request->status = \Config::get('const.REQUEST_STATUS.EXCEPT');
            $request->updated_at = Carbon::now();
            $request->updated_user_id = $user->id;
            $request->save();

            $request_works = RequestWork::where('request_id', $req->request_id)->get();
            foreach ($request_works as $request_work) {
                $request_work->is_active = \Config::get('const.ACTIVE_FLG.INACTIVE');
                $request_work->updated_at = Carbon::now();
                $request_work->updated_user_id = $user->id;
                $request_work->save();

                $request_work->load('task');
                $tasks = $request_work->task;
                foreach ($tasks as $task) {
                    if ($task->status == \Config::get('const.TASK_STATUS.NONE') || $task->status == \Config::get('const.TASK_STATUS.ON')) {
                        $task->is_active = \Config::get('const.FLG.INACTIVE');
                        $task->updated_at = Carbon::now();
                        $task->updated_user_id = $user->id;
                        $task->save();
                    }
                }
            }

            // ログ登録
            $request_log_attributes = [
                'request_id' => $req->request_id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.REQUEST_EXCEPTED'),
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_id' => $request->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    public function replicateRequest(Request $req)
    {
        $user = \Auth::user();
        $current_time  = Carbon::now();

        $request_id = $req->request_id;

        // DB登録
        \DB::beginTransaction();
        try {
            // 元の依頼データをコピー
            $original_request = RequestModel::find($request_id);
            $new_request = $original_request->replicate();

            // 変更が必要な各カラムに以下の内容で値をセット
            $new_request->master_request_id = $request_id;
            $new_request->status = \Config::get('const.REQUEST_STATUS.DOING');
            $new_request->is_deleted = \Config::get('const.DELETE_FLG.ACTIVE');
            $new_request->created_user_id = $user->id;
            $new_request->updated_user_id = $user->id;
            $new_request->save();

            // 元の依頼の最初に作成された依頼作業(request_works)データをコピー
            $original_request_work = RequestWork::with(['requestMails', 'requestFiles'])
                ->where('request_id', $request_id)
                ->oldest()
                ->first();

            $new_request_work = $original_request_work->replicate();

            $new_request_work->request_id = $new_request->id;
            $new_request_work->before_work_id = null;
            //$new_request_work->group_id = null; TODO 格納する値の検討 一旦はコピー元のままとする
            $new_request_work->is_active = \Config::get('const.ACTIVE_FLG.ACTIVE');
            $new_request_work->is_deleted = \Config::get('const.DELETE_FLG.ACTIVE');
            $new_request_work->created_user_id = $user->id;
            $new_request_work->updated_user_id = $user->id;
            $new_request_work->save();

            // 取込の依頼ログを登録
            $request_log_attributes = [
                'request_id' => $new_request->id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED'),
                'request_work_id' => $new_request_work->id,
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            // 元の依頼の取込データを紐付け
            if ($original_request_work->requestMails) {
                foreach ($original_request_work->requestMails as $request_mail) {
                    $request_mail->requestWorks()
                        ->attach($new_request_work->id, ['created_user_id' => $user->id, 'updated_user_id' => $user->id]);
                }
            }

            if ($original_request_work->requestFiles) {
                foreach ($original_request_work->requestFiles as $request_file) {
                    $request_file->requestWorks()
                        ->attach($new_request_work->id, [
                            'content' => $request_file->pivot->content,
                            'row_no' => $request_file->pivot->row_no,
                            'created_user_id' => $user->id,
                            'updated_user_id' => $user->id
                        ]);
                }
            }

            // 元の依頼の関連メール(request_related_mails)を紐付け
            if ($req->related_mail_replication_flag === true) {
                $related_mails = \DB::table('request_related_mails')
                                    ->select(
                                        'request_related_mails.request_mail_id as related_mail_id',
                                        'request_related_mails.created_at as created_at',
                                        'request_related_mails.created_user_id as created_user_id',
                                        'request_related_mails.updated_at as updated_at'
                                    )
                                    ->where('request_related_mails.request_id', $request_id)
                                    ->get();

                $insert_related_mails = array();

                foreach ($related_mails as $related_mail) {
                    // created_at, created_user_id, updated_atはコピー元と同じように表示したいため変更しない
                    // updated_user_idは編集権限を付与したい関係でコピーした人に変更する
                    $insert_related_mails += array($related_mail->related_mail_id=>[
                        'created_at' => $related_mail->created_at,
                        'created_user_id' => $related_mail->created_user_id,
                        'updated_at' => $related_mail->updated_at,
                        'updated_user_id' => $user->id
                    ]);
                }

                $new_request->relatedMails()->attach($insert_related_mails);
            }

            // 元の依頼の補足情報(request_additional_infos)データをコピー
            if ($req->additional_info_replication_flag === true) {
                $additional_infos = RequestAdditionalInfo::with('requestAdditionalInfoAttachments')
                                    ->where('request_additional_infos.request_id', $request_id)
                                    ->where('is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                                    ->get();

                $insert_additional_info_attachments = array();

                foreach ($additional_infos as $additional_info) {
                    // created_at, created_user_id, updated_atはコピー元と同じように表示したいため変更しない
                    // updated_user_idは編集権限を付与したい関係でコピーした人に変更する
                    // created_atとupdated_atの自動更新を防ぐため、insertを使用。
                    DB::table('request_additional_infos')->insert([
                        'request_id' => $new_request->id,
                        'content' => $additional_info->content,
                        'created_at' => $additional_info->created_at,
                        'created_user_id' => $additional_info->created_user_id,
                        'updated_at' => $additional_info->updated_at,
                        'updated_user_id' => $user->id
                    ]);

                    $request_additional_info_attachments = $additional_info->requestAdditionalInfoAttachments;

                    // 添付ファイルが無ければ次のループへ
                    if (!$request_additional_info_attachments) {
                        continue;
                    }

                    $insert_additional_info_id = DB::getPdo()->lastInsertId();

                    foreach ($request_additional_info_attachments as $relevant_additional_info_attachment) {
                        // created_at, created_user_id, updated_atはコピー元と同じように表示したいため変更しない
                        // updated_user_idは編集権限を付与したい関係でコピーした人に変更する
                        array_push($insert_additional_info_attachments, [
                            'request_additional_info_id' => $insert_additional_info_id,
                            'name' => $relevant_additional_info_attachment->name,
                            'file_path' => $relevant_additional_info_attachment->file_path,
                            'created_at' => $relevant_additional_info_attachment->created_at,
                            'created_user_id' => $relevant_additional_info_attachment->created_user_id,
                            'updated_at' => $relevant_additional_info_attachment->updated_at,
                            'updated_user_id' => $user->id
                        ]);
                    }
                }
                RequestAdditionalInfoAttachment::insert($insert_additional_info_attachments);
            }

            //コピー先依頼詳細の補足情報(request_additional_infos)を登録
            $request_additional_info = new RequestAdditionalInfo;
            $request_additional_info->request_id = $new_request->id;
            $request_additional_info->content = $req->prefix.url('/management/requests/'.$request_id);
            $request_additional_info->created_user_id = \Config::get('const.SYSTEM_USER_ID');
            $request_additional_info->updated_user_id = \Config::get('const.SYSTEM_USER_ID');
            $request_additional_info->save();

            // 割振キューを登録
            $queue = new Queue;
            $queue->process_type = \Config::get('const.QUEUE_TYPE.ALLOCATE');
            $queue->argument = json_encode(['request_work_id' => $new_request_work->id, 'operators' => []]);
            $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = $user->id;
            $queue->updated_user_id = $user->id;
            $queue->save();

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_id' => $request_id,
                'url' => url('/management/requests/'.$new_request->id)
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_id' => $request_id,
            ]);
        }
    }

    // 関連メール取込用のエイリアスを生成
    public function aliasMailAddress(Request $req)
    {
        $user = \Auth::user();

        // エイリアスの元となるアカウントは、import_mail_accounts の使用中のアカウントを暫定的にハードコーディングで保持して使用している。
        $base_account = \Config::get('admin.request.main_account_of_import_mail');
        $position = strpos($base_account, '@');
        if (!$position) {
            return response()->json([
                'error' => 'エラーが発生しました。',
            ]);
        }
        $account_name = substr($base_account, 0, $position);
        $domain = strstr($base_account, '@');

        // 依頼詳細と作業詳細とでエイリアスを出し分ける
        $target_str = $req->request_work_id ? 'requestwork' : 'request';
        $target_id = $req->request_work_id ? $req->request_work_id : $req->request_id;
        // 念のため重複アカウント生成回避
        $unique = false;
        do {
            /*
             * [エイリアスのフォーマット]
             *
             * ■依頼詳細用
             * __取込用アカウントの@以前__+request-___ランダム文字列___-__依頼ID__@__取込用アカウントの@以降__

            * ■作業詳細用
             * __取込用アカウントの@以前__+requestwork-___ランダム文字列___-__依頼作業ID__@__取込用アカウントの@以降__
            */
            $alias = $account_name.'+'.$target_str.'-'.Str::random(8).'-'.$target_id.$domain;
            $exist_cnt = ImportRequestRelatedMailAlias::where('alias', $alias)->count();
            if ($exist_cnt < 1) {
                $unique = true;
                break;
            }
        } while ($unique = true);

        \DB::beginTransaction();
        try {
            $imoprt_related_mail_account_alias = new ImportRequestRelatedMailAlias;
            $imoprt_related_mail_account_alias->request_id = $req->request_id;
            $imoprt_related_mail_account_alias->request_work_id = $req->request_work_id;
            $imoprt_related_mail_account_alias->alias = $alias;
            $imoprt_related_mail_account_alias->is_open_to_client = $req->is_open_to_client;
            $imoprt_related_mail_account_alias->is_open_to_work = $req->is_open_to_work;
            $imoprt_related_mail_account_alias->from = $req->from;
            $imoprt_related_mail_account_alias->created_user_id = $user->id;
            $imoprt_related_mail_account_alias->save();

            \DB::commit();

            return response()->json([
                'alias' => $alias,
                'is_open_to_client' => $imoprt_related_mail_account_alias->is_open_to_client,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'error' => 'エラーが発生しました。',
            ]);
        }
    }

    // 関連メール取得
    public function relatedMailList(Request $req)
    {
        $request = RequestModel::find($req->request_id);

        if ($req->request_work_id) {
            // 作業詳細用
            $request_work = RequestWork::find($req->request_work_id);
            $related_mails = $request->relatedMails()->orderBy('created_at', 'desc')->get();
        } else {
            // 依頼詳細用
            $request = RequestModel::find($req->request_id);
            $related_mails = $request->relatedMails()->orderBy('created_at', 'desc')->get();
        }
        $related_mails->load('requestMailAttachments');

        foreach ($related_mails as &$related_mail) {
            $related_mail->original_body = $related_mail->body;
            if ($related_mail->mail_content_type === RequestMail::CONTENT_TYPE_HTML) {
                $related_mail->body = strip_tags($related_mail->body, '<br>');
            } else {
                $related_mail->body = nl2br(e($related_mail->body));
            }
        }

        return response()->json([
            'related_mails' => $related_mails,
        ]);
    }

    // 関連メール更新
    public function updateRelatedMail(Request $req)
    {
        if (!$req->request_mail_id) {
            return response()->json([
                'result' => 'error',
            ]);
        }

        $request = RequestModel::find($req->request_id);

        \DB::beginTransaction();
        try {
            if ($req->request_work_id) {
                // 作業詳細用
                $request_work = RequestWork::find($req->request_work_id);
                $request_work->requestWorkRelatedMails()->updateExistingPivot($req->request_mail_id, ['is_open_to_client' => $req->is_open_to_client, 'is_open_to_work' => $req->is_open_to_work]);
            } else {
                // 依頼詳細用
                $request = RequestModel::find($req->request_id);
                $request->relatedMails()->updateExistingPivot($req->request_mail_id, ['is_open_to_client' => $req->is_open_to_client, 'is_open_to_work' => $req->is_open_to_work]);
            }

            \DB::commit();

            return response()->json([
                'result' => 'success',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    // 関連メール削除
    public function deletRelatedMail(Request $req)
    {
        if (!$req->request_mail_id) {
            return response()->json([
                'result' => 'error',
            ]);
        }

        \DB::beginTransaction();
        try {
            if ($req->request_work_id) {
                // 作業詳細用
                $request_work = RequestWork::find($req->request_work_id);
                $request_work->requestWorkRelatedMails()->detach($req->request_mail_id);
            } else {
                // 依頼詳細用
                $request = RequestModel::find($req->request_id);
                $request->relatedMails()->detach($req->request_mail_id);
            }

            \DB::commit();

            return response()->json([
                'result' => 'success',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    public function getGuestClientAccountIssueStatus(Request $req)
    {
        $request_id = $req->request_id;

        // 発行済みゲストクライアント
        $issued_guest_clients = GuestClient::where('request_id', $request_id)->get();

        $target_guest_clients = [];
        // 依頼がメールの場合
        if ($req->request_mail_id) {
            $target_guest_clients = array_merge($target_guest_clients, $this->getGuestTargetsFromRequestMail($req->request_mail_id));
        }

        return response()->json([
            'target_guest_clients' => $target_guest_clients,
            'issued_guest_clients' => $issued_guest_clients,
        ]);
    }

    // ゲストクライアントアカウント発行
    public function issueGuestClientAccount(Request $req)
    {
        $user = \Auth::user();

        $targets = $req->targets;
        $request_id = $req->request_id;
        $business_name = Business::getNameByRequestId($request_id);

        // 依頼メールの場合
        $request_mail_id = $req->request_mail_id;
        $request_mail = RequestMail::with('requestMailAttachments')->where('id', $request_mail_id)->first();

        \DB::beginTransaction();
        try {
            foreach ($targets as $target) {
                // 念のため重複トークン生成回避
                $unique = false;
                $num = 1;
                do {
                    $token = Hash::make($target['email'].$request_id);
                    $exist_cnt = GuestClient::where('token', $token)->count();
                    if ($exist_cnt < 1) {
                        $unique = true;
                        break;
                    }
                    // 10回失敗したらループから抜けてエラーを返す
                    if ($num > 10) {
                        throw new \Exception('Failed to generate guest_clients.token '.$num.' times.');
                    }
                    $num += 1;
                } while ($unique = true);

                $guest_client = new GuestClient;
                $guest_client->email = $target['email'];
                $guest_client->email_notation = $target['email_notation'];
                $guest_client->type = $target['type'];
                $guest_client->request_id = $request_id;
                $guest_client->token = $token;
                $guest_client->created_at = Carbon::now();
                $guest_client->created_user_id = $user->id;
                $guest_client->updated_at = Carbon::now();
                $guest_client->updated_user_id = $user->id;
                $guest_client->save();

                // 送信メール内容用データ
                $url = \Config::get('app.url').'/guest_client/issue_password?token='.$token.'&request_id='.$request_id;
                if ($request_mail) {
                    if ($request_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                        $request_mail->body = strip_tags($request_mail->body, '<br>');
                    } else {
                        $request_mail->body = nl2br(e($request_mail->body));
                    }
                }
                $mail_body_data = [
                    'send_to_email' => $target['email'],
                    'request_id' => $request_id,
                    'url' => $url,
                    'request_mail' => $request_mail,
                ];

                $subject = '【' . \Config::get('app.name') . '】'.$business_name.'：進捗状況URL送付（依頼ID：'.$request_id.'）';
                $subject .= $request_mail->subject ? ' '.$request_mail->subject : '';
                $send_mail = new SendMail;
                $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                $send_mail->to = $target['email'];
                $send_mail->subject = $subject;
                $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
                $send_mail->body = \View::make('requests.emails/issue_guest_client_account')->with($mail_body_data)->render();
                $send_mail->created_user_id = $user->id;
                $send_mail->updated_user_id = $user->id;
                $send_mail->save();

                // 依頼メールの添付ファイル
                $attachments = $request_mail->requestMailAttachments;
                if (count($attachments) > 0) {
                    foreach ($attachments as $attachment) {
                        $send_mail_attachment = new SendMailAttachment;
                        $send_mail_attachment->send_mail_id = $send_mail->id;
                        $send_mail_attachment->name = $attachment->name;
                        $send_mail_attachment->file_path = $attachment->file_path;
                        $send_mail_attachment->created_at = Carbon::now();
                        $send_mail_attachment->created_user_id = $user->id;
                        $send_mail_attachment->updated_at = Carbon::now();
                        $send_mail_attachment->updated_user_id = $user->id;
                        $send_mail_attachment->save();
                    }
                }

                // 処理キュー登録（承認）
                $queue = new Queue;
                $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
                $queue->argument = json_encode(['mail_id' => $send_mail->id]);
                $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();
            }

            \DB::commit();

            return response()->json([
                'result' => 'success',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    protected function getStatusUpdatedAt($process, $request_work)
    {
        $updated_at = '';
        switch ($process) {
            case \Config::get('const.PROCESS_TYPE.ALLOCATION'):
                // 未割振
                $updated_at = $request_work['created_at'];
                break;
            case \Config::get('const.PROCESS_TYPE.WORK'):
                // 割振済み、タスク未完了
                $updated_at = Task::where('request_work_id', $request_work['id'])->min('created_at');
                break;
            case \Config::get('const.PROCESS_TYPE.APPROVAL'):
                // 承認待ち
                $updated_at = Task::where('request_work_id', $request_work['id'])->max('updated_at');
                break;
            case \Config::get('const.PROCESS_TYPE.DELIVERY'):
                // 納品待ち
                $delivery = Delivery::getByRequestWorkId($request_work['id']);
                $updated_at = date('Y-m-d H:i:s', strtotime($delivery->created_at));
                break;
        }

        return $updated_at;
    }

    protected function makeToBeCheckedTaskInfo($request_work, $task)
    {
        $to_be_checked_task = [
            'step_name' => $request_work['step']['name'],
            'request_work_code' => $request_work['code'],
            'task_url' => '/biz/'.$request_work['step']['url'].'/'.$request_work['id'].'/'.$task['id'].'/create',
            'type' => json_decode($task['task_results'][0]['content'])->results->type,
            'operator_id' => $task['user_id'],
            'updated_at' => $task['task_results'][0]['updated_at'],
        ];

        return $to_be_checked_task;
    }

    protected function getGuestTargetsFromRequestMail($request_mail_id)
    {
        $targets = [];
        $request_mail = RequestMail::select('from', 'cc', 'bcc')->where('id', $request_mail_id)->first();

        if ($request_mail->from) {
            $from = $request_mail->from;
            $targets[] = $this->getGuestTarget($from, \Config::get('const.CLIENT_ISSUE_TARGET_TYPE.MAIL_FROM'));
        }
        if ($request_mail->cc) {
            $cc_arr = explode(',', $request_mail->cc);
            foreach ($cc_arr as $cc) {
                $targets[] = $this->getGuestTarget($cc, \Config::get('const.CLIENT_ISSUE_TARGET_TYPE.MAIL_CC'));
            }
        }
        if ($request_mail->bcc) {
            $bcc_arr = explode(',', $request_mail->bcc);
            foreach ($bcc_arr as $bcc) {
                $targets[] = $this->getGuestTarget($bcc, \Config::get('const.CLIENT_ISSUE_TARGET_TYPE.MAIL_BCC'));
            }
        }

        return $targets;
    }

    private function getGuestTarget($mail_holder, $type)
    {
        $email_notation = $mail_holder;
        if (preg_match("/[A-Za-z0-9\-\.\_]+@[A-Za-z0-9\-\_]+\.[A-Za-z0-9\-\.\_]+/", $mail_holder, $address)) {
            $email_notation = $address[0];
        }
        $target = [
            'type' => $type,
            'email' => $email_notation,
            'email_notation' => $mail_holder,
        ];

        return $target;
    }

    private function requestAll($base)
    {
        return array(
            'step_name' => '【依頼】',
            'request_count' => $base->request_all_count,
            'all_excluded_count' => $base->request_excluded_count,
        );
    }

    private function requestCompleted($base)
    {
        return array(
            'step_name' => '【完了】',
            'all_completed_count'  => $base->request_completed_count,
        );
    }

    public function changeDeliveryDeadline(Request $req)
    {
        \DB::beginTransaction();
        try {
            $deadline = $req->input('deadline');
            $id = $req->input('id');

            // 排他チェック-------------------------------------------------------
            // ほかのユーザにより更新されているデータを取得

            $requests = \DB::table('requests')
                ->where('id', $id)
                ->where(function ($query) {
                    $query->where('requests.status', '=', \Config::get('const.REQUEST_STATUS.FINISH'))
                    ->orWhere('requests.status', '=', \Config::get('const.REQUEST_STATUS.EXCEPT'));
                })
                ->count();

            if ($requests >= 1) {
                return response()->json([
                    'status' => 400
                ]);
            }
            // 排他チェック-------------------------------------------------------

            \DB::table('requests')
                ->where('id', $id)
                ->update(['deadline' => new Carbon($deadline)]);

            \DB::commit();

            return response()->json([
                'status' => 200
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * [POST]依頼メールの詳細を取得する
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRequestMailDetail(Request $request): JsonResponse
    {
        // 依頼メールID
        $requestMailId = $request->request_mail_id;
        // 依頼メールテンプレート情報を取得
        $requestMail = $this->tryCreateMailTemplateData($requestMailId);
        return response()->json([
            'request_mail' => $requestMail,
        ]);
    }
}
