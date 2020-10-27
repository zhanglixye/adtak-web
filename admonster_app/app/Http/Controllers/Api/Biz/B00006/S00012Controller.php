<?php

namespace App\Http\Controllers\Api\Biz\B00006;

use App\Http\Controllers\Api\Biz\BaseController;
use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\Queue;
use App\Models\Task;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Storage;

class S00012Controller extends BaseController
{
    /** @var string メール作成画面 */
    const CONTENT_NODE_KEY_MAIL_MAIN = 'G00000_27';
    /** @var string ファイルID */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID = 'C00800_32';
    /** @var string ファイル添付 */
    const CONTENT_NODE_KEY_MAIL_ATTACH = 'uploadFiles';
    /** @var string ファイルseq */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_SEQ_NO = 'file_seq_no';
    /** @var string 『★交通費(AP) メール』 type */
    const CONTENT_NODE_KEY_AP_TYPE = 0;
    /** @var string 『★交通費(常駐)メール』 type */
    const CONTENT_NODE_KEY_PERMANENT_TYPE = 1;

    /** @var string 承认前的『★交通費(AP) メール』 シートFile List */
    const CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST = 'C00800_1';
    /** @var string 承认前的『★交通費(常駐)メール』シート File List */
    const CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST = 'C00800_2';
    /** @var string 承认前的所有文件 */
    const CONTENT_NODE_KEY_BEFORE_WORK_FILE_LIST = 'before_file_list';

    /** @var string 承认后的『★交通費(AP) メール』 シートFile List */
    const CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST = 'C00800_3';
    /** @var string 承认后的『★交通費(常駐)メール』シート File List */
    const CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST = 'C00800_4';
    /** @var string 承认后的所有文件 */
    const CONTENT_NODE_KEY_APPROVAL_FILE_LIST = 'approval_file_list';

    /*---------------------------------------s00011 attach file json key for pdf preview-------------------------------------------
    /** @var string 交通費PDF upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN = 'C00800_24';
    /** @var string AP file seq no */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE = 'C00800_25';
    /** @var string 常驻 file seq no */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO = 'C00800_26';

