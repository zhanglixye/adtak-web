<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ExclusiveException;
use App\Http\Controllers\Controller;
use App\Services\Traits\RequestFileTrait;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestFile;
use App\Services\Traits\RequestMailTrait;
use App\Models\Approval;
use App\Models\Task;
use App\Models\Label;
use App\Models\ItemConfig;
use App\Models\TaskComment;
use App\Models\TaskResultFile;
use Carbon\Carbon;
use DB;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;

class VerificationController extends Controller
{
    use RequestFileTrait;
    use RequestMailTrait;

    public function edit(Request $req)
    {
        $user = \Auth::user();

        $request_work_id = $req->input('request_work_id');
        $task_id = $req->input('task_id');
        try {
            $task = Task::findOrFail($task_id);// 指定したタスクを取得

            // タスクに対応する管理者か確認
            $count = RequestWork::join('requests', 'request_works.request_id', '=', 'requests.id')
                ->join('businesses', 'requests.business_id', '=', 'businesses.id')
                ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
                ->where('request_works.id', $request_work_id)
                ->where('businesses_admin.user_id', $user->id)
                ->count();

            // 担当のタスクでもなくタスクに関連するビジネスの管理者でもない
            if ($task->user_id !== $user->id && $count === 0) {
                throw new \Exception('invalid access user id : ' . $user->id);
            }

            $task_comment = $task->taskComment;
            if (is_null($task_comment)) {// DB未登録
                $task_comment = new TaskComment;

                $task_result = $task->taskResults()->latest('id')->first();// 最新のtask_result情報を取得
                if (is_null($task_result)) {
                    throw new \Exception('No task result data. task id : ' . $task->id);
                }

                $task_comment->fill(
                    [
                        'content' => json_decode($task_result->content),
                        'global_comment' => ''
                    ]
                );
            } else {// DB登録済み
                $task_comment->content = json_decode($task_comment->content);
            }

            // ------------------------------
            // 依頼情報をセット
            $approval = Approval::where('request_work_id', $request_work_id)->first();
            $sql = DB::table('approvals')
                ->select(
                    DB::raw($approval->status . ' as process'),
                    'businesses.name as business_name',
                    'requests.id as id',
                    'requests.status as request_status',
                    'requests.is_deleted as request_is_deleted',
                    'request_works.id as request_work_id',
                    'request_works.is_active as request_work_is_active',
                    'steps.id as step_id',
                    'steps.url as step_url',
                    'tasks.user_id as approved_user_id',
                    'request_mails.id as mail_id'
                )
                ->join('request_works', 'approvals.request_work_id', '=', 'request_works.id')
                ->join('requests', 'request_works.request_id', '=', 'requests.id')
                ->join('businesses', 'requests.business_id', '=', 'businesses.id')
                ->join('steps', 'request_works.step_id', '=', 'steps.id')
                ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
                ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
                ->leftJoin('approval_tasks as delivered_approval_tasks', 'delivered_approval_tasks.id', '=', 'deliveries.approval_task_id')
                ->leftJoin('tasks', 'delivered_approval_tasks.task_id', '=', 'tasks.id')
                ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
                ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
                ->where('request_works.id', $request_work_id);

            if ($approval->status == \Config::get('const.APPROVAL_STATUS.DONE')) {
                // 承認済み
                $sql->whereNotNull('tasks.user_id');
            } else {
                // 未承認
                $sql->whereNull('tasks.user_id');
            }
            $request = $sql->first();

            // 依頼メール
            if (!is_null($request->mail_id)) {
                // メール情報の取得
                $request_mail_template_info = $this->tryCreateMailTemplateData($request->mail_id);
                $request->request_mail = $request_mail_template_info;
            }

            // 依頼ファイル
            $request_file = RequestFile::getWithPivotByRequestWorkId($request_work_id);
            $label_data = new \stdClass();
            if (!is_null($request_file)) {
                $file_import_configs = $this->getFileImportConfigs($request->step_id);
                $column_configs = $file_import_configs['column_configs'];
                // 依頼内容情報
                if ($request_file->content) {
                    $request_file->content = $this->generateRequestFileItemData($column_configs, $request_file->content);
                }
                $request->request_file = $request_file;
            }

            // 2019/10/31 ラベル情報はすべて渡して、画面内で操作する方が良いがデータ量などを考慮した対応が必要
            // Commit ID:d1439d08e3f972e558af85578db4bf8365b9837d
            // ラベル情報の全件取得
            $label_data = Label::getLangKeySetAll();

            // ------------------------------
            // 該当作業の項目を取得する

            // 指定したstep_idで重複したsort番号は除外（ID番号が高い方を優先）
            $ids = ItemConfig::select(DB::raw('MAX(id) as id'))
                ->where('step_id', $request->step_id)
                ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))// 削除していない
                ->groupBy('sort')
                ->pluck('id');

