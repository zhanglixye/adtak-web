<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestMail;
use App\Models\RequestFile;
use App\Models\RequestLog;
use App\Models\Task;
use App\Models\Approval;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Step;
use App\Models\Label;
use Carbon\Carbon;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\Traits\RequestMailTrait;
use App\Services\Traits\RequestFileTrait;

class RequestWorksController extends RequestsController
{
    use RequestLogStoreTrait;
    use RequestMailTrait;
    use RequestFileTrait;

    public function index(Request $req)
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            'request_work_ids' => $req->input('request_work_ids'),
            'request_work_name' => $req->input('request_work_name'),
            'business_name' => $req->input('business_name'),
            'step_name' => $req->input('step_name'),
            // 'client_name' => $req->input('client_name'),
            'operator_name' => $req->input('operator_name'),
            'date_type' => $req->input('date_type'),
            'from' => $req->input('from'),
            'to' => $req->input('to'),
            'request_file_name' => $req->input('request_file_name'),
            'request_mail_subject' => $req->input('request_mail_subject'),
            'request_mail_to' => $req->input('request_mail_to'),
            'status' => $req->input('status'),
            'self' => $req->input('self'),
            'completed' => $req->input('completed'),
            'inactive' => $req->input('inactive'),
            'excluded' => $req->input('excluded'),
            'page' => $req->get('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        // 検索条件
        // プルダウンの値設定用に作成したが現状は不要
        // $searches = Business::getSearchConditions($user->id);

        // 検索結果
        $request_works = RequestModel::getRelatedDataSetList($user->id, $form);

        // 自分が管理する業務の担当候補者
        // $request_worksにはuser_idの一覧を保持し、$operatorsから詳細取得としたほうがデータ容量を節約できる為分けて取得
        // $candidates = User::getCandidatesByAdminUser($user->id);

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        return response()->json([
            'request_works' => $request_works,
            'candidates' => $candidates,
        ]);
    }

    public function show(Request $req)
    {
        $request_work_id = $req->request_work_id;

        $sub = RequestModel::getRequestProcesses();

        $query = RequestWork::query();
        $request_work = $query
            ->where('id', $request_work_id)
            ->with([
                'request',
                'request.business',
                'requestMails',
                'requestFiles',
                'step',
                'task',
                'task.taskResults',
                'approval',
                'approval.approvalTask.delivery',
                'requestWorkRelatedMails' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'requestWorkRelatedMails.requestMailAttachments',
            ])
            ->join(\DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id')
            ->first();

        $related_mails = $request_work->requestWorkRelatedMails;

        $request_work = $request_work->toArray();

        // 依頼ログ取得
        $request_logs = RequestLog::getList($request_work['request_id'], ['request_work_id' => $request_work_id]);

        // 依頼メール取得
        $request_mail = RequestWork::find($request_work_id)->requestMails()->select('id')->first();

        $request_mail_template_info = null;
        if (!is_null($request_mail)) {
            $request_mail_template_info = $this->tryCreateMailTemplateData($request_mail->id);
        }

        foreach ($related_mails as &$related_mail) {
            $related_mail->original_body = $related_mail->body;
            if ($related_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                $related_mail->body = strip_tags($related_mail->body, '<br>');
            } else {
                $related_mail->body = nl2br(e($related_mail->body));
            }
        }

        // 依頼ファイル取得
        $request_file = RequestFile::join('request_work_files', 'request_files.id', '=', 'request_work_files.request_file_id')
                            ->where('request_work_files.request_work_id', $request_work_id)
                            ->first();

        $label_data = new \stdClass();
        if ($request_file) {
            // RequestContentsに対応させるための処理
            $clone = clone $request_file;
            $clone->file_id = $request_file->id;
            $clone->file_name = $request_file->name;
            $clone->file_row_no = $request_file->row_no;
            $clone->file_created_at = $request_file->created_at->toDateTimeString();// コピーするとクライアントでオブジェクトになるので、文字列化したものを渡す
            $clone->file_created_user_id = $request_file->created_user_id;
            $request_file = $clone;

            $file_import_configs = $this->getFileImportConfigs($request_work['step_id']);
            $column_configs = $file_import_configs['column_configs'];
            // 依頼内容情報
            if (isset($request_file->content)) {
                $request_file->content = $this->generateRequestFileItemData($column_configs, $request_file->content);
            }
            // ラベルデータ
            $label_ids = [];
            foreach ($column_configs as $column_config) {
                $label_ids[] = $column_config->label_id;
            }
            $label_data = Label::getLangKeySetByIds($label_ids);
        }


        $to_be_checked_data = [];

        $import_info = [];
        $allocation_info = [];
        $tasks_info = [];

        // 取込情報
        $import_info['operator_id'] = $request_work['created_user_id'];
        $import_info['executed_at'] = $request_work['created_at'];

        // タスク情報
        $tasks = $request_work['task'];
        $allocation = [];
        foreach ($tasks as $task) {
            // 割振情報
            $allocation['operator_id'] = $task['created_user_id'];
            $allocation['executed_at'] = $task['created_at'];
            $allocation_info[] = $allocation;
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

        // プロセス情報
        $request_work['import_info'] = $import_info;
        $request_work['allocation_info'] = $allocation_info;
        $request_work['tasks_info'] = $tasks_info;
        $request_work['approval_info'] = $approval;
        $request_work['delivery_info'] = $delivery;

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::get();

        return response()->json([
            'request_work' => $request_work,
            'request_mail' => $request_mail_template_info,
            'request_file' => $request_file,
            'to_be_checked_data' => $to_be_checked_data,
            'candidates' => $candidates,
            'request_logs' => $request_logs,
            'related_mails' => $related_mails,
            'label_data' => $label_data,
        ]);
    }
}
