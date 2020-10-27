<?php

namespace App\Http\Controllers\Api\Biz\B00014;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\task_result_file;
use App\Models\TaskResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;
use Storage;
use function GuzzleHttp\json_encode;

class S00020Controller extends MailController
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
            $content_main_key = $this->MAIL_CONTENT_MAIN[0];
            $attach_file_key = array_keys($this->MAIL_RETURN_ATTACH_KEY)[0];

            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 1没有作业履历，创建新的作业履历
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
                        ]
                ];
                $json_encode = json_encode($content_array);
                $task_result_po->content = $json_encode;
                $task_result_po->save();

                // 1-2 重新构造 task_result_info
                $base_info['task_result_info'] = $task_result_po;
            } else {
                // 1根据作业履历获取文件
                // 1-1反序列化作业履历的content，得到对象
                $content_array = json_decode($task_result_info->content, true);
                 // 1-4处理附件
                $attach_file_array = $this->getAttachFiles($req);
                $attach_file = MailController::arrayIfNull($attach_file_array, [$attach_file_key], []);
                $content_array[$content_main_key][$attach_file_key] = $attach_file;
                // 1-6 重新构造 task_result_info
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return MailController::error('初期化失敗しました。');
        }
    }
}