    public function create(Request $req)
    {
        try {
            $base_info = parent::create($req)->original;
            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 没有作业履历，创建新的作业履历
                // 全部文件的集合
                $all_file_array = [];

                // 通过前作业ID获取『★交通費(AP) メール』 シート 的文件集合
                $before_work_id = $base_info['request_info']->before_work_id;
                if ($before_work_id === null) {
                    return MailController::error('前工程の作業は見つかりませんでした。');
                }
                $ap_file_array = $this->getBeforeTaskFiles($before_work_id, self::CONTENT_NODE_KEY_AP_TYPE);
                // 遍历文件，构造seq_no数组,并放入全部文件的集合
                $ap_file_seq_no_array = [];
                $max_seq_no = 0;
                foreach ($ap_file_array as $file) {
                    $max_seq_no++;
                    $file['seq_no'] = $max_seq_no;
                    array_push($ap_file_seq_no_array, $max_seq_no);
                    array_push($all_file_array, $file);
                }

                // 通过前作业ID获取『★交通費(常駐)メール』シート 的文件集合
                $permanent_file_array = $this->getBeforeTaskFiles($before_work_id, self::CONTENT_NODE_KEY_PERMANENT_TYPE);
                $permanent_file_seq_no_array = [];
                // 遍历文件，构造seq_no数组
                foreach ($permanent_file_array as $file) {
                    $max_seq_no++;
                    $file['seq_no'] = $max_seq_no;
                    array_push($permanent_file_seq_no_array, $max_seq_no);
                    array_push($all_file_array, $file);
                }

                // 创建新的作业履历到数据库
                $task_result_po = new TaskResult();
                $task_result_po->task_id = $req->task_id;
                $task_result_po->step_id = $base_info['request_info']->step->id;
                $task_result_po->started_at = Carbon::now()->format('Y/m/d H:i:s');
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
                    self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST => $ap_file_seq_no_array,
                    self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST => $permanent_file_seq_no_array
                ];
                $task_result_po->content = json_encode($content_array);
                $task_result_po->save();

                // 保存文件到数据库
                foreach ($all_file_array as $file) {
                    $task_result_file_po = new TaskResultFile;
                    $task_result_file_po->task_result_id = $task_result_po->id;
                    $task_result_file_po->seq_no = $file['seq_no'];
                    $task_result_file_po->name = $file['file_name'];
                    $task_result_file_po->file_path = $file['file_path'];
                    $task_result_file_po->created_user_id = \Auth::user()->id;
                    $task_result_file_po->updated_user_id = \Auth::user()->id;
                    $task_result_file_po->save();
                }

                // 插入文件内容结点,构造返回报文
                $content_array[self::CONTENT_NODE_KEY_BEFORE_WORK_FILE_LIST] = $all_file_array;
                $content_array[self::CONTENT_NODE_KEY_APPROVAL_FILE_LIST] = [];
                $task_result_po->content = json_encode($content_array);
                $base_info['task_result_info'] = $task_result_po;
                return response()->json($base_info);
            } else {
                // 根据作业履历获取文件
                // 反序列化作业履历的content，得到对象
                $content = json_decode($task_result_info->content, true);
                // 获取作业实绩的Id
                $task_result_id = MailController::arrayIfNullFail($base_info, ['task_result_info'], 'task_result_info not exist!', true)->id;

                //==============================================处理承认前的文件======================================================

                // 取得『★交通費(AP) メール』 シート 的文件Seq_no集合
                $ap_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST], []);
                // 通过seq_no获取文件的base64表示
                $ap_file_array = self::getTaskFiles($task_result_id, $ap_file_seq_no_array, self::CONTENT_NODE_KEY_AP_TYPE);

                // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
                $permanent_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST], []);
                // 通过seq_no获取文件的base64表示
                $permanent_file_array = self::getTaskFiles($task_result_id, $permanent_file_seq_no_array, self::CONTENT_NODE_KEY_PERMANENT_TYPE);

                // 合并AP和常駐的文件到一个数组
                $all_file_array = [];
                foreach ($ap_file_array as $file) {
                    array_push($all_file_array, $file);
                }
                foreach ($permanent_file_array as $file) {
                    array_push($all_file_array, $file);
                }

                //================================================处理承认后的文件====================================================

                // 取得『★交通費(AP) メール』 シート 的文件Seq_no集合
                $approval_ap_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST], []);
                // 通过seq_no获取文件的base64表示
                $approval_ap_file_array = self::getTaskFiles($task_result_id, $approval_ap_file_seq_no_array, self::CONTENT_NODE_KEY_AP_TYPE);

                // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
                $approval_permanent_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST], []);
                // 通过seq_no获取文件的base64表示
                $approval_permanent_file_array = self::getTaskFiles($task_result_id, $approval_permanent_file_seq_no_array, self::CONTENT_NODE_KEY_PERMANENT_TYPE);

                // 合并AP和常駐的文件到一个数组
                $approval_all_file_array = [];
                foreach ($approval_ap_file_array as $file) {
                    array_push($approval_all_file_array, $file);
                }
                foreach ($approval_permanent_file_array as $file) {
                    array_push($approval_all_file_array, $file);
                }
                // 处理page3的文件
                if (MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_MAIL_MAIN, self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID])) {
                    // 1-4-1取得邮件 的文件Seq_no集合
                    $mail_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_MAIL_MAIN, self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID]);
                    // 1-4-2通过seq_no获取文件的信息
                    $mail_file_info_array = MailController::getTaskResultFile($task_result_id, $mail_file_seq_no_array);
                    // 1-4-3构造文件结点Jsono数据
                    $content[self::CONTENT_NODE_KEY_MAIL_MAIN][self::CONTENT_NODE_KEY_MAIL_ATTACH] = $mail_file_info_array;
                }
                // 转换报文格式
                $content[self::CONTENT_NODE_KEY_BEFORE_WORK_FILE_LIST] = $all_file_array;
                $content[self::CONTENT_NODE_KEY_APPROVAL_FILE_LIST] = $approval_all_file_array;

                $base_info['task_result_info']->content = json_encode($content);
                return response()->json($base_info);
            }
        } catch (\Exception $e) {
            report($e);
            return MailController::error('初期化失敗しました。');
        }
    }

    /**
     * 承认
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function approval(Request $req)
    {
        $base_info = parent::create($req)->original;
        $time_zone = $req->time_zone;

        \DB::beginTransaction();
        try {
            // 排他处理
            MailController::exclusiveTask($req->task_id, \Auth::user()->id);

            // 根据作业履历获取未承认PDF文件
            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                return MailController::error('作業実績は見つかりませんでした。');
            }
            // 反序列化作业履历的content，得到对象
            $content = json_decode($task_result_info->content, true);
            // 获取作业实绩的Id
            $task_result_id = MailController::arrayIfNull($base_info, ['task_result_info'])->id;

            if (MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST]) === null) {
                //没有承认过，处理pdf文件
                $max_seq_no = 0;
                $final_file_array = [];
                //==============================================处理承认前的文件======================================================

                // 取得『★交通費(AP) メール』 シート 的文件Seq_no集合
                $ap_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST], []);
                //取得承认前文件信息
                $Ap_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $ap_file_seq_no_array);
                $ap_file_seq_no_new_array = [];
                // 添加到文件数组用于写入数据库
                foreach ($Ap_file_array as $item) {
                    //生成，增加到新文件数组
                    $max_seq_no++;
                    $task_result_file_po = new TaskResultFile;
                    $task_result_file_po->seq_no = $max_seq_no;
                    $task_result_file_po->name = $item['file_name'];
                    $task_result_file_po->file_path = $item['file_path'];
                    array_push($final_file_array, $task_result_file_po);
                    array_push($ap_file_seq_no_new_array, $max_seq_no);
                }

                // 承认『★交通費(AP) メール』 シート 的文件，返回加完印章的文件信息数组
                $approval_ap_file_array = self::approvalPdf($task_result_id, $ap_file_seq_no_array, $time_zone);
                $approval_ap_file_seq_no_array = [];
                // 添加到文件数组用于写入数据库
                foreach ($approval_ap_file_array as $item) {
                    //生成，增加到新文件数组
                    $max_seq_no++;
                    $task_result_file_po = new TaskResultFile;
                    $task_result_file_po->seq_no = $max_seq_no;
                    $task_result_file_po->name = $item['file_name'];
                    $task_result_file_po->file_path = $item['file_path'];
                    array_push($final_file_array, $task_result_file_po);
                    array_push($approval_ap_file_seq_no_array, $max_seq_no);
                }

                // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
                $permanent_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST], []);
                //取得承认前文件信息
                $permanent_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $permanent_file_seq_no_array);
                $permanent_file_seq_no_new_array = [];
                // 添加到文件数组用于写入数据库
                foreach ($permanent_file_array as $item) {
                    //生成，增加到新文件数组
                    $max_seq_no++;
                    $task_result_file_po = new TaskResultFile;
                    $task_result_file_po->seq_no = $max_seq_no;
                    $task_result_file_po->name = $item['file_name'];
                    $task_result_file_po->file_path = $item['file_path'];
                    array_push($final_file_array, $task_result_file_po);
                    array_push($permanent_file_seq_no_new_array, $max_seq_no);
                }
                // 承认『★交通費(常駐)メール』シート 的文件，返回加完印章的文件信息数组
                $approval_permanent_file_array = self::approvalPdf($task_result_id, $permanent_file_seq_no_array, $time_zone);
                $approval_permanent_file_seq_no_array = [];
                // 添加到文件数组用于写入数据库
                foreach ($approval_permanent_file_array as $item) {
                    //生成，增加到新文件数组
                    $max_seq_no++;
                    $task_result_file_po = new TaskResultFile;
                    $task_result_file_po->seq_no = $max_seq_no;
                    $task_result_file_po->name = $item['file_name'];
                    $task_result_file_po->file_path = $item['file_path'];
                    array_push($final_file_array, $task_result_file_po);
                    array_push($approval_permanent_file_seq_no_array, $max_seq_no);
                }

                // 构造content
                $content_array = [
                    'results' =>
                        [
                            'type' => config('const.TASK_RESULT_TYPE.HOLD'),
                            'comment' => '',
                            'mail_id' => []
                        ],
                    self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST => $ap_file_seq_no_new_array,
                    self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST => $permanent_file_seq_no_new_array,
                    self::CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST => $approval_ap_file_seq_no_array,
                    self::CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST => $approval_permanent_file_seq_no_array
                ];

                $result = self::partStore($content_array, $final_file_array);
                $task_result_id = $result['task_result']->id;

                //================================================返回承认后的文件内容====================================================

                // 通过seq_no获取文件的base64表示
                $approval_ap_file_array = self::getTaskFiles($task_result_id, $approval_ap_file_seq_no_array, self::CONTENT_NODE_KEY_AP_TYPE);
                // 通过seq_no获取文件的base64表示
                $approval_permanent_file_array = self::getTaskFiles($task_result_id, $approval_permanent_file_seq_no_array, self::CONTENT_NODE_KEY_PERMANENT_TYPE);

                // 合并AP和常駐的文件到一个数组
                $approval_all_file_array = [];
                foreach ($approval_ap_file_array as $file) {
                    array_push($approval_all_file_array, $file);
                }
                foreach ($approval_permanent_file_array as $file) {
                    array_push($approval_all_file_array, $file);
                }

                // 转换报文格式
                $content[self::CONTENT_NODE_KEY_BEFORE_WORK_FILE_LIST] = [];
                $content[self::CONTENT_NODE_KEY_APPROVAL_FILE_LIST] = $approval_all_file_array;
                $base_info['task_result_info']->content = json_encode($content);
            } else {
                //承认过，不再返回文件内容
                $content[self::CONTENT_NODE_KEY_BEFORE_WORK_FILE_LIST] = [];
                $content[self::CONTENT_NODE_KEY_APPROVAL_FILE_LIST] = [];
                $content['useBeforeFile'] = '1';
                $base_info['task_result_info']->content = json_encode($content);
            }
            \DB::commit();
            return response()->json($base_info);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error('承認失敗しました。');
        }
    }

    /**
     * 临时保存
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function tmpSave(Request $req)
    {
        return self::create($req);
    }

    /**
     * 保存
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function done(Request $req)
    {
        $base_info = parent::create($req)->original;
        $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
        \DB::beginTransaction();
        try {
            // 根据作业履历获取文件
            // 反序列化作业履历的content，得到对象
            $content = json_decode($task_result_info->content, true);
            // 获取作业实绩的Id
            $task_result_id = MailController::arrayIfNullFail($base_info, ['task_result_info'], 'task_result_info not exist!', true)->id;

            $max_seq_no = 0;
            $final_file_array = [];
            //==============================================处理承认前的文件======================================================

            // 取得『★交通費(AP) メール』 シート 的文件Seq_no集合
            $ap_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST], []);
            //取得承认前文件信息
            $Ap_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $ap_file_seq_no_array);
            $ap_file_seq_no_new_array = [];
            // 添加到文件数组用于写入数据库
            foreach ($Ap_file_array as $item) {
                //生成，增加到新文件数组
                $max_seq_no++;
                $task_result_file_po = new TaskResultFile;
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->name = $item['file_name'];
                $task_result_file_po->file_path = $item['file_path'];
                array_push($final_file_array, $task_result_file_po);
                array_push($ap_file_seq_no_new_array, $max_seq_no);
            }
            // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
            $permanent_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST], []);
            //取得承认前文件信息
            $permanent_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $permanent_file_seq_no_array);
            $permanent_file_seq_no_new_array = [];
            // 添加到文件数组用于写入数据库
            foreach ($permanent_file_array as $item) {
                //生成，增加到新文件数组
                $max_seq_no++;
                $task_result_file_po = new TaskResultFile;
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->name = $item['file_name'];
                $task_result_file_po->file_path = $item['file_path'];
                array_push($final_file_array, $task_result_file_po);
                array_push($permanent_file_seq_no_new_array, $max_seq_no);
            }
            //================================================处理承认后的文件====================================================

            // 取得『★交通費(AP) メール』 シート 的文件Seq_no集合
            $approval_ap_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST], []);
            //取得承认文件信息
            $approval_ap_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $approval_ap_file_seq_no_array);
            $approval_ap_file_seq_no_new_array = [];
            // 添加到文件数组用于写入数据库
            foreach ($approval_ap_file_array as $item) {
                //生成，增加到新文件数组
                $max_seq_no++;
                $task_result_file_po = new TaskResultFile;
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->name = $item['file_name'];
                $task_result_file_po->file_path = $item['file_path'];
                array_push($final_file_array, $task_result_file_po);
                array_push($approval_ap_file_seq_no_new_array, $max_seq_no);
            }
            // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
            $approval_permanent_file_seq_no_array = MailController::arrayIfNull($content, [self::CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST], []);
            //取得承认文件信息
            $approval_permanent_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $approval_permanent_file_seq_no_array);
            $approval_permanent_file_seq_no_new_array = [];
            // 添加到文件数组用于写入数据库
            foreach ($approval_permanent_file_array as $item) {
                //生成，增加到新文件数组
                $max_seq_no++;
                $task_result_file_po = new TaskResultFile;
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->name = $item['file_name'];
                $task_result_file_po->file_path = $item['file_path'];
                array_push($final_file_array, $task_result_file_po);
                array_push($approval_permanent_file_seq_no_new_array, $max_seq_no);
            }

            if (empty($approval_ap_file_seq_no_array) && empty($approval_permanent_file_seq_no_array)) {
                return MailController::error('全工程の作業はまだ承認されていませんでした。');
            }

            // 构造content
            $content_array = [
                'results' =>
                    [
                        'type' => config('const.TASK_RESULT_TYPE.DONE'),
                        'comment' => '',
                        'mail_id' => []
                    ],
                self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST => $ap_file_seq_no_new_array,
                self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST => $permanent_file_seq_no_new_array,
                self::CONTENT_NODE_KEY_APPROVAL_AP_FILE_SEQ_NO_LIST => $approval_ap_file_seq_no_new_array,
                self::CONTENT_NODE_KEY_APPROVAL_PERMANENT_FILE_SEQ_NO_LIST => $approval_permanent_file_seq_no_new_array
            ];

            self::partStore($content_array, $final_file_array);
            \DB::commit();
            return MailController::success();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error('保存失敗しました。');
        }
    }

    /**
     * 却下
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function reject(Request $req)
    {
        try {
            $task_id = $req->task_id;
            $base_info = parent::create($req)->original;
            // 排他处理
            MailController::exclusiveTask($task_id, \Auth::user()->id);
            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                return MailController::error('作業実績は見つかりませんでした。');
            }
            $task_content = json_decode($task_result_info->content, true);
            $task_content['results']['type'] = config('const.TASK_RESULT_TYPE.RETURN');

            // 取得『★交通費(AP) メール』 シート 的文件Seq_no集合
            $ap_file_seq_no_array = MailController::arrayIfNull($task_content, [self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST], []);
            //取得文件信息
            $Ap_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_info->id, $ap_file_seq_no_array);
            // 遍历文件，构造seq_no数组,并放入全部文件的集合
            $ap_file_seq_no_array = [];
            $all_file_array = [];
            $max_seq_no = 0;
            foreach ($Ap_file_array as $file) {
                $max_seq_no++;
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->name = $file['file_name'];
                $task_result_file_po->file_path = $file['file_path'];
                array_push($ap_file_seq_no_array, $max_seq_no);
                array_push($all_file_array, $task_result_file_po);
            }
            $task_content[self::CONTENT_NODE_KEY_BEFORE_AP_FILE_SEQ_NO_LIST] = $ap_file_seq_no_array;

            // 取得『★交通費(常駐)メール』シート 的文件Seq_no集合
            $permanent_file_seq_no_array = MailController::arrayIfNull($task_content, [self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST], []);
            //取得文件信息
            $permanent_file_array = self::getFileInfoListByTaskResultIdAndSeqNO($task_result_info->id, $permanent_file_seq_no_array);
            $permanent_file_seq_no_array = [];
            // 遍历文件，构造seq_no数组,并放入全部文件的集合
            foreach ($permanent_file_array as $file) {
                $max_seq_no++;
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $max_seq_no;
                $task_result_file_po->name = $file['file_name'];
                $task_result_file_po->file_path = $file['file_path'];
                array_push($permanent_file_seq_no_array, $max_seq_no);
                array_push($all_file_array, $task_result_file_po);
            }
            $task_content[self::CONTENT_NODE_KEY_BEFORE_PERMANENT_FILE_SEQ_NO_LIST] = $permanent_file_seq_no_array;
            self::partStore($task_content, $all_file_array);
            return MailController::success();
        } catch (\Exception $e) {
            report($e);
            return MailController::error('却下失敗しました。');
        }
    }

    /**
     * 通过前作业ID获取指定类型的文件
     * @param int $before_work_id 前作业ID
     * @param int $type 0：『★交通費(AP) メール』, 1: 『★交通費(常駐)メール』
     * @return array 文件数组
     */
    private function getBeforeTaskFiles(int $before_work_id, int $type): array
    {
        $before_task_id = DB::table('deliveries')->select('approval_tasks.task_id')
            ->join('approval_tasks', 'deliveries.approval_task_id', 'approval_tasks.id')
            ->join('approvals', 'approval_tasks.approval_id', 'approvals.id')
            ->join('request_works', 'approvals.request_work_id', 'request_works.id')
            ->where('request_works.id', $before_work_id)
            ->where("approval_tasks.approval_result", config('const.APPROVAL_RESULT.OK'))
            ->where("approvals.status", config('const.APPROVAL_STATUS.DONE'))
            ->where("approvals.result_type", config('const.APPROVAL_RESULT.OK'))
            ->first();
        if ($before_task_id === null || $before_task_id->task_id === null) {
            throw new \Exception('before task not exist or not approved yet!');
        }
        $before_task_id = $before_task_id->task_id;
        $before_task_result_info = TaskResult::with('taskResultFiles')->where('task_id', $before_task_id)
            ->orderBy('id', 'desc')->first();
        $before_task_result_content = $before_task_result_info->content;
        $before_task_result_content = json_decode($before_task_result_content, true);
        $task_reuslt_id = $before_task_result_info->id;
        if ($type === 0) {
            $seq = MailController::arrayIfNull($before_task_result_content, [self::CONTENT_NODE_KEY_EP_TWO_MAIN, self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE], []);
        }
        if ($type === 1) {
            $seq = MailController::arrayIfNull($before_task_result_content, [self::CONTENT_NODE_KEY_EP_TWO_MAIN, self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO], []);
        }
        if (empty($seq)) {
            return [];
        }
        $response = [];
        foreach ($seq as $v) {
            $file_name_path = self::getFileInfoByTaskResultIdAndSeqNO($task_reuslt_id, $v);
            if ($file_name_path) {
                $file = CommonDownloader::base64FileFromS3($file_name_path['file_path'], $file_name_path['file_name']);
                $data = $file[0];
                $file_arry = [];
                $file_arry['file_name'] = $file_name_path['file_name'];
                $file_arry['file_path'] = $file_name_path['file_path'];
                $file_arry['file_data'] = $data;
                $file_arry['file_type'] = $type;
                $file_arry['seq_no'] = $file_name_path['seq_no'];
                array_push($response, $file_arry);
            }
        }
        return $response;
    }

    /**
     * 获取多个文件的路径和文件名称
     * @param int $task_result_id タスク実績Id
     * @param array $seq_no_array タスク実績（ファイル）SeqNo
     * @return array タスク実績（ファイル）
     * @throws \Exception
     */
    private function getFileInfoListByTaskResultIdAndSeqNO($task_result_id, $seq_no_array)
    {
        $file_array = [];
        foreach ($seq_no_array as $seq_no) {
            $file = self::getFileInfoByTaskResultIdAndSeqNO($task_result_id, $seq_no);
            array_push($file_array, $file);
        }
        return $file_array;
    }

    /**
     * 获取文件路径和文件名称
     * @param int $task_result_id タスク実績Id
     * @param int $seq_no タスク実績（ファイル）SeqNo
     * @return array タスク実績（ファイル）
     * @throws \Exception
     */
    private function getFileInfoByTaskResultIdAndSeqNO(int $task_result_id, int $seq_no)
    {

        $task_reuslt_file = TaskResultFile::where('task_result_id', $task_result_id)->where('seq_no', $seq_no)->first();
        if ($task_reuslt_file) {
            return ['file_name' => $task_reuslt_file->name, 'file_path' => $task_reuslt_file->file_path, 'seq_no' => $task_reuslt_file->seq_no];
        } else {
            throw new \Exception('file not exists, task_result_id:' . $task_result_id . ' seq_no:' . $seq_no);
        }
        return [];
    }

    /**
     * 通过作业履历ID获取指定的文件
     * @param int $task_result_id 作业履历ID
     * @param array $seq_no_array 文件序号的数组
     * @param int $type 0：『★交通費(AP) メール』, 1: 『★交通費(常駐)メール』
     * @return array 文件数组
     */
    private function getTaskFiles(int $task_result_id, array $seq_no_array, int $type): array
    {
        $fileArray = [];
        foreach ($seq_no_array as $seqNo) {
            $task_result_file = TaskResultFile::where('task_result_id', $task_result_id)
                ->where('seq_no', $seqNo)
                ->firstOrFail();
            $fileData = CommonDownloader::base64FileFromS3($task_result_file['file_path'], $task_result_file['name'])[0];
            $file['file_name'] = $task_result_file['file_name'];
            $file['file_path'] = $task_result_file['file_path'];
            $file['file_data'] = $fileData;
            $file['file_type'] = $type;
            array_push($fileArray, $file);
        }
        return $fileArray;
    }

    /**
     * PDF增加水印
     * @param int $task_result_id タスク実績ID
     * @param array $seq_no_array タスク実績（ファイル）SeqNo
     * @param int $time_zone timezone
     * @return array
     * @throws \Exception
     */
    private function approvalPdf(int $task_result_id, array $seq_no_array, int $time_zone): array
    {
        // ローカルに一時保存
        $seal_tmp_disk_file_name = time() . '_s00016_' . \Auth::user()->id . '_seal_tmp.png';
        $seal_tmp_file_path = storage_path() . '/app/public/' . $seal_tmp_disk_file_name;
        $this->makeSeal($time_zone, $seal_tmp_file_path);


        $approval_pdf_array = [];
        foreach ($seq_no_array as $seqNo) {
            $file_info = self::getFileInfoByTaskResultIdAndSeqNO($task_result_id, $seqNo);
            $db_file_name = $file_info['file_name'];
            $db_file_path = $file_info['file_path'];
            $seq_no = $file_info['seq_no'];
            list($file_name, $tmp_disk, $tmp_file_name, $tmp_file_path, $mime_type, $file_size) = CommonDownloader::getFileFromS3($db_file_path, $db_file_name);

            $pdf = new Fpdi();
            $page_count = $pdf->setSourceFile($tmp_file_path);//原始PDF地址

            for ($pageNo = 1; $pageNo <= $page_count; $pageNo++) {
                // import a page
                $template_id = $pdf->importPage($pageNo);

                // get the size of the imported page
                $size = $pdf->getTemplateSize($template_id);

                // create a page (landscape or portrait depending on the imported page size)
                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', array($size['width'], $size['height']));
                } else {
                    $pdf->AddPage('P', array($size['width'], $size['height']));
                }

                // use the imported page
                $pdf->useTemplate($template_id);

                // Place the graphics
                $pdf->image(
                    $seal_tmp_file_path,
                    config('biz.b00006.IMAGE_SET.IMAGE_X'),
                    config('biz.b00006.IMAGE_SET.IMAGE_Y'),
                    config('biz.b00006.IMAGE_SET.IMAGE_W')
                );//图片地址
            }
            // ローカルに一時保存
            $tmp_disk = Storage::disk('public');
            $tmp_disk_file_name = time() . $file_name;
            $tmp_file_path = storage_path() . '/app/public/' . $tmp_disk_file_name;
            // 增加印章后的PDF文件输出到临时文件
            $pdf->Output('F', $tmp_file_path);
            // 临时文件上传到S3
            try {
                $tmp_file = $tmp_disk->get($tmp_disk_file_name);
                //上传文件
                $upload_data = array(
                    'business_id' => 'B00006',
                    'file' => array(
                        'file_name' => $file_name
                    , 'file_data' => $tmp_file
                    )
                );
                $s3_file_path = MailController::uploadFile($upload_data);
                array_push($approval_pdf_array, ['file_path' => $s3_file_path, 'file_name' => $file_name]);
            } finally {
                // 一時ファイルを削除
                $tmp_disk->delete($tmp_disk_file_name);
            }
        }
        return $approval_pdf_array;
    }

    /**
     * 保存 作業履歴
     * @param array $task_result_content 最後に表示していたページ
     * @param array $task_result_file_array key in json content
     * @param array|null $work_time 作業時間
     * @return array タスク実績
     * @throws \Exception
     */
    private function partStore(array $task_result_content, array $task_result_file_array, array $work_time = null): array
    {
        $this->user = \Auth::user();
        \DB::beginTransaction();

        try {
            // ========================================
            // 排他チェック
            // ========================================
            MailController::exclusiveTask($this->task_id, \Auth::user()->id);

            // ========================================
            // 保存処理
            // ========================================
            $result = [];
            $result_type = (int)$task_result_content['results']['type'];
            $task_result_content['results']['type'] = $result_type;
            if ($result_type === \Config::get('const.TASK_RESULT_TYPE.DONE')) {
                // 完了
                $result = $this->saveTask($task_result_content, $task_result_file_array, $work_time);
            } elseif ($result_type === \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                // 問い合わせ（不明あり）
                $result = $this->taskContact($task_result_content, $task_result_file_array, $work_time);
            } elseif ($result_type === \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                // 一時保存（対応中）
                $result = $this->taskTemporarySave($task_result_content, $task_result_file_array, $work_time);
            } elseif ($result_type === \Config::get('const.TASK_RESULT_TYPE.RETURN')) {
                // 却下
                $result = $this->taskContact($task_result_content, $task_result_file_array, $work_time);
            }
            \DB::commit();
            return $result;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 保存タスク実績和关联的タスク実績（ファイル）
     * @param array $content 実作業内容
     * @param array $task_result_file_array タスク実績（ファイル）
     * @param array|null $work_time 作業時間
     * @return array 保存后的タスク実績和所有的タスク実績（ファイル）
     */
    private function saveTask(array $content, array $task_result_file_array, array $work_time = null)
    {
        // タスクのステータスを完了に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.DONE');
        $task->updated_user_id = $this->user->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = $this->user->id;
        $task_result->updated_user_id = $this->user->id;
        $task_result->save();

        MailController::taskFilePersistence($task_result->id, $task_result_file_array);

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = $this->user->id;
        $queue->updated_user_id = $this->user->id;
        $queue->save();

        // ログ登録
        $request_log_attributes = [
            'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
            'request_id' => $this->request_id,
            'request_work_id' => $this->request_work_id,
            'task_id' => $this->task_id,
            'created_user_id' => $this->user->id,
            'updated_user_id' => $this->user->id,
        ];
        $this->storeRequestLog($request_log_attributes);

        return ['task_result' => $task_result, 'task_result_file_array' => $task_result_file_array];
    }

    /**
     * 保存タスク実績
     * @param array $content 実作業内容
     * @param array $task_result_file_po_array タスク実績（ファイル）
     * @param array|null $work_time 作業時間
     */
    private function taskContact(array $content, array $task_result_file_po_array, array $work_time = null)
    {
        // タスクのステータスを完了に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.DONE');
        $task->updated_user_id = $this->user->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = $this->user->id;
        $task_result->updated_user_id = $this->user->id;
        $task_result->save();

        MailController::taskFilePersistence($task_result->id, $task_result_file_po_array);

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = $this->user->id;
        $queue->updated_user_id = $this->user->id;
        $queue->save();

        // ログ登録
        $request_log_attributes = [
            'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT'),
            'request_id' => $this->request_id,
            'request_work_id' => $this->request_work_id,
            'task_id' => $this->task_id,
            'created_user_id' => $this->user->id,
            'updated_user_id' => $this->user->id,
        ];
        $this->storeRequestLog($request_log_attributes);
        return ['task_result' => $task_result, 'task_result_file_array' => $task_result_file_po_array];
    }

    /**
     * 一時保存（対応中）
     * @param array $content 実作業内容
     * @param array $task_result_file_po_array タスク実績（ファイル）
     * @param array|null $work_time 作業時間
     */
    private function taskTemporarySave(array $content, array $task_result_file_po_array, array $work_time = null)
    {
        // タスクのステータスを対応中に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.ON');
        $task->updated_user_id = $this->user->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = $this->user->id;
        $task_result->updated_user_id = $this->user->id;
        $task_result->save();

        MailController::taskFilePersistence($task_result->id, $task_result_file_po_array);
        return ['task_result' => $task_result, 'task_result_file_array' => $task_result_file_po_array];
    }

    /**
     * 生成印章
     * @param float $time_zone 客户端的时区
     * @param string|null $save_path 保存路径,为空直接写出到浏览器
     */
    private function makeSeal($time_zone, string $save_path = null): void
    {
        $timestamp = time() + $time_zone * 3600;
        $date = date('Y.m.d', (int)$timestamp);
        $this->fillImage(
            '/var/www/storage/biz/b00006/seal.png',
            '/var/www/storage/biz/b00006/ipag.ttf',
            [[50, 120, 135, 'ADP承認'], [60, 60, 277, $date], [50, 100, 420, '社長 山﨑']],
            $save_path
        );
    }

    /**
     * @param string $img_path 图片实际地址
     * @param string $font_path 字体文件路径
     * @param array $text_array 填充的文本
     * @param string $save_path 生成填充后的文件（自定义）
     * @return bool
     */
    private function fillImage(string $img_path, string $font_path, array $text_array, string $save_path = null): bool
    {
        $imgInfo = @getimagesize($img_path);//获取图片相关信息,抑制报错，不存在则返回false
        if ($imgInfo === false) {
            //此处提示报错信息
            return false;
        }
        $mime = $imgInfo['mime'];//获取图片类型
        $createFun = str_replace('/', 'createfrom', $mime);//由文件或 URL 创建一个新图象，动态生成创建图像的方法。
        $image = $createFun($img_path);
        $back = imagecolorallocate($image, 218, 52, 53);//设置画笔颜色
        foreach ($text_array as $item) {
            $tt = imagettftext($image, $item[0], 0, $item[1], $item[2], $back, $font_path, $item[3]);//书写文字方式
        }
        imagesavealpha($image, true);//设置保存PNG时保留透明通道信息
        if ($save_path === null) {
            header("content-type:image/png");
            imagepng($image);//生成图片到浏览器
            return true;
        } else {
            $bool = imagepng($image, $save_path);
            return $bool;
        }
    }
}