            $items = ItemConfig::select(
                'id',
                'item_key',
                'item_type',
                'label_id',
                'option',
                'diff_check_level'
            )
                ->whereIn('id', $ids)
                ->orderBy('sort', 'asc')
                ->get();

            $tmp = array();
            $item_configs = array();
            foreach ($items as $item) {
                $item_keys = explode('/', $item->item_key);
                $item->group = $item_keys[0];
                $item->key = end($item_keys);
                $item->option = is_null($item->option) ? new \stdClass() : json_decode($item->option);
                $item->item_config_values = $item->itemConfigValues()
                    ->select('label_id', 'sort')
                    ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                    ->get();
                $tmp[$item_keys[0]][] = $item;
            }

            foreach ($tmp as $obj) {
                $item_configs[] = $obj;
            }

            // ------------------------------
            // 実作業内容を取得
            $sub = \DB::table('task_results')
                ->select(\DB::raw('max(task_results.id) as task_result_id'))
                ->groupBy('task_results.task_id');

            $query = \DB::table('tasks')
                ->select(
                    'tasks.is_active as is_active',
                    'tasks.status as status',
                    'task_results.id as task_result_id',
                    'tasks.id as task_id',
                    'task_results.step_id as step_id',
                    'task_results.finished_at as finished_at',
                    'task_results.content as content',
                    'tasks.request_work_id as request_work_id',
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.user_image_path as user_image_path'
                )
                ->join('users', 'tasks.user_id', '=', 'users.id')
                ->leftJoin('task_results', 'tasks.id', '=', 'task_results.task_id')
                ->where('tasks.request_work_id', $request_work_id)
                // 最新のタスクの結果と未実施のタスクを取得するための条件
                ->where(function ($query) use ($sub) {
                    $query->whereIn('task_results.id', $sub->pluck('task_result_id'))
                        ->orWhereNull('task_results.id');
                })
                ->orderBy('task_results.finished_at', 'desc');

            $task_results = $query->get();

            // ---C00800用の処理
            $items = [];
            foreach ($item_configs as $array) {
                foreach ($array as $value) {
                    if ($value->item_type === 800) {
                        array_push($items, $value);
                    }
                }
            }

            // 書き換え
            foreach ($items as $item) {
                foreach ($task_results as $task_result) {
                    if (is_null($task_result->content)) {
                        continue;
                    }
                    $content = json_decode($task_result->content, true); // 連想配列で取り出す

                    // 不要なキーを削除
                    if (array_key_exists('values', $content)) {
                        $content_values = $content['values'];
                        unset($content['values']);
                        $content = array_merge($content, $content_values);
                    }
                    if (array_key_exists('components', $content)) {
                        unset($content['components']);
                    }

                    if (strcmp($item->group, $item->item_key) == 0) { // 第一階層
                        if (array_key_exists($item->item_key, $content)) {
                            $data = $content[$item->item_key];
                            $content[$item->item_key] = $this->createDataInTheFormC00800($task_result->task_result_id, $data);
                        }
                    } else { // 第二階層
                        if (array_key_exists($item->group, $content)) {
                            if (array_key_exists($item->key, $content[$item->group])) {
                                $data = $content[$item->group][$item->key];
                                $content[$item->group][$item->key] = $this->createDataInTheFormC00800($task_result->task_result_id, $data);
                            } elseif (is_array($content[$item->group])) {
                                foreach ($content[$item->group] as &$value) {
                                    if (array_key_exists($item->key, $value)) {
                                        $data = $value[$item->key];
                                        $value[$item->key] = $this->createDataInTheFormC00800($task_result->task_result_id, $data);
                                    }
                                }
                                unset($value);
                            }
                        }
                    }
                    $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
                }
            }
            // ---C00800用の処理

