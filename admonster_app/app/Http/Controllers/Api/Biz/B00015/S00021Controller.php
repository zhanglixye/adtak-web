<?php

namespace App\Http\Controllers\Api\Biz\B00015;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\RequestMailAttachment;
use App\Models\task_result_file;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;
use Storage;
use function GuzzleHttp\json_encode;

class S00021Controller extends MailController
{

    /**
     * オペ統用画面.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function create(Request $req)
    {
        try {
            $base_info = parent::create($req)->original;

            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 1没有作业履历，创建新的作业履历
                // 生成默认附件列表
                $default_attachments = self::generateDefaultAttachments($base_info['task_info']->id);
                $default_attachments_seq_no = array();
                foreach ($default_attachments as $attachment) {
                    array_push($default_attachments_seq_no, $attachment['file_seq_no']);
                }

                // 1-1创建新的作业履历到数据库
                $task_result_po = new TaskResult();
                $task_result_po->task_id = $base_info['task_info']->id;
                $task_result_po->step_id = $base_info['request_info']->step->id;
                $task_result_po->created_user_id = \Auth::user()->id;
                $task_result_po->updated_user_id = \Auth::user()->id;
                // 构造content
                $content_array = [
                    'results' =>
                        [
                            'type' => config('const.TASK_RESULT_TYPE.NOT_WORKING'),
                            'comment' => '',
                            'mail_id' => []
                        ],
                    'lastDisplayedPage' => '1',
                    'G00000_1' => [
                        'C00800_6' => $default_attachments_seq_no,
                        'C00800_6_uploadFiles' => $default_attachments
                    ]

                ];

                $json_encode = json_encode($content_array);
                $task_result_po->content = $json_encode;

                \DB::beginTransaction();
                try {
                    $task_result_po->save();
                    foreach ($default_attachments as $file) {
                        $task_result_file_po = new TaskResultFile();
                        $task_result_file_po->seq_no = $file['file_seq_no'];
                        $task_result_file_po->name = $file['file_name'];
                        $task_result_file_po->file_path = $file['file_path'];
                        $task_result_file_po->size = $file['size'];
                        $task_result_file_po->width = $file['width'];
                        $task_result_file_po->height = $file['height'];
                        $task_result_file_po->task_result_id = $task_result_po->id;
                        $task_result_file_po->created_user_id = \Auth::user()->id;
                        $task_result_file_po->updated_user_id = \Auth::user()->id;
                        $task_result_file_po->save();
                    }
                    \DB::commit();
                } catch (\Throwable $e) {
                    \DB::rollback();
                    throw $e;
                }

                // 1-2 重新构造 task_result_info
                $base_info['task_result_info'] = $task_result_po;
            } else {
                // 1根据作业履历获取文件
                // 1-1反序列化作业履历的content，得到对象
                $content_array = json_decode($task_result_info->content, true);
                // 1-2获取作业实绩的Id
                $task_result_id = self::arrayIfNullFail($base_info, ['task_result_info'], 'task_result_info not exist!', true)->id;
                $file_seq_no_array = self::arrayIfNull($content_array, ['G00000_1', 'C00800_6'], []);
                // 1-3处理附件
                $file_info_array = self::getTaskResultFile($task_result_id, $file_seq_no_array);
                $content_array['G00000_1']['C00800_6_uploadFiles'] = $file_info_array;
                // 1-6 重新构造 task_result_info
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return MailController::error('初期化失敗しました。');
        }
    }

    /**
     * 一時保存
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function hold(Request $req)
    {
        try {
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイル形式に不備はあります。");
            }
            $attach_file_array = $this->process('1', $data, \Config::get('const.TASK_RESULT_TYPE.HOLD'));
            return $this->success($attach_file_array);
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 確認
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function done(Request $req)
    {
        try {
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイル形式に不備はあります。");
            }
            //input validation
            $media_info_check_box_yes = self::arrayIfNull($data, ['C00400_2']);
            $media_info_check_box_no = self::arrayIfNull($data, ['C00400_3']);
            if (empty($media_info_check_box_yes) && empty($media_info_check_box_no)) {
                return self::error('媒体資料の掲載（紐づけ）状況確認のチェックは必須です。ご確認ください');
            }
            $menu_no = self::arrayIfNull($data, ['C00100_4']);
            if (empty($menu_no) && $menu_no != 0) {
                return self::error('メニュー数の入力は必須です。ご確認ください');
            }
            if (!is_numeric($menu_no) || ((int)$menu_no) != $menu_no || $menu_no < 0) {
                return self::error('メニュー数に整数しか入力できないため、ご確認ください');
            }
            $work_time_total = (double)$this->arrayIfNull($data, ['G00000_35','C00100_38'], -1);
            if ($work_time_total < 0) {
                return self::error('作業時間のご入力に不備はあります。ご確認ください。');
            }

            // 作業履歴
            $this->process('1', $data, \Config::get('const.TASK_RESULT_TYPE.DONE'));
            return $this->success();
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 問い合わせ（不明あり）
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function makeContact(Request $req)
    {
        try {
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイル形式に不備はあります。");
            }
            $this->process('1', $data, \Config::get('const.TASK_RESULT_TYPE.CONTACT'));
            return $this->success();
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 保存 作業履歴
     * @param string $last_displayed_page 最後に表示していたページ
     * @param array $data page data as json fromat
     * @param int $task_result_type task result type
     * @return array attach file array
     * @throws \Exception
     */
    public function process(string $last_displayed_page, array $data, int $task_result_type): array
    {
        \DB::beginTransaction();

        try {
            // ========================================
            // 排他チェック
            // ========================================
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($data, ['G00000_35', 'C00700_36']);
            $work_time['finished_at'] = $this->arrayIfNull($data, ['G00000_35', 'C00700_37']);
            $work_time['total'] = $this->arrayIfNull($data, ['G00000_35', 'C00100_38']);
            self::defaultWorkTime($work_time, $this->task_id);
            $data['G00000_35']['C00700_36'] = $work_time['started_at'];
            $data['G00000_35']['C00700_37'] = $work_time['finished_at'];
            $data['G00000_35']['C00100_38'] = $work_time['total'];

            // 增量处理用户提交的附件
            $final_file_po_array = self::attachFileUpdate(
                $this->task_id,
                $this->BUSINESS_ID,
                $this->arrayIfNull($data, ['C00800_6_uploadFiles'], [])
            );

            //将最终的文件SeqNo保存到Content中
            $final_file_seq_no_array = [];
            foreach ($final_file_po_array as $file) {
                array_push($final_file_seq_no_array, $file->seq_no);
            }
            self::arrayKeySet($data, ['C00800_6'], $final_file_seq_no_array);
            self::arrayKeyUnset($data, ['C00800_6_uploadFiles']);

            // ========================================
            // 保存処理
            // ========================================
            // 最新の作業履歴
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($task_result_info === null) {
                $task_result_content = [];
            } else {
                $task_result_content = json_decode($task_result_info->content, true);  // 作業内容
            }

            $task_result_content['results']['type'] = (int)$task_result_type;
            $task_result_content['lastDisplayedPage'] = $last_displayed_page;
            self::arrayKeySet($task_result_content, ['G00000_1'], $data);

            self::defaultWorkTime($work_time, $this->task_id);

//            self::arrayKeySet($task_result_content, ['G00000_1','G00000_35'], $work_time);
            $task_result_content['results']['type'] = (int)$task_result_type;
            $task_result_content[self::CONTENT_NODE_KEY_LAST_DISPLAYED_PAGE] = $last_displayed_page;


            if ($task_result_type === \Config::get('const.TASK_RESULT_TYPE.DONE')) {
                $this->taskSave($task_result_content, $final_file_po_array, $work_time);
            } elseif ($task_result_type === \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                // 問い合わせ（不明あり）
                $this->taskContact($task_result_content, $final_file_po_array, $work_time);
            } elseif ($task_result_type === \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                // 一時保存（対応中）
                $this->taskTemporarySave($task_result_content, $final_file_po_array, $work_time);
            }
            \DB::commit();
            return $final_file_po_array;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 获取ファイル添付.
     * @param int $task_id 作業ID
     * @return mixed ファイル添付
     */
    public function generateDefaultAttachments(int $task_id)
    {
        // 依頼メール
        $request_mail_po = self::getEmailByTaskId($task_id);

        $fileArray = array();
        $attachments = RequestMailAttachment::where('request_mail_id', $request_mail_po->id)->get();
        $max_seq_no = 0;
        // 遍历所有file
        foreach ($attachments as $file) {
            //生成，增加到新文件数组
            $attach_file = array();
            $attach_file['file_seq_no'] = $max_seq_no;
            $attach_file['file_name'] = $file->name;
            $attach_file['file_path'] = $file->file_path;
            $attach_file['size'] = $file->size;
            $attach_file['width'] = $file->width;
            $attach_file['height'] = $file->height;
            array_push($fileArray, $attach_file);
            $max_seq_no++;
        }
        return $fileArray;
    }

    /**
     * 通过作業Id，获取依頼メール
     * @param int $task_id 作業Id
     * @return mixed
     */
    private function getEmailByTaskId(int $task_id)
    {
        $result = \DB::table('request_mails')
            ->select(
                'request_mails.id',
                'request_mails.mail_account_id',
                'request_mails.create_status',
                'request_mails.message',
                'request_mails.message_id',
                'request_mails.references',
                'request_mails.in_reply_to',
                'request_mails.reply_to',
                'request_mails.from',
                'request_mails.to',
                'request_mails.cc',
                'request_mails.bcc',
                'request_mails.subject',
                'request_mails.content_type',
                'request_mails.body',
                'request_mails.recieved_at',
                'request_mails.is_deleted',
                'request_mails.created_at',
                'request_mails.created_user_id',
                'request_mails.updated_at',
                'request_mails.updated_user_id'
            )
            ->join('request_work_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->join('tasks', 'request_work_mails.request_work_id', '=', 'tasks.request_work_id')
            ->where('tasks.id', $task_id)
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('request_mails.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();
        return $result;
    }
}