            // ---C00801用の処理
            $items = [];
            foreach ($item_configs as $array) {
                foreach ($array as $value) {
                    if ($value->item_type === \Config::get('const.ITEM_CONFIG_TYPE.SINGLE_FILE_INPUT')) {
                        array_push($items, $value);
                    }
                }
            }

            foreach ($items as $item) {
                foreach ($task_results as $task_result) {
                    if (is_null($task_result->content)) {
                        continue;
                    }

                    $content = json_decode($task_result->content, true); // 連想配列で取り出す
                    if (strcmp($item->group, $item->item_key) == 0) { // 第一階層
                        if (array_key_exists($item->item_key, $content)) {
                            $file_id = $content[$item->item_key];
                            if (is_null($file_id)) {
                                continue;
                            }
                            $content[$item->item_key] = $this->createDataInTheFormC00801($task_result->task_result_id, $file_id);
                        }
                    } else { // 第二階層
                        if (array_key_exists($item->group, $content)) {
                            if (array_key_exists($item->key, $content[$item->group])) {// グループの中が1つの場合
                                $file_id = $content[$item->group][$item->key];
                                if (is_null($file_id)) {
                                    continue;
                                }
                                $content[$item->group][$item->key] = $this->createDataInTheFormC00801($task_result->task_result_id, $file_id);
                            } elseif (is_array($content[$item->group])) {// グループに複数の場合
                                foreach ($content[$item->group] as $key => $group) {
                                    if (array_key_exists($item->key, $group)) {
                                        $file_id = $group[$item->key];
                                        if (is_null($file_id)) {
                                            $content[$item->group][$key][$item->key] = null;
                                            continue;
                                        }
                                        $content[$item->group][$key][$item->key] = $this->createDataInTheFormC00801($task_result->task_result_id, $file_id);
                                    }
                                }
                            }
                        }
                    }
                    $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
                }
            }
            // ---C00801用の処理

            // 作業担当の候補者を取得
            $business_id = RequestModel::find(RequestWork::find($request_work_id)->request_id)->business_id;
            $businesses_candidates = \DB::table('users')
                ->select(
                    'users.id as id',
                    'users.name as name',
                    'users.user_image_path as user_image_path'
                )
                ->join('businesses_candidates', function ($join) use ($business_id) {
                    $join->on('users.id', '=', 'businesses_candidates.user_id')
                    ->where('users.is_deleted', '=', \Config::get('const.FLG.INACTIVE'))
                    ->where('businesses_candidates.business_id', '=', $business_id);
                })
                ->get();

            // 業務管理者か判断
            // $request->is_business_admin = true;

            return response()->json([
                'result' => 'success',
                'request' => $request,
                'item_configs' => $item_configs,
                'task_results' => $task_results,
                'businesses_candidates' => $businesses_candidates,
                'started_at' => Carbon::now(),
                'label_data' => $label_data,
                'task_comment' => $task_comment
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error'
            ]);
        }
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        // DB登録
        \DB::beginTransaction();
        try {
            $request_work_id = $req->input('request_work_id');
            $task_id = $req->input('task_id');
            $input_task_comment = json_decode($req->input('task_comment'));


            $task = Task::findOrFail($task_id);// 指定したタスクを取得

            // 担当のタスクでもなくタスクに関連するビジネスの管理者でもない
            if ($task->user_id !== $user->id) {
                throw new \Exception('invalid access user id : ' . $user->id . ' task id : ' . $task->id);
            }

            $task_comment = $task->taskComment;

            // 排他制御--------------------------------------

            // タスクが更新されている場合
            // 先にtask commentを登録されていた場合もカバー
            $started_dt = new Carbon($req->input('started_at'));
            $task_updated_dt = new Carbon($task->updated_at);
            if ($started_dt < $task_updated_dt) {
                throw new ExclusiveException("Task data has been updated");
            }

            if (isset($task_comment)) {
                // 先に更新されていた場合
                $task_comment_updated_dt = new Carbon($task_comment->updated_at);
                if ($started_dt < $task_comment_updated_dt) {
                    throw new ExclusiveException("Task comment data has been updated");
                }
            }
            // 排他制御--------------------------------------

            if (is_null($task_comment)) {
                // task_commentの登録
                TaskComment::create(
                    [
                        'task_id' => $task_id,
                        'step_id' => RequestWork::findOrFail($request_work_id)->step_id,
                        'content' => json_encode($input_task_comment->content),
                        'global_comment' => $input_task_comment->global_comment,
                        'updated_user_id' => $user->id,
                        'created_user_id' => $user->id,
                    ]
                );
                $task->update(
                    [
                        'is_verified' => \Config::get('const.FLG.ACTIVE')
                    ]
                );
            } else {
                // task_commentの更新
                $task_comment->update(
                    [
                        'content' => json_encode($input_task_comment->content),
                        'global_comment' => $input_task_comment->global_comment,
                        'updated_user_id' => $user->id
                    ]
                );
                $task_comment->touch();// 同じ内容での更新の場合にupdated_atを更新するための処理
            }

            \DB::commit();

            return response()->json([
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error'
            ]);
        }
    }
    private function createDataInTheFormC00800(int $task_result_id, array $task_result_file_ids): array
    {
        // 必要な項目はselectで宣言
        $task_result_files = TaskResultFile::select('seq_no', 'name', 'file_path', DB::raw('0 as size'), 'created_at')
            ->where('task_result_id', $task_result_id)
            ->whereIn('seq_no', $task_result_file_ids)
            ->get();

        // ファイルサイズを取得
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        foreach ($task_result_files as $value) {
            $value->size = $disk->size($value->file_path);

            // ハッシュの生成
            $path = '';
            if (\Config::get('filesystems.cloud') === 's3') {
                $path = $disk->temporaryUrl(
                    $value->file_path,
                    now()->addMinutes(1)
                );
            } else {
                $path = $disk->path($value->file_path);
            }
            $value->hash = hash_file('sha512', $path);
        }

        return $task_result_files->toArray();
    }

    private function createDataInTheFormC00801(int $task_result_id, int $task_result_file_id): object
    {
        // 必要な項目はselectで宣言
        $task_result_file = TaskResultFile::select('seq_no', 'name', 'file_path', 'size', 'created_at')
            ->where('task_result_id', $task_result_id)
            ->where('seq_no', $task_result_file_id)
            ->first();

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        // ファイルサイズを取得
        if (is_null($task_result_file->size)) {
            $task_result_file->size = $disk->size($task_result_file->file_path);
        }
        // mimeTypeを取得
        $task_result_file->mime_type = $disk->mimetype($task_result_file->file_path);

        // ハッシュの生成
        $path = '';
        if (\Config::get('filesystems.cloud') === 's3') {
            $path = $disk->temporaryUrl(
                $task_result_file->file_path,
                now()->addMinutes(1)
            );
        } else {
            $path = $disk->path($task_result_file->file_path);
        }
        $task_result_file->hash = hash_file('sha512', $path);

        return $task_result_file;
    }
}
